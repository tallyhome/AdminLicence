@extends('layouts.main')
@section('content')
<div class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-blue-800 mb-8">{{ __('faq.title') }}</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div>
            <h2 class="font-semibold text-blue-700 mb-2">{{ __('faq.q1') }}</h2>
            <p class="text-gray-600">{{ __('faq.a1') }}</p>
        </div>
        <div>
            <h2 class="font-semibold text-blue-700 mb-2">{{ __('faq.q2') }}</h2>
            <p class="text-gray-600">{{ __('faq.a2') }}</p>
        </div>
        <div>
            <h2 class="font-semibold text-blue-700 mb-2">{{ __('faq.q3') }}</h2>
            <p class="text-gray-600">{{ __('faq.a3') }}</p>
        </div>
        <div>
            <h2 class="font-semibold text-blue-700 mb-2">{{ __('faq.q4') }}</h2>
            <p class="text-gray-600">{{ __('faq.a4') }}</p>
        </div>
    </div>
</div>
@endsection 