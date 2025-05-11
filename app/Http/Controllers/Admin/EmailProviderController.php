<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Mail\MailProviderFactory;
use App\Services\MailService;
use Illuminate\Http\Request;

class EmailProviderController extends Controller
{
    protected $mailService;
    protected $providers = [
        'smtp' => 'SMTP',
        'mailgun' => 'Mailgun',
        'phpmail' => 'PHPMail',
        'mailchimp' => 'Mailchimp',
    ];

    public function __construct(MailService $mailService)
    {
        $this->mailService = $mailService;
    }

    public function index()
    {
        $settings = $this->mailService->getSettings();
        $providers = $this->providers;

        return view('admin.email.providers', compact('settings', 'providers'));
    }

    public function templates()
    {
        return view('admin.email.templates');
    }

    public function storeTemplate(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'required|string',
            'variables' => 'nullable|array'
        ]);

        // Logique pour sauvegarder le template

        return redirect()->route('admin.email.templates')
            ->with('success', 'Template enregistré avec succès');
    }

    public function updateProvider(Request $request)
    {
        $validated = $request->validate([
            'provider' => 'required|string',
            'settings' => 'required|array'
        ]);

        $this->mailService->updateSettings([
            'provider' => $validated['provider'],
            $validated['provider'] => $validated['settings']
        ]);

        return redirect()->route('admin.mail.providers.index')->with('success', 'Configuration mise à jour avec succès.');
    }

    public function testProvider(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            $this->mailService->send(
                $request->email,
                'Test de configuration email',
                'Ceci est un email de test pour vérifier la configuration de votre fournisseur d\'email.'
            );

            return back()->with('success', 'Email de test envoyé avec succès');
        } catch (\Exception $e) {
            return back()->with('error', 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage());
        }
    }
}