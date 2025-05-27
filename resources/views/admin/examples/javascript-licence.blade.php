@extends('admin.layouts.app')

@section('title', 'Exemple d\'intégration JavaScript')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Intégration de licence avec JavaScript</h1>
                <a href="{{ route('admin.documentation.licence') }}" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Retour à la documentation
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Exemple d'intégration avec JavaScript</h5>
                </div>
                <div class="card-body">
                    <p>Voici comment vérifier une clé de licence en utilisant JavaScript. Cet exemple utilise l'API fetch pour effectuer une requête au serveur de licences.</p>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note :</strong> Pour des raisons de sécurité, il est recommandé d'effectuer la vérification côté serveur plutôt que côté client. Cet exemple est fourni à titre indicatif pour les applications qui ne peuvent pas utiliser une approche côté serveur.
                    </div>
                    
                    <h5>Exemple d'intégration avec JavaScript</h5>
                    <pre><code class="language-javascript">// Fonction pour vérifier une licence avec JavaScript
async function verifierLicence(cleSeriale, domaine = null, adresseIP = null) {
    // URL de l'API de vérification - utiliser le point d'entrée PHP direct qui est le plus fiable
    const apiUrl = 'https://licence.votredomaine.com/api/check-serial.php';
    
    // Données à envoyer
    const donnees = {
        serial_key: cleSeriale,
        domain: domaine || window.location.hostname,
        ip_address: adresseIP || '127.0.0.1' // L'IP doit être fournie côté serveur
    };
    
    try {
        // Effectuer la requête
        const reponse = await fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(donnees)
        });
        
        // Vérifier le code de statut HTTP
        if (!reponse.ok) {
            throw new Error(`Erreur HTTP: ${reponse.status}`);
        }
        
        // Analyser la réponse JSON
        const resultat = await reponse.json();
        
        // Vérifier les différents statuts de la clé
        const statut = resultat.data?.status;
        const estExpire = resultat.data?.is_expired || false;
        const estSuspendu = resultat.data?.is_suspended || false;
        const estRevoque = resultat.data?.is_revoked || false;
        const dateExpiration = resultat.data?.expires_at;
        
        // Préparer les données de réponse avec plus d'informations
        const donnees = {
            token: resultat.data?.token,
            project: resultat.data?.project,
            expires_at: dateExpiration,
            status: statut,
            is_expired: estExpire,
            is_suspended: estSuspendu,
            is_revoked: estRevoque
        };
        
        return {
            success: (resultat.status === 'success'),
            message: resultat.message || 'Erreur inconnue',
            donnees: donnees
        };
    } catch (erreur) {
        console.error('Erreur lors de la vérification de licence:', erreur);
        return {
            success: false,
            message: `Erreur de connexion: ${erreur.message}`,
            donnees: null
        };
    }
}

// Exemple d'utilisation de la fonction
document.addEventListener('DOMContentLoaded', async function() {
    const cleSeriale = 'VOTRE-CLE-DE-LICENCE'; // Remplacez par votre clé de licence
    
    try {
        const resultat = await verifierLicence(cleSeriale);
        
        if (resultat.success) {
            console.log('Licence valide!');
            console.log('Expire le:', resultat.donnees.expires_at);
            console.log('Projet:', resultat.donnees.project);
            
            // Activer les fonctionnalités premium
            activerFonctionnalitesPremium();
        } else {
            console.error('Licence invalide:', resultat.message);
            
            // Vérifier les différents états de la licence
            if (resultat.donnees) {
                if (resultat.donnees.is_expired) {
                    console.error('La licence a expiré le', resultat.donnees.expires_at);
                    afficherMessageExpiration(resultat.donnees.expires_at);
                } else if (resultat.donnees.is_suspended) {
                    console.error('La licence est suspendue');
                    afficherMessageSuspension();
                } else if (resultat.donnees.is_revoked) {
                    console.error('La licence a été révoquée');
                    afficherMessageRevocation();
                }
            }
            
            // Limiter les fonctionnalités
            limiterFonctionnalites();
        }
    } catch (erreur) {
        console.error('Erreur lors de la vérification:', erreur);
        // Traiter l'erreur (afficher un message, journaliser, etc.)
    }
});

