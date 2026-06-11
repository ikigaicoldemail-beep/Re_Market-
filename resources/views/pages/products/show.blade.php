@extends('layouts.app')

@section('title', isset($ogProduct) ? $ogProduct->title . ' — ReMarket' : 'Product Details')

@section('meta')
@if(isset($ogProduct))
<meta property="og:type" content="product">
<meta property="og:title" content="{{ $ogProduct->title }}">
<meta property="og:description" content="{{ Str::limit($ogProduct->description ?? '', 200) }}">
<meta property="og:url" content="{{ url('/products/' . $ogProduct->id) }}">
@if($ogProduct->images->isNotEmpty())
<meta property="og:image" content="{{ $ogProduct->images->firstWhere('is_primary', true)?->url ?? $ogProduct->images->first()->url }}">
@endif
<meta property="product:price:amount" content="{{ $ogProduct->price_amount }}">
<meta property="product:price:currency" content="{{ $ogProduct->currency ?? 'USD' }}">
@endif
@endsection

@section('content')
@include('components.toast')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="productDetail()" x-init="init">
    {{-- Breadcrumb --}}
    <nav x-show="product" class="text-sm text-gray-500 mb-4 flex items-center gap-1 flex-wrap" aria-label="Breadcrumb" style="display:none">
        <a href="/" class="hover:text-indigo-600">Home</a>
        <span>/</span>
        <template x-if="product?.category?.name">
            <span class="inline-flex items-center gap-1">
                <a :href="'/products?category=' + product.category.id" class="hover:text-indigo-600" x-text="product.category.name"></a>
                <span>/</span>
            </span>
        </template>
        <span class="text-gray-900 font-medium truncate max-w-xs" x-text="product?.title"></span>
    </nav>

    <div x-show="loading" class="text-center py-20 text-gray-500" data-i18n="product.loading">Loading product...</div>

    <div x-show="error" class="text-center py-20" style="display:none">
        <p class="text-red-600 mb-4" x-text="error"></p>
        <a href="/" class="text-indigo-600 hover:text-indigo-700" data-i18n="product.back_to_browse">Back to browse</a>
    </div>

    <div x-show="product && !loading" class="grid lg:grid-cols-2 gap-8" style="display:none">
        {{-- Image Gallery --}}
        <div>
            <div class="aspect-square bg-white rounded-xl overflow-hidden border border-gray-200">
                <img :src="activeImage" :alt="product?.title"
                    onerror="this.src='https://placehold.co/600x600/e5e7eb/9ca3af?text=No+Image'"
                    class="w-full h-full object-cover">
            </div>
            <div class="grid grid-cols-5 gap-2 mt-3" x-show="product?.images?.length > 1" style="display:none">
                <template x-for="img in (product?.images || [])" :key="img.id">
                    <button @click="setActive(img)"
                        :class="activeImage === heroSrc(img) ? 'ring-2 ring-indigo-600' : 'ring-1 ring-gray-200'"
                        class="aspect-square bg-white rounded-lg overflow-hidden">
                        <img :src="img.urls?.thumb_webp || img.urls?.thumb || img.url" loading="lazy" class="w-full h-full object-cover">
                    </button>
                </template>
            </div>
        </div>

        {{-- Details --}}
        <div>
            <h1 class="text-3xl font-semibold text-gray-900" x-text="product?.title"></h1>

            <div class="flex items-center gap-3 mt-2 flex-wrap">
                <span class="text-sm text-gray-500" x-text="product?.location_city || product?.location_country_code || ''"></span>
                <span x-show="product?.condition"
                    class="text-xs px-2 py-0.5 rounded-full font-medium"
                    :class="conditionChipClasses(product?.condition?.color)"
                    style="display:none">
                    <span x-text="product?.condition?.name"></span>
                    <span class="opacity-60 ml-1" x-text="'· ' + (product?.condition?.quality_score ?? '') + '/100'"></span>
                </span>
                <span class="text-xs text-gray-400" x-show="product?.published_at || product?.created_at"
                    x-text="formatRelativeTime(product?.published_at || product?.created_at)" style="display:none"></span>
            </div>

            <div class="mt-4 flex items-baseline gap-3 flex-wrap">
                <p class="text-3xl font-bold text-indigo-600" x-text="formatPrice(activePrice, product?.currency)"></p>
                <p x-show="activeOriginalPrice && activeOriginalPrice > activePrice"
                    class="text-lg text-gray-400 line-through" style="display:none"
                    x-text="formatPrice(activeOriginalPrice, product?.currency)"></p>
                <span x-show="activeOriginalPrice && activeOriginalPrice > activePrice"
                    class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-700" style="display:none"
                    x-text="'-' + Math.round((1 - activePrice/activeOriginalPrice) * 100) + '%'"></span>
            </div>

            {{-- Variant picker --}}
            <div x-show="hasVariants" class="mt-4" style="display:none">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2" data-i18n="product.choose_variant">Choose variant</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="v in product?.variants || []" :key="v.id">
                        <button @click="selectedVariantId = v.id"
                            :disabled="v.stock_quantity <= 0"
                            :class="selectedVariantId === v.id ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
                            class="border rounded-lg px-3 py-1.5 text-sm transition disabled:opacity-40 disabled:cursor-not-allowed">
                            <span x-text="v.label"></span>
                            <span x-show="v.stock_quantity <= 0" class="ml-1 text-xs text-red-600" style="display:none">· out</span>
                        </button>
                    </template>
                </div>
            </div>

            <p class="text-sm mt-2" x-show="activeStock > 0" style="display:none"
               :class="activeStock <= 3 ? 'text-amber-600' : 'text-gray-600'">
                <span x-text="activeStock <= 3 ? 'Only a few left' : 'In stock'"></span>
            </p>
            <p class="text-sm text-red-600 mt-2" x-show="activeStock <= 0" data-i18n="product.out_of_stock" style="display:none">Out of stock</p>

            {{-- Brand pill --}}
            <a x-show="product?.brand" :href="'/?brand=' + product?.brand?.id"
                class="inline-flex items-center gap-2 mt-3 px-3 py-1.5 bg-white border border-gray-200 rounded-full text-sm hover:bg-gray-50" style="display:none">
                <template x-if="product?.brand?.logo_url">
                    <img :src="product?.brand?.logo_url" class="w-5 h-5 rounded object-contain">
                </template>
                <span class="font-medium text-gray-700" x-text="product?.brand?.name"></span>
            </a>

            {{-- Seller / Store --}}
            <div class="mt-6 p-4 bg-white rounded-xl border border-gray-200" x-show="product?.store" style="display:none">
                <p class="text-xs text-gray-500 mb-1" data-i18n="product.sold_by">Sold by</p>
                <div class="flex items-center gap-3">
                    <a :href="'/stores/' + product?.store?.id" class="font-medium text-gray-900 hover:text-indigo-600" x-text="product?.store?.name"></a>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-6 space-y-3">
                {{-- Primary CTA: Chat with Seller --}}
                <button @click="messageSeller()" :disabled="messagingSeller"
                    x-show="product?.user_id && (!window.auth.user() || product.user_id !== window.auth.user().id)"
                    class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold text-base hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed inline-flex items-center justify-center gap-2"
                    style="display:none">
                    <template x-if="!messagingSeller">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
