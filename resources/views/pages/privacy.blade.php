@extends('layouts.app')

@section('title', 'Privacy Policy')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 prose">
    <h1 class="text-3xl font-semibold text-gray-900 mb-6">Privacy Policy</h1>

    <p class="text-sm text-gray-500 mb-8">Last updated: {{ date('F j, Y') }}</p>

    <section class="space-y-6 text-gray-700 text-sm leading-relaxed">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">1. Information we collect</h2>
            <p>ReMarket collects the information you provide when you create an account (name, email, phone), list a product (titles, descriptions, images, location), or connect a social account (Facebook/TikTok profile id and access token).</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">2. How we use it</h2>
            <p>We use this information to operate the marketplace (matching buyers and sellers, processing orders, sending notifications) and to post on your behalf to social platforms when you opt in.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">3. Social account data</h2>
            <p>When you connect a Facebook Page, we store the Page ID, name, and a Page Access Token (encrypted). We use the token only to publish posts you authorize. You can disconnect at any time from <a href="/social/accounts" class="text-indigo-600 underline">Social Accounts</a>, which deletes the stored token.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">4. Data deletion</h2>
            <p>To request deletion of your account and all associated data, sign in and email <a href="mailto:services@ikigai2.com" class="text-indigo-600 underline">services@ikigai2.com</a> with the subject "Delete my account". We will remove your data within 30 days.</p>
            <p class="mt-2">You can also disconnect a social account independently from the Social Accounts page; this immediately revokes our stored token and removes your Page from our records.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">5. Contact</h2>
            <p>Questions about this policy: <a href="mailto:services@ikigai2.com" class="text-indigo-600 underline">services@ikigai2.com</a></p>
        </div>
    </section>
</div>
@endsection
