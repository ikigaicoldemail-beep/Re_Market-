<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Second-Hand Marketplace')</title>
    @yield('meta')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">
    <header class="bg-white sticky top-0 z-40 shadow-sm shadow-gray-100/80">
        {{-- MAIN ROW: logo + search + icons --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-6 h-16">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="shrink-0 flex items-center" aria-label="Second Market home">
                    <img src="{{ asset('images/logo_secondMarket_nav.png') }}" alt="Second Market" class="h-10 sm:h-11 w-auto object-contain">
                </a>

                {{-- Search --}}
                <div class="flex-1 max-w-2xl hidden md:block">
                    <form action="{{ route('home') }}" method="GET" class="flex items-stretch bg-white border-2 border-indigo-100 rounded-md focus-within:border-indigo-400 transition">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search for products..."
                            data-i18n="filters.search_placeholder" data-i18n-placeholder
                            class="flex-1 px-4 py-2.5 text-sm bg-transparent focus:outline-none">
                        <button type="submit" class="bg-indigo-600 text-white px-5 font-semibold text-sm rounded-r-sm hover:bg-indigo-700 flex items-center gap-1.5">
                            <x-heroicon-o-magnifying-glass class="w-4 h-4"/>
                            <span class="hidden sm:inline" data-i18n="nav.search">Search</span>
                        </button>
                    </form>
                </div>

                {{-- Right action cluster --}}
                <div class="flex items-center gap-4 ml-auto" x-data>
                    {{-- Language switcher --}}
                    <div class="relative" x-data="{ open: false, locale: (window.currentLocale && window.currentLocale()) || 'en' }"
                         x-init="window.addEventListener('i18n:changed', e => locale = e.detail.locale)">
                        <button type="button" @click="open = !open"
                            class="h-9 px-2.5 rounded-full border border-gray-200 bg-white text-gray-700 hover:border-indigo-200 hover:text-indigo-700 hover:bg-indigo-50/60 flex items-center gap-1.5 text-xs font-semibold transition"
                            :aria-label="locale === 'km' ? 'Switch language, Khmer selected' : 'Switch language, English selected'">
                            <x-heroicon-o-language class="w-4 h-4"/>
                            <span x-text="locale === 'km' ? 'KM' : 'EN'"></span>
                            <x-heroicon-m-chevron-down class="w-3.5 h-3.5"/>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition
                             class="absolute right-0 mt-2 w-36 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50" style="display:none">
                            <button type="button" @click="window.setLocale('en'); open = false"
                                :class="locale === 'en' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'"
                                class="w-full text-left px-3 py-2 text-sm flex items-center justify-between">
                                <span>English</span>
                                <span class="text-xs font-semibold">EN</span>
                            </button>
                            <button type="button" @click="window.setLocale('km'); open = false"
                                :class="locale === 'km' ? 'bg-indigo-50 text-indigo-700' : 'text-gray-700 hover:bg-gray-50'"
                                class="w-full text-left px-3 py-2 text-sm flex items-center justify-between">
                                <span>Khmer</span>
                                <span class="text-xs font-semibold">KM</span>
                            </button>
                        </div>
                    </div>

                    {{-- Compare --}}
                    <a href="{{ route('compare') }}" class="hidden sm:flex relative items-center gap-2 text-gray-700 hover:text-indigo-700">
                        <span class="relative">
                            <x-heroicon-o-arrows-right-left class="w-6 h-6"/>
                            <span x-show="$store.compare.count > 0"
                                x-text="$store.compare.count"
                                class="absolute -top-1.5 -right-2 bg-indigo-600 text-white text-[10px] rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center font-semibold" style="display:none"></span>
                        </span>
                        <span class="text-sm hidden lg:inline" data-i18n="nav.compare">Compare</span>
                    </a>

                    {{-- Wishlist --}}
                    <a href="{{ route('wishlist') }}" class="hidden sm:flex relative items-center gap-2 text-gray-700 hover:text-indigo-700">
                        <x-heroicon-o-heart class="w-6 h-6"/>
                        <span class="text-sm hidden lg:inline" data-i18n="nav.wishlist">Wishlist</span>
                    </a>

                    {{-- Messages (only when logged in) --}}
                    <template x-if="$store.auth.loggedIn">
                        <a href="{{ route('messages.index') }}" class="relative text-gray-700 hover:text-indigo-700" title="Messages" :title="window.t ? window.t('nav.messages') : 'Messages'">
                            <x-heroicon-o-chat-bubble-left-right class="w-6 h-6"/>
                            <span x-show="$store.chat.unread > 0"
                                x-text="$store.chat.unread"
                                class="absolute -top-1.5 -right-2 bg-indigo-600 text-white text-[10px] rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center font-semibold" style="display:none"></span>
                        </a>
                    </template>

                    {{-- Notifications --}}
                    <template x-if="$store.auth.loggedIn">
                        @include('components.notification-bell')
                    </template>

                    {{-- Account --}}
                    <template x-if="!$store.auth.loggedIn">
                        <div class="flex items-center gap-2 text-sm">
                            <a href="{{ route('login') }}" class="text-gray-700 hover:text-indigo-700 px-3 py-2" data-i18n="nav.signin">Sign in</a>
                            <a href="{{ route('register') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium" data-i18n="nav.signup">Sign up</a>
                        </div>
                    </template>

                    <template x-if="$store.auth.loggedIn">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-indigo-700">
                                <span class="w-9 h-9 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-semibold"
                                      x-text="$store.auth.user?.name?.charAt(0)?.toUpperCase() || '?'"></span>
                                <span x-text="$store.auth.user?.name" class="hidden xl:inline font-medium"></span>
                                <x-heroicon-m-chevron-down class="w-4 h-4 hidden lg:inline"/>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-50" style="display:none">
                                <a href="{{ route('profile') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <x-heroicon-o-user class="w-4 h-4"/><span>Profile</span>
                                </a>
                                <a href="{{ route('messages.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <x-heroicon-o-chat-bubble-left-right class="w-4 h-4"/><span>Messages</span>
                                </a>
                                <a href="{{ route('wishlist') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <x-heroicon-o-heart class="w-4 h-4"/><span>Wishlist</span>
                                </a>
                                <hr class="my-1 border-gray-200">
                                <a href="{{ route('me.products.create') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-indigo-600 font-medium hover:bg-indigo-50">
                                    <x-heroicon-o-plus-circle class="w-4 h-4"/><span>Sell an Item</span>
                                </a>
                                <a href="{{ route('me.products.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <x-heroicon-o-squares-2x2 class="w-4 h-4"/><span>My Listings</span>
                                </a>
                                <a href="{{ route('me.store') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    <x-heroicon-o-building-storefront class="w-4 h-4"/><span>My Store</span>
                                </a>
                                <template x-if="$store.auth.user?.role === 'admin'">
                                    <div>
                                        <hr class="my-1 border-gray-200">
                                        <a href="{{ route('admin.index') }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                            <x-heroicon-o-cog-6-tooth class="w-4 h-4"/><span>Admin Dashboard</span>
                                        </a>
                                    </div>
                                </template>
                                <hr class="my-1 border-gray-200">
                                <button @click="$store.auth.logout()" class="flex items-center gap-2 w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4"/><span>Sign out</span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- Floating compare bar --}}
    <div x-data x-show="$store.compare.count > 0"
        class="fixed bottom-4 right-4 z-40 bg-white shadow-xl border border-gray-200 rounded-2xl px-4 py-3 flex items-center gap-3"
        style="display:none">
        <x-heroicon-o-arrows-right-left class="w-5 h-5 text-indigo-600"/>
        <span class="text-sm text-gray-700">
            <strong x-text="$store.compare.count"></strong>
            <span> product<span x-show="$store.compare.count !== 1" style="display:none">s</span></span>
        </span>
        <a href="{{ route('compare') }}" class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg font-medium hover:bg-indigo-700">Compare</a>
        <button @click="$store.compare.clear()" class="text-gray-400 hover:text-red-600" title="Clear">
            <x-heroicon-m-x-mark class="w-4 h-4"/>
        </button>
    </div>

    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} ReMarket. All rights reserved.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