</svg>
                            <span x-text="activeStock <= 0 ? 'Ask about availability' : 'Chat with Seller'"></span>
                        </span>
                    </template>
                    <span x-show="messagingSeller" style="display:none">Opening chat...</span>
                </button>

                {{-- Own listing CTAs --}}
                <div x-show="product?.user_id && window.auth.user()?.id === product.user_id"
                    class="space-y-2" style="display:none">
                    <a :href="'/me/products/' + productId + '/edit'"
                        class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold text-base hover:bg-indigo-700 inline-flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10"/>
</svg>
                        Edit listing
                    </a>
                    <template x-if="product?.status !== 'sold'">
                        <div>
                            <template x-if="!confirmSold">
                                <button @click="confirmSold = true"
                                    class="w-full border border-gray-300 text-gray-700 py-2.5 rounded-lg font-medium hover:bg-gray-50 inline-flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
</svg>
                                    Mark as sold
                                </button>
                            </template>
                            <template x-if="confirmSold">
                                <div class="flex items-center justify-center gap-3 py-2.5 text-sm">
                                    <span class="text-gray-600">Mark this listing as sold?</span>
                                    <button @click="markAsSold()" :disabled="markingAsSold"
                                        class="text-white bg-red-600 hover:bg-red-700 px-3 py-1.5 rounded-lg font-medium disabled:opacity-50">
                                        <span x-text="markingAsSold ? 'Marking...' : 'Yes, sold'"></span>
                                    </button>
                                    <button @click="confirmSold = false" class="text-gray-500 hover:text-gray-700 px-3 py-1.5 rounded-lg border border-gray-300">Cancel</button>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="product?.status === 'sold'">
                        <p class="text-center text-sm text-gray-500 py-1">This listing is marked as sold.</p>
                    </template>
                </div>

                {{-- Wishlist + Share --}}
                <div class="grid grid-cols-2 gap-3">
                    <button @click="toggleWishlist()" :disabled="togglingWishlist"
                        class="border border-gray-300 py-2.5 rounded-lg font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 inline-flex items-center justify-center gap-2">
                        <template x-if="!inWishlist">
                            <span class="inline-flex items-center gap-1.5">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
