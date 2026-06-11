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
            overflow: visible;
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
            border-radius: 7px 0 0 7px;
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
        .rm-search .rm-search-submit {
            border-radius: 0 7px 7px 0;
        }
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
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/>
</svg>
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
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
</svg>
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
                                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
</svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-900">Drag an image here</p>
                                    <p class="text-xs text-gray-500 my-2">or paste an image</p>
                                    <button type="button" @click="$refs.visualSearchInput.click()" class="rm-vsearch-upload">
                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
</svg>
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
                    <button type="submit" class="rm-search-submit">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
</svg>
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
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z"/>
  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z"/>
</svg>
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
                                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
</svg>
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
                                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/>
</svg>
                                </div>
                                <p class="text-sm font-medium text-gray-900">Drag an image here</p>
                                <p class="text-xs text-gray-500 my-2">or paste an image</p>
                                <button type="button" @click="$refs.mobileVisualSearchInput.click()" class="rm-vsearch-upload">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
</svg>
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
                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
</svg>
                        <span x-show="$store.compare.count > 0"
                              x-text="$store.compare.count"
                              class="rm-badge" style="display:none"></span>
                    </span>
                    <span class="hidden lg:inline text-xs" data-i18n="nav.compare">Compare</span>
                </a>

                {{-- Wishlist --}}
                <a href="{{ route('wishlist') }}" class="rm-ibtn hidden sm:inline-flex">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
</svg>
                    <span class="hidden lg:inline text-xs" data-i18n="nav.wishlist">Wishlist</span>
                </a>

                {{-- Language switcher --}}
                <div class="relative" x-data="{ open: false, locale: (window.currentLocale && window.currentLocale()) || 'en' }"
                     x-init="window.addEventListener('i18n:changed', e => locale = e.detail.locale)">
                    <button @click="open = !open" class="rm-lang">
                        <span x-text="locale === 'km' ? 'ខ្មែរ' : 'EN'"></span>
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
</svg>
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
                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
</svg>
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
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
</svg>
                            <span>Sell</span>
                        </a>

                        {{-- Avatar dropdown --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="rm-avatar-btn">
                                <span class="rm-avatar-circle"
                                      x-text="$store.auth.user?.name?.charAt(0)?.toUpperCase() || '?'"></span>
                                <span x-text="$store.auth.user?.name" class="hidden xl:inline font-medium max-w-[7rem] truncate"></span>
                                <svg class="w-3.5 h-3.5 hidden lg:inline" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
</svg>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition
                                 class="rm-dropdown" style="display:none;">

                                <a href="{{ route('profile') }}">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
</svg> Profile
                                </a>
                                <a href="{{ route('messages.index') }}">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/>
</svg> Messages
                                </a>
                                <a href="{{ route('wishlist') }}">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/>
</svg> Wishlist
                                </a>

                                <hr>

                                <a href="{{ route('me.products.create') }}" class="sell-link">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
</svg> Sell an Item
                                </a>
                                <a href="{{ route('me.products.index') }}">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
</svg> My Listings
                                </a>
                                <a href="{{ route('me.store') }}">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
</svg> My Store
                                </a>
                                <a href="{{ route('social.accounts') }}">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
  <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244"/>
</svg> Social Accounts
                                </a>

                                <template x-if="$store.auth.user?.role === 'admin'">
                                    <div>
                                        <hr>
                                        <a href="{{ route('admin.index') }}">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/>
  <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
</svg> Admin Dashboard
                                        </a>
                                    </div>
                                </template>

                                <hr>

                                <button @click="$store.auth.logout()" class="signout">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/>
</svg> Sign out
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
        <svg class="w-4 h-4 text-indigo-600 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
</svg>
        <span class="compare-label">
            <strong x-text="$store.compare.count"></strong>
            <span> product<span x-show="$store.compare.count !== 1" style="display:none">s</span></span>
        </span>
        <a href="{{ route('compare') }}" class="compare-go">Compare</a>
        <button @click="$store.compare.clear()" class="compare-clear" title="Clear">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
  <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z"/>
</svg>
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
                <a href="{{ route('about') }}">About</a>
                <a href="{{ route('help') }}">Help</a>
                <a href="{{ route('privacy') }}">Privacy</a>
                <a href="{{ route('terms') }}">Terms</a>
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
