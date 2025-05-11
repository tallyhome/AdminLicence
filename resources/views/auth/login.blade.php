<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Administration des Licences</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @vite(['resources/css/login.css'])
</head>
<body>
    <div class="login-container">
        <div class="login-page">
            <div class="login-left">
                <div class="login-header">
                    <h1>Bienvenue sur<br>AdminLicence</h1>
                    <p>Créez votre compte et commencez à<br>gérer vos licences efficacement</p>
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
                        <i class="fas fa-headset"></i>
                        <span></span>
                    </div>
                </div>
                <div class="login-footer">
                    <p>Déjà inscrit ?</p>
                    <a href="{{ route('admin.login') }}" class="btn-secondary">Connexion</a>
                </div>
            </div>
            
            <div class="login-right">
                <div class="login-form">
                    <h2>Créer un compte</h2>

                    @if ($errors->any())
                        <div class="alert">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.register') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="form-label">
                                <i class="far fa-user"></i>
                                Nom complet
                            </label>
                            <input id="name" 
                                   name="name" 
                                   type="text" 
                                   class="form-input" 
                                   value="{{ old('name') }}" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="email" class="form-label">
                                <i class="far fa-envelope"></i>
                                Adresse e-mail
                            </label>
                            <input id="email" 
                                   name="email" 
                                   type="email" 
                                   class="form-input" 
                                   value="{{ old('email') }}" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock"></i>
                                Mot de passe
                            </label>
                            <input id="password" 
                                   name="password" 
                                   type="password" 
                                   class="form-input" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation" class="form-label">
                                <i class="fas fa-check-circle"></i>
                                Confirmation
                            </label>
                            <input id="password_confirmation" 
                                   name="password_confirmation" 
                                   type="password" 
                                   class="form-input" 
                                   required>
                        </div>

                        <div class="checkbox-group">
                            <input id="terms" 
                                   name="terms" 
                                   type="checkbox" 
                                   required>
                            <label for="terms">
                                J'accepte les <a href="#">conditions d'utilisation</a> et la <a href="#">politique de confidentialité</a>
                            </label>
                        </div>

                        <button type="submit" class="btn-primary">
                            <i class="fas fa-user-plus"></i>
                            Créer mon compte
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>