@extends('layouts.admin')

@section('title', 'Admin · Reports')
@section('page-title', 'Product reports')

@section('content')
<div x-data="adminReports()" x-init="init">
    <p class="text-sm text-gray-500 mb-6">Buyer-flagged listings awaiting moderator review.</p>

    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 grid sm:grid-cols-3 gap-3">
        <select x-model="filters.status" @change="fetch()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="open">Open</option>
            <option value="resolved">Resolved</option>
            <option value="dismissed">Dismissed</option>
            <option value="">All</option>
        </select>
        <select x-model="filters.reason" @change="fetch()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
            <option value="">Any reason</option>
            <template x-for="r in reasons" :key="r.value">
                <option :value="r.value" x-text="r.label"></option>
            </template>
        </select>
        <button @click="clearFilters()" class="text-sm text-indigo-600 hover:text-indigo-700 text-left">Clear filters</button>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading reports...</div>
    <div x-show="!loading && reports.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200 text-gray-500" style="display:none">
        No reports match these filters.
    </div>

    <div x-show="!loading && reports.length > 0" class="space-y-3" style="display:none">
        <template x-for="r in reports" :key="r.id">
            <div class="bg-white rounded-xl border p-4"
                 :class="r.status === 'open' ? 'border-red-200' : 'border-gray-200'">
                <div class="flex gap-4">
                    <a :href="'/products/' + r.product_id" class="shrink-0">
                        <img :src="productImage(r)" :alt="r.product?.title"
                             onerror="this.src='https://placehold.co/80x80/e5e7eb/9ca3af?text=No+Image'"
                             class="w-20 h-20 rounded-lg object-cover bg-gray-100">
                    </a>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <a :href="'/products/' + r.product_id" class="font-medium text-gray-900 hover:text-indigo-600 line-clamp-1" x-text="r.product?.title || '(deleted product)'"></a>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Reported <span x-text="formatDate(r.created_at)"></span>
                                    by <span class="font-medium" x-text="r.reporter?.name || '(deleted user)'"></span>
                                </p>
                            </div>
                            <span class="text-xs px-2 py-1 rounded-full font-medium"
                                  :class="r.status === 'open' ? 'bg-red-100 text-red-700' : r.status === 'resolved' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                  x-text="r.status"></span>
                        </div>

                        <div class="mt-2">
                            <span class="text-xs uppercase tracking-wide font-medium text-gray-500">Reason:</span>
                            <span class="text-sm text-gray-800" x-text="r.reason_label"></span>
                        </div>

                        <p x-show="r.details" class="text-sm text-gray-700 mt-1 whitespace-pre-line" style="display:none" x-text="r.details"></p>

                        <div x-show="r.status !== 'open'" class="mt-2 text-xs text-gray-500" style="display:none">
                            <span x-text="r.status"></span>
                            <span x-show="r.resolver">by <span class="font-medium" x-text="r.resolver?.name"></span></span>
                            <span x-show="r.resolved_at"> on <span x-text="formatDate(r.resolved_at)"></span></span>
                            <p x-show="r.resolution_note" class="mt-1 text-gray-600 italic" x-text="'Note: ' + r.resolution_note" style="display:none"></p>
                        </div>

                        <div x-show="r.status === 'open'" class="mt-3 flex flex-wrap items-center gap-2" style="display:none">
                            <input type="text" :placeholder="'Note (optional)'" x-model="r._note"
                                   class="flex-1 min-w-[200px] px-2 py-1.5 text-sm border border-gray-300 rounded-lg">
                            <button @click="resolve(r, 'resolved')" class="text-sm bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700">Resolve</button>
                            <button @click="resolve(r, 'dismissed')" class="text-sm bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-300">Dismiss</button>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="meta.last_page > 1" class="flex items-center justify-center gap-2 mt-6" style="display:none">
            <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg disabled:opacity-50">Previous</button>
            <span class="text-sm text-gray-600 px-3">Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span></span>
            <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                class="px-3 py-2 text-sm border border-gray-300 rounded-lg disabled:opacity-50">Next</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function adminReports() {
        return {
            reports: [],
            reasons: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            filters: { status: 'open', reason: '' },
            loading: true,
            async init() {
                await Promise.all([this.fetch(), this.fetchReasons()]);
            },
            async fetchReasons() {
                try {
                    const { data } = await window.api.get('/product-reports/reasons');
                    this.reasons = data.reasons || [];
                } catch {}
            },
            async fetch(page = 1) {
                this.loading = true;
                try {
                    const params = { page };
                    if (this.filters.status) params.status = this.filters.status;
                    if (this.filters.reason) params.reason = this.filters.reason;
                    const { data } = await window.api.get('/admin/reports', { params });
                    this.reports = (data.reports || []).map(r => ({ ...r, _note: '' }));
                    this.meta = data.meta || this.meta;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            async resolve(r, status) {
                try {
                    await window.api.patch('/admin/reports/' + r.id, { status, resolution_note: r._note || null });
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Report ' + status + '.' } }));
                    this.fetch(this.meta.current_page);
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            clearFilters() {
                this.filters = { status: 'open', reason: '' };
                this.fetch();
            },
            goToPage(page) {
                this.fetch(page);
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },
            productImage(r) {
                const imgs = r.product?.images || [];
                if (imgs.length === 0) return 'https://placehold.co/80x80/e5e7eb/9ca3af?text=No+Image';
                return (imgs.find(i => i.is_primary) || imgs[0]).url;
            },
            formatDate(s) {
                if (!s) return '';
                try { return new Date(s).toLocaleString(); } catch { return s; }
            },
        };
    }
</script>
@endpush
@endsection
