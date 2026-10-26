<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Estado de {{ $order->number }} · TallerLab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f5f7fa;color:#15233b;font:15px 'DM Sans',sans-serif}.wrap{max-width:700px;margin:0 auto;padding:48px 20px}.brand{color:#315cf5;font:800 17px Manrope,sans-serif;margin-bottom:28px}.card{background:#fff;border:1px solid #e8edf3;border-radius:14px;padding:26px;box-shadow:0 12px 36px #15233b0a;margin-bottom:14px}.eyebrow{color:#8290a5;text-transform:uppercase;letter-spacing:1.2px;font-size:11px;font-weight:700}h1{font:700 26px Manrope;margin:7px 0}.device{color:#68768c;margin:0 0 25px}.status{border-radius:10px;padding:16px;background:#e9efff;color:#3156c8;font-weight:700;font-size:17px}.status.ready{background:#e4f7f1;color:#138d76}.status.cancelled{background:#ffeded;color:#cc5c5d}.track{display:flex;gap:5px;margin:20px 0 8px}.track span{height:6px;border-radius:9px;background:#e8edf3;flex:1}.track span.done{background:#315cf5}.track span.ready{background:#16a895}.meta{display:grid;grid-template-columns:1fr 1fr;gap:18px;border-top:1px solid #e8edf3;margin-top:24px;padding-top:20px}.meta small{display:block;color:#8290a5;margin-bottom:5px}.foot{color:#8290a5;font-size:12px;text-align:center;margin-top:18px}.table{width:100%;border-collapse:collapse;margin-top:14px}.table th,.table td{text-align:left;padding:11px 8px;border-bottom:1px solid #e8edf3}.table th{font-size:12px;color:#8290a5}.table tfoot th{text-align:right;color:#15233b}.invoice{margin-top:15px;padding:13px;border-radius:9px;background:#e4f7f1;color:#138d76}.muted{color:#8290a5}@media(max-width:520px){.wrap{padding:28px 14px}.card{padding:20px}.meta{grid-template-columns:1fr}}
    </style>
</head>
<body>
<main class="wrap">
    <div class="brand">TallerLab · Gestión técnica</div>
    <section class="card">
        <p class="eyebrow">Seguimiento de orden de servicio</p>
        <h1>{{ $order->number }}</h1>
        <p class="device">{{ $order->device_type }} · {{ trim(($order->brand ?? '').' '.($order->model ?? '')) ?: 'Equipo' }}</p>
        @php
            $progressStatuses = ['Recibido', 'En diagnóstico', 'Esperando aprobación', 'En reparación', 'Listo para entrega', 'Entregado'];
            $progress = array_search($order->status, $progressStatuses, true);
            $isCancelled = $order->status === 'Cancelado';
            $isReady = in_array($order->status, ['Listo para entrega', 'Entregado'], true);
        @endphp
        <div class="status {{ $isCancelled ? 'cancelled' : ($isReady ? 'ready' : '') }}">Estado actual: {{ $order->status }}</div>
        @if(!$isCancelled)
            <div class="track" aria-label="Progreso del servicio">
                @foreach($progressStatuses as $index => $status)
                    <span class="{{ $progress !== false && $index <= $progress ? ($isReady ? 'ready' : 'done') : '' }}" title="{{ $status }}"></span>
                @endforeach
            </div>
        @endif
        <div class="meta">
            <div><small>Fecha de recepción</small><strong>{{ \Carbon\Carbon::parse($order->received_at)->format('d/m/Y') }}</strong></div>
            <div><small>Última actualización</small><strong>{{ \Carbon\Carbon::parse($order->updated_at)->format('d/m/Y H:i') }}</strong></div>
        </div>
    </section>
    <section class="card">
        <p class="eyebrow">Trabajos y repuestos de la orden</p>
        @if($chargeLines->isNotEmpty())
            <table class="table"><thead><tr><th>Tipo</th><th>Detalle</th><th>Cant.</th><th>Importe</th></tr></thead><tbody>
                @foreach($chargeLines as $line)
                    <tr><td>{{ $line->type }}</td><td>{{ $line->description }}</td><td>{{ $line->quantity }}</td><td>${{ number_format($line->quantity * $line->unit_price, 2) }}</td></tr>
                @endforeach
            </tbody><tfoot><tr><th colspan="3">Total de cargos</th><th>${{ number_format($chargeTotal, 2) }}</th></tr></tfoot></table>
        @else
            <p class="muted">Todavía no se han registrado repuestos ni trabajos facturables.</p>
        @endif
        @if($invoice)
            <div class="invoice">Factura {{ $invoice->number }} · {{ $invoice->status }}<br><strong>Total de factura: ${{ number_format($invoice->total, 2) }}</strong></div>
        @else
            <p class="muted">Los cargos pueden cambiar mientras la orden siga en proceso.</p>
        @endif
    </section>
    <p class="foot">Esta página es solo de consulta. Para cambios o consultas, comunícate directamente con el taller.</p>
</main>
</body>
</html>
