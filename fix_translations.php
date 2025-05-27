<?php

// Script pour corriger les fichiers de traduction et ajouter les nouvelles clés
$languages = ['ar', 'ja', 'ru', 'tr', 'zh'];

// Nouvelles clés de traduction à ajouter pour chaque langue
$translations = [
    'ar' => [
        'no_license_key_configured' => 'لم يتم تكوين مفتاح الترخيص في ملف .env.',
        'api_verification_error' => 'خطأ أثناء التحقق المباشر من واجهة برمجة التطبيقات',
        'valid_via_direct_api' => 'الترخيص صالح عبر واجهة برمجة التطبيقات المباشرة',
        'invalid_via_direct_api' => 'الترخيص غير صالح عبر واجهة برمجة التطبيقات المباشرة',
        'status_detail' => 'الحالة: :status',
        'expired_on' => 'انتهت صلاحيته في :date',
        'expires_on_date' => 'تنتهي صلاحيته في :date',
        'expiry_detail' => 'انتهاء الصلاحية: :expiry',
        'registered_domain' => 'النطاق المسجل: :domain',
        'registered_ip' => 'عنوان IP المسجل: :ip',
        'license_valid' => 'الترخيص صالح.',
        'api_valid_service_invalid' => 'تشير واجهة برمجة التطبيقات إلى أن الترخيص صالح، لكن خدمة الترخيص تعتبره غير صالح. مشكلة تكوين محتملة.',
        'license_invalid_with_api_message' => 'الترخيص غير صالح وفقًا لواجهة برمجة التطبيقات والخدمة. رسالة API: :message',
        'license_details_header' => 'تفاصيل الترخيص:',
        'verification_error' => 'حدث خطأ أثناء التحقق من الترخيص: :error'
    ],
    'ja' => [
        'no_license_key_configured' => '.envファイルにライセンスキーが設定されていません。',
        'api_verification_error' => 'API直接検証中のエラー',
        'valid_via_direct_api' => '直接APIを介して有効なライセンス',
        'invalid_via_direct_api' => '直接APIを介して無効なライセンス',
        'status_detail' => 'ステータス: :status',
        'expired_on' => ':dateに期限切れ',
        'expires_on_date' => ':dateに期限切れ',
        'expiry_detail' => '有効期限: :expiry',
        'registered_domain' => '登録ドメイン: :domain',
        'registered_ip' => '登録IPアドレス: :ip',
        'license_valid' => 'ライセンスは有効です。',
        'api_valid_service_invalid' => 'APIはライセンスが有効であることを示していますが、ライセンスサービスはそれを無効と見なしています。潜在的な構成の問題。',
        'license_invalid_with_api_message' => 'APIとサービスによると、ライセンスは有効ではありません。APIメッセージ: :message',
        'license_details_header' => 'ライセンスの詳細:',
        'verification_error' => 'ライセンスの検証中にエラーが発生しました: :error'
    ],
    'ru' => [
        'no_license_key_configured' => 'В файле .env не настроен лицензионный ключ.',
        'api_verification_error' => 'Ошибка при прямой проверке API',
        'valid_via_direct_api' => 'Лицензия действительна через прямой API',
        'invalid_via_direct_api' => 'Лицензия недействительна через прямой API',
        'status_detail' => 'Статус: :status',
        'expired_on' => 'истек :date',
        'expires_on_date' => 'истекает :date',
        'expiry_detail' => 'Срок действия: :expiry',
        'registered_domain' => 'Зарегистрированный домен: :domain',
        'registered_ip' => 'Зарегистрированный IP-адрес: :ip',
        'license_valid' => 'Лицензия действительна.',
        'api_valid_service_invalid' => 'API указывает, что лицензия действительна, но служба лицензирования считает ее недействительной. Возможная проблема с конфигурацией.',
        'license_invalid_with_api_message' => 'Лицензия недействительна согласно API и службе. Сообщение API: :message',
        'license_details_header' => 'Сведения о лицензии:',
        'verification_error' => 'Произошла ошибка при проверке лицензии: :error'
    ],
    'tr' => [
        'no_license_key_configured' => '.env dosyasında yapılandırılmış lisans anahtarı yok.',
        'api_verification_error' => 'Doğrudan API doğrulaması sırasında hata',
        'valid_via_direct_api' => 'Doğrudan API aracılığıyla geçerli lisans',
        'invalid_via_direct_api' => 'Doğrudan API aracılığıyla geçersiz lisans',
        'status_detail' => 'Durum: :status',
        'expired_on' => ':date tarihinde sona erdi',
        'expires_on_date' => ':date tarihinde sona eriyor',
        'expiry_detail' => 'Son Kullanma: :expiry',
        'registered_domain' => 'Kayıtlı alan adı: :domain',
        'registered_ip' => 'Kayıtlı IP adresi: :ip',
        'license_valid' => 'Lisans geçerlidir.',
        'api_valid_service_invalid' => 'API, lisansın geçerli olduğunu gösteriyor, ancak lisans hizmeti bunu geçersiz olarak kabul ediyor. Olası yapılandırma sorunu.',
        'license_invalid_with_api_message' => 'Lisans, API ve hizmete göre geçerli değil. API mesajı: :message',
        'license_details_header' => 'Lisans ayrıntıları:',
        'verification_error' => 'Lisans doğrulanırken bir hata oluştu: :error'
    ],
    'zh' => [
        'no_license_key_configured' => '.env文件中未配置许可证密钥。',
        'api_verification_error' => '直接API验证期间出错',
        'valid_via_direct_api' => '通过直接API有效的许可证',
        'invalid_via_direct_api' => '通过直接API无效的许可证',
        'status_detail' => '状态：:status',
        'expired_on' => '已于:date过期',
        'expires_on_date' => '将于:date过期',
        'expiry_detail' => '到期：:expiry',
        'registered_domain' => '注册域名：:domain',
        'registered_ip' => '注册IP地址：:ip',
        'license_valid' => '许可证有效。',
        'api_valid_service_invalid' => 'API表明许可证有效，但许可证服务认为它无效。可能存在配置问题。',
        'license_invalid_with_api_message' => '根据API和服务，许可证无效。API消息：:message',
        'license_details_header' => '许可证详情：',
        'verification_error' => '验证许可证时发生错误：:error'
    ]
];

