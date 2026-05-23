@extends('layouts.app')

@section('title', 'Visual Search')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="visualSearch()">
    <div class="mb-8">
        <h1 class="text-2xl font-semibold text-gray-900">Visual Search</h1>
        <p class="text-sm text-gray-500 mt-1">Upload a photo and find similar products in the marketplace.</p>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Upload panel --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 p-5 sticky top-20">
                <form @submit.prevent="search()">
                    <label for="image" class="block text-sm font-medium text-gray-700 mb-2">Reference image</label>

                    <label
                        class="block aspect-square border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-indigo-500 transition relative overflow-hidden"
                        :class="preview ? 'border-solid' : ''">
                        <input type="file" id="image" accept="image/jpeg,image/png,image/webp" class="hidden" @change="onFile($event)">
                        <template x-if="!preview">
                            <div class="flex flex-col items-center justify-center h-full text-gray-400 p-6">
                                <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-700">Click to upload</p>
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG, WEBP up to 5MB</p>
                            </div>
                        </template>
                        <template x-if="preview">
                            <img :src="preview" class="w-full h-full object-cover">
                        </template>
                    </label>

                    <div class="mt-4">
                        <label for="topK" class="block text-sm font-medium text-gray-700 mb-1">Results to return</label>
                        <select id="topK" x-model.number="topK" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="5">5 products</option>
                            <option value="10">10 products</option>
                            <option value="15">15 products</option>
                            <option value="20">20 products</option>
                        </select>
                    </div>

                    <button type="submit" :disabled="!file || searching"
                        class="w-full mt-4 bg-indigo-600 text-white py-2.5 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span x-show="!searching">Find similar</span>
                        <span x-show="searching" style="display:none">Searching...</span>
                    </button>

                    <button type="button" @click="reset()" x-show="preview"
                        class="w-full mt-2 text-sm text-gray-500 hover:text-gray-700"
                        style="display:none">Clear</button>
                </form>
            </div>
        </div>

        {{-- Results --}}
        <div class="lg:col-span-2">
            <div x-show="!searched && !searching" class="bg-white rounded-xl border border-gray-200 p-12 text-center" style="display:none">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="text-gray-500">Upload an image to begin.</p>
            </div>

            <div x-show="searching" class="text-center py-20 text-gray-500" style="display:none">
                Searching for similar products...
            </div>

            <div x-show="searched && !searching && products.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center" style="display:none">
                <p class="text-gray-500">No similar products found.</p>
                <p class="text-xs text-gray-400 mt-2" x-text="log?.error_message || ''"></p>
            </div>

            <div x-show="!searching && products.length > 0" style="display:none">
                <div class="flex items-center justify-between mb-4">
                    <p class="text-sm text-gray-600">
                        <span x-text="products.length"></span> similar product<span x-show="products.length !== 1">s</span> found
                    </p>
                    <p class="text-xs text-gray-400" x-text="log?.provider ? 'via ' + log.provider : ''"></p>
                </div>
                <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-4">
                    <template x-for="product in products" :key="product.id">
                        <a :href="'/products/' + product.id"
                            class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition group">
                            <div class="aspect-square bg-gray-100 overflow-hidden">
                                <img :src="primaryImage(product)"
                                    onerror="this.src='https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image'"
                                    class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                            </div>
                            <div class="p-3">
                                <p class="text-sm font-medium text-gray-900 line-clamp-2" x-text="product.title"></p>
                                <p class="text-base font-bold text-indigo-600 mt-1" x-text="formatPrice(product.price_amount, product.currency)"></p>
                            </div>
                        </a>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function visualSearch() {
        return {
            file: null,
            preview: '',
            topK: 10,
            searching: false,
            searched: false,
            products: [],
            log: null,
            onFile(e) {
                const f = e.target.files?.[0];
                if (!f) return;
                if (f.size > 5 * 1024 * 1024) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Image must be under 5MB.' } }));
                    return;
                }
                this.file = f;
                this.preview = URL.createObjectURL(f);
                this.searched = false;
                this.products = [];
            },
            reset() {
                this.file = null;
                this.preview = '';
                this.searched = false;
                this.products = [];
                this.log = null;
            },
            primaryImage(product) {
                if (product.images && product.images.length > 0) {
                    const primary = product.images.find(i => i.is_primary) || product.images[0];
                    return primary.url;
                }
                return 'https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image';
            },
            async search() {
                if (!this.file) return;
                this.searching = true;
                this.searched = false;
                this.products = [];
                try {
                    const form = new FormData();
                    form.append('image', this.file);
                    form.append('top_k', this.topK);
                    const { data } = await window.api.post('/ai/similarity-search', form, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    });
                    this.products = data.products || [];
                    this.log = data.log || null;
                    this.searched = true;
                    if (this.products.length === 0) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'No similar items found.' } }));
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.searching = false;
                }
            },
        };
    }
</script>
@endpush
@endsection
