<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture #{{ $invoice->number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }
        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            border-bottom: 1px solid #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .invoice-details {
            float: right;
            text-align: right;
        }
        .invoice-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .client-details, .payment-details {
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f5f5f5;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
            font-size: 12px;
            color: #777;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-weight: bold;
            color: white;
        }
        .status-paid {
            background-color: #28a745;
        }
        .status-pending {
            background-color: #dc3545;
        }
        .status-refunded {
            background-color: #ffc107;
            color: #333;
        }
        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <div class="logo">{{ config('app.name') }}</div>
            <div class="invoice-details">
                <div class="invoice-title">Facture #{{ $invoice->number }}</div>
                <div>Date d'émission: {{ $invoice->created_at->format('d/m/Y') }}</div>
                <div>Date d'échéance: {{ $invoice->due_at ? $invoice->due_at->format('d/m/Y') : 'N/A' }}</div>
                <div>
                    Statut: 
                    <span class="status {{ $invoice->status === 'paid' ? 'status-paid' : ($invoice->status === 'refunded' ? 'status-refunded' : 'status-pending') }}">
                        {{ $invoice->status === 'paid' ? 'Payée' : ($invoice->status === 'refunded' ? 'Remboursée' : 'En attente') }}
                    </span>
                </div>
            </div>
        </div>

        <div class="clearfix">
            <div style="float: left; width: 50%;">
                <div class="section-title">Émetteur</div>
                <div>{{ config('app.name') }}</div>
                <div>123 Rue de l'Exemple</div>
                <div>75000 Paris, France</div>
                <div>contact@example.com</div>
                <div>+33 1 23 45 67 89</div>
                <div>SIRET: 123 456 789 00012</div>
                <div>TVA: FR12345678900</div>
            </div>

            <div style="float: right; width: 50%;">
                <div class="section-title">Facturé à</div>
                <div>{{ $invoice->tenant->name }}</div>
                <div>{{ $invoice->billing_details['address'] ?? 'Adresse non spécifiée' }}</div>
                <div>{{ $invoice->billing_details['email'] ?? 'Email non spécifié' }}</div>
            </div>
        </div>

        <div style="margin-top: 40px;">
            <div class="section-title">Détails de la facture</div>
            <table>
                <thead>
                    <tr>
                        <th>Description</th>
                        <th class="text-right">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @if($invoice->items->count() > 0)
                        @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-right">{{ number_format($item->amount, 2) }} {{ strtoupper($invoice->currency) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td>{{ $invoice->description }}</td>
                            <td class="text-right">{{ number_format($invoice->total, 2) }} {{ strtoupper($invoice->currency) }}</td>
                        </tr>
                    @endif
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td>Total</td>
                        <td class="text-right">{{ number_format($invoice->total, 2) }} {{ strtoupper($invoice->currency) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="margin-top: 20px;">
            <div class="section-title">Informations de paiement</div>
            <div>Méthode: {{ $invoice->payment_method_type === 'credit_card' ? 'Carte de crédit' : ($invoice->payment_method_type === 'paypal' ? 'PayPal' : 'Autre') }}</div>
            <div>Date de paiement: {{ $invoice->paid_at ? $invoice->paid_at->format('d/m/Y H:i') : 'N/A' }}</div>
            <div>ID de transaction: {{ $invoice->provider_id ?? 'N/A' }}</div>
        </div>

        <div class="footer">
            <p>Merci pour votre confiance. Pour toute question concernant cette facture, veuillez contacter notre service client.</p>
            <p>Cette facture a été générée automatiquement et ne nécessite pas de signature.</p>
        </div>
    </div>
</body>
</html>
