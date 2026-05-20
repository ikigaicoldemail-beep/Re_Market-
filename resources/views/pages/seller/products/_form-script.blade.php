<script>
    function productForm(opts) {
        return {
            mode: opts.mode, // 'create' | 'edit'
            initLoading: true,
            saving: false,
            needsStore: false,
            uploadingImages: false,
            store: null,
            categories: [],
            conditions: [],
            brands: [],
            existingImages: [],
            pendingImages: [], // for create mode: queued file uploads
            productId: null,
            autoPostEnabled: false,
            scheduleEnabled: false,
            specRows: [], // [{key, value}, ...] -- serialised to form.specs on submit
            variants: [], // [{id?, label, sku, price_amount, stock_quantity, attributes}, ...]
            addVariant() { this.variants.push({ id: null, label: '', sku: '', price_amount: this.form.price_amount || 0, stock_quantity: 1, attributes: {} }); },
            removeVariant(idx) { this.variants.splice(idx, 1); },
            form: {
                store_id: null,
                title: '',
                description: '',
                price_amount: 0,
                original_price_amount: '',
                currency: 'USD',
                stock_quantity: 1,
                category_id: '',
                brand_id: '',
                product_condition_id: '',
                location_country_code: '',
                location_state: '',
                location_city: '',
                status: 'published',
                visibility: 'public',
                allow_offers: true,
                auto_post: 'facebook',
                schedule_at: '',
            },
            addSpec() { this.specRows.push({ key: '', value: '' }); },
            removeSpec(idx) { this.specRows.splice(idx, 1); },
            async init() {
                if (this.mode === 'edit') {
                    const segments = window.location.pathname.split('/').filter(Boolean);
                    this.productId = parseInt(segments[segments.length - 2]);
                }
                await Promise.all([this.loadStore(), this.loadCategories(), this.loadConditions(), this.loadBrands()]);
                if (this.mode === 'edit' && this.productId) {
                    await this.loadProduct();
                }
                // Auto-enable scheduling when arriving from the Scheduled Listings page
                if (this.mode === 'create') {
                    const params = new URLSearchParams(window.location.search);
                    if (params.get('schedule') === '1') {
                        this.scheduleEnabled = true;
                        const t = new Date(Date.now() + 30 * 60 * 1000);
                        this.form.schedule_at = this.toLocalDatetimeInput(t);
                    }
                }
                this.initLoading = false;
            },
            async loadStore() {
                try {
                    const { data } = await window.api.get('/me/store');
                    this.store = data.store;
                    this.form.store_id = data.store.id;
                } catch (e) {
                    if (e?.response?.status === 404) this.needsStore = true;
                }
            },
            async loadCategories() {
                try {
                    const { data } = await window.api.get('/categories');
                    this.categories = data.categories || [];
                } catch {}
            },
            async loadConditions() {
                try {
                    const { data } = await window.api.get('/product-conditions');
                    this.conditions = data.product_conditions || [];
                } catch {}
            },
            async loadBrands() {
                try {
                    const { data } = await window.api.get('/brands');
                    this.brands = data.brands || [];
                } catch {}
            },
            async loadProduct() {
                try {
                    const { data } = await window.api.get('/products/' + this.productId);
                    const p = data.product;
                    this.form = {
                        store_id: p.store_id,
                        title: p.title || '',
                        description: p.description || '',
                        price_amount: p.price_amount || 0,
                        original_price_amount: p.original_price_amount || '',
                        currency: p.currency || 'USD',
                        stock_quantity: p.stock_quantity ?? 1,
                        category_id: p.category_id || '',
                        brand_id: p.brand_id || '',
                        product_condition_id: p.product_condition_id || '',
                        location_country_code: p.location_country_code || '',
                        location_state: p.location_state || '',
                        location_city: p.location_city || '',
                        status: p.status || 'published',
                        visibility: p.visibility || 'public',
                        allow_offers: !!p.allow_offers,
                        auto_post: p.auto_post || 'facebook',
                        schedule_at: this.toLocalDatetimeInput(p.schedule_at),
                    };
                    this.autoPostEnabled = !!p.auto_post;
                    this.scheduleEnabled = !!p.schedule_at;
                    this.existingImages = p.images || [];
                    this.specRows = p.specs && typeof p.specs === 'object'
                        ? Object.entries(p.specs).map(([key, value]) => ({ key, value: String(value ?? '') }))
                        : [];
                    this.variants = Array.isArray(p.variants) ? p.variants.map(v => ({
                        id: v.id, label: v.label, sku: v.sku || '',
                        price_amount: v.price_amount, original_price_amount: v.original_price_amount,
                        stock_quantity: v.stock_quantity ?? 0, attributes: v.attributes || {},
                        is_default: !!v.is_default,
                    })) : [];
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            addPending(event) {
                this.pendingImages = Array.from(event.target.files || []);
            },
            toLocalDatetimeInput(value) {
                if (!value) return '';
                const d = new Date(value);
                if (isNaN(d.getTime())) return '';
                const pad = (n) => String(n).padStart(2, '0');
                return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
            },
            minScheduleAt() {
                return this.toLocalDatetimeInput(new Date());
            },
            formatLocal(value) {
                if (!value) return '';
                const d = new Date(value);
                if (isNaN(d.getTime())) return '';
                return d.toLocaleString(undefined, {
                    month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit',
                });
            },
            buildPayload() {
                const payload = { ...this.form };
                if (payload.currency) payload.currency = payload.currency.toUpperCase();
                if (payload.location_country_code) payload.location_country_code = payload.location_country_code.toUpperCase();
                if (!this.autoPostEnabled) payload.auto_post = null;
                if (!this.scheduleEnabled) payload.schedule_at = null;
                else if (payload.schedule_at) payload.schedule_at = new Date(payload.schedule_at).toISOString();
                // Serialize specs: {Storage: "128 GB", RAM: "8 GB"}
                const specs = {};
                this.specRows.forEach(r => {
                    const k = (r.key || '').trim();
                    const v = (r.value || '').trim();
                    if (k && v) specs[k] = v;
                });
                payload.specs = Object.keys(specs).length > 0 ? specs : null;
                payload.variants = this.variants
                    .filter(v => (v.label || '').trim().length > 0)
                    .map((v, i) => ({
                        id: v.id || null,
                        label: v.label.trim(),
                        sku: v.sku || null,
                        price_amount: Number(v.price_amount) || 0,
                        original_price_amount: v.original_price_amount ? Number(v.original_price_amount) : null,
                        stock_quantity: Number(v.stock_quantity) || 0,
                        attributes: v.attributes && Object.keys(v.attributes).length > 0 ? v.attributes : null,
                        is_default: !!v.is_default,
                        sort_order: i,
                    }));
                Object.keys(payload).forEach(k => {
                    if (payload[k] === '' || payload[k] === undefined) payload[k] = null;
                });
                return payload;
            },
            async submit() {
                this.saving = true;
                try {
                    const payload = this.buildPayload();
                    let productId, message;
                    if (this.mode === 'create') {
                        const { data } = await window.api.post('/products', payload);
                        productId = data.product.id;
                        message = data.message || 'Product created!';
                        if (this.pendingImages.length > 0) {
                            await this.uploadImagesFor(productId, this.pendingImages);
                        }
                    } else {
                        const { data } = await window.api.put('/products/' + this.productId, payload);
                        productId = data.product.id;
                        message = data.message || 'Product updated!';
                    }
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message } }));
                    setTimeout(() => window.location.href = '/me/products', 600);
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.saving = false;
                }
            },
            async uploadImagesFor(productId, files) {
                const fd = new FormData();
                files.forEach(f => fd.append('images[]', f));
                await window.api.post('/products/' + productId + '/images', fd, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
            },
            async uploadImages(event) {
                const files = Array.from(event.target.files || []);
                if (!files.length || !this.productId) return;
                this.uploadingImages = true;
                try {
                    await this.uploadImagesFor(this.productId, files);
                    await this.loadProduct();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Images uploaded.' } }));
                    event.target.value = '';
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.uploadingImages = false;
                }
            },
        };
    }
</script>
