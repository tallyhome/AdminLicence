<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title data-i18n="admin_login.title">{{ t('admin_login.title') }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/css/login.css'])
    <script src="{{ asset('js/translations.js') }}"></script>
</head>
<body>
    <div class="login-page">
        <!-- Sélecteur de langue -->
        <div class="language-selector" style="position: absolute; top: 20px; right: 20px; z-index: 1000;">
            <select id="language-selector" class="border rounded px-2 py-1 text-sm bg-white shadow-sm">
                    <option value="fr" {{ App::getLocale() == 'fr' ? 'selected' : '' }}>Français</option>
                    <option value="en" {{ App::getLocale() == 'en' ? 'selected' : '' }}>English</option>
                    <option value="es" {{ App::getLocale() == 'es' ? 'selected' : '' }}>Español</option>
                    <option value="ru" {{ App::getLocale() == 'ru' ? 'selected' : '' }}>Русский</option>
                    <option value="de" {{ App::getLocale() == 'de' ? 'selected' : '' }}>Deutsch</option>
                    <option value="it" {{ App::getLocale() == 'it' ? 'selected' : '' }}>Italiano</option>
                    <option value="nl" {{ App::getLocale() == 'nl' ? 'selected' : '' }}>Nederlands</option>
                    <option value="pt" {{ App::getLocale() == 'pt' ? 'selected' : '' }}>Português</option>
                    <option value="zh" {{ App::getLocale() == 'zh' ? 'selected' : '' }}>中文</option>
                    <option value="ja" {{ App::getLocale() == 'ja' ? 'selected' : '' }}>日本語</option>
                    <option value="ar" {{ App::getLocale() == 'ar' ? 'selected' : '' }}>العربية</option>
                    <option value="tr" {{ App::getLocale() == 'tr' ? 'selected' : '' }}>Türkçe</option>
                </select>
        </div>
        
        <!-- Partie gauche -->
        <div class="login-left">
            <div class="login-header">
                <h1><span data-i18n="admin_login.welcome">{{ t('admin_login.welcome') }}</span><br><span data-i18n="admin_login.app_name">{{ t('admin_login.app_name') }}</span></h1>
                <p><br><span data-i18n="admin_login.subtitle">{{ t('admin_login.subtitle') }}</span></p>
            </div>

            <div class="features-list">
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <span data-i18n="admin_login.features.secure_management">{{ t('admin_login.features.secure_management') }}</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-line"></i>
                    <span data-i18n="admin_login.features.tracking_analysis">{{ t('admin_login.features.tracking_analysis') }}</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-plus-circle"></i>
                    <span data-i18n="admin_login.features.and_more">{{ t('admin_login.features.and_more') }}</span>
                </div>
            </div>
        </div>

        <!-- Partie droite -->
        <div class="login-right">
            <div class="login-form">
                <h2 data-i18n="admin_login.login">{{ t('admin_login.login') }}</h2>

                @if(session('error'))
                    <div class="alert">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="email" data-i18n="admin_login.email">{{ t('admin_login.email') }}</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               value="{{ old('email') }}" 
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password" data-i18n="admin_login.password">{{ t('admin_login.password') }}</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-input" 
                               required>
                    </div>

                    <div class="checkbox-group">
                        <input type="checkbox" 
                               id="remember" 
                               name="remember">
                        <label for="remember" data-i18n="admin_login.remember_me">{{ t('admin_login.remember_me') }}</label>
                    </div>

                    <button type="submit" class="btn-primary" data-i18n="admin_login.login_button">{{ t('admin_login.login_button') }}</button>

                    @php
                        use Illuminate\Support\Facades\Route;
                    @endphp
                    
                    @if(Route::has('admin.password.request'))
                        <div class="form-footer">
                            <a href="{{ route('admin.password.request') }}">
                                <span data-i18n="admin_login.forgot_password">{{ t('admin_login.forgot_password') }}</span>
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</body>
</html>