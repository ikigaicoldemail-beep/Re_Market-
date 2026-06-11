@extends('layouts.app')

@section('title', 'Terms of Service')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12 prose">
    <h1 class="text-3xl font-semibold text-gray-900 mb-6">Terms of Service</h1>

    <p class="text-sm text-gray-500 mb-8">Last updated: {{ date('F j, Y') }}</p>

    <section class="space-y-6 text-gray-700 text-sm leading-relaxed">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">1. Acceptance of terms</h2>
            <p>By creating an account or using ReMarket, you agree to these Terms of Service. If you do not agree, please do not use the marketplace.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">2. Your account</h2>
            <p>You are responsible for keeping your login details secure and for all activity that happens under your account. You must provide accurate information and be old enough to form a binding contract in your jurisdiction.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">3. Listings and sales</h2>
            <p>Sellers are responsible for the accuracy of their listings, including descriptions, photos, condition, and price. Items must be legal to sell and must not infringe anyone's rights. ReMarket is a platform that connects buyers and sellers and is not a party to the transactions between them.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">4. Prohibited conduct</h2>
            <p>You agree not to post misleading, fraudulent, illegal, or offensive content, not to interfere with the platform, and not to misuse other users' information. We may remove content or suspend accounts that violate these terms.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">5. Content you provide</h2>
            <p>You retain ownership of the content you post. By posting, you grant ReMarket a license to display and distribute that content as needed to operate the marketplace and, where you opt in, to publish to connected social platforms.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">6. Disclaimer and liability</h2>
            <p>The service is provided "as is" without warranties of any kind. To the extent permitted by law, ReMarket is not liable for disputes between users or for losses arising from use of the platform.</p>
        </div>

        <div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">7. Changes and contact</h2>
            <p>We may update these terms from time to time; continued use means you accept the changes. See our <a href="{{ route('privacy') }}" class="text-indigo-600 underline">Privacy Policy</a> for how we handle your data. Questions: <a href="mailto:services@ikigai2.com" class="text-indigo-600 underline">services@ikigai2.com</a>.</p>
        </div>
    </section>
</div>
@endsection
