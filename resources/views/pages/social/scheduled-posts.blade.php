@extends('layouts.app')

@section('title', 'Scheduled Posts')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="scheduledPosts()" x-init="fetch">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Scheduled Social Posts</h1>
            <p class="text-sm text-gray-500 mt-1">
                Facebook posts queued to publish at a future time.
                To schedule when a product becomes visible on this website instead, use the
                <a href="{{ route('me.products.create') }}" class="text-indigo-600 underline">product form's "Schedule publishing"</a> option.
            </p>
        </div>
        <button @click="showCreate = true; loadCreateOptions()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700">
            + Schedule Post
        </button>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading...</div>

    <div x-show="!loading && posts.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center" style="display:none">
        <p class="text-gray-500 mb-4">No scheduled posts yet.</p>
    </div>

    <div x-show="!loading && posts.length > 0" class="space-y-3" style="display:none">
        <template x-for="p in posts" :key="p.id">
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 min-w-0">
                    <div class="w-16 h-16 bg-gray-100 rounded-lg shrink-0 overflow-hidden">
                        <img :src="postImage(p)" onerror="this.src='https://placehold.co/100x100/e5e7eb/9ca3af?text=N/A'"
                            class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 line-clamp-1" x-text="p.social_post?.product?.title || 'Product'"></p>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span x-text="(p.social_post?.platform || '').toUpperCase()"></span>
                            <span class="mx-1">·</span>
                            <span x-text="formatDate(p.scheduled_for)"></span>
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    <span :class="statusClass(p.status)" class="text-xs px-2 py-1 rounded-full font-medium" x-text="p.status"></span>
                    <button @click="cancelPost(p)" :disabled="!canCancel(p.status)"
                        class="text-sm text-red-600 hover:text-red-700 disabled:opacity-30 disabled:cursor-not-allowed">Cancel</button>
                </div>
            </div>
        </template>
    </div>

    {{-- Create modal --}}
    <div x-show="showCreate" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4" @click.self="showCreate = false" style="display:none">
        <div class="bg-white rounded-xl max-w-md w-full p-6 max-h-[90vh] overflow-y-auto">
            <h2 class="text-lg font-semibold mb-4">Schedule a product post</h2>
            <form @submit.prevent="submitCreate" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
                    <select x-model.number="form.product_id" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">— Select your product —</option>
                        <template x-for="p in myProducts" :key="p.id">
                            <option :value="p.id" x-text="p.title"></option>
                        </template>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Post to *</label>
                    <select x-model="form.post_to"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="marketplace">Marketplace's Facebook page</option>
                        <option value="user_account">My connected Facebook account</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Both options publish to Facebook — this page does not control website visibility.</p>
                </div>

                <div x-show="form.post_to === 'user_account'" style="display:none">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Account *</label>
                    <select x-model.number="form.social_account_id"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">— Select account —</option>
                        <template x-for="a in mySocialAccounts" :key="a.id">
                            <option :value="a.id" x-text="(a.platform || '').toUpperCase() + ' — ' + (a.provider_account_name || a.provider_user_id)"></option>
                        </template>
                    </select>
                    <p x-show="mySocialAccounts.length === 0" class="text-xs text-red-600 mt-1" style="display:none">
                        No connected accounts. <a href="/social/accounts" class="underline">Connect one</a> first.
                    </p>
                </div>

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

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Caption</label>
                    <textarea x-model="form.caption" rows="3" maxlength="1000" placeholder="Leave blank to auto-generate..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>

                <div class="flex gap-2 pt-2">
                    <button type="submit" :disabled="creating"
                        class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 disabled:opacity-50">
                        <span x-show="!creating">Schedule</span>
                        <span x-show="creating" style="display:none">Scheduling...</span>
                    </button>
                    <button type="button" @click="showCreate = false" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function scheduledPosts() {
        const todayStr = new Date().toISOString().slice(0, 10);
        return {
            today: todayStr,
            posts: [],
            loading: true,
            showCreate: false,
            creating: false,
            myProducts: [],
            mySocialAccounts: [],
            form: {
                product_id: '',
                post_to: 'marketplace',
                social_account_id: '',
                scheduled_date: todayStr,
                scheduled_time: '12:00',
                caption: '',
            },
            postImage(p) {
                const img = p.social_post?.product?.images?.[0];
                return img?.url || 'https://placehold.co/100x100/e5e7eb/9ca3af?text=N/A';
            },
            statusClass(status) {
                const map = {
                    scheduled: 'bg-blue-100 text-blue-800',
                    processing: 'bg-yellow-100 text-yellow-800',
                    published: 'bg-green-100 text-green-800',
                    failed: 'bg-red-100 text-red-800',
                    cancelled: 'bg-gray-100 text-gray-600',
                };
                return map[status] || 'bg-gray-100 text-gray-700';
            },
            canCancel(status) {
                return status === 'scheduled';
            },
            formatDate(date) {
                if (!date) return '';
                return new Date(date).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' });
            },
            async fetch() {
                this.loading = true;
                try {
                    const { data } = await window.api.get('/scheduled-posts');
                    this.posts = data.scheduled_posts || data.data || [];
                    // Some controllers return collection directly — normalize
                    if (!Array.isArray(this.posts) && this.posts.data) this.posts = this.posts.data;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            async loadCreateOptions() {
                try {
                    const [productsRes, accountsRes] = await Promise.all([
                        window.api.get('/me/products'),
                        window.api.get('/social/accounts'),
                    ]);
                    this.myProducts = productsRes.data.products || [];
                    this.mySocialAccounts = accountsRes.data.social_accounts || [];
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
            async submitCreate() {
                this.creating = true;
                try {
                    const payload = {
                        product_id: this.form.product_id,
                        post_to: this.form.post_to,
                        scheduled_date: this.form.scheduled_date,
                        scheduled_time: this.form.scheduled_time,
                        caption: this.form.caption || null,
                    };
                    if (this.form.post_to === 'user_account') {
                        payload.social_account_id = this.form.social_account_id;
                    }
                    await window.api.post('/products/schedule-post', payload);
                    this.showCreate = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Post scheduled!' } }));
                    await this.fetch();
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.creating = false;
                }
            },
            async cancelPost(p) {
                if (!confirm('Cancel this scheduled post?')) return;
                try {
                    await window.api.delete('/scheduled-posts/' + p.id);
                    p.status = 'cancelled';
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'Post cancelled.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
