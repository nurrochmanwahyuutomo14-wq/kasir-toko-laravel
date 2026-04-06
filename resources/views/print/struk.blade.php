<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk #{{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 58mm;
            padding: 5px;
            font-size: 12px;
        }

        .text-center {
            text-align: center;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .total {
            font-weight: bold;
            font-size: 14px;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="text-center">
        <strong>TOKO</strong><br>
        Blitar, Jawa Timur<br>
        {{ $transaction->created_at->format('d/m/Y H:i') }}
    </div>
    <div class="line"></div>
    <table>
        @foreach($transaction->details as $detail)
        <tr>
            <td colspan="2">{{ $detail->product->name }}</td>
        </tr>
        <tr>
            <td>{{ $detail->qty }} x {{ number_format($detail->price) }}</td>
            <td style="text-align: right;">{{ number_format($detail->subtotal) }}</td>
        </tr>
        @endforeach
    </table>
    <div class="line"></div>
    <table>
        <tr class="total">
            <td>TOTAL</td>
            <td style="text-align: right;">Rp {{ number_format($transaction->total_price) }}</td>
        </tr>
        <tr>
            <td>Bayar ({{ $transaction->payment_method }})</td>
            <td style="text-align: right;">Rp {{ number_format($transaction->amount_paid) }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td style="text-align: right;">Rp {{ number_format($transaction->change_amount) }}</td>
        </tr>
    </table>
    <div class="line"></div>
    <div class="text-center">Terima Kasih Atas Kunjungan Anda</div>
</body>

</html>