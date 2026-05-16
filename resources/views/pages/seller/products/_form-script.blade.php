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
            existingImages: [],
            pendingImages: [], // for create mode: queued file uploads
            productId: null,
            autoPostEnabled: false,
            scheduleEnabled: false,
            form: {
                store_id: null,
                title: '',
                description: '',
                price_amount: 0,
                currency: 'USD',
                stock_quantity: 1,
                category_id: '',
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
            async init() {
                if (this.mode === 'edit') {
                    const segments = window.location.pathname.split('/').filter(Boolean);
                    this.productId = parseInt(segments[segments.length - 2]);
                }
                await Promise.all([this.loadStore(), this.loadCategories(), this.loadConditions()]);
                if (this.mode === 'edit' && this.productId) {
                    await this.loadProduct();
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
            async loadProduct() {
                try {
                    const { data } = await window.api.get('/products/' + this.productId);
                    const p = data.product;
                    this.form = {
                        store_id: p.store_id,
                        title: p.title || '',
                        description: p.description || '',
                        price_amount: p.price_amount || 0,
                        currency: p.currency || 'USD',
                        stock_quantity: p.stock_quantity ?? 1,
                        category_id: p.category_id || '',
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
            buildPayload() {
                const payload = { ...this.form };
                if (payload.currency) payload.currency = payload.currency.toUpperCase();
                if (payload.location_country_code) payload.location_country_code = payload.location_country_code.toUpperCase();
                if (!this.autoPostEnabled) payload.auto_post = null;
                if (!this.scheduleEnabled) payload.schedule_at = null;
                else if (payload.schedule_at) payload.schedule_at = new Date(payload.schedule_at).toISOString();
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
