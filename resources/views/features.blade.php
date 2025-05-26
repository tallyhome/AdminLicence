@extends('layouts.main')
@section('content')
<div class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-blue-800 mb-8">{{ __('features.title') }}</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-blue-700 mb-2">{{ __('features.license_management') }}</h2>
            <p class="text-gray-600">{{ __('features.license_management_desc') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-blue-700 mb-2">{{ __('features.api') }}</h2>
            <p class="text-gray-600">{{ __('features.api_desc') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-blue-700 mb-2">{{ __('features.updates') }}</h2>
            <p class="text-gray-600">{{ __('features.updates_desc') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-blue-700 mb-2">{{ __('features.security') }}</h2>
            <p class="text-gray-600">{{ __('features.security_desc') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-blue-700 mb-2">{{ __('features.integrations') }}</h2>
            <p class="text-gray-600">{{ __('features.integrations_desc') }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="font-semibold text-blue-700 mb-2">{{ __('features.support') }}</h2>
            <p class="text-gray-600">{{ __('features.support_desc') }}</p>
        </div>
    </div>
</div>
@endsection 