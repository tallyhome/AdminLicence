@extends('layouts.main')
@section('content')
<div class="container mx-auto px-4 py-16">
    <h1 class="text-3xl font-bold text-blue-800 mb-8">{{ __('pricing.title') }}</h1>
    <div class="flex flex-col md:flex-row gap-8 justify-center">
        <div class="bg-white rounded-lg shadow p-8 flex-1 max-w-sm">
            <div class="text-xl font-bold text-blue-700 mb-2">{{ __('pricing.basic') }}</div>
            <div class="text-3xl font-bold mb-4">$65</div>
            <ul class="text-gray-600 mb-6 text-left list-disc list-inside">
                <li>{{ __('pricing.basic_1') }}</li>
                <li>{{ __('pricing.basic_2') }}</li>
                <li>{{ __('pricing.basic_3') }}</li>
            </ul>
            <a href="#" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('pricing.buy') }}</a>
        </div>
        <div class="bg-white rounded-lg shadow p-8 flex-1 max-w-sm border-2 border-blue-600">
            <div class="text-xl font-bold text-blue-700 mb-2">{{ __('pricing.extended') }}</div>
            <div class="text-3xl font-bold mb-4">$240</div>
            <ul class="text-gray-600 mb-6 text-left list-disc list-inside">
                <li>{{ __('pricing.extended_1') }}</li>
                <li>{{ __('pricing.extended_2') }}</li>
                <li>{{ __('pricing.extended_3') }}</li>
            </ul>
            <a href="#" class="block w-full text-center px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">{{ __('pricing.buy') }}</a>
        </div>
    </div>
</div>
@endsection 