</svg>
                                <span data-i18n="product.save_wishlist">Save</span>
                            </span>
                        </template>
                        <template x-if="inWishlist">
                            <span class="inline-flex items-center gap-1.5 text-red-600">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path d="m11.645 20.91-.007-.003-.022-.012a15.247 15.247 0 0 1-.383-.218 25.18 25.18 0 0 1-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0 1 12 5.052 5.5 5.5 0 0 1 16.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 0 1-4.244 3.17 15.247 15.247 0 0 1-.383.219l-.022.012-.007.004-.003.001a.752.752 0 0 1-.704 0l-.003-.001Z"/>
</svg>
                                <span data-i18n="product.in_wishlist">Saved</span>
                            </span>
                        </template>
                    </button>
                    <button @click="showShare = true" class="border border-gray-300 py-2.5 rounded-lg font-medium text-gray-700 hover:bg-gray-50 inline-flex items-center justify-center gap-1.5">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z"/>
</svg>
                        <span data-i18n="product.share">Share</span>
                    </button>
                </div>

                {{-- Find visually similar (promoted to button) --}}
                <button @click="findSimilar({ force: true, scroll: true, redirectGuest: true })"
                    class="w-full border border-indigo-300 text-indigo-600 py-2.5 rounded-lg font-medium hover:bg-indigo-50 transition inline-flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/>
</svg>
                    <span data-i18n="product.find_similar">Find visually similar</span>
                </button>

                {{-- Compare (demoted to text link) --}}
                <div class="text-center">
                    <button @click="$store.compare.toggle(product.id)"
                        class="text-sm text-gray-500 hover:text-gray-700 underline"
                        x-text="$store.compare.has(product?.id) ? 'Remove from compare' : 'Add to compare'">
                    </button>
                </div>

                <button @click="openReport()"
                    x-show="product?.user_id && (!window.auth.user() || product.user_id !== window.auth.user().id)"
                    class="w-full text-xs text-gray-500 hover:text-red-600 inline-flex items-center justify-center gap-1.5" style="display:none">
                    <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 21v-6m0 0 2.77-.693a9 9 0 0 1 6.208.682l.108.054a9 9 0 0 0 6.086.71l3.114-.732a48.524 48.524 0 0 1-.005-10.499l-3.11.732a9 9 0 0 1-6.085-.711l-.108-.054a9 9 0 0 0-6.208-.682L3 4.5M3 15V4.5"/>
