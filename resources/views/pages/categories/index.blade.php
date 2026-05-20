@extends('layouts.app')

@section('title', 'Browse Categories')

@section('content')
@include('components.toast')

<div x-data="categoriesIndex()" x-init="init">
    <div class="bg-gradient-to-br from-indigo-600 to-purple-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <h1 class="text-3xl font-semibold" data-i18n="categories.title">Shop by Category</h1>
            <p class="text-indigo-100 mt-2" data-i18n="categories.subtitle">Pick a category to dive into subcategories and products.</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div x-show="loading" class="text-center py-20 text-gray-500" data-i18n="categories.loading">Loading categories...</div>

        <div x-show="!loading && parents.length === 0" class="text-center py-20 bg-white rounded-xl border border-gray-200" style="display:none">
            <p class="text-gray-500" data-i18n="categories.empty">No categories available yet.</p>
        </div>

        <div x-show="!loading && parents.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4" style="display:none">
            <template x-for="cat in parents" :key="cat.id">
                <a :href="'/categories/' + cat.slug"
                    class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition group">
                    <div class="aspect-square bg-gradient-to-br from-indigo-100 to-pink-100 flex items-center justify-center overflow-hidden">
                        <template x-if="cat.logo_url">
                            <img :src="cat.logo_url" :alt="cat.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        </template>
                        <template x-if="!cat.logo_url">
                            <span class="text-4xl font-bold text-indigo-400" x-text="cat.name.charAt(0).toUpperCase()"></span>
                        </template>
                    </div>
                    <div class="p-3 text-center">
                        <h3 class="font-medium text-gray-900 truncate" x-text="cat.name"></h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span x-text="childCount(cat.id)"></span> subcategor<span x-text="childCount(cat.id) === 1 ? 'y' : 'ies'"></span>
                        </p>
                    </div>
                </a>
            </template>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function categoriesIndex() {
        return {
            categories: [],
            loading: true,
            get parents() {
                return this.categories.filter(c => !c.parent_id && c.status === 'active');
            },
            childCount(parentId) {
                return this.categories.filter(c => c.parent_id === parentId).length;
            },
            async init() {
                try {
                    const { data } = await window.api.get('/categories');
                    this.categories = data.categories || [];
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
        };
    }
</script>
@endpush
@endsection
