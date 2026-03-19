<!DOCTYPE html>
<html>
<head>
    <title>Facture E-Shop</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 50px; border-bottom: 2px solid #1A1A2E; padding-bottom: 20px; }
        .details { margin-bottom: 30px; line-height: 1.6; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th { background-color: #1A1A2E; color: white; padding: 12px; text-align: left; }
        .table td { border: 1px solid #ddd; padding: 12px; }
        .total { font-weight: bold; margin-top: 30px; text-align: right; font-size: 1.2rem; color: #FF6B35; }
        .footer { margin-top: 50px; text-align: center; font-size: 0.8rem; color: #888; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Facture E-Shop</h1>
        <p>Référence Commande: #{{ substr($order->id, 0, 8) }}</p>
    </div>
    
    <div class="details">
        <p><strong>Destinataire:</strong> {{ $order->user->name }}</p>
        <p><strong>Email:</strong> {{ $order->user->email }}</p>
        <p><strong>Date d'émission:</strong> {{ now()->format('d/m/Y') }}</p>
        <p><strong>Statut:</strong> {{ ucfirst($order->status) }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Désignation du Produit</th>
                <th>Prix Unitaire</th>
                <th>Quantité</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $order->product->name }}</td>
                <td>{{ number_format($order->product->price, 0, ',', ' ') }} FCFA</td>
                <td>1</td>
                <td>{{ number_format($order->product->price, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        Montant Total à Payer: {{ number_format($order->product->price, 0, ',', ' ') }} FCFA
    </div>

    <div class="footer">
        <p>Merci de votre confiance sur Le Shop Du Coin !</p>
        <p>E-Shop - Solution de commerce en ligne moderne</p>
    </div>
</body>
</html>
