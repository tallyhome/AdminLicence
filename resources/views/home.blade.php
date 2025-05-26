@extends('layouts.main')
@section('content')
<div class="bg-gradient-to-b from-blue-50 to-white py-16">
    <div class="container mx-auto px-4 flex flex-col lg:flex-row items-center justify-between gap-12">
        <div class="max-w-xl">
            <h1 class="text-4xl font-bold mb-4 text-blue-800">{{ __('landing.hero_title') }}</h1>
            <p class="text-lg text-gray-700 mb-6">{{ __('landing.hero_subtitle') }}</p>
            <a href="{{ route('direct.admin.login') }}" class="inline-block px-6 py-3 bg-blue-600 text-white rounded shadow hover:bg-blue-700 font-semibold">{{ __('landing.cta_login') }}</a>
        </div>
        <div class="flex-1 flex justify-center">
            <img src="/images/logo.png" alt="Dashboard AdminLicence" class="rounded-lg shadow-lg w-full max-w-md">
        </div>
    </div>
</div>
<div class="container mx-auto px-4 py-12">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
        <div>
            <div class="text-3xl font-bold text-blue-700">520+</div>
            <div class="text-gray-600 mt-2">{{ __('landing.stats_clients') }}</div>
        </div>
        <div>
            <div class="text-3xl font-bold text-blue-700">50+</div>
            <div class="text-gray-600 mt-2">{{ __('landing.stats_ratings') }}</div>
        </div>
        <div>
            <div class="text-3xl font-bold text-blue-700">67</div>
            <div class="text-gray-600 mt-2">{{ __('landing.stats_projects') }}</div>
        </div>
    </div>
</div>
<div class="bg-blue-50 py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-center mb-10">{{ __('landing.features_title') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-blue-700 mb-2">{{ __('landing.feature_licenses') }}</h3>
                <p class="text-gray-600">{{ __('landing.feature_licenses_desc') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-blue-700 mb-2">{{ __('landing.feature_api') }}</h3>
                <p class="text-gray-600">{{ __('landing.feature_api_desc') }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-blue-700 mb-2">{{ __('landing.feature_updates') }}</h3>
                <p class="text-gray-600">{{ __('landing.feature_updates_desc') }}</p>
            </div>
        </div>
    </div>
</div>
<div class="container mx-auto px-4 py-16">
    <h2 class="text-2xl font-bold text-center mb-10">{{ __('landing.api_title') }}</h2>
    <div class="flex flex-col md:flex-row gap-8 items-center justify-center">
        <div class="bg-gray-100 rounded-lg p-6 flex-1">
            <h3 class="font-semibold text-blue-700 mb-2">{{ __('landing.api_verify') }}</h3>
            <pre class="bg-gray-900 text-green-400 rounded p-4 text-xs overflow-x-auto">curl -X POST https://adminlicence.fr/api/verify_license \ 
-H "API-KEY: VOTRE_CLE_API" \ 
-d '{"license_key": "XXXX-XXXX-XXXX-XXXX"}'</pre>
        </div>
        <div class="bg-gray-100 rounded-lg p-6 flex-1">
            <h3 class="font-semibold text-blue-700 mb-2">{{ __('landing.api_update') }}</h3>
            <pre class="bg-gray-900 text-green-400 rounded p-4 text-xs overflow-x-auto">curl -X POST https://adminlicence.fr/api/check_update \ 
-H "API-KEY: VOTRE_CLE_API" \ 
-d '{"product_id": "123"}'</pre>
        </div>
    </div>
</div>
<div class="bg-blue-50 py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-center mb-10">{{ __('landing.pricing_title') }}</h2>
        <div class="flex flex-col md:flex-row gap-8 justify-center">
            <div class="bg-white rounded-lg shadow p-8 flex-1 max-w-sm">
                <div class="text-xl font-bold text-blue-700 mb-2">{{ __('landing.pricing_basic') }}</div>
                <div class="text-3xl font-bold mb-4">$65</div>
                <ul class="text-gray-600 mb-6 text-left list-disc list-inside">
                    <li>{{ __('landing.pricing_basic_1') }}</li>
                    <li>{{ __('landing.pricing_basic_2') }}</li>
                    <li>{{ __('landing.pricing_basic_3') }}</li>
                </ul>
                <a href="#" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('landing.pricing_buy') }}</a>
            </div>
            <div class="bg-white rounded-lg shadow p-8 flex-1 max-w-sm border-2 border-blue-600">
                <div class="text-xl font-bold text-blue-700 mb-2">{{ __('landing.pricing_extended') }}</div>
                <div class="text-3xl font-bold mb-4">$240</div>
                <ul class="text-gray-600 mb-6 text-left list-disc list-inside">
                    <li>{{ __('landing.pricing_extended_1') }}</li>
                    <li>{{ __('landing.pricing_extended_2') }}</li>
                    <li>{{ __('landing.pricing_extended_3') }}</li>
                </ul>
                <a href="#" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('landing.pricing_buy') }}</a>
            </div>
        </div>
    </div>
</div>
<div class="container mx-auto px-4 py-16">
    <h2 class="text-2xl font-bold text-center mb-10">{{ __('landing.testimonials_title') }}</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-lg font-semibold text-blue-700 mb-2">{{ __('landing.testimonial_1_name') }}</div>
            <div class="text-gray-600 mb-2">{{ __('landing.testimonial_1_text') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-lg font-semibold text-blue-700 mb-2">{{ __('landing.testimonial_2_name') }}</div>
            <div class="text-gray-600 mb-2">{{ __('landing.testimonial_2_text') }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-lg font-semibold text-blue-700 mb-2">{{ __('landing.testimonial_3_name') }}</div>
            <div class="text-gray-600 mb-2">{{ __('landing.testimonial_3_text') }}</div>
        </div>
    </div>
</div>
<div class="bg-blue-50 py-16">
    <div class="container mx-auto px-4">
        <h2 class="text-2xl font-bold text-center mb-10">{{ __('landing.faq_title') }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h3 class="font-semibold text-blue-700 mb-2">{{ __('landing.faq_q1') }}</h3>
                <p class="text-gray-600">{{ __('landing.faq_a1') }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-blue-700 mb-2">{{ __('landing.faq_q2') }}</h3>
                <p class="text-gray-600">{{ __('landing.faq_a2') }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-blue-700 mb-2">{{ __('landing.faq_q3') }}</h3>
                <p class="text-gray-600">{{ __('landing.faq_a3') }}</p>
            </div>
            <div>
                <h3 class="font-semibold text-blue-700 mb-2">{{ __('landing.faq_q4') }}</h3>
                <p class="text-gray-600">{{ __('landing.faq_a4') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection 