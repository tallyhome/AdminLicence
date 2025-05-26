<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Paramètres de vérification de licence
        Setting::setByKey('license_check_frequency', 5, 'Fréquence de vérification de la licence (1 fois tous les X visites)');
        Setting::setByKey('last_license_check', now()->toDateTimeString(), 'Date de la dernière vérification de licence');
        Setting::setByKey('license_valid', false, 'Statut de validité de la licence');
    }
}
