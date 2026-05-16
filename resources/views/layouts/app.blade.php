<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Second-Hand Marketplace')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">
    <header class="bg-white border-b border-gray-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-8">
                    <a href="{{ route('home') }}" class="text-xl font-semibold text-indigo-600">
                        ReMarket
                    </a>
                    <nav class="hidden md:flex gap-6 text-sm text-gray-700">
                        <a href="{{ route('home') }}" class="hover:text-indigo-600">Browse</a>
                        <a href="{{ route('search.visual') }}" class="hover:text-indigo-600">Visual&nbsp;Search</a>
                        <a href="{{ route('me.products.create') }}" class="hover:text-indigo-600 font-medium text-indigo-600">+ Sell</a>
                        <a href="{{ route('wishlist') }}" class="hover:text-indigo-600">Wishlist</a>
                    </nav>
                </div>

                <div class="flex-1 max-w-xl mx-6 hidden md:block">
                    <form action="{{ route('home') }}" method="GET">
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search for products..."
                                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </form>
                </div>

                <div class="flex items-center gap-3" x-data>
                    <template x-if="$store.auth.loggedIn">
                        <a href="{{ route('messages.index') }}" class="relative p-2 text-gray-700 hover:text-indigo-600" title="Messages">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.86 9.86 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span x-show="$store.chat.unread > 0"
                                x-text="$store.chat.unread"
                                class="absolute -top-1 -right-1 bg-indigo-600 text-white text-xs rounded-full min-w-[18px] h-[18px] px-1 flex items-center justify-center" style="display:none"></span>
                        </a>
                    </template>

                    <a href="{{ route('cart') }}" class="relative p-2 text-gray-700 hover:text-indigo-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span x-show="$store.auth.loggedIn && $store.cart.count > 0"
                            x-text="$store.cart.count"
                            class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" style="display:none"></span>
                    </a>

                    <template x-if="!$store.auth.loggedIn">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-indigo-600 px-3 py-2">Sign in</a>
                            <a href="{{ route('register') }}" class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Sign up</a>
                        </div>
                    </template>

                    <template x-if="$store.auth.loggedIn">
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 text-sm text-gray-700 hover:text-indigo-600 px-3 py-2">
                                <span class="w-8 h-8 bg-indigo-100 text-indigo-700 rounded-full flex items-center justify-center font-medium"
                                    x-text="$store.auth.user?.name?.charAt(0)?.toUpperCase() || '?'"></span>
                                <span x-text="$store.auth.user?.name" class="hidden md:inline"></span>
                            </button>
                            <div x-show="open" @click.outside="open = false" x-transition
                                class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-lg shadow-lg py-1" style="display:none">
                                <div class="px-4 py-2 text-xs text-gray-400 uppercase tracking-wider">Account</div>
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Profile</a>
                                <a href="{{ route('orders.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My Orders</a>
                                <a href="{{ route('messages.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Messages</a>
                                <a href="{{ route('wishlist') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Wishlist</a>
                                <a href="{{ route('addresses') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Addresses</a>
                                <hr class="my-1 border-gray-200">
                                <div class="px-4 py-2 text-xs text-gray-400 uppercase tracking-wider">Selling</div>
                                <a href="{{ route('me.store') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My Store</a>
                                <a href="{{ route('me.products.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">My Products</a>
                                <a href="{{ route('social.accounts') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Social Accounts</a>
                                <a href="{{ route('social.scheduled-posts') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Scheduled Posts</a>
                                <template x-if="$store.auth.user?.role === 'admin'">
                                    <div>
                                        <hr class="my-1 border-gray-200">
                                        <div class="px-4 py-2 text-xs text-gray-400 uppercase tracking-wider">Admin</div>
                                        <a href="{{ route('admin.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Dashboard</a>
                                    </div>
                                </template>
                                <hr class="my-1 border-gray-200">
                                <button @click="$store.auth.logout()" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-50">Sign out</button>
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

    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} ReMarket. All rights reserved.
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
