@extends('layouts.main')
@section('content')
<div class="container mx-auto px-4 py-16 max-w-3xl">
    <h1 class="text-3xl font-bold text-blue-800 mb-8">{{ __('cgv.title') }}</h1>
    <h2 class="font-semibold text-blue-700 mb-4">{{ __('cgv.1_title') }}</h2>
    <p class="text-gray-600 mb-4">{{ __('cgv.1_text') }}</p>
    <h2 class="font-semibold text-blue-700 mb-4">{{ __('cgv.2_title') }}</h2>
    <p class="text-gray-600 mb-4">{{ __('cgv.2_text') }}</p>
    <h2 class="font-semibold text-blue-700 mb-4">{{ __('cgv.3_title') }}</h2>
    <ul class="list-disc list-inside text-gray-600 mb-4">
        <li>{{ __('cgv.3_1') }}</li>
        <li>{{ __('cgv.3_2') }}</li>
        <li>{{ __('cgv.3_3') }}</li>
    </ul>
    <h2 class="font-semibold text-blue-700 mb-4">{{ __('cgv.4_title') }}</h2>
    <p class="text-gray-600 mb-4">{{ __('cgv.4_text') }}</p>
</div>
@endsection 