// Parcourir les langues et créer la section settings_license si elle n'existe pas
foreach ($languages as $lang) {
    $filePath = __DIR__ . '/resources/locales/' . $lang . '/translation.json';
    
    if (!file_exists($filePath)) {
        echo "Le fichier $filePath n'existe pas.\n";
        continue;
    }
    
    echo "Traitement du fichier $lang...\n";
    
    // Lire le contenu du fichier
    $content = file_get_contents($filePath);
    if (!$content) {
        echo "Erreur lors de la lecture du fichier: $filePath\n";
        continue;
    }
    
    // Vérifier si le fichier contient déjà la section settings_license
    if (strpos($content, '"settings_license"') !== false) {
        echo "Le fichier contient déjà la section settings_license. Mise à jour des clés...\n";
        
        // Essayer de décoder le JSON
        $jsonData = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Erreur lors du décodage JSON: " . json_last_error_msg() . "\n";
            
            // Approche alternative: ajouter les clés manuellement
            echo "Tentative d'ajout manuel des clés...\n";
            
            // Trouver la position de la section settings_license
            $pos = strpos($content, '"settings_license"');
            if ($pos === false) {
                echo "Impossible de trouver la section settings_license.\n";
                continue;
            }
            
            // Trouver la position de la section license
            $licensePos = strpos($content, '"license"', $pos);
            if ($licensePos === false) {
                echo "Impossible de trouver la section license.\n";
                continue;
            }
            
            // Trouver la position de l'accolade ouvrante après license
            $openBracePos = strpos($content, '{', $licensePos);
            if ($openBracePos === false) {
                echo "Impossible de trouver l'accolade ouvrante.\n";
                continue;
            }
            
            // Ajouter les nouvelles clés après l'accolade ouvrante
            $newContent = substr($content, 0, $openBracePos + 1) . "\n";
            
            foreach ($translations[$lang] as $key => $value) {
                $newContent .= '            "' . $key . '": "' . addslashes($value) . '",'."\n";
            }
            
            $newContent .= substr($content, $openBracePos + 1);
            
            // Écrire le contenu mis à jour
            if (file_put_contents($filePath, $newContent)) {
                echo "Mise à jour manuelle réussie: $filePath\n";
            } else {
                echo "Erreur lors de l'écriture dans le fichier: $filePath\n";
            }
            
            continue;
        }
        
        // Si le décodage a réussi, mettre à jour les clés
        if (!isset($jsonData['settings_license']) || !isset($jsonData['settings_license']['license'])) {
            echo "La section settings_license.license n'existe pas. Création...\n";
            $jsonData['settings_license'] = ['license' => []];
        }
        
        // Ajouter les nouvelles clés
        foreach ($translations[$lang] as $key => $value) {
            $jsonData['settings_license']['license'][$key] = $value;
        }
        
        // Encoder le contenu JSON avec formatage
        $newContent = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Erreur lors de l'encodage JSON: " . json_last_error_msg() . "\n";
            continue;
        }
        
        // Écrire le contenu mis à jour
        if (file_put_contents($filePath, $newContent)) {
            echo "Mise à jour réussie: $filePath\n";
        } else {
            echo "Erreur lors de l'écriture dans le fichier: $filePath\n";
        }
    } else {
        echo "La section settings_license n'existe pas. Création...\n";
        
        // Trouver un point d'insertion approprié (juste avant la dernière accolade)
        $lastBracePos = strrpos($content, '}');
        if ($lastBracePos === false) {
            echo "Impossible de trouver la dernière accolade.\n";
            continue;
        }
        
        // Créer la section settings_license
        $settingsLicenseSection = ',
    "settings_license": {
        "license": {';
        
        foreach ($translations[$lang] as $key => $value) {
            $settingsLicenseSection .= '
            "' . $key . '": "' . addslashes($value) . '",';
        }
        
        // Supprimer la dernière virgule
        $settingsLicenseSection = rtrim($settingsLicenseSection, ',');
        
        $settingsLicenseSection .= '
        }
    }';
        
        // Insérer la section dans le contenu
        $newContent = substr($content, 0, $lastBracePos) . $settingsLicenseSection . substr($content, $lastBracePos);
        
        // Écrire le contenu mis à jour
        if (file_put_contents($filePath, $newContent)) {
            echo "Création réussie: $filePath\n";
        } else {
            echo "Erreur lors de l'écriture dans le fichier: $filePath\n";
        }
    }
}

echo "Mise à jour des traductions terminée.\n";
