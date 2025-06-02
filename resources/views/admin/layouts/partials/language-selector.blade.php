<div class="dropdown">
    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="min-width: 70px; cursor: pointer;">
        <i class="flag-icon flag-icon-{{ app()->getLocale() === 'en' ? 'gb' : app()->getLocale() }} me-1"></i>
        <span class="d-none d-md-inline">{{ strtoupper(app()->getLocale()) }}</span>
    </a>
    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
        @foreach(config('app.available_locales') as $locale)
            <li>
                <form action="{{ route('admin.set.language') }}" method="POST" class="language-form">
                    @csrf
                    <input type="hidden" name="locale" value="{{ $locale }}">
                    <button type="submit" class="dropdown-item d-flex align-items-center">
                        <i class="flag-icon flag-icon-{{ $locale === 'en' ? 'gb' : $locale }} me-2"></i>
                        {{ strtoupper($locale) }}
                    </button>
                </form>
            </li>
        @endforeach
    </ul>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Assurer que le dropdown de langue fonctionne correctement
        const languageDropdown = document.getElementById('languageDropdown');
        if (languageDropdown) {
            // Utiliser l'API Bootstrap pour initialiser le dropdown
            const dropdownInstance = new bootstrap.Dropdown(languageDropdown);
            
            // Ajouter un gestionnaire d'événements pour le clic
            languageDropdown.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation(); // Ajout de stopPropagation pour éviter les conflits
            });
        }
        
        // Assurer que les formulaires de langue fonctionnent correctement
        document.querySelectorAll('.language-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                // Laisser le formulaire se soumettre normalement
                console.log('Formulaire de langue soumis pour: ' + form.querySelector('input[name="locale"]').value);
            });
        });
    });
</script>