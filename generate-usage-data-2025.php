<?php

// Script pour générer des données fictives d'utilisation des clés pour les 30 derniers jours
// Date actuelle: 27 mai 2025

// Charger l'environnement Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\LicenceHistory;
use App\Models\SerialKey;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

// Définir la date actuelle comme le 27 mai 2025
$currentDate = Carbon::createFromDate(2025, 5, 27);
echo "Date actuelle simulée: " . $currentDate->format('Y-m-d') . "\n";

// Supprimer les anciennes données d'historique
echo "Suppression des anciennes données d'historique...\n";
LicenceHistory::where('created_at', '>=', $currentDate->copy()->subDays(30))->delete();

// Récupérer toutes les clés de série actives
$serialKeys = SerialKey::where('status', 'active')->get();

if ($serialKeys->isEmpty()) {
    echo "Aucune clé de série active trouvée. Impossible de générer des données fictives.\n";
    exit(1);
}

// Actions possibles pour l'historique
$actions = ['verification', 'activation', 'check', 'update'];

// Générer des données pour les 30 derniers jours
echo "Génération de données fictives d'utilisation des clés pour les 30 derniers jours...\n";

// Désactiver les contraintes de clés étrangères temporairement
DB::statement('SET FOREIGN_KEY_CHECKS=0');

// Tableau pour stocker les statistiques générées
$generatedStats = [];

// Générer des données pour chaque jour
for ($day = 30; $day >= 0; $day--) {
    $date = $currentDate->copy()->subDays($day)->format('Y-m-d');
    
    // Générer un nombre aléatoire d'utilisations pour ce jour
    // Avec une tendance à la hausse pour simuler une croissance
    $baseCount = rand(5, 20);
    $growthFactor = (30 - $day) / 10; // Plus on se rapproche d'aujourd'hui, plus il y a d'activité
    $count = (int) ($baseCount + ($baseCount * $growthFactor));
    
    // Ajouter quelques pics aléatoires pour rendre le graphique plus intéressant
    if (rand(1, 10) > 8) {
        $count += rand(10, 30);
    }
    
    // Limiter le nombre maximum à 100 pour éviter des valeurs extrêmes
    $count = min($count, 100);
    
    echo "Génération de $count enregistrements pour le $date...\n";
    $generatedStats[$date] = $count;
    
    // Générer les enregistrements pour ce jour
    for ($i = 0; $i < $count; $i++) {
        // Sélectionner une clé aléatoire
        $serialKey = $serialKeys->random();
        
        // Sélectionner une action aléatoire
        $action = $actions[array_rand($actions)];
        
        // Créer un enregistrement d'historique avec une date/heure aléatoire pour ce jour
        $hour = rand(0, 23);
        $minute = rand(0, 59);
        $second = rand(0, 59);
        $timestamp = Carbon::parse("$date $hour:$minute:$second");
        
        LicenceHistory::create([
            'serial_key_id' => $serialKey->id,
            'action' => $action,
            'details' => [
                'ip_address' => long2ip(rand(0, 4294967295)), // IP aléatoire
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'domain' => 'example' . rand(1, 100) . '.com'
            ],
            'performed_by' => null, // Aucun utilisateur spécifique
            'ip_address' => long2ip(rand(0, 4294967295)), // IP aléatoire
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ]);
    }
}

// Réactiver les contraintes de clés étrangères
DB::statement('SET FOREIGN_KEY_CHECKS=1');

echo "\nGénération terminée! Données fictives créées pour les 30 derniers jours.\n";
echo "\nStatistiques générées par jour:\n";

// Afficher un résumé des données générées
ksort($generatedStats); // Trier par date
foreach ($generatedStats as $date => $count) {
    echo "$date: $count enregistrements\n";
}

echo "\nN'oubliez pas de modifier le DashboardController.php pour utiliser la date actuelle (2025-05-27)\n";
echo "au lieu de la date fixe de 2023 pour afficher les données correctement.\n";