</svg>
                    <span data-i18n="product.report">Report this listing</span>
                </button>
            </div>

            {{-- Description --}}
            <div class="mt-8">
                <h2 class="font-semibold text-gray-900 mb-2" data-i18n="product.description">Description</h2>
                <p class="text-gray-700 whitespace-pre-line" x-text="product?.description"></p>
            </div>

            {{-- Specifications --}}
            <div class="mt-8" x-show="hasSpecs" style="display:none">
                <h2 class="font-semibold text-gray-900 mb-3" data-i18n="product.specifications">Specifications</h2>
                <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="row in specEntries" :key="row[0]">
                                <tr>
                                    <td class="px-4 py-2.5 bg-gray-50 text-gray-600 font-medium w-1/3" x-text="row[0]"></td>
                                    <td class="px-4 py-2.5 text-gray-900" x-text="row[1]"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Reviews section --}}
    <div x-show="product && !loading" class="mt-12 max-w-4xl" style="display:none">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold text-gray-900" data-i18n="product.reviews">Reviews</h2>
            <button x-show="canWriteReview" @click="openReview()"
                class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700" style="display:none">
                <span x-text="myReview ? 'Edit my review' : 'Write a review'"></span>
            </button>
        </div>

        <div x-show="reviewsSummary.total === 0" class="bg-white border border-gray-200 rounded-xl p-6 text-center text-sm text-gray-500" style="display:none">
            No reviews yet. <span x-show="canWriteReview" style="display:none">Be the first to leave one!</span>
        </div>

        <div x-show="reviewsSummary.total > 0" class="bg-white border border-gray-200 rounded-xl p-5 mb-5 grid sm:grid-cols-[auto_1fr] gap-6" style="display:none">
            <div class="text-center">
                <p class="text-4xl font-bold text-gray-900" x-text="reviewsSummary.average?.toFixed(1)"></p>
                <div class="flex justify-center gap-0.5 text-yellow-400 my-1">
                    <template x-for="i in 5" :key="i">
                        <span x-text="i &lt;= Math.round(reviewsSummary.average) ? '★' : '☆'"></span>
                    </template>
                </div>
                <p class="text-xs text-gray-500"><span x-text="reviewsSummary.total"></span> reviews</p>
            </div>
            <div class="space-y-1">
                <template x-for="r in [5,4,3,2,1]" :key="r">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-4 text-gray-600" x-text="r"></span>
                        <span class="text-yellow-400">★</span>
                        <div class="flex-1 bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="bg-yellow-400 h-2"
                                 :style="'width: ' + (reviewsSummary.total > 0 ? (reviewsSummary.breakdown[r] / reviewsSummary.total * 100) : 0) + '%'"></div>
                        </div>
                        <span class="w-8 text-right text-gray-500" x-text="reviewsSummary.breakdown[r] ?? 0"></span>
                    </div>
                </template>
            </div>
        </div>

        <div x-show="!canWriteReview && reviewEligibility.checked && !myReview" class="text-xs text-gray-500 mb-4" style="display:none">
            <span x-text="reviewEligibility.reason"></span>
        </div>

        <div class="space-y-3">
            <template x-for="r in reviews" :key="r.id">
                <div class="bg-white border border-gray-200 rounded-xl p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-medium shrink-0"
                                 x-text="(r.reviewer?.name || '?').charAt(0).toUpperCase()"></div>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 text-sm truncate" x-text="r.reviewer?.name || 'Anonymous'"></p>
                                <p class="text-xs text-gray-400" x-text="formatRelativeTime(r.created_at)"></p>
                            </div>
                        </div>
                        <div class="text-yellow-400 text-sm shrink-0">
                            <template x-for="i in 5" :key="i">
                                <span x-text="i &lt;= r.rating ? '★' : '☆'"></span>
                            </template>
                        </div>
                    </div>
                    <p x-show="r.title" class="mt-2 font-medium text-gray-900" x-text="r.title" style="display:none"></p>
                    <p x-show="r.body" class="mt-1 text-sm text-gray-700 whitespace-pre-line" x-text="r.body" style="display:none"></p>

                    <div x-show="r.seller_reply" class="mt-3 ml-6 pl-3 border-l-2 border-gray-200" style="display:none">
                        <p class="text-xs text-gray-500 mb-1">
                            Seller replied <span x-text="formatRelativeTime(r.seller_replied_at)"></span>
                        </p>
                        <p class="text-sm text-gray-700 whitespace-pre-line" x-text="r.seller_reply"></p>
                    </div>

                    <div x-show="r.user_id === (window.auth.user()?.id ?? 0)" class="mt-2 flex gap-3 text-xs" style="display:none">
                        <template x-if="!r._confirmDelete">
                            <button @click="r._confirmDelete = true" class="text-red-600 hover:text-red-700">Delete</button>
                        </template>
                        <template x-if="r._confirmDelete">
                            <span class="inline-flex items-center gap-2">
                                <span class="text-gray-500">Delete review?</span>
                                <button @click="deleteReview(r)" class="text-red-600 font-medium hover:text-red-700">Yes, delete</button>
                                <button @click="r._confirmDelete = false" class="text-gray-400 hover:text-gray-600">Cancel</button>
                            </span>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    {{-- Write/edit review modal --}}
    <div x-show="showReview" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @click.self="showReview = false" style="display:none">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <h2 class="text-lg font-semibold mb-3">
                <span x-text="myReview ? 'Edit your review' : 'Write a review'"></span>
            </h2>
            <form @submit.prevent="submitReview()" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating *</label>
                    <div class="flex gap-1 text-3xl text-yellow-400">
                        <template x-for="i in 5" :key="i">
                            <button type="button" @click="reviewForm.rating = i"
                                :class="i &lt;= reviewForm.rating ? 'opacity-100' : 'opacity-30 hover:opacity-60'">
                                <span x-text="'★'"></span>
                            </button>
                        </template>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Title (optional)</label>
                    <input type="text" x-model="reviewForm.title" maxlength="120"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Your experience</label>
                    <textarea x-model="reviewForm.body" rows="4" maxlength="5000"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="What did you like? What didn't work?"></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" :disabled="reviewSubmitting || reviewForm.rating < 1"
                        class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                        <span x-show="!reviewSubmitting" x-text="myReview ? 'Save changes' : 'Submit review'"></span>
                        <span x-show="reviewSubmitting" style="display:none">Submitting...</span>
                    </button>
                    <button type="button" @click="showReview = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Similar products section --}}
    <div x-show="product && !loading" x-ref="similarSection" class="mt-12" style="display:none">
        <div class="flex items-center justify-between gap-4 mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-900" data-i18n="product.similar_title">Visually similar products</h2>
                <p class="text-sm text-gray-500 mt-1">More listings that match this item by image, category, condition, and price.</p>
            </div>
            <button @click="findSimilar({ force: true, redirectGuest: true })" :disabled="similarLoading"
                class="shrink-0 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 disabled:opacity-50">
                <span x-text="similarLoading ? 'Searching...' : 'Refresh'"></span>
            </button>
        </div>

        <div x-show="similarLoading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
            <template x-for="i in 5" :key="i">
                <div class="animate-pulse">
                    <div class="aspect-square bg-gray-100 rounded-lg"></div>
                    <div class="h-4 bg-gray-100 rounded mt-3"></div>
                    <div class="h-4 bg-gray-100 rounded mt-2 w-2/3"></div>
                </div>
            </template>
        </div>

        <div x-show="!similarLoading && similarError" class="bg-white border border-gray-200 rounded-lg p-6 text-center text-sm text-gray-500" style="display:none">
            <p x-text="similarError"></p>
            <a x-show="!window.auth.isLoggedIn()" :href="'/login?next=' + encodeURIComponent(window.location.pathname)"
                class="inline-flex mt-3 text-indigo-600 font-medium hover:text-indigo-700" style="display:none">
                Sign in
            </a>
        </div>

        <div x-show="!similarLoading && !similarError && similarChecked && similarProducts.length === 0"
            class="bg-white border border-gray-200 rounded-lg p-6 text-center text-sm text-gray-500" style="display:none">
            No similar products found yet.
        </div>

        <div x-show="!similarLoading && similarProducts.length > 0"
            class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4" style="display:none">
            <template x-for="p in similarProducts" :key="p.id">
                <a :href="'/products/' + p.id" class="block group bg-white border border-gray-200 rounded-lg overflow-hidden hover:border-indigo-200 hover:shadow-sm transition">
                    <div class="aspect-square bg-gray-100 overflow-hidden">
                        <img :src="similarPrimaryImage(p)"
                            onerror="this.src='https://placehold.co/300x300/e5e7eb/9ca3af?text=No+Image'"
                            class="w-full h-full object-cover group-hover:scale-105 transition">
                    </div>
                    <div class="p-3">
                        <p class="text-sm font-medium text-gray-900 line-clamp-2 min-h-[2.5rem]" x-text="p.title"></p>
                        <div class="flex items-center justify-between gap-2 mt-2">
                            <p class="text-sm font-bold text-indigo-600" x-text="formatPrice(p.price_amount, p.currency)"></p>
                            <span x-show="p.similarity_score" class="text-[11px] text-gray-500"
                                x-text="Math.round((p.similarity_score || 0) * 100) + '%'"></span>
                        </div>
                    </div>
                </a>
            </template>
        </div>
    </div>

    {{-- Report modal --}}
    <div x-show="showReport" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @click.self="showReport = false" style="display:none">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
            <h2 class="text-lg font-semibold mb-1">Report this listing</h2>
            <p class="text-sm text-gray-500 mb-4">Help us keep the marketplace safe. Our moderators will review your report.</p>
            <form @submit.prevent="submitReport()" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                    <select x-model="reportForm.reason" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="" disabled>— Select a reason —</option>
                        <template x-for="r in reportReasons" :key="r.value">
                            <option :value="r.value" x-text="r.label"></option>
                        </template>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Details (optional)</label>
                    <textarea x-model="reportForm.details" rows="3" maxlength="2000"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"
                        placeholder="What's wrong with this listing?"></textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="submit" :disabled="reportSubmitting"
                        class="flex-1 bg-red-600 text-white py-2 rounded-lg font-medium hover:bg-red-700 disabled:opacity-50">
                        <span x-show="!reportSubmitting">Submit report</span>
                        <span x-show="reportSubmitting" style="display:none">Submitting...</span>
                    </button>
                    <button type="button" @click="showReport = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Share modal --}}
    <div x-show="showShare && product" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showShare = false" style="display:none">
        <div class="bg-white rounded-xl max-w-sm w-full p-6">
            <h2 class="text-lg font-semibold mb-4">Share product</h2>

            <div class="space-y-2">
                <button @click="shareToPlatform('facebook')" :disabled="sharingPlatform"
                    class="w-full flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-50">
                    <div class="w-9 h-9 bg-[#1877F2] rounded-full flex items-center justify-center text-white font-bold">f</div>
                    <span class="text-sm font-medium">Share on Facebook</span>
                </button>
                <button @click="copyShareLink()"
                    class="w-full flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="w-9 h-9 bg-gray-100 rounded-full flex items-center justify-center text-gray-700">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 015.656 0 4 4 0 010 5.656l-3 3a4 4 0 01-5.656-5.656l1.1-1.1"/></svg>
                    </div>
                    <span class="text-sm font-medium">Copy link</span>
                </button>
            </div>

            <button @click="showShare = false" class="w-full mt-4 py-2 text-sm text-gray-500 hover:text-gray-700">Cancel</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function productDetail() {
        return {
            productId: {{ $id ?? 'null' }},
            product: null,
            activeImage: '',
            loading: true,
            error: '',
            togglingWishlist: false,
            inWishlist: false,
            showShare: false,
            sharingPlatform: false,
            shareUrl: '',
            messagingSeller: false,
            similarLoading: false,
            similarChecked: false,
            similarError: '',
            similarProducts: [],
            showReport: false,
            reportReasons: [],
            reportForm: { reason: '', details: '' },
            reportSubmitting: false,
            confirmSold: false,
            markingAsSold: false,
            reviews: [],
            reviewsSummary: { total: 0, average: 0, breakdown: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 } },
            myReview: null,
            reviewEligibility: { checked: false, eligible: false, reason: '' },
            showReview: false,
            reviewForm: { rating: 0, title: '', body: '' },
            reviewSubmitting: false,
            async init() {
                await this.fetch();
                await Promise.all([
                    this.checkWishlist(),
                    this.fetchReviews(),
                    this.checkReviewEligibility(),
                    this.findSimilar(),
                ]);
            },
            get canWriteReview() {
                return this.reviewEligibility.eligible && !this.myReview;
            },
            get hasSpecs() {
                return this.product?.specs && typeof this.product.specs === 'object' && Object.keys(this.product.specs).length > 0;
            },
            get hasProductImages() {
                return Array.isArray(this.product?.images) && this.product.images.length > 0;
            },
            get specEntries() {
                return this.product?.specs ? Object.entries(this.product.specs) : [];
            },
            selectedVariantId: null,
            get hasVariants() {
                return Array.isArray(this.product?.variants) && this.product.variants.length > 0;
            },
            get selectedVariant() {
                if (!this.hasVariants) return null;
                const list = this.product.variants;
                if (this.selectedVariantId) {
                    return list.find(v => v.id === this.selectedVariantId) || list[0];
                }
                return list.find(v => v.is_default) || list[0];
            },
            get activePrice() {
                return this.selectedVariant?.price_amount ?? this.product?.price_amount;
            },
            get activeOriginalPrice() {
                return this.selectedVariant?.original_price_amount ?? this.product?.original_price_amount;
            },
            get activeStock() {
                return this.selectedVariant?.stock_quantity ?? this.product?.stock_quantity;
            },
            async checkWishlist() {
                if (!window.auth.isLoggedIn() || !this.product) return;
                try {
                    const { data } = await window.api.get('/wishlist/check', { params: { product_id: this.product.id } });
                    this.inWishlist = data.wishlisted ?? false;
                } catch {}
            },
            async messageSeller() {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                if (!this.product?.user_id) return;
                this.messagingSeller = true;
                try {
                    const { data } = await window.api.post('/conversations', {
                        recipient_user_id: this.product.user_id,
                        product_id: this.product.id,
                    });
                    const convId = data?.conversation?.id;
                    if (convId) {
                        window.location.href = '/messages/' + convId;
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.messagingSeller = false;
                }
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/products/' + this.productId);
                    this.product = data.product;
                    const imgs = this.product?.images || [];
                    const primary = imgs.length > 0 ? (imgs.find(i => i.is_primary) || imgs[0]) : null;
                    this.activeImage = primary ? this.heroSrc(primary)
                        : 'https://placehold.co/600x600/e5e7eb/9ca3af?text=No+Image';
                } catch (e) {
                    this.error = window.extractApiError(e) || 'Product not found.';
                } finally {
                    this.loading = false;
                }
            },
            async toggleWishlist() {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                this.togglingWishlist = true;
                try {
                    if (this.inWishlist) {
                        await window.api.delete('/wishlist/' + this.product.id);
                        this.inWishlist = false;
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Removed from wishlist.' } }));
                    } else {
                        await window.api.post('/wishlist', { product_id: this.product.id });
                        this.inWishlist = true;
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Saved to wishlist!' } }));
                    }
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.togglingWishlist = false;
                }
            },
            async ensureShareUrl() {
                if (this.shareUrl) return this.shareUrl;
                try {
                    const { data } = await window.api.get('/products/' + this.product.id + '/share');
                    this.shareUrl = data.share.share_url;
                } catch {
                    this.shareUrl = window.location.origin + '/products/' + this.product.id;
                }
                return this.shareUrl;
            },
            shareToPlatform(platform) {
                // Construct URL immediately — no await before window.open or the
                // browser treats it as a popup and blocks it.
                const url = window.location.origin + '/products/' + this.product.id;
                if (platform === 'facebook') {
                    window.open(
                        'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url),
                        '_blank',
                        'width=600,height=600,noopener,noreferrer'
                    );
                }
                this.showShare = false;
            },
            heroSrc(img) {
                return img.urls?.large_webp || img.urls?.large || img.url;
            },
            setActive(img) {
                this.activeImage = this.heroSrc(img);
            },
            similarPrimaryImage(p) {
                if (p.images && p.images.length > 0) {
                    const primary = p.images.find(i => i.is_primary) || p.images[0];
                    return primary.urls?.card_webp || primary.urls?.card || primary.url;
                }
                return 'https://placehold.co/300x300/e5e7eb/9ca3af?text=No+Image';
            },
            async findSimilar(options = {}) {
                if (!this.product) return;
                if (!this.hasProductImages) {
                    this.similarChecked = true;
                    this.similarProducts = [];
                    this.similarError = 'Add at least one product image to find similar items.';
                    return;
                }

                if (!window.auth.isLoggedIn()) {
                    if (options.redirectGuest) {
                        window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                        return;
                    }

                    this.similarChecked = true;
                    this.similarError = 'Sign in to see visually similar products.';
                    return;
                }

                if (options.scroll) {
                    this.$nextTick(() => this.$refs.similarSection?.scrollIntoView({ behavior: 'smooth', block: 'start' }));
                }

                if (this.similarProducts.length > 0 && !options.force) return;
                this.similarLoading = true;
                this.similarChecked = false;
                this.similarError = '';
                try {
                    const { data } = await window.api.post('/ai/similarity-search', {
                        product_id: this.product.id,
                        top_k: 20,
                    });
                    this.similarProducts = (data.products || []).filter(p => p.id !== this.product.id);
                } catch (e) {
                    const message = window.extractApiError(e);
                    this.similarError = e?.response?.status === 422
                        ? 'Similar items are not ready for this listing yet.'
                        : message;
                    if (options.force) {
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: this.similarError } }));
                    }
                } finally {
                    this.similarChecked = true;
                    this.similarLoading = false;
                }
            },
            async fetchReviews() {
                try {
                    const { data } = await window.api.get('/products/' + this.productId + '/reviews', { params: { per_page: 50 } });
                    this.reviews = data.reviews || [];
                    this.reviewsSummary = data.summary || this.reviewsSummary;
                    const me = window.auth.user();
                    this.myReview = me ? this.reviews.find(r => r.user_id === me.id) : null;
                } catch {}
            },
            async checkReviewEligibility() {
                if (!window.auth.isLoggedIn() || !this.product) {
                    this.reviewEligibility = { checked: true, eligible: false, reason: 'Sign in to leave a review.' };
                    return;
                }
                try {
                    const { data } = await window.api.get('/products/' + this.product.id + '/review-eligibility');
                    this.reviewEligibility = { checked: true, eligible: data.eligible, reason: data.reason || '' };
                } catch {
                    this.reviewEligibility = { checked: true, eligible: false, reason: '' };
                }
            },
            openReview() {
                this.reviewForm = this.myReview
                    ? { rating: this.myReview.rating, title: this.myReview.title || '', body: this.myReview.body || '' }
                    : { rating: 0, title: '', body: '' };
                this.showReview = true;
            },
            async submitReview() {
                if (this.reviewForm.rating < 1) return;
                this.reviewSubmitting = true;
                try {
                    if (this.myReview) {
                        await window.api.put('/reviews/' + this.myReview.id, this.reviewForm);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Review updated.' } }));
                    } else {
                        await window.api.post('/products/' + this.productId + '/reviews', this.reviewForm);
                        window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Thanks for your review!' } }));
                    }
                    this.showReview = false;
                    await this.fetchReviews();
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.reviewSubmitting = false;
                }
            },
            async deleteReview(r) {
                try {
                    await window.api.delete('/reviews/' + r.id);
                    r._confirmDelete = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Review deleted.' } }));
                    this.myReview = null;
                    await this.fetchReviews();
                } catch (e) {
                    r._confirmDelete = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            async markAsSold() {
                this.markingAsSold = true;
                try {
                    await window.api.put('/products/' + this.productId, { status: 'sold' });
                    this.product.status = 'sold';
                    this.confirmSold = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Listing marked as sold.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.markingAsSold = false;
                }
            },
            async openReport() {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                this.showReport = true;
                this.reportForm = { reason: '', details: '' };
                if (this.reportReasons.length === 0) {
                    try {
                        const { data } = await window.api.get('/product-reports/reasons');
                        this.reportReasons = data.reasons || [];
                    } catch {}
                }
            },
            async submitReport() {
                if (!this.reportForm.reason) return;
                this.reportSubmitting = true;
                try {
                    const { data } = await window.api.post('/products/' + this.product.id + '/report', this.reportForm);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: data.message || 'Report submitted.' } }));
                    this.showReport = false;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.reportSubmitting = false;
                }
            },
            async copyShareLink() {
                const url = await this.ensureShareUrl();
                try {
                    await navigator.clipboard.writeText(url);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Link copied!' } }));
                    this.showShare = false;
                } catch {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Could not copy. Use the URL bar.' } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
