<?php

// Script pour ajouter les traductions de licence à tous les fichiers de traduction
$languages = [
    'ar' => [
        'title' => 'إدارة الترخيص',
        'info_title' => 'معلومات الترخيص',
        'installation_key' => 'مفتاح ترخيص التثبيت',
        'copy_key' => 'نسخ المفتاح',
        'status' => 'حالة الترخيص',
        'status_label' => 'الحالة',
        'valid' => 'صالح',
        'invalid' => 'غير صالح',
        'expiry_date' => 'تاريخ انتهاء الصلاحية',
        'expiry_date_label' => 'تاريخ انتهاء الصلاحية',
        'expires_on' => 'ينتهي في',
        'last_check' => 'آخر فحص',
        'never' => 'أبدا',
        'check_now' => 'تحقق الآن',
        'details' => 'تفاصيل الترخيص',
        'domain' => 'النطاق',
        'ip_address' => 'عنوان IP',
        'not_defined' => 'غير محدد',
        'status_active' => 'نشط',
        'status_suspended' => 'معلق',
        'status_revoked' => 'ملغى',
        'no_details' => 'لا توجد معلومات مفصلة متاحة لمفتاح الترخيص هذا.',
        'configuration' => 'إعدادات الترخيص',
        'key_saved_in_env' => 'سيتم حفظ هذا المفتاح في ملف .env الخاص بتطبيقك.',
        'env_not_exists' => 'ملف .env غير موجود بعد. سيتم إنشاؤه تلقائيًا.',
        'save_settings' => 'حفظ الإعدادات',
        'manual_verification' => 'التحقق اليدوي',
        'manual_verification_desc' => 'يمكنك فرض التحقق الفوري من ترخيص التثبيت. سيؤدي ذلك إلى تحديث حالة الصلاحية والمعلومات المرتبطة بها.',
        'debug_info' => 'معلومات التصحيح',
        'detected_value' => 'القيمة المكتشفة',
        'not_found' => 'غير موجود',
        'http_code' => 'رمز HTTP',
        'raw_api_response' => 'استجابة API الخام',
        'no_response' => 'لا توجد استجابة',
        'unviewable_format' => 'تنسيق غير قابل للعرض',
        'auto_dismiss_alerts' => 'إغلاق التنبيهات تلقائيًا بعد 5 ثوانٍ',
        'copied' => 'تم النسخ!'
    ],
    'de' => [
        'title' => 'Lizenzverwaltung',
        'info_title' => 'Lizenzinformationen',
        'installation_key' => 'Installations-Lizenzschlüssel',
        'copy_key' => 'Schlüssel kopieren',
        'status' => 'Lizenzstatus',
        'status_label' => 'Status',
        'valid' => 'Gültig',
        'invalid' => 'Ungültig',
        'expiry_date' => 'Ablaufdatum',
        'expiry_date_label' => 'Ablaufdatum',
        'expires_on' => 'Läuft ab am',
        'last_check' => 'Letzte Überprüfung',
        'never' => 'Nie',
        'check_now' => 'Jetzt überprüfen',
        'details' => 'Lizenzdetails',
        'domain' => 'Domain',
        'ip_address' => 'IP-Adresse',
        'not_defined' => 'Nicht definiert',
        'status_active' => 'Aktiv',
        'status_suspended' => 'Ausgesetzt',
        'status_revoked' => 'Widerrufen',
        'no_details' => 'Für diesen Lizenzschlüssel sind keine detaillierten Informationen verfügbar.',
        'configuration' => 'Lizenzkonfiguration',
        'key_saved_in_env' => 'Dieser Schlüssel wird in der .env-Datei Ihrer Anwendung gespeichert.',
        'env_not_exists' => 'Die .env-Datei existiert noch nicht. Sie wird automatisch erstellt.',
        'save_settings' => 'Einstellungen speichern',
        'manual_verification' => 'Manuelle Überprüfung',
        'manual_verification_desc' => 'Sie können eine sofortige Überprüfung der Installationslizenz erzwingen. Dies aktualisiert den Gültigkeitsstatus und die zugehörigen Informationen.',
        'debug_info' => 'Debug-Informationen',
        'detected_value' => 'Erkannter Wert',
        'not_found' => 'Nicht gefunden',
        'http_code' => 'HTTP-Code',
        'raw_api_response' => 'Rohe API-Antwort',
        'no_response' => 'Keine Antwort',
        'unviewable_format' => 'Nicht anzeigbares Format',
        'auto_dismiss_alerts' => 'Benachrichtigungen nach 5 Sekunden automatisch ausblenden',
        'copied' => 'Kopiert!'
    ],
    'it' => [
        'title' => 'Gestione Licenze',
        'info_title' => 'Informazioni Licenza',
        'installation_key' => 'Chiave di Licenza di Installazione',
        'copy_key' => 'Copia Chiave',
        'status' => 'Stato della Licenza',
        'status_label' => 'Stato',
        'valid' => 'Valida',
        'invalid' => 'Non valida',
        'expiry_date' => 'Data di Scadenza',
        'expiry_date_label' => 'Data di Scadenza',
        'expires_on' => 'Scade il',
        'last_check' => 'Ultima Verifica',
        'never' => 'Mai',
        'check_now' => 'Verifica Ora',
        'details' => 'Dettagli Licenza',
        'domain' => 'Dominio',
        'ip_address' => 'Indirizzo IP',
        'not_defined' => 'Non definito',
        'status_active' => 'Attiva',
        'status_suspended' => 'Sospesa',
        'status_revoked' => 'Revocata',
        'no_details' => 'Nessuna informazione dettagliata disponibile per questa chiave di licenza.',
        'configuration' => 'Configurazione Licenza',
        'key_saved_in_env' => 'Questa chiave sarà salvata nel file .env della tua applicazione.',
        'env_not_exists' => 'Il file .env non esiste ancora. Verrà creato automaticamente.',
        'save_settings' => 'Salva Impostazioni',
        'manual_verification' => 'Verifica Manuale',
        'manual_verification_desc' => 'Puoi forzare una verifica immediata della licenza di installazione. Questo aggiornerà lo stato di validità e le informazioni associate.',
        'debug_info' => 'Informazioni di Debug',
        'detected_value' => 'Valore rilevato',
        'not_found' => 'Non trovato',
        'http_code' => 'Codice HTTP',
        'raw_api_response' => 'Risposta API grezza',
        'no_response' => 'Nessuna risposta',
        'unviewable_format' => 'Formato non visualizzabile',
        'auto_dismiss_alerts' => 'Chiudi automaticamente gli avvisi dopo 5 secondi',
        'copied' => 'Copiato!'
    ],
    'ja' => [
        'title' => 'ライセンス管理',
        'info_title' => 'ライセンス情報',
        'installation_key' => 'インストールライセンスキー',
        'copy_key' => 'キーをコピー',
        'status' => 'ライセンスステータス',
        'status_label' => 'ステータス',
        'valid' => '有効',
        'invalid' => '無効',
        'expiry_date' => '有効期限',
        'expiry_date_label' => '有効期限',
        'expires_on' => '有効期限日',
        'last_check' => '最終確認',
        'never' => '確認なし',
        'check_now' => '今すぐ確認',
        'details' => 'ライセンス詳細',
        'domain' => 'ドメイン',
        'ip_address' => 'IPアドレス',
        'not_defined' => '未定義',
        'status_active' => 'アクティブ',
        'status_suspended' => '一時停止',
        'status_revoked' => '取り消し済み',
        'no_details' => 'このライセンスキーの詳細情報はありません。',
        'configuration' => 'ライセンス設定',
        'key_saved_in_env' => 'このキーはアプリケーションの.envファイルに保存されます。',
        'env_not_exists' => '.envファイルはまだ存在しません。自動的に作成されます。',
        'save_settings' => '設定を保存',
        'manual_verification' => '手動検証',
        'manual_verification_desc' => 'インストールライセンスの即時検証を強制できます。これにより、有効性ステータスと関連情報が更新されます。',
        'debug_info' => 'デバッグ情報',
        'detected_value' => '検出値',
        'not_found' => '見つかりません',
        'http_code' => 'HTTPコード',
        'raw_api_response' => '生のAPI応答',
        'no_response' => '応答なし',
        'unviewable_format' => '表示不可能な形式',
        'auto_dismiss_alerts' => '5秒後にアラートを自動的に閉じる',
        'copied' => 'コピーしました！'
    ],
    'nl' => [
        'title' => 'Licentiebeheer',
        'info_title' => 'Licentie-informatie',
        'installation_key' => 'Installatie Licentiesleutel',
        'copy_key' => 'Kopieer Sleutel',
        'status' => 'Licentiestatus',
        'status_label' => 'Status',
        'valid' => 'Geldig',
        'invalid' => 'Ongeldig',
        'expiry_date' => 'Vervaldatum',
        'expiry_date_label' => 'Vervaldatum',
        'expires_on' => 'Verloopt op',
        'last_check' => 'Laatste Controle',
        'never' => 'Nooit',
        'check_now' => 'Nu Controleren',
        'details' => 'Licentiedetails',
        'domain' => 'Domein',
        'ip_address' => 'IP-adres',
        'not_defined' => 'Niet gedefinieerd',
        'status_active' => 'Actief',
        'status_suspended' => 'Opgeschort',
        'status_revoked' => 'Ingetrokken',
        'no_details' => 'Geen gedetailleerde informatie beschikbaar voor deze licentiesleutel.',
        'configuration' => 'Licentieconfiguratie',
        'key_saved_in_env' => 'Deze sleutel wordt opgeslagen in het .env-bestand van uw applicatie.',
        'env_not_exists' => 'Het .env-bestand bestaat nog niet. Het wordt automatisch aangemaakt.',
        'save_settings' => 'Instellingen Opslaan',
        'manual_verification' => 'Handmatige Verificatie',
        'manual_verification_desc' => 'U kunt een onmiddellijke verificatie van de installatielicentie forceren. Dit zal de geldigheidsstatus en bijbehorende informatie bijwerken.',
        'debug_info' => 'Debug Informatie',
        'detected_value' => 'Gedetecteerde waarde',
        'not_found' => 'Niet gevonden',
        'http_code' => 'HTTP-code',
        'raw_api_response' => 'Ruwe API-respons',
        'no_response' => 'Geen respons',
        'unviewable_format' => 'Niet-weergavebaar formaat',
        'auto_dismiss_alerts' => 'Sluit waarschuwingen automatisch na 5 seconden',
        'copied' => 'Gekopieerd!'
    ],
    'pt' => [
        'title' => 'Gerenciamento de Licença',
        'info_title' => 'Informações da Licença',
        'installation_key' => 'Chave de Licença de Instalação',
        'copy_key' => 'Copiar Chave',
        'status' => 'Status da Licença',
        'status_label' => 'Status',
        'valid' => 'Válida',
        'invalid' => 'Inválida',
        'expiry_date' => 'Data de Expiração',
        'expiry_date_label' => 'Data de Expiração',
        'expires_on' => 'Expira em',
        'last_check' => 'Última Verificação',
        'never' => 'Nunca',
        'check_now' => 'Verificar Agora',
        'details' => 'Detalhes da Licença',
        'domain' => 'Domínio',
        'ip_address' => 'Endereço IP',
        'not_defined' => 'Não definido',
        'status_active' => 'Ativa',
        'status_suspended' => 'Suspensa',
        'status_revoked' => 'Revogada',
        'no_details' => 'Não há informações detalhadas disponíveis para esta chave de licença.',
        'configuration' => 'Configuração da Licença',
        'key_saved_in_env' => 'Esta chave será salva no arquivo .env da sua aplicação.',
        'env_not_exists' => 'O arquivo .env ainda não existe. Ele será criado automaticamente.',
        'save_settings' => 'Salvar Configurações',
        'manual_verification' => 'Verificação Manual',
        'manual_verification_desc' => 'Você pode forçar uma verificação imediata da licença de instalação. Isso atualizará o status de validade e as informações associadas.',
        'debug_info' => 'Informações de Depuração',
        'detected_value' => 'Valor detectado',
        'not_found' => 'Não encontrado',
        'http_code' => 'Código HTTP',
        'raw_api_response' => 'Resposta bruta da API',
        'no_response' => 'Sem resposta',
        'unviewable_format' => 'Formato não visualizável',
        'auto_dismiss_alerts' => 'Fechar alertas automaticamente após 5 segundos',
        'copied' => 'Copiado!'
    ],
    'ru' => [
        'title' => 'Управление лицензией',
        'info_title' => 'Информация о лицензии',
        'installation_key' => 'Ключ установочной лицензии',
        'copy_key' => 'Копировать ключ',
        'status' => 'Статус лицензии',
        'status_label' => 'Статус',
        'valid' => 'Действительна',
        'invalid' => 'Недействительна',
        'expiry_date' => 'Дата истечения срока',
        'expiry_date_label' => 'Дата истечения срока',
        'expires_on' => 'Истекает',
        'last_check' => 'Последняя проверка',
        'never' => 'Никогда',
        'check_now' => 'Проверить сейчас',
        'details' => 'Детали лицензии',
        'domain' => 'Домен',
        'ip_address' => 'IP-адрес',
        'not_defined' => 'Не определено',
        'status_active' => 'Активна',
        'status_suspended' => 'Приостановлена',
        'status_revoked' => 'Отозвана',
        'no_details' => 'Для этого лицензионного ключа нет подробной информации.',
        'configuration' => 'Настройка лицензии',
        'key_saved_in_env' => 'Этот ключ будет сохранен в файле .env вашего приложения.',
        'env_not_exists' => 'Файл .env еще не существует. Он будет создан автоматически.',
        'save_settings' => 'Сохранить настройки',
        'manual_verification' => 'Ручная проверка',
        'manual_verification_desc' => 'Вы можете принудительно выполнить немедленную проверку установочной лицензии. Это обновит статус действительности и связанную информацию.',
        'debug_info' => 'Отладочная информация',
        'detected_value' => 'Обнаруженное значение',
        'not_found' => 'Не найдено',
        'http_code' => 'HTTP-код',
        'raw_api_response' => 'Необработанный ответ API',
        'no_response' => 'Нет ответа',
        'unviewable_format' => 'Неотображаемый формат',
        'auto_dismiss_alerts' => 'Автоматически закрывать оповещения через 5 секунд',
        'copied' => 'Скопировано!'
    ],
    'tr' => [
        'title' => 'Lisans Yönetimi',
        'info_title' => 'Lisans Bilgileri',
        'installation_key' => 'Kurulum Lisans Anahtarı',
        'copy_key' => 'Anahtarı Kopyala',
        'status' => 'Lisans Durumu',
        'status_label' => 'Durum',
        'valid' => 'Geçerli',
        'invalid' => 'Geçersiz',
        'expiry_date' => 'Son Kullanma Tarihi',
        'expiry_date_label' => 'Son Kullanma Tarihi',
        'expires_on' => 'Sona erme tarihi',
        'last_check' => 'Son Kontrol',
        'never' => 'Hiçbir zaman',
        'check_now' => 'Şimdi Kontrol Et',
        'details' => 'Lisans Detayları',
        'domain' => 'Alan Adı',
        'ip_address' => 'IP Adresi',
        'not_defined' => 'Tanımlanmamış',
        'status_active' => 'Aktif',
        'status_suspended' => 'Askıya Alınmış',
        'status_revoked' => 'İptal Edilmiş',
        'no_details' => 'Bu lisans anahtarı için ayrıntılı bilgi bulunmamaktadır.',
        'configuration' => 'Lisans Yapılandırması',
        'key_saved_in_env' => 'Bu anahtar uygulamanızın .env dosyasına kaydedilecektir.',
        'env_not_exists' => '.env dosyası henüz mevcut değil. Otomatik olarak oluşturulacaktır.',
        'save_settings' => 'Ayarları Kaydet',
        'manual_verification' => 'Manuel Doğrulama',
        'manual_verification_desc' => 'Kurulum lisansının anında doğrulanmasını zorlayabilirsiniz. Bu, geçerlilik durumunu ve ilişkili bilgileri güncelleyecektir.',
        'debug_info' => 'Hata Ayıklama Bilgileri',
        'detected_value' => 'Algılanan değer',
        'not_found' => 'Bulunamadı',
        'http_code' => 'HTTP Kodu',
        'raw_api_response' => 'Ham API Yanıtı',
        'no_response' => 'Yanıt yok',
        'unviewable_format' => 'Görüntülenemeyen format',
        'auto_dismiss_alerts' => 'Uyarıları 5 saniye sonra otomatik kapat',
        'copied' => 'Kopyalandı!'
    ],
    'zh' => [
        'title' => '许可证管理',
        'info_title' => '许可证信息',
        'installation_key' => '安装许可证密钥',
        'copy_key' => '复制密钥',
        'status' => '许可证状态',
        'status_label' => '状态',
        'valid' => '有效',
        'invalid' => '无效',
        'expiry_date' => '到期日期',
        'expiry_date_label' => '到期日期',
        'expires_on' => '到期于',
        'last_check' => '最后检查',
        'never' => '从未',
        'check_now' => '立即检查',
        'details' => '许可证详情',
        'domain' => '域名',
        'ip_address' => 'IP地址',
        'not_defined' => '未定义',
        'status_active' => '活跃',
        'status_suspended' => '已暂停',
        'status_revoked' => '已撤销',
        'no_details' => '此许可证密钥没有可用的详细信息。',
        'configuration' => '许可证配置',
        'key_saved_in_env' => '此密钥将保存在您应用程序的.env文件中。',
        'env_not_exists' => '.env文件尚不存在。它将自动创建。',
        'save_settings' => '保存设置',
        'manual_verification' => '手动验证',
        'manual_verification_desc' => '您可以强制立即验证安装许可证。这将更新有效性状态和相关信息。',
        'debug_info' => '调试信息',
        'detected_value' => '检测到的值',
        'not_found' => '未找到',
        'http_code' => 'HTTP代码',
        'raw_api_response' => '原始API响应',
        'no_response' => '无响应',
        'unviewable_format' => '不可查看的格式',
        'auto_dismiss_alerts' => '5秒后自动关闭警报',
        'copied' => '已复制！'
    ]
];

