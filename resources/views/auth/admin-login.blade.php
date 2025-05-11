<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdminLicence - Connexion</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/css/login.css'])
</head>
<body>
    <div class="login-page">
        <!-- Partie gauche -->
        <div class="login-left">
            <div class="login-header">
                <h1>Bienvenue sur<br>AdminLicence</h1>
                <p><br>gérer vos licences efficacement</p>
            </div>

            <div class="features-list">
                <div class="feature-item">
                    <i class="fas fa-shield-alt"></i>
                    <span>Gestion sécurisée de vos licences</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Suivi et analyse de l'utilisation</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>et plus encore</span>
                </div>
            </div>
        </div>

        <!-- Partie droite -->
        <div class="login-right">
            <div class="login-form">
                <h2>Connexion</h2>

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
                        <label class="form-label" for="email">Adresse e-mail</label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               class="form-input" 
                               value="{{ old('email') }}" 
                               required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Mot de passe</label>
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
                        <label for="remember">Se souvenir de moi</label>
                    </div>

                    <button type="submit" class="btn-primary">Se connecter</button>

                    @php
                        use Illuminate\Support\Facades\Route;
                    @endphp
                    
                    @if(Route::has('admin.password.request'))
                        <div class="form-footer">
                            <a href="{{ route('admin.password.request') }}">
                                Mot de passe oublié ?
                            </a>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</body>
</html>