<?php

// Script pour ajouter les nouvelles clés de traduction du contrôleur de licence à tous les fichiers de traduction
$languages = [
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
    'de' => [
        'no_license_key_configured' => 'In der .env-Datei ist kein Lizenzschlüssel konfiguriert.',
        'api_verification_error' => 'Fehler bei der direkten API-Überprüfung',
        'valid_via_direct_api' => 'Lizenz über direkte API gültig',
        'invalid_via_direct_api' => 'Lizenz über direkte API ungültig',
        'status_detail' => 'Status: :status',
        'expired_on' => 'abgelaufen am :date',
        'expires_on_date' => 'läuft ab am :date',
        'expiry_detail' => 'Ablauf: :expiry',
        'registered_domain' => 'Registrierte Domain: :domain',
        'registered_ip' => 'Registrierte IP-Adresse: :ip',
        'license_valid' => 'Die Lizenz ist gültig.',
        'api_valid_service_invalid' => 'Die API zeigt an, dass die Lizenz gültig ist, aber der Lizenzdienst betrachtet sie als ungültig. Mögliches Konfigurationsproblem.',
        'license_invalid_with_api_message' => 'Die Lizenz ist laut API und Dienst nicht gültig. API-Nachricht: :message',
        'license_details_header' => 'Lizenzdetails:',
        'verification_error' => 'Bei der Überprüfung der Lizenz ist ein Fehler aufgetreten: :error'
    ],
    'es' => [
        'no_license_key_configured' => 'No hay ninguna clave de licencia configurada en el archivo .env.',
        'api_verification_error' => 'Error durante la verificación directa de la API',
        'valid_via_direct_api' => 'Licencia válida a través de API directa',
        'invalid_via_direct_api' => 'Licencia inválida a través de API directa',
        'status_detail' => 'Estado: :status',
        'expired_on' => 'expiró el :date',
        'expires_on_date' => 'expira el :date',
        'expiry_detail' => 'Expiración: :expiry',
        'registered_domain' => 'Dominio registrado: :domain',
        'registered_ip' => 'Dirección IP registrada: :ip',
        'license_valid' => 'La licencia es válida.',
        'api_valid_service_invalid' => 'La API indica que la licencia es válida, pero el servicio de licencias la considera inválida. Posible problema de configuración.',
        'license_invalid_with_api_message' => 'La licencia no es válida según la API y el servicio. Mensaje de la API: :message',
        'license_details_header' => 'Detalles de la licencia:',
        'verification_error' => 'Se produjo un error al verificar la licencia: :error'
    ],
    'it' => [
        'no_license_key_configured' => 'Nessuna chiave di licenza è configurata nel file .env.',
        'api_verification_error' => 'Errore durante la verifica diretta dell\'API',
        'valid_via_direct_api' => 'Licenza valida tramite API diretta',
        'invalid_via_direct_api' => 'Licenza non valida tramite API diretta',
        'status_detail' => 'Stato: :status',
        'expired_on' => 'scaduta il :date',
        'expires_on_date' => 'scade il :date',
        'expiry_detail' => 'Scadenza: :expiry',
        'registered_domain' => 'Dominio registrato: :domain',
        'registered_ip' => 'Indirizzo IP registrato: :ip',
        'license_valid' => 'La licenza è valida.',
        'api_valid_service_invalid' => 'L\'API indica che la licenza è valida, ma il servizio di licenza la considera non valida. Potenziale problema di configurazione.',
        'license_invalid_with_api_message' => 'La licenza non è valida secondo l\'API e il servizio. Messaggio API: :message',
        'license_details_header' => 'Dettagli della licenza:',
        'verification_error' => 'Si è verificato un errore durante la verifica della licenza: :error'
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
    'nl' => [
        'no_license_key_configured' => 'Er is geen licentiesleutel geconfigureerd in het .env-bestand.',
        'api_verification_error' => 'Fout tijdens directe API-verificatie',
        'valid_via_direct_api' => 'Licentie geldig via directe API',
        'invalid_via_direct_api' => 'Licentie ongeldig via directe API',
        'status_detail' => 'Status: :status',
        'expired_on' => 'verlopen op :date',
        'expires_on_date' => 'verloopt op :date',
        'expiry_detail' => 'Vervaldatum: :expiry',
        'registered_domain' => 'Geregistreerd domein: :domain',
        'registered_ip' => 'Geregistreerd IP-adres: :ip',
        'license_valid' => 'De licentie is geldig.',
        'api_valid_service_invalid' => 'De API geeft aan dat de licentie geldig is, maar de licentieservice beschouwt deze als ongeldig. Mogelijk configuratieprobleem.',
        'license_invalid_with_api_message' => 'De licentie is niet geldig volgens de API en service. API-bericht: :message',
        'license_details_header' => 'Licentiedetails:',
        'verification_error' => 'Er is een fout opgetreden bij het verifiëren van de licentie: :error'
    ],
    'pt' => [
        'no_license_key_configured' => 'Nenhuma chave de licença está configurada no arquivo .env.',
        'api_verification_error' => 'Erro durante a verificação direta da API',
        'valid_via_direct_api' => 'Licença válida via API direta',
        'invalid_via_direct_api' => 'Licença inválida via API direta',
        'status_detail' => 'Status: :status',
        'expired_on' => 'expirou em :date',
        'expires_on_date' => 'expira em :date',
        'expiry_detail' => 'Expiração: :expiry',
        'registered_domain' => 'Domínio registrado: :domain',
        'registered_ip' => 'Endereço IP registrado: :ip',
        'license_valid' => 'A licença é válida.',
        'api_valid_service_invalid' => 'A API indica que a licença é válida, mas o serviço de licença a considera inválida. Possível problema de configuração.',
        'license_invalid_with_api_message' => 'A licença não é válida de acordo com a API e o serviço. Mensagem da API: :message',
        'license_details_header' => 'Detalhes da licença:',
        'verification_error' => 'Ocorreu um erro ao verificar a licença: :error'
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

// Fonction pour ajouter les nouvelles clés de traduction à un fichier de traduction
function addLicenseControllerTranslations($filePath, $translations) {
    // Lire le contenu du fichier
    $content = file_get_contents($filePath);
    if (!$content) {
        echo "Erreur lors de la lecture du fichier: $filePath\n";
        return false;
    }

    // Décoder le contenu JSON
    $jsonData = json_decode($content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur lors du décodage JSON: " . json_last_error_msg() . " dans $filePath\n";
        return false;
    }

    // Vérifier si la section settings_license.license existe
    if (!isset($jsonData['settings_license']) || !isset($jsonData['settings_license']['license'])) {
        echo "La section settings_license.license n'existe pas dans: $filePath\n";
        return false;
    }

    // Ajouter les nouvelles clés de traduction
    $updated = false;
    foreach ($translations as $key => $value) {
        if (!isset($jsonData['settings_license']['license'][$key])) {
            $jsonData['settings_license']['license'][$key] = $value;
            $updated = true;
        }
    }

    if (!$updated) {
        echo "Aucune nouvelle clé à ajouter dans: $filePath\n";
        return true;
    }

    // Encoder le contenu JSON avec formatage
    $newContent = json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Erreur lors de l'encodage JSON: " . json_last_error_msg() . " dans $filePath\n";
        return false;
    }

    // Écrire le contenu mis à jour
    if (file_put_contents($filePath, $newContent)) {
        echo "Mise à jour réussie: $filePath\n";
        return true;
    } else {
        echo "Erreur lors de l'écriture dans le fichier: $filePath\n";
        return false;
    }
}

// Parcourir les langues et mettre à jour les fichiers
$baseDir = __DIR__ . '/resources/locales/';
foreach ($languages as $lang => $translations) {
    $filePath = $baseDir . $lang . '/translation.json';
    if (file_exists($filePath)) {
        echo "Mise à jour du fichier $lang...\n";
        addLicenseControllerTranslations($filePath, $translations);
    } else {
        echo "Le fichier $filePath n'existe pas.\n";
    }
}

echo "Mise à jour des traductions du contrôleur de licence terminée.\n";
