{{-- Shared product form. Expects Alpine component productForm() to be initialised on a parent. --}}
<div x-show="!initLoading && !needsStore" style="display:none">
    {{-- Images --}}
    <div x-show="mode === 'edit'" class="bg-white rounded-xl border border-gray-200 p-6 mb-4" style="display:none">
        <h2 class="font-semibold text-gray-900 mb-3">Images</h2>
        <div x-show="existingImages.length === 0" class="text-sm text-gray-500 mb-3" style="display:none">No images uploaded yet.</div>
        <div x-show="existingImages.length > 0" class="grid grid-cols-3 sm:grid-cols-4 gap-3 mb-4" style="display:none">
            <template x-for="img in existingImages" :key="img.id">
                <div class="relative aspect-square bg-gray-100 rounded-lg overflow-hidden border"
                    :class="img.is_primary ? 'border-indigo-500 ring-2 ring-indigo-300' : 'border-gray-200'">
                    <img :src="img.url" class="w-full h-full object-cover">
                    <span x-show="img.is_primary" class="absolute top-1 left-1 text-[10px] bg-indigo-600 text-white px-1.5 py-0.5 rounded" style="display:none">Primary</span>
                </div>
            </template>
        </div>

        <label class="block">
            <span class="text-sm font-medium text-gray-700">Upload more images</span>
            <input type="file" multiple accept="image/*" @change="uploadImages($event)"
                class="mt-1 block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
            <p class="text-xs text-gray-500 mt-1">JPG/PNG/WebP, up to 5MB each.</p>
        </label>
        <p x-show="uploadingImages" class="text-sm text-indigo-600 mt-2" style="display:none">Uploading...</p>
    </div>

    {{-- Main form --}}
    <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 p-6 space-y-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input type="text" x-model="form.title" required maxlength="255"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
            <textarea x-model="form.description" required rows="4" maxlength="10000"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Price (in cents) *</label>
                <input type="number" min="0" x-model.number="form.price_amount" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <p class="text-xs text-gray-500 mt-1">e.g. 1500 = $15.00</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
                <input type="text" x-model="form.currency" maxlength="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                <input type="number" min="0" x-model.number="form.stock_quantity"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select x-model="form.category_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Select —</option>
                    <template x-for="c in categories" :key="c.id">
                        <option :value="c.id" x-text="c.name"></option>
                    </template>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Condition</label>
                <select x-model="form.product_condition_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Select —</option>
                    <template x-for="c in conditions" :key="c.id">
                        <option :value="c.id" x-text="c.name"></option>
                    </template>
                </select>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                <input type="text" x-model="form.location_country_code" maxlength="2" placeholder="US"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">State</label>
                <input type="text" x-model="form.location_state"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                <input type="text" x-model="form.location_city"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select x-model="form.status"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="draft">Draft (hidden)</option>
                    <option value="published">Published (visible)</option>
                    <option value="sold">Sold</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Visibility</label>
                <select x-model="form.visibility"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="public">Public</option>
                    <option value="followers_only">Followers only</option>
                    <option value="private">Private</option>
                </select>
            </div>
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" x-model="form.allow_offers">
            <span>Allow buyers to make offers</span>
        </label>

        {{-- Auto-post + schedule --}}
        <div class="border-t border-gray-200 pt-4">
            <h3 class="font-medium text-gray-900 mb-3">Social posting (optional)</h3>

            <label class="flex items-start gap-2 text-sm mb-3">
                <input type="checkbox" x-model="autoPostEnabled" class="mt-1">
                <span>
                    <span class="font-medium">Auto-post to social when published</span>
                    <span class="block text-xs text-gray-500">Automatically posts this product to your connected social accounts.</span>
                </span>
            </label>

            <div x-show="autoPostEnabled" class="ml-6 mb-4" style="display:none">
                <label class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
                <select x-model="form.auto_post"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="facebook">Facebook</option>
                </select>
                <p class="text-xs text-gray-400 mt-1">TikTok is pending platform review.</p>
            </div>

            <label class="flex items-start gap-2 text-sm">
                <input type="checkbox" x-model="scheduleEnabled" class="mt-1">
                <span>
                    <span class="font-medium">Schedule publishing</span>
                    <span class="block text-xs text-gray-500">Make the listing visible at a specific date/time instead of right now.</span>
                </span>
            </label>

            <div x-show="scheduleEnabled" class="ml-6 mt-2" style="display:none">
                <input type="datetime-local" x-model="form.schedule_at"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-200">
            <button type="submit" :disabled="saving"
                class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                <span x-show="!saving" x-text="mode === 'create' ? 'Create product' : 'Save changes'"></span>
                <span x-show="saving" style="display:none">Saving...</span>
            </button>
            <template x-if="mode === 'create'">
                <label class="border border-gray-300 text-gray-700 px-4 py-2.5 rounded-lg cursor-pointer hover:bg-gray-50">
                    <span x-text="pendingImages.length > 0 ? pendingImages.length + ' image(s) ready' : 'Add images'"></span>
                    <input type="file" multiple accept="image/*" @change="addPending($event)" class="hidden">
                </label>
            </template>
            <a href="/me/products" class="px-4 py-2.5 text-gray-600 hover:text-gray-900">Cancel</a>
        </div>
    </form>
</div>
