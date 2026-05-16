@extends('layouts.app')

@section('title', 'Admin · Users')

@section('content')
@include('components.auth-guard')
@include('components.toast')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="adminUsers()" x-init="fetch">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="/admin" class="text-sm text-gray-500 hover:text-indigo-600">← Admin</a>
            <h1 class="text-2xl font-semibold text-gray-900 mt-1">Users</h1>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 mb-4 grid sm:grid-cols-4 gap-3">
        <input type="text" x-model="filters.search" @keydown.enter="apply()" placeholder="Search name, email, phone..."
            class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 sm:col-span-2">
        <select x-model="filters.role" @change="apply()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All roles</option>
            <option value="user">User</option>
            <option value="seller">Seller</option>
            <option value="admin">Admin</option>
        </select>
        <select x-model="filters.status" @change="apply()" class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <option value="">All statuses</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="suspended">Suspended</option>
            <option value="banned">Banned</option>
        </select>
    </div>

    <div x-show="loading" class="text-center py-20 text-gray-500">Loading users...</div>

    <div x-show="!loading" class="bg-white rounded-xl border border-gray-200 overflow-x-auto" style="display:none">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-for="user in users" :key="user.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-medium text-sm shrink-0"
                                    x-text="(user.name || '?').charAt(0).toUpperCase()"></div>
                                <p class="font-medium text-gray-900" x-text="user.name"></p>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700" x-text="user.email"></td>
                        <td class="px-4 py-3">
                            <select :value="user.role" @change="updateRole(user, $event.target.value)"
                                class="text-xs border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                <option value="user">user</option>
                                <option value="seller">seller</option>
                                <option value="admin">admin</option>
                            </select>
                        </td>
                        <td class="px-4 py-3">
                            <select :value="user.status" @change="updateStatus(user, $event.target.value)"
                                :class="statusClass(user.status)"
                                class="text-xs rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-indigo-500 border-0">
                                <option value="active">active</option>
                                <option value="inactive">inactive</option>
                                <option value="suspended">suspended</option>
                                <option value="banned">banned</option>
                            </select>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500" x-text="formatDate(user.created_at)"></td>
                        <td class="px-4 py-3 text-right">
                            <button @click="remove(user)" class="text-sm text-red-600 hover:text-red-700">Delete</button>
                        </td>
                    </tr>
                </template>
                <tr x-show="users.length === 0">
                    <td colspan="6" class="text-center py-10 text-sm text-gray-500">No users found.</td>
                </tr>
            </tbody>
        </table>

        <div x-show="meta.last_page > 1" class="flex items-center justify-center gap-2 py-4 border-t border-gray-200" style="display:none">
            <button @click="goToPage(meta.current_page - 1)" :disabled="meta.current_page <= 1"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Previous</button>
            <span class="text-sm text-gray-600">Page <span x-text="meta.current_page"></span> of <span x-text="meta.last_page"></span></span>
            <button @click="goToPage(meta.current_page + 1)" :disabled="meta.current_page >= meta.last_page"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 disabled:opacity-50">Next</button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function adminUsers() {
        return {
            users: [],
            meta: { current_page: 1, last_page: 1, total: 0 },
            page: 1,
            filters: { search: '', role: '', status: '' },
            loading: true,
            statusClass(status) {
                const map = {
                    active: 'bg-green-100 text-green-800',
                    inactive: 'bg-gray-100 text-gray-700',
                    suspended: 'bg-yellow-100 text-yellow-800',
                    banned: 'bg-red-100 text-red-800',
                };
                return map[status] || 'bg-gray-100 text-gray-700';
            },
            formatDate(ts) {
                return ts ? new Date(ts).toLocaleDateString() : '';
            },
            async fetch() {
                this.loading = true;
                try {
                    const params = { page: this.page };
                    if (this.filters.search) params.search = this.filters.search;
                    if (this.filters.role) params.role = this.filters.role;
                    if (this.filters.status) params.status = this.filters.status;
                    const { data } = await window.api.get('/admin/users', { params });
                    this.users = data.users || [];
                    this.meta = data.meta || this.meta;
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                } finally {
                    this.loading = false;
                }
            },
            apply() {
                this.page = 1;
                this.fetch();
            },
            goToPage(page) {
                this.page = page;
                this.fetch();
            },
            async updateRole(user, role) {
                try {
                    await window.api.patch('/admin/users/' + user.id, { role });
                    user.role = role;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Role updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.fetch();
                }
            },
            async updateStatus(user, status) {
                try {
                    await window.api.patch('/admin/users/' + user.id, { status });
                    user.status = status;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Status updated.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                    this.fetch();
                }
            },
            async remove(user) {
                if (!confirm('Delete user "' + user.name + '"? This cannot be undone.')) return;
                try {
                    await window.api.delete('/admin/users/' + user.id);
                    this.users = this.users.filter(u => u.id !== user.id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'info', message: 'User deleted.' } }));
                } catch (e) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: window.extractApiError(e) } }));
                }
            },
        };
    }
</script>
@endpush
@endsection