// Fonctions d'exemple pour gérer les différents états de licence
function activerFonctionnalitesPremium() {
    // Activer toutes les fonctionnalités premium
    document.querySelectorAll('.premium-feature').forEach(el => {
        el.classList.remove('disabled');
    });
}

function limiterFonctionnalites() {
    // Désactiver les fonctionnalités premium
    document.querySelectorAll('.premium-feature').forEach(el => {
        el.classList.add('disabled');
    });
}

function afficherMessageExpiration(dateExpiration) {
    // Afficher un message d'expiration
    const message = `Votre licence a expiré le ${dateExpiration}. Veuillez la renouveler pour continuer à utiliser les fonctionnalités premium.`;
    afficherMessage(message, 'warning');
}

function afficherMessageSuspension() {
    // Afficher un message de suspension
    const message = 'Votre licence est actuellement suspendue. Veuillez contacter le support pour plus d\'informations.';
    afficherMessage(message, 'warning');
}

function afficherMessageRevocation() {
    // Afficher un message de révocation
    const message = 'Votre licence a été révoquée. Veuillez contacter le support pour plus d\'informations.';
    afficherMessage(message, 'danger');
}

function afficherMessage(message, type) {
    // Créer et afficher un message à l'utilisateur
    const container = document.querySelector('.licence-messages') || document.body;
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    container.prepend(alertDiv);
}</code></pre>

                    <h5 class="mt-4">Utilisation dans une application Node.js (côté serveur)</h5>
                    <p>Pour une meilleure sécurité, effectuez la vérification côté serveur :</p>
                    
                    <pre><code class="language-javascript">// Exemple avec Node.js et Express
const express = require('express');
const axios = require('axios');
const app = express();

app.use(express.json());

// Middleware de vérification de licence
async function verifierLicenceMiddleware(req, res, next) {
    // Récupérer la clé de licence depuis la configuration
    const cleSeriale = process.env.LICENCE_KEY;
    
    if (!cleSeriale) {
        console.warn('Clé de licence non configurée');
        return res.status(403).json({ 
            success: false, 
            message: 'Clé de licence non configurée' 
        });
    }
    
    try {
        // Effectuer la requête API
        const response = await axios.post('https://licence.votredomaine.com/api/check-serial.php', {
            serial_key: cleSeriale,
            domain: req.hostname,
            ip_address: req.ip
        });
        
        const data = response.data;
        
        if (data.status === 'success') {
            // Vérifier si la licence est expirée, suspendue ou révoquée
            if (data.data.is_expired) {
                return res.status(403).json({ 
                    success: false, 
                    message: 'Licence expirée',
                    expires_at: data.data.expires_at 
                });
            }
            
            if (data.data.is_suspended) {
                return res.status(403).json({ 
                    success: false, 
                    message: 'Licence suspendue' 
                });
            }
            
            if (data.data.is_revoked) {
                return res.status(403).json({ 
                    success: false, 
                    message: 'Licence révoquée' 
                });
            }
            
            // Licence valide, continuer
            req.licenceInfo = data.data;
            return next();
        } else {
            // Licence invalide
            return res.status(403).json({ 
                success: false, 
                message: data.message || 'Licence invalide' 
            });
        }
    } catch (error) {
        console.error('Erreur lors de la vérification de licence:', error.message);
        return res.status(500).json({ 
            success: false, 
            message: 'Erreur lors de la vérification de la licence' 
        });
    }
}

// Appliquer le middleware aux routes protégées
app.use('/api/protected', verifierLicenceMiddleware);

// Exemple de route protégée
app.get('/api/protected/data', (req, res) => {
    // La licence a été vérifiée à ce stade
    res.json({ 
        success: true, 
        message: 'Données protégées', 
        data: { 
            /* vos données protégées */ 
        },
        licence: req.licenceInfo
    });
});

// Démarrer le serveur
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Serveur démarré sur le port ${PORT}`);
});</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    pre {
        background-color: #f8f9fa;
        padding: 1rem;
        border-radius: 0.25rem;
    }
    code {
        font-size: 0.875rem;
    }
</style>
@endpush

@push('scripts')
<script>
    // Highlight.js pour la coloration syntaxique du code
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('pre code').forEach((block) => {
            hljs.highlightElement(block);
        });
    });
</script>
@endpush
