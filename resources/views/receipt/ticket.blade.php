<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reçu {{ $sale->number }}</title>
<style>
  @page { size: 80mm auto; margin: 3mm; }
  body{font-family:Arial,Helvetica,sans-serif;font-size:12px;color:#000;max-width:300px;margin:0 auto;padding:10px}
  table{width:100%;border-collapse:collapse} td{padding:2px 0;vertical-align:top}
  .r{text-align:right}.c{text-align:center} hr{border:0;border-top:1px dashed #000;margin:8px 0}
  .bar{display:flex;gap:8px;justify-content:center;margin-bottom:12px}
  .bar button,.bar a{padding:9px 16px;border-radius:8px;border:1px solid #999;background:#fff;font-weight:700;text-decoration:none;color:#000;cursor:pointer}
  .bar button{background:#0a8f3c;color:#fff;border-color:#0a8f3c}
  @media print{.bar{display:none} body{padding:0}}
</style></head>
<body>
@php($S = \App\Support\Setting::class)
@php($M = \App\Support\Money::class)
<div class="bar"><button onclick="window.print()">🖨️ Imprimer</button>@if (! empty($back))<a href="{{ $back }}">➕ Nouvelle vente</a>@endif<a href="{{ route('invoice', $sale) }}" target="_blank">Facture A4</a></div>
<div class="c"><img src="{{ asset('images/logo.jpg') }}" alt="" style="width:150px" onerror="this.style.display='none'"></div>
<div class="c"><b>{{ $S::get('company_name') }}</b><br>
  @if($S::get('phone')){{ $S::get('phone') }}<br>@endif
  @if($S::get('address')){{ $S::get('address') }}<br>@endif</div>
<hr>
<div style="text-align:center;color:#0a8f3c;font-weight:700;margin-bottom:6px">✅ Vente enregistrée</div>
<div>Reçu {{ $sale->number }}<br>{{ $sale->created_at->format('d/m/Y H:i') }}<br>Client : {{ $sale->customer_name }}@if($sale->seller)<br>Vendeur : {{ $sale->seller->name }}@endif</div>
<hr>
<table>
@foreach ($sale->items as $i)
  <tr><td colspan="2"><b>{{ $i->name }}</b></td></tr>
  <tr><td>{{ $i->quantity }} × {{ $M::fmt($i->unit_price) }}</td><td class="r">{{ $M::fmt($i->quantity * $i->unit_price) }}</td></tr>
@endforeach
</table>
<hr>
<table>
  @if ($sale->discount > 0)<tr><td>Remise</td><td class="r">−{{ $M::fmt($sale->discount) }}</td></tr>@endif
  <tr><td><b>TOTAL</b></td><td class="r"><b>{{ $M::fmt($sale->total) }}</b></td></tr>
  @if ($sale->tax > 0)<tr><td>dont TVA</td><td class="r">{{ $M::fmt($sale->tax) }}</td></tr>@endif
  <tr><td>Payé</td><td class="r">{{ $M::fmt($sale->paid) }}</td></tr>
  @if ($sale->balance > 0)<tr><td><b>Reste à payer</b></td><td class="r"><b>{{ $M::fmt($sale->balance) }}</b></td></tr>@endif
</table>
<hr>
<div class="c">{{ $S::get('invoice_footer') }}<br>Merci de votre visite !</div>
<script>if (location.search.includes('print')) window.onload = () => window.print();</script>
</body></html>
