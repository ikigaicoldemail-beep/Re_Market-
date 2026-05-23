@extends('layouts.app')

@section('title', 'Scheduled Listings')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="scheduledListings()" x-init="fetch">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900" data-i18n="seller.scheduled_title">Scheduled Listings</h1>
            <p class="text-sm text-gray-500 mt-1">
                Products queued to become visible on the website at a future time.
                For Facebook post scheduling instead, see
                <a href="{{ route('social.scheduled-posts') }}" class="text-indigo-600 underline">Scheduled Social Posts</a>.
            </p>
        </div>
        <a href="{{ route('me.products.create') }}?schedule=1" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + Schedule Post
        </a>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="!loading && scheduled.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center" style="display:none">
        <p class="text-gray-500 mb-4">No scheduled listings yet.</p>
        <a href="{{ route('me.products.create') }}?schedule=1" class="inline-block bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700">
            Schedule a product
        </a>
    </div>

    <div x-show="!loading && scheduled.length > 0" class="space-y-3" style="display:none">
        <template x-for="p in scheduled" :key="p.id">
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-16 h-16 bg-gray-100 rounded-lg shrink-0 overflow-hidden">
                        <img :src="primaryImage(p)" onerror="this.src='https://placehold.co/100x100/e5e7eb/9ca3af?text=N/A'"
                            class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0">
                        <a :href="'/me/products/' + p.id + '/edit'"
                            class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-1" x-text="p.title"></a>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span x-text="formatPrice(p.price_amount, p.currency)"></span>
                            <span class="mx-1">·</span>
                            <span class="text-purple-700" x-text="'Goes live ' + formatDate(p.schedule_at)"></span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <span class="text-xs px-2 py-1 rounded-full font-medium bg-purple-100 text-purple-800">scheduled</span>
                    <button @click="openReschedule(p)" class="text-sm text-indigo-600 hover:text-indigo-700">Reschedule</button>
                    <button @click="publishNow(p)" class="text-sm text-green-700 hover:text-green-800">Publish now</button>
                    <button @click="cancelSchedule(p)" class="text-sm text-red-600 hover:text-red-700">Cancel</button>
                </div>
            </div>
        </template>
    </div>

    {{-- Reschedule modal --}}
    <div x-show="showModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showModal = false" style="display:none">
        <div class="bg-white rounded-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-semibold mb-1">Reschedule listing</h2>
            <p class="text-xs text-gray-500 mb-4">The product stays hidden from the public site until the chosen time.</p>
            <form @submit.prevent="submit" class="space-y-3">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                        <input type="date" x-model="form.scheduled_date" required :min="today"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Time *</label>
                        <input type="time" x-model="form.scheduled_time" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" :disabled="saving"
                        class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                        <span x-show="!saving">Reschedule</span>
                        <span x-show="saving" style="display:none">Saving...</span>
                    </button>
                    <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function scheduledListings() {
        const todayStr = new Date().toISOString().slice(0, 10);
        return {
            today: todayStr,
            allProducts: [],
            loading: true,
            showModal: false,
            saving: false,
            editingId: null,
            form: {
                scheduled_date: todayStr,
                scheduled_time: '12:00',
            },
            get scheduled() {
                return this.allProducts.filter(p => p.status === 'scheduled');
            },
            primaryImage(p) {
                if (p.images && p.images.length > 0) {
                    const primary = p.images.find(i => i.is_primary) || p.images[0];
                    return primary.url;
                }
                return 'https://placehold.co/100x100/e5e7eb/9ca3af?text=N/A';
            },
            formatPrice(amount, currency) {
                if (amount == null) return '';
                try {
                    return new Intl.NumberFormat(undefined, { style: 'currency', currency: currency || 'USD' })
                        .format(amount / 100);
                } catch {
                    return (amount / 100).toFixed(2) + ' ' + (currency || 'USD');
                }
            },
            formatDate(value) {
                if (!value) return '';
                const d = new Date(value);
                if (isNaN(d.getTime())) return '';
                return d.toLocaleString(undefined, {
                    month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit',
                });
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/me/products', { params: { per_page: 50 } });
                    this.allProducts = data.products || [];
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            openReschedule(p) {
                this.editingId = p.id;
                const d = p.schedule_at ? new Date(p.schedule_at) : new Date();
                this.form.scheduled_date = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
                this.form.scheduled_time = `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
                this.showModal = true;
            },
            async submit() {
                this.saving = true;
                try {
                    if (!this.editingId) throw new Error('No listing selected.');
                    const localIso = new Date(`${this.form.scheduled_date}T${this.form.scheduled_time}`);
                    if (isNaN(localIso.getTime()) || localIso <= new Date()) {
                        throw new Error('Pick a future date and time.');
                    }
                    await window.api.put('/products/' + this.editingId, { schedule_at: localIso.toISOString() });
                    this.showModal = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Listing rescheduled.' } }));
                    await this.fetch();
                } catch (e) {
                    const msg = e?.message && !e?.response ? e.message : window.extractApiError(e);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: msg } }));
                } finally {
                    this.saving = false;
                }
            },
            async publishNow(p) {
                if (!confirm(`Publish "${p.title}" right now?`)) return;
                try {
                    await window.api.put('/products/' + p.id, { schedule_at: null, status: 'published' });
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Listing published.' } }));
                    await this.fetch();
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            async cancelSchedule(p) {
                if (!confirm(`Cancel the schedule for "${p.title}"? It will move back to draft.`)) return;
                try {
                    await window.api.put('/products/' + p.id, { schedule_at: null, status: 'draft' });
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Schedule cancelled.' } }));
                    await this.fetch();
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
