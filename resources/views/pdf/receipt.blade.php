<!doctype html>
<html lang="fr"><head><meta charset="utf-8">
<style>
  @page { margin: 8px; }
  body{font-family:'DejaVu Sans',sans-serif;font-size:9px;color:#000}
  table{width:100%;border-collapse:collapse}
  td{padding:2px 0;vertical-align:top}
  .r{text-align:right} .c{text-align:center}
  hr{border:0;border-top:1px dashed #000;margin:5px 0}
</style></head>
<body>
@php($S = \App\Support\Setting::class)
@php($M = \App\Support\Money::class)
<div class="c"><img src="{{ public_path('images/logo.jpg') }}" style="width:150px"></div>
<div class="c">
  <b>{{ $S::get('company_name') }}</b><br>
  @if($S::get('phone')){{ $S::get('phone') }}<br>@endif
  @if($S::get('address')){{ $S::get('address') }}<br>@endif
</div>
<hr>
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
<div class="c">{{ $S::get('invoice_footer') }}</div>
</body></html>
