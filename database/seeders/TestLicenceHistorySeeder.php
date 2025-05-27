<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LicenceHistory;
use App\Models\SerialKey;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TestLicenceHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Supprimer toutes les données existantes dans la table licence_histories
        echo "Suppression des données existantes dans la table licence_histories...\n";
        DB::table('licence_histories')->truncate();

        // Récupérer des clés de série et des projets existants pour les référencer
        $serialKeys = SerialKey::all();
        $projects = Project::all();

        // Si aucune clé de série n'existe, en créer quelques-unes
        if ($serialKeys->isEmpty()) {
            echo "Aucune clé de série trouvée. Création de quelques clés de test...\n";
            
            // Créer un projet si nécessaire
            if ($projects->isEmpty()) {
                $project = Project::create([
                    'name' => 'Projet Test',
                    'description' => 'Projet créé pour les tests',
                    'status' => 'active'
                ]);
                $projects = Project::all();
            } else {
                $project = $projects->first();
            }
            
            // Créer quelques clés de série
            for ($i = 1; $i <= 5; $i++) {
                SerialKey::create([
                    'serial_key' => 'TEST-KEY-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'project_id' => $project->id,
                    'status' => 'active',
                    'expires_at' => Carbon::now()->addYear(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]);
            }
            
            $serialKeys = SerialKey::all();
        }

        // Date actuelle (27 mai 2025)
        $currentDate = Carbon::createFromDate(2025, 5, 27);
        $startDate = $currentDate->copy()->subDays(30);

        echo "Génération des données d'utilisation pour la période du {$startDate->format('Y-m-d')} au {$currentDate->format('Y-m-d')}...\n";

        // Types d'actions pour l'historique
        $actionTypes = ['verify', 'activation', 'update_check', 'validation'];
        $domains = ['example.com', 'testdomain.org', 'app.myapp.com', 'client.software.net', 'desktop.app'];
        $ipAddresses = ['192.168.1.1', '10.0.0.1', '172.16.0.1', '8.8.8.8', '1.1.1.1'];

        // Générer des données avec une distribution plus réaliste
        // Plus d'activité les jours de semaine, moins le weekend
        // Une tendance à la hausse progressive
        $totalEntries = 0;
        $historyEntries = [];

        for ($i = 0; $i <= 30; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            // Déterminer le nombre d'entrées pour ce jour
            // Base: entre 5 et 30 entrées
            $baseEntries = rand(5, 30);
            
            // Moins d'activité le weekend (samedi=6, dimanche=0)
            $dayOfWeek = $date->dayOfWeek;
            $weekendMultiplier = ($dayOfWeek == 0 || $dayOfWeek == 6) ? 0.5 : 1.0;
            
            // Tendance à la hausse: plus d'activité vers la fin de la période
            $trendMultiplier = 1.0 + ($i / 30); // De 1.0 à 2.0
            
            // Nombre final d'entrées pour ce jour
            $numEntries = round($baseEntries * $weekendMultiplier * $trendMultiplier);
            
            echo "Jour {$i} ({$date->format('Y-m-d')}): Génération de {$numEntries} entrées...\n";
            
            // Créer les entrées pour ce jour
            for ($j = 0; $j < $numEntries; $j++) {
                $serialKey = $serialKeys->random();
                $actionType = $actionTypes[array_rand($actionTypes)];
                $domain = $domains[array_rand($domains)];
                $ipAddress = $ipAddresses[array_rand($ipAddresses)];
                
                // Heure aléatoire dans la journée
                $dateTime = $date->copy()->addHours(rand(8, 22))->addMinutes(rand(0, 59))->addSeconds(rand(0, 59));
                
                $historyEntries[] = [
                    'serial_key_id' => $serialKey->id,
                    'action' => $actionType,
                    'ip_address' => $ipAddress,
                    'domain' => $domain,
                    'created_at' => $dateTime,
                    'updated_at' => $dateTime
                ];
                
                $totalEntries++;
                
                // Insérer par lots de 100 entrées pour éviter les problèmes de mémoire
                if (count($historyEntries) >= 100) {
                    LicenceHistory::insert($historyEntries);
                    $historyEntries = [];
                }
            }
        }
        
        // Insérer les entrées restantes
        if (!empty($historyEntries)) {
            LicenceHistory::insert($historyEntries);
        }

        echo "Génération terminée! {$totalEntries} entrées ont été créées dans la table licence_histories.\n";
        echo "Vous pouvez maintenant rafraîchir le tableau de bord pour voir le graphique mis à jour.\n";
    }
}
