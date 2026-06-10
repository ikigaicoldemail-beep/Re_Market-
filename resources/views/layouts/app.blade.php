<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ReMarket – Second-Hand Marketplace')</title>
    @yield('meta')
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ─── Reset / base ─────────────────────────────────────── */
        *, *::before, *::after { box-sizing: border-box; }

        /* ─── NAV SHELL ─────────────────────────────────────────── */
        .rm-header {
            position: sticky;
            top: 0;
            z-index: 40;
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Top bar */
        .rm-topbar {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            height: 60px;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Logo */
        .rm-logo {
            flex-shrink: 0;
            font-size: 1.25rem;
            font-weight: 600;
            letter-spacing: -0.4px;
            text-decoration: none;
            line-height: 1;
        }
        .rm-logo .re  { color: #4f46e5; }
        .rm-logo .mkt { color: #111827; }

        /* Search */
        .rm-search {
            flex: 1;
            max-width: 500px;
            display: flex;
            align-items: stretch;
            border: 1.5px solid #e0e7ff;
            border-radius: 8px;
            overflow: hidden;
            transition: border-color .15s;
            background: #f9fafb;
        }
        .rm-search:focus-within {
            border-color: #6366f1;
            background: #fff;
        }
        .rm-search input {
            flex: 1;
            padding: .55rem .875rem;
            font-size: .875rem;
            background: transparent;
            border: none;
            outline: none;
            color: #111827;
            font-family: inherit;
        }
        .rm-search input::placeholder { color: #9ca3af; }
        .rm-search button {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: .35rem;
            padding: 0 1.1rem;
            background: #4f46e5;
            color: #fff;
            font-size: .8125rem;
            font-weight: 600;
            font-family: inherit;
            border: none;
            cursor: pointer;
            transition: background .15s;
        }
        .rm-search button:hover { background: #4338ca; }
        .rm-vsearch-wrap {
            position: relative;
            display: flex;
            border-left: 1px solid #e0e7ff;
        }
        .rm-search .rm-vsearch-btn,
        .rm-vsearch-btn {
            width: 42px;
            padding: 0;
            background: transparent;
            color: #6b7280;
            justify-content: center;
        }
        .rm-search .rm-vsearch-btn:hover,
        .rm-search .rm-vsearch-btn.is-open,
        .rm-vsearch-btn:hover,
        .rm-vsearch-btn.is-open {
            background: #eef2ff;
            color: #4f46e5;
        }
        .rm-vsearch-popover {
            position: absolute;
            right: 0;
            top: calc(100% + .75rem);
            width: 24rem;
            max-width: calc(100vw - 2rem);
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, .12);
            z-index: 60;
            padding: 1rem;
        }
        .rm-vsearch-popover::before {
            content: "";
            position: absolute;
            right: 1rem;
            top: -.5rem;
            width: 1rem;
            height: 1rem;
            background: #fff;
            border-left: 1px solid #e5e7eb;
            border-top: 1px solid #e5e7eb;
            transform: rotate(45deg);
        }
        .rm-vsearch-drop {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            background: #f9fafb;
            padding: 1.5rem 1rem;
            text-align: center;
            transition: background .15s, border-color .15s;
        }
        .rm-vsearch-drop.is-dragging {
            border-color: #6366f1;
            background: #eef2ff;
        }
        .rm-search .rm-vsearch-upload,
        .rm-vsearch-upload {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .4rem;
            border-radius: 7px;
            background: #4f46e5;
            color: #fff;
            padding: .55rem .9rem;
            font-size: .8125rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            font-family: inherit;
        }
        .rm-search .rm-vsearch-upload:hover,
        .rm-vsearch-upload:hover { background: #4338ca; }
        .rm-search .rm-vsearch-close,
        .rm-vsearch-close {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border: none;
            border-radius: 6px;
            background: transparent;
            color: #9ca3af;
            cursor: pointer;
        }
        .rm-search .rm-vsearch-close:hover,
        .rm-vsearch-close:hover { background: #f3f4f6; color: #374151; }

        /* Right actions cluster */
        .rm-actions {
            display: flex;
            align-items: center;
            gap: .25rem;
            margin-left: auto;
        }

        /* Generic icon-button */
        .rm-ibtn {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            padding: .4rem .6rem;
            border-radius: 7px;
            border: none;
            background: transparent;
            color: #4b5563;
            font-size: .8125rem;
            font-family: inherit;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;
            transition: background .12s, color .12s;
            position: relative;
        }
        .rm-ibtn:hover { background: #f3f4f6; color: #4f46e5; }
        .rm-ibtn svg { width: 1.125rem; height: 1.125rem; flex-shrink: 0; }

        /* Badge on icon */
        .rm-badge {
            position: absolute;
            top: 2px; right: 2px;
            background: #4f46e5;
            color: #fff;
            font-size: .625rem;
            font-weight: 700;
            border-radius: 999px;
            min-width: 15px;
            height: 15px;
            padding: 0 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        /* Vertical divider */
        .rm-vdiv {
            width: 1px;
            height: 20px;
            background: #e5e7eb;
            flex-shrink: 0;
            margin: 0 .25rem;
        }

        /* Language picker */
        .rm-lang {
            display: inline-flex;
            align-items: center;
            gap: .25rem;
            padding: .35rem .65rem;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            font-size: .8125rem;
            color: #4b5563;
            background: transparent;
            cursor: pointer;
            font-family: inherit;
            transition: border-color .12s, color .12s;
        }
        .rm-lang:hover { border-color: #6366f1; color: #4f46e5; }
        .rm-lang svg { width: .75rem; height: .75rem; }

        /* Sell CTA */
        .rm-sell {
            display: inline-flex;
            align-items: center;
            gap: .35rem;
            padding: .45rem .9rem;
            background: #4f46e5;
            color: #fff;
            font-size: .8125rem;
            font-weight: 600;
            font-family: inherit;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            text-decoration: none;
            transition: background .15s;
            white-space: nowrap;
        }
        .rm-sell:hover { background: #4338ca; }
        .rm-sell svg { width: .875rem; height: .875rem; }

        /* Avatar button */
        .rm-avatar-btn {
            display: inline-flex;
            align-items: center;
            gap: .4rem;
            padding: .3rem .45rem;
            border: none;
            background: transparent;
            border-radius: 8px;
            cursor: pointer;
            font-family: inherit;
            color: #374151;
            font-size: .8125rem;
            transition: background .12s;
        }
        .rm-avatar-btn:hover { background: #f3f4f6; }
        .rm-avatar-btn svg { width: .875rem; height: .875rem; color: #6b7280; }

        /* Avatar circle */
        .rm-avatar-circle {
            width: 32px; height: 32px;
            background: #e0e7ff;
            color: #4338ca;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .75rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        /* Auth buttons */
        .rm-signin {
            padding: .4rem .75rem;
            font-size: .8125rem;
            color: #4b5563;
            text-decoration: none;
            border-radius: 7px;
            font-family: inherit;
            transition: background .12s, color .12s;
        }
        .rm-signin:hover { background: #f3f4f6; color: #4f46e5; }
        .rm-signup {
            padding: .4rem .85rem;
            font-size: .8125rem;
            font-weight: 600;
            background: #4f46e5;
            color: #fff;
            text-decoration: none;
            border-radius: 7px;
            transition: background .15s;
        }
        .rm-signup:hover { background: #4338ca; }

        /* ─── SUB-NAV ────────────────────────────────────────────── */
        .rm-subnav {
            border-top: 1px solid #f3f4f6;
        }
        .rm-subnav-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            height: 38px;
            gap: 0;
        }
        .rm-snlink {
            height: 100%;
            display: inline-flex;
            align-items: center;
            padding: 0 .875rem;
            font-size: .8125rem;
            color: #6b7280;
            text-decoration: none;
            border-bottom: 2px solid transparent;
            transition: color .12s, border-color .12s;
        }
        .rm-snlink:first-child { padding-left: 0; }
        .rm-snlink:hover,
        .rm-snlink.active { color: #4f46e5; border-bottom-color: #4f46e5; }

        /* ─── DROPDOWN MENU ──────────────────────────────────────── */
        .rm-dropdown {
            position: absolute;
            right: 0;
            top: calc(100% + 6px);
            width: 13rem;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: .375rem 0;
            z-index: 50;
            box-shadow: 0 4px 16px -2px rgba(0,0,0,.1);
        }
        .rm-dropdown a,
        .rm-dropdown button {
            display: flex;
            align-items: center;
            gap: .5rem;
            width: 100%;
            padding: .5rem 1rem;
            font-size: .8125rem;
            color: #374151;
            text-decoration: none;
            background: transparent;
            border: none;
            cursor: pointer;
            font-family: inherit;
            text-align: left;
            transition: background .1s;
        }
        .rm-dropdown a:hover,
        .rm-dropdown button:hover { background: #f9fafb; }
        .rm-dropdown a svg,
        .rm-dropdown button svg { width: .875rem; height: .875rem; color: #9ca3af; flex-shrink: 0; }
        .rm-dropdown .sell-link { color: #4f46e5; font-weight: 600; }
        .rm-dropdown .sell-link:hover { background: #eef2ff; }
        .rm-dropdown .sell-link svg { color: #4f46e5; }
        .rm-dropdown .signout { color: #dc2626; }
        .rm-dropdown .signout:hover { background: #fef2f2; }
        .rm-dropdown .signout svg { color: #dc2626; }
        .rm-dropdown hr { border: none; border-top: 1px solid #f3f4f6; margin: .25rem 0; }

        /* ─── COMPARE BAR ────────────────────────────────────────── */
        .rm-compare-bar {
            position: fixed;
            bottom: 1.25rem;
            right: 1.25rem;
            z-index: 40;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: .625rem 1rem;
            display: flex;
            align-items: center;
            gap: .75rem;
        }
        .rm-compare-bar .compare-label {
            font-size: .8125rem;
            color: #4b5563;
        }
        .rm-compare-bar .compare-label strong { color: #111827; }
        .rm-compare-bar .compare-go {
            font-size: .75rem;
            font-weight: 600;
            background: #4f46e5;
            color: #fff;
            padding: .35rem .75rem;
            border-radius: 6px;
            text-decoration: none;
            transition: background .15s;
        }
        .rm-compare-bar .compare-go:hover { background: #4338ca; }
        .rm-compare-bar .compare-clear {
            display: flex;
            align-items: center;
            background: transparent;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            padding: 0;
            transition: color .12s;
        }
        .rm-compare-bar .compare-clear:hover { color: #dc2626; }
        .rm-compare-bar .compare-clear svg { width: 1rem; height: 1rem; }

        /* ─── FOOTER ─────────────────────────────────────────────── */
        .rm-footer {
            background: #fff;
            border-top: 1px solid #f3f4f6;
            margin-top: 3rem;
        }
        .rm-footer-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 1.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .rm-footer-logo { font-size: .9375rem; font-weight: 600; text-decoration: none; }
        .rm-footer-logo .re { color: #4f46e5; }
        .rm-footer-logo .mkt { color: #111827; }
        .rm-footer-copy { font-size: .8125rem; color: #9ca3af; }
        .rm-footer-links { display: flex; gap: 1.25rem; }
        .rm-footer-links a {
            font-size: .8125rem;
            color: #6b7280;
            text-decoration: none;
            transition: color .12s;
        }
        .rm-footer-links a:hover { color: #4f46e5; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased min-h-screen flex flex-col">

    {{-- ═══════════════════════════════════════════════════════════
         HEADER
    ═══════════════════════════════════════════════════════════ --}}
    <header class="rm-header">

        {{-- ── Top bar ──────────────────────────────────────────── --}}
        <div class="rm-topbar">

            {{-- Logo --}}
            <a href="{{ route('home') }}" class="rm-logo">
                <span class="re">Re</span><span class="mkt">Market</span>
            </a>

            {{-- Search --}}
            <div class="rm-search flex-1 max-w-lg hidden md:flex" x-data="visualSearchLauncher()">
                <form action="{{ route('home') }}" method="GET" class="contents">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Search for products…"
                        data-i18n="filters.search_placeholder"
                        data-i18n-placeholder
                    >
                    <div class="rm-vsearch-wrap">
                        <button type="button"
                                @click="open = !open"
                                :class="open ? 'is-open' : ''"
                                class="rm-vsearch-btn"
                                title="Search by photo"
                                aria-label="Search by photo">
                            <x-heroicon-o-camera class="w-5 h-5"/>
                        </button>
                        <div x-show="open"
                             x-transition.origin.top.right
                             @click.outside="open = false"
                             @paste.window="dispatchPastedFile($event)"
                             class="rm-vsearch-popover"
                             style="display:none">
                            <div style="position:relative;">
                                <div class="flex items-start justify-between gap-3 mb-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">Search by image</h3>
                                        <p class="text-sm text-gray-500 mt-0.5">Find similar listings from a photo.</p>
                                    </div>
                                    <button type="button" @click="open = false" class="rm-vsearch-close" aria-label="Close image search">
                                        <x-heroicon-m-x-mark class="w-5 h-5"/>
                                    </button>
                                </div>
                                <div @dragenter.prevent.stop="dragging = true"
                                     @dragover.prevent.stop="dragging = true; $event.dataTransfer.dropEffect = 'copy'"
                                     @dragleave.prevent.stop="dragging = false"
                                     @drop.prevent.stop="dispatchDroppedFile($event)"
                                     @paste.stop="dispatchPastedFile($event)"
                                     :class="dragging ? 'is-dragging' : ''"
                                     class="rm-vsearch-drop">
                                    <div class="w-12 h-12 mx-auto rounded-full bg-white border border-gray-200 text-indigo-600 flex items-center justify-center mb-3">
                                        <x-heroicon-o-photo class="w-6 h-6"/>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Drag an image here</p>
                                    <p class="text-xs text-gray-500 my-2">or paste an image</p>
                                    <button type="button" @click="$refs.visualSearchInput.click()" class="rm-vsearch-upload">
                                        <x-heroicon-o-arrow-up-tray class="w-4 h-4"/>
                                        Upload photo
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input x-ref="visualSearchInput"
                               type="file"
                               accept="image/png,image/jpeg,image/webp"
                               capture="environment"
                               @change="dispatchVisualFile($event)"
                               class="hidden">
                    </div>
                    <button type="submit">
                        <x-heroicon-o-magnifying-glass class="w-4 h-4"/>
                        <span class="hidden sm:inline" data-i18n="nav.search">Search</span>
                    </button>
                </form>
            </div>

            {{-- Right-side actions --}}
            <div class="rm-actions" x-data>

                {{-- Mobile visual search --}}
                <div class="md:hidden relative" x-data="visualSearchLauncher()">
                    <button type="button"
                            @click="open = !open"
                            :class="open ? 'text-indigo-700 bg-indigo-50' : ''"
                            class="rm-ibtn"
                            title="Search by photo"
                            aria-label="Search by photo">
                        <x-heroicon-o-camera class="w-5 h-5"/>
                    </button>
                    <div x-show="open"
                         x-transition.origin.top.right
                         @click.outside="open = false"
                         @paste.window="dispatchPastedFile($event)"
                         class="rm-vsearch-popover"
                         style="display:none; right:0;">
                        <div style="position:relative;">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div>
                                    <h3 class="text-base font-semibold text-gray-900">Search by image</h3>
                                    <p class="text-sm text-gray-500 mt-0.5">Find similar listings from a photo.</p>
                                </div>
                                <button type="button" @click="open = false" class="rm-vsearch-close" aria-label="Close image search">
                                    <x-heroicon-m-x-mark class="w-5 h-5"/>
                                </button>
                            </div>
                            <div @dragenter.prevent.stop="dragging = true"
                                 @dragover.prevent.stop="dragging = true; $event.dataTransfer.dropEffect = 'copy'"
                                 @dragleave.prevent.stop="dragging = false"
                                 @drop.prevent.stop="dispatchDroppedFile($event)"
                                 @paste.stop="dispatchPastedFile($event)"
                                 :class="dragging ? 'is-dragging' : ''"
                                 class="rm-vsearch-drop">
                                <div class="w-12 h-12 mx-auto rounded-full bg-white border border-gray-200 text-indigo-600 flex items-center justify-center mb-3">
                                    <x-heroicon-o-photo class="w-6 h-6"/>
                                </div>
                                <p class="text-sm font-medium text-gray-900">Drag an image here</p>
                                <p class="text-xs text-gray-500 my-2">or paste an image</p>
                                <button type="button" @click="$refs.mobileVisualSearchInput.click()" class="rm-vsearch-upload">
                                    <x-heroicon-o-arrow-up-tray class="w-4 h-4"/>
                                    Upload photo
                                </button>
                            </div>
                        </div>
                    </div>
                    <input x-ref="mobileVisualSearchInput"
                           type="file"
                           accept="image/png,image/jpeg,image/webp"
                           capture="environment"
                           @change="dispatchVisualFile($event)"
                           class="hidden">
                </div>

                {{-- Compare --}}
                <a href="{{ route('compare') }}" class="rm-ibtn hidden sm:inline-flex" :aria-label="window.t ? window.t('nav.compare') : 'Compare'">
                    <span style="position:relative;display:inline-flex;">
                        <x-heroicon-o-arrows-right-left class="w-5 h-5"/>
                        <span x-show="$store.compare.count > 0"
                              x-text="$store.compare.count"
                              class="rm-badge" style="display:none"></span>
                    </span>
                    <span class="hidden lg:inline text-xs" data-i18n="nav.compare">Compare</span>
                </a>

                {{-- Wishlist --}}
                <a href="{{ route('wishlist') }}" class="rm-ibtn hidden sm:inline-flex">
                    <x-heroicon-o-heart class="w-5 h-5"/>
                    <span class="hidden lg:inline text-xs" data-i18n="nav.wishlist">Wishlist</span>
                </a>

                {{-- Language switcher --}}
                <div class="relative" x-data="{ open: false, locale: (window.currentLocale && window.currentLocale()) || 'en' }"
                     x-init="window.addEventListener('i18n:changed', e => locale = e.detail.locale)">
                    <button @click="open = !open" class="rm-lang">
                        <span x-text="locale === 'km' ? 'ខ្មែរ' : 'EN'"></span>
                        <x-heroicon-m-chevron-down class="w-3 h-3"/>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition
                         class="rm-dropdown" style="display:none; width:9rem;">
                        <button @click="window.setLocale('en'); open = false"
                            :class="locale === 'en' ? 'bg-indigo-50 text-indigo-700' : ''"
                            class="text-left">🇺🇸 English</button>
                        <button @click="window.setLocale('km'); open = false"
                            :class="locale === 'km' ? 'bg-indigo-50 text-indigo-700' : ''"
                            class="text-left">🇰🇭 ខ្មែរ</button>
                    </div>
                </div>

                {{-- Messages (logged-in only) --}}
                <template x-if="$store.auth.loggedIn">
                    <a href="{{ route('messages.index') }}" class="rm-ibtn"
                       :title="window.t ? window.t('nav.messages') : 'Messages'">
                        <span style="position:relative;display:inline-flex;">
                            <x-heroicon-o-chat-bubble-left-right class="w-5 h-5"/>
                            <span x-show="$store.chat.unread > 0"
                                  x-text="$store.chat.unread"
                                  class="rm-badge" style="display:none"></span>
                        </span>
                    </a>
                </template>

                {{-- Notifications --}}
                <template x-if="$store.auth.loggedIn">
                    @include('components.notification-bell')
                </template>

                <div class="rm-vdiv"></div>

                {{-- ── Guest: Sign in / Sign up ──────────────────── --}}
                <template x-if="!$store.auth.loggedIn">
                    <div class="flex items-center gap-1.5">
                        <a href="{{ route('login') }}" class="rm-signin" data-i18n="nav.signin">Sign in</a>
                        <a href="{{ route('register') }}" class="rm-signup" data-i18n="nav.signup">Sign up</a>
                    </div>
                </template>

                {{-- ── Logged-in: Sell + Avatar dropdown ─────────── --}}
                <template x-if="$store.auth.loggedIn">
                    <div class="flex items-center gap-1.5">

                        {{-- Sell button --}}
                        <a href="{{ route('me.products.create') }}" class="rm-sell hidden sm:inline-flex">
                            <x-heroicon-o-plus-circle class="w-4 h-4"/>
                            <span>Sell</span>
                        </a>

                        {{-- Avatar dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="rm-avatar-btn">
                                <span class="rm-avatar-circle"
                                      x-text="$store.auth.user?.name?.charAt(0)?.toUpperCase() || '?'"></span>
                                <span x-text="$store.auth.user?.name" class="hidden xl:inline font-medium max-w-[7rem] truncate"></span>
                                <x-heroicon-m-chevron-down class="w-3.5 h-3.5 hidden lg:inline"/>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="rm-dropdown" style="display:none;">

                                <a href="{{ route('profile') }}">
                                    <x-heroicon-o-user class="w-4 h-4"/> Profile
                                </a>
                                <a href="{{ route('messages.index') }}">
                                    <x-heroicon-o-chat-bubble-left-right class="w-4 h-4"/> Messages
                                </a>
                                <a href="{{ route('wishlist') }}">
                                    <x-heroicon-o-heart class="w-4 h-4"/> Wishlist
                                </a>

                                <hr>

                                <a href="{{ route('me.products.create') }}" class="sell-link">
                                    <x-heroicon-o-plus-circle class="w-4 h-4"/> Sell an Item
                                </a>
                                <a href="{{ route('me.products.index') }}">
                                    <x-heroicon-o-squares-2x2 class="w-4 h-4"/> My Listings
                                </a>
                                <a href="{{ route('me.store') }}">
                                    <x-heroicon-o-building-storefront class="w-4 h-4"/> My Store
                                </a>

                                <template x-if="$store.auth.user?.role === 'admin'">
                                    <div>
                                        <hr>
                                        <a href="{{ route('admin.index') }}">
                                            <x-heroicon-o-cog-6-tooth class="w-4 h-4"/> Admin Dashboard
                                        </a>
                                    </div>
                                </template>

                                <hr>

                                <button @click="$store.auth.logout()" class="signout">
                                    <x-heroicon-o-arrow-right-on-rectangle class="w-4 h-4"/> Sign out
                                </button>
                            </div>
                        </div>
                    </div>
                </template>

            </div>{{-- /.rm-actions --}}
        </div>{{-- /.rm-topbar --}}

        {{-- ── Sub-nav ──────────────────────────────────────────── --}}
        <div class="rm-subnav">
            <div class="rm-subnav-inner">
                <a href="{{ route('stores.index') }}"
                   class="rm-snlink {{ request()->routeIs('stores.*') ? 'active' : '' }}"
                   data-i18n="nav.shops">Shops</a>
                <a href="{{ route('home') }}#categories"
                   class="rm-snlink"
                   data-i18n="home.categories">Categories</a>
            </div>
        </div>

    </header>{{-- /.rm-header --}}


    {{-- ═══════════════════════════════════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════════════════════════════════ --}}
    <main class="flex-1">
        @yield('content')
    </main>


    {{-- ═══════════════════════════════════════════════════════════
         FLOATING COMPARE BAR
    ═══════════════════════════════════════════════════════════ --}}
    <div x-data x-show="$store.compare.count > 0"
         class="rm-compare-bar"
         style="display:none;">
        <x-heroicon-o-arrows-right-left class="w-4 h-4 text-indigo-600 flex-shrink-0"/>
        <span class="compare-label">
            <strong x-text="$store.compare.count"></strong>
            <span> product<span x-show="$store.compare.count !== 1" style="display:none">s</span></span>
        </span>
        <a href="{{ route('compare') }}" class="compare-go">Compare</a>
        <button @click="$store.compare.clear()" class="compare-clear" title="Clear">
            <x-heroicon-m-x-mark class="w-4 h-4"/>
        </button>
    </div>


    {{-- ═══════════════════════════════════════════════════════════
         FOOTER
    ═══════════════════════════════════════════════════════════ --}}
    <footer class="rm-footer">
        <div class="rm-footer-inner">
            <a href="{{ route('home') }}" class="rm-footer-logo">
                <span class="re">Re</span><span class="mkt">Market</span>
            </a>
            <nav class="rm-footer-links">
                <a href="#">About</a>
                <a href="#">Help</a>
                <a href="#">Privacy</a>
                <a href="#">Terms</a>
            </nav>
            <p class="rm-footer-copy">&copy; {{ date('Y') }} ReMarket. All rights reserved.</p>
        </div>
    </footer>

    <script>
        function visualSearchLauncher() {
            return {
                open: false,
                dragging: false,
                dispatchVisualFile(event) {
                    const file = event.target.files?.[0];
                    event.target.value = '';
                    this.dispatchFile(file);
                },
                dispatchDroppedFile(event) {
                    this.dragging = false;
                    this.dispatchFile(this.firstImage(event.dataTransfer?.files));
                },
                dispatchPastedFile(event) {
                    const file = this.firstImage(event.clipboardData?.files) || this.firstImageItem(event.clipboardData?.items);
                    if (!file) return;
                    event.preventDefault();
                    this.dispatchFile(file);
                },
                dispatchFile(file) {
                    if (!file || !file.type?.startsWith('image/')) return;

                    window.__pendingVisualSearchFile = file;
                    window.dispatchEvent(new CustomEvent('visual-search:file', {
                        detail: { file },
                    }));
                    this.open = false;
                    this.dragging = false;
                },
                firstImage(files) {
                    return Array.from(files || []).find(file => file.type?.startsWith('image/'));
                },
                firstImageItem(items) {
                    const item = Array.from(items || []).find(entry => entry.kind === 'file' && entry.type?.startsWith('image/'));
                    return item ? item.getAsFile() : null;
                },
            };
        }
    </script>

    @stack('scripts')
</body>
</html>
