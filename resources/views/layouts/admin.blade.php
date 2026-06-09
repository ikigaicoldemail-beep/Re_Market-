<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin · ReMarket')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    @include('components.admin-guard')
    @include('components.toast')

    @php
        $current = Route::currentRouteName();
        $navItems = [
            ['route' => 'admin.index',      'label' => 'Dashboard',  'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
            ['route' => 'admin.users',      'label' => 'Users',      'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-5.13a4 4 0 11-8 0 4 4 0 018 0zm6 0a4 4 0 11-8 0 4 4 0 018 0z"/>'],
            ['route' => 'admin.stores',     'label' => 'Stores',     'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18l-2 13H5L3 3zm6 17a2 2 0 11-4 0 2 2 0 014 0zm10 0a2 2 0 11-4 0 2 2 0 014 0z"/>'],
            ['route' => 'admin.products',   'label' => 'Products',   'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
            ['route' => 'admin.categories', 'label' => 'Categories', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>'],
            ['route' => 'admin.brands',     'label' => 'Brands',     'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>'],
            ['route' => 'admin.banners',    'label' => 'Promo Banners', 'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
            ['route' => 'admin.reports',    'label' => 'Reports',    'svg' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21l1.5-7.5a4 4 0 014-3.5h11a4 4 0 014 3.5L25 21M12 3v4m0 4h.01"/>'],
        ];
    @endphp

    <div class="min-h-screen flex" x-data="{ sidebarOpen: false }">
        {{-- Sidebar (fixed on desktop, slide-over on mobile) --}}
        <div x-show="sidebarOpen" @click="sidebarOpen = false"
             class="fixed inset-0 bg-black/40 z-30 lg:hidden" style="display:none"></div>

        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed lg:static inset-y-0 left-0 w-64 bg-white border-r border-gray-200 z-40 transform transition-transform duration-200 flex flex-col">
            <div class="h-14 flex items-center gap-2 px-4 border-b border-gray-200 shrink-0">
                <a href="{{ route('admin.index') }}" class="flex items-center" aria-label="Second Market admin">
                    <img src="{{ asset('images/logo_secondMarket_nav.png') }}" alt="Second Market" class="h-9 w-auto object-contain">
                </a>
                <button @click="sidebarOpen = false" class="ml-auto lg:hidden p-1 text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <nav class="flex-1 overflow-y-auto py-3">
                @foreach ($navItems as $item)
                    @php $active = $current === $item['route']; @endphp
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 px-4 py-2.5 text-sm font-medium transition border-l-2
                              {{ $active
                                  ? 'bg-indigo-50 text-indigo-700 border-indigo-600'
                                  : 'text-gray-700 border-transparent hover:bg-gray-50 hover:text-indigo-700' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['svg'] !!}</svg>
                        <span>{{ $item['label'] }}</span>
                        @if ($item['route'] === 'admin.reports')
                            <span x-data x-init="
                                window.api.get('/admin/stats')
                                  .then(r => { const n = r.data?.stats?.reports?.open ?? 0; if (n > 0) $el.style.display = 'inline-flex'; $el.textContent = n; })
                                  .catch(() => {});
                            " class="ml-auto text-[10px] bg-red-600 text-white px-1.5 py-0.5 rounded-full font-semibold" style="display:none"></span>
                        @endif
                    </a>
                @endforeach
            </nav>

            <div class="border-t border-gray-200 p-3 text-xs text-gray-500">
                <a href="{{ route('home') }}" class="block py-1 hover:text-indigo-600">← Back to site</a>
            </div>
        </aside>

        {{-- Main column --}}
        <div class="flex-1 flex flex-col min-w-0 lg:ml-0">
            <header class="h-14 bg-white border-b border-gray-200 flex items-center px-4 sm:px-6 sticky top-0 z-20">
                <button @click="sidebarOpen = true" class="lg:hidden p-1 text-gray-500 hover:text-gray-700 mr-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-base sm:text-lg font-semibold text-gray-900 truncate">@yield('page-title', 'Admin')</h1>

                <div class="ml-auto flex items-center gap-3" x-data>
                    <a href="{{ route('home') }}" class="hidden sm:inline-flex text-xs text-gray-500 hover:text-indigo-600">View site →</a>
                    <template x-if="$store.auth.loggedIn">
                        @include('components.notification-bell')
                    </template>
                    <template x-if="$store.auth.loggedIn">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-indigo-600 px-2 py-1">
                                <span class="w-8 h-8 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-medium text-sm"
                                      x-text="$store.auth.user?.name?.charAt(0)?.toUpperCase() || '?'"></span>
                                <span x-text="$store.auth.user?.name" class="hidden md:inline"></span>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-lg py-1" style="display:none">
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                                <a href="{{ route('home') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Back to site</a>
                                <hr class="my-1 border-gray-200">
                                <button @click="$store.auth.logout()" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Sign out</button>
                            </div>
                        </div>
                    </template>
                </div>
            </header>

            <main class="flex-1 px-4 sm:px-6 lg:px-8 py-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
