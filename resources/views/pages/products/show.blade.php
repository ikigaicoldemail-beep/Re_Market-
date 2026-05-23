@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
@include('components.toast')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="productDetail()" x-init="init">
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
                <template x-for="img in product.images" :key="img.id">
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

            <p class="text-sm text-gray-600 mt-2" x-show="activeStock > 0" style="display:none">
                <span x-text="activeStock"></span> <span data-i18n="product.in_stock">in stock</span>
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
                <div class="flex items-center justify-between gap-3">
                    <a :href="'/stores/' + product?.store?.id" class="font-medium text-gray-900 hover:text-indigo-600" x-text="product?.store?.name"></a>
                    <div class="flex gap-2">
                        <a x-show="product?.store?.telegram_url" :href="product?.store?.telegram_url" target="_blank" rel="noopener"
                            title="Chat on Telegram"
                            class="w-9 h-9 rounded-full bg-sky-500 text-white flex items-center justify-center hover:bg-sky-600" style="display:none">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M21.426 2.574L2.13 9.97c-.948.36-.943.86-.171 1.094l4.94 1.541 11.43-7.214c.54-.328 1.034-.151.63.21l-9.258 8.36-.357 5.346c.357 0 .515-.164.715-.358l1.717-1.667 3.563 2.633c.658.363 1.13.175 1.292-.611l2.342-10.99c.243-1.122-.382-1.587-1.547-1.14z"/></svg>
                        </a>
                        <a x-show="product?.store?.messenger_url" :href="product?.store?.messenger_url" target="_blank" rel="noopener"
                            title="Chat on Messenger"
                            class="w-9 h-9 rounded-full bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700" style="display:none">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2C6.477 2 2 6.145 2 11.243c0 2.835 1.387 5.36 3.55 7.06V22l3.245-1.78c.864.24 1.78.367 2.705.367 5.523 0 10-4.145 10-9.243C22 6.145 17.523 2 12 2zm.987 12.421l-2.55-2.717-4.937 2.717 5.43-5.764 2.61 2.717 4.876-2.717-5.43 5.764z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="mt-6 space-y-3">
                <button @click="addToCart()" :disabled="addingToCart || product?.stock_quantity <= 0"
                    class="w-full bg-indigo-600 text-white py-3 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span x-show="!addingToCart" data-i18n="product.add_to_cart">Add to cart</span>
                    <span x-show="addingToCart" style="display:none">Adding...</span>
                </button>

                <div class="grid grid-cols-2 gap-3">
                    <button @click="toggleWishlist()" :disabled="togglingWishlist"
                        class="border border-gray-300 py-2.5 rounded-lg font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50">
                        <span x-show="!inWishlist" data-i18n="product.save_wishlist">Save to wishlist</span>
                        <span x-show="inWishlist" data-i18n="product.in_wishlist" style="display:none">In wishlist</span>
                    </button>
                    <button @click="showShare = true" class="border border-gray-300 py-2.5 rounded-lg font-medium text-gray-700 hover:bg-gray-50" data-i18n="product.share">
                        Share
                    </button>
                </div>

                <button @click="$store.compare.toggle(product.id)"
                    :class="$store.compare.has(product?.id) ? 'border-indigo-500 bg-indigo-50 text-indigo-700' : 'border-gray-300 text-gray-700 hover:bg-gray-50'"
                    class="w-full border py-2.5 rounded-lg font-medium transition inline-flex items-center justify-center gap-2">
                    <template x-if="!$store.compare.has(product?.id)">
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-o-arrows-right-left class="w-4 h-4"/>
                            <span data-i18n="product.compare_add">Add to compare</span>
                        </span>
                    </template>
                    <template x-if="$store.compare.has(product?.id)">
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-s-check class="w-4 h-4"/>
                            <span data-i18n="product.compare_in">In compare</span>
                        </span>
                    </template>
                </button>

                <button @click="messageSeller()" :disabled="messagingSeller"
                    x-show="product?.user_id && (!window.auth.user() || product.user_id !== window.auth.user().id)"
                    class="w-full mt-3 border border-indigo-300 text-indigo-700 py-2.5 rounded-lg font-medium hover:bg-indigo-50 disabled:opacity-50 inline-flex items-center justify-center gap-2"
                    style="display:none">
                    <template x-if="!messagingSeller">
                        <span class="inline-flex items-center gap-2">
                            <x-heroicon-o-chat-bubble-left-right class="w-4 h-4"/>
                            <span data-i18n="product.message_seller">Message seller</span>
                        </span>
                    </template>
                    <span x-show="messagingSeller" style="display:none">Opening chat...</span>
                </button>

                <button @click="findSimilar()" class="w-full mt-3 text-sm text-indigo-600 hover:text-indigo-700 underline" data-i18n="product.find_similar">
                    Find visually similar items
                </button>

                <button @click="openReport()"
                    x-show="product?.user_id && (!window.auth.user() || product.user_id !== window.auth.user().id)"
                    class="w-full mt-1 text-xs text-gray-500 hover:text-red-600 inline-flex items-center justify-center gap-1.5" style="display:none">
                    <x-heroicon-o-flag class="w-3.5 h-3.5"/>
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
                        <button @click="deleteReview(r)" class="text-red-600 hover:text-red-700">Delete</button>
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

    {{-- Similar products modal --}}
    <div x-show="showSimilar" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showSimilar = false" style="display:none">
        <div class="bg-white rounded-xl max-w-3xl w-full max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold">Visually similar products</h2>
                <button @click="showSimilar = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>
            <div class="overflow-y-auto p-4">
                <div x-show="similarLoading" class="text-center py-10 text-gray-500">Searching...</div>
                <div x-show="!similarLoading && similarProducts.length === 0" class="text-center py-10 text-gray-500" style="display:none">
                    No similar products found.
                </div>
                <div x-show="!similarLoading && similarProducts.length > 0" class="grid sm:grid-cols-2 md:grid-cols-3 gap-4" style="display:none">
                    <template x-for="p in similarProducts" :key="p.id">
                        <a :href="'/products/' + p.id" class="block group">
                            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden">
                                <img :src="similarPrimaryImage(p)"
                                    onerror="this.src='https://placehold.co/300x300/e5e7eb/9ca3af?text=No+Image'"
                                    class="w-full h-full object-cover group-hover:scale-105 transition">
                            </div>
                            <p class="text-sm font-medium text-gray-900 mt-2 line-clamp-2" x-text="p.title"></p>
                            <p class="text-sm font-bold text-indigo-600" x-text="formatPrice(p.price_amount, p.currency)"></p>
                        </a>
                    </template>
                </div>
            </div>
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
            addingToCart: false,
            togglingWishlist: false,
            inWishlist: false,
            showShare: false,
            sharingPlatform: false,
            shareUrl: '',
            messagingSeller: false,
            showSimilar: false,
            similarLoading: false,
            similarProducts: [],
            showReport: false,
            reportReasons: [],
            reportForm: { reason: '', details: '' },
            reportSubmitting: false,
            reviews: [],
            reviewsSummary: { total: 0, average: 0, breakdown: { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 } },
            myReview: null,
            reviewEligibility: { checked: false, eligible: false, reason: '' },
            showReview: false,
            reviewForm: { rating: 0, title: '', body: '' },
            reviewSubmitting: false,
            async init() {
                const segments = window.location.pathname.split('/').filter(Boolean);
                this.productId = parseInt(segments[segments.length - 1]);
                await this.fetch();
                await this.checkWishlist();
                await this.fetchReviews();
                this.checkReviewEligibility();
            },
            get canWriteReview() {
                return this.reviewEligibility.eligible && !this.myReview;
            },
            get hasSpecs() {
                return this.product?.specs && typeof this.product.specs === 'object' && Object.keys(this.product.specs).length > 0;
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
                    const { data } = await window.api.get('/wishlist', { params: { per_page: 100 } });
                    const list = Array.isArray(data?.products) ? data.products : [];
                    this.inWishlist = list.some(p => p?.id === this.product.id);
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
                    const imgs = this.product.images || [];
                    const primary = imgs.length > 0 ? (imgs.find(i => i.is_primary) || imgs[0]) : null;
                    this.activeImage = primary ? this.heroSrc(primary)
                        : 'https://placehold.co/600x600/e5e7eb/9ca3af?text=No+Image';
                } catch (e) {
                    this.error = window.extractApiError(e) || 'Product not found.';
                } finally {
                    this.loading = false;
                }
            },
            async addToCart() {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                this.addingToCart = true;
                try {
                    const payload = { product_id: this.product.id, quantity: 1 };
                    if (this.selectedVariant?.id) payload.variant_id = this.selectedVariant.id;
                    await window.api.post('/cart/items', payload);
                    await Alpine.store('cart').refresh();
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Added to cart!' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.addingToCart = false;
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
            async shareToPlatform(platform) {
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                this.sharingPlatform = true;
                try {
                    const url = await this.ensureShareUrl();
                    await window.api.post('/products/share', {
                        product_id: this.product.id,
                        platform,
                        destination: url,
                    });
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: `Shared on ${platform}!` } }));
                    // Also open intent URL for facebook
                    if (platform === 'facebook') {
                        window.open('https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url), '_blank', 'width=600,height=600');
                    }
                    this.showShare = false;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.sharingPlatform = false;
                }
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
            async findSimilar() {
                if (!this.product) return;
                if (!window.auth.isLoggedIn()) {
                    window.location.href = '/login?next=' + encodeURIComponent(window.location.pathname);
                    return;
                }
                this.showSimilar = true;
                if (this.similarProducts.length > 0) return;
                this.similarLoading = true;
                try {
                    const { data } = await window.api.post('/ai/similarity-search', {
                        product_id: this.product.id,
                        top_k: 9,
                    });
                    this.similarProducts = (data.products || []).filter(p => p.id !== this.product.id);
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.showSimilar = false;
                } finally {
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
                const me = window.auth.user();
                if (!me) {
                    this.reviewEligibility = { checked: true, eligible: false, reason: 'Sign in to leave a review.' };
                    return;
                }
                if (me.id === this.product?.user_id) {
                    this.reviewEligibility = { checked: true, eligible: false, reason: '' };
                    return;
                }
                if (this.myReview) {
                    this.reviewEligibility = { checked: true, eligible: true, reason: '' };
                    return;
                }
                try {
                    const { data } = await window.api.get('/orders', { params: { per_page: 50 } });
                    const orders = data.orders || [];
                    const hasPaid = orders.some(o => o.payment_status === 'paid'
                        && (o.items || []).some(i => i.product_id === this.product.id));
                    if (hasPaid) {
                        this.reviewEligibility = { checked: true, eligible: true, reason: '' };
                    } else {
                        this.reviewEligibility = { checked: true, eligible: false, reason: 'Only buyers who have purchased this product can leave a review.' };
                    }
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
                if (!confirm('Delete your review?')) return;
                try {
                    await window.api.delete('/reviews/' + r.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Review deleted.' } }));
                    this.myReview = null;
                    await this.fetchReviews();
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
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
