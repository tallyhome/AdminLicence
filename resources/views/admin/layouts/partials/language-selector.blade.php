@php
use Illuminate\Support\Facades\Session;
@endphp

<!-- Language Selector -->
<div class="nav-item dropdown language-selector">
    <button class="nav-link dropdown-toggle d-flex align-items-center" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 70px;">
        @php
            $locale = Session::get('locale', app()->getLocale());
            $countryCode = $locale === 'en' ? 'gb' : $locale;
        @endphp
        <span class="flag-icon flag-icon-{{ $countryCode }} me-2"></span>
        {{ strtoupper($locale) }}
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="languageDropdown" style="min-width: 100px;">
        @foreach(config('app.available_locales', ['fr', 'en', 'es', 'de', 'it', 'pt', 'nl', 'ru', 'zh', 'ja', 'tr', 'ar']) as $locale)
            <form action="{{ route('admin.set.language') }}" method="POST" class="block">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">
                <button type="submit" class="dropdown-item d-flex align-items-center {{ app()->getLocale() == $locale ? 'active' : '' }}">
                    <span class="flag-icon flag-icon-{{ $locale === 'en' ? 'gb' : $locale }} me-2"></span>
                    {{ strtoupper($locale) }}
                </button>
            </form>
        @endforeach
    </ul>
</div> 