// Fonction pour ajouter la section settings_license à un fichier de traduction
function addLicenseTranslations($filePath, $translations) {
    // Lire le contenu du fichier
    $content = file_get_contents($filePath);
    if (!$content) {
        echo "Erreur lors de la lecture du fichier: $filePath\n";
        return false;
    }

    // Vérifier si la section settings_license existe déjà
    if (strpos($content, '"settings_license"') !== false) {
        echo "La section settings_license existe déjà dans: $filePath\n";
        return true;
    }

    // Trouver un point d'insertion approprié (juste avant licence_documentation)
    $insertPoint = strpos($content, '"licence_documentation"');
    if ($insertPoint === false) {
        // Essayer un autre point d'insertion (avant la dernière accolade)
        $insertPoint = strrpos($content, '}');
        if ($insertPoint === false) {
            echo "Impossible de trouver un point d'insertion dans: $filePath\n";
            return false;
        }
        // Reculer pour trouver le début de la ligne
        $lineStart = strrpos(substr($content, 0, $insertPoint), "\n") + 1;
        $insertPoint = $lineStart;
    } else {
        // Reculer pour trouver le début de la ligne
        $lineStart = strrpos(substr($content, 0, $insertPoint), "\n") + 1;
        $insertPoint = $lineStart;
    }

    // Générer la section settings_license
    $licenseSection = '    "settings_license": {
        "license": ' . json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . '
    },
';

    // Insérer la section dans le contenu
    $newContent = substr($content, 0, $insertPoint) . $licenseSection . substr($content, $insertPoint);

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
        addLicenseTranslations($filePath, $translations);
    } else {
        echo "Le fichier $filePath n'existe pas.\n";
    }
}

echo "Mise à jour des traductions terminée.\n";
