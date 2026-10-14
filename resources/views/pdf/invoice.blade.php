<!doctype html>
<html lang="fr"><head><meta charset="utf-8">
<style>
  body{font-family:'DejaVu Sans',sans-serif;font-size:12px;color:#111}
  table{width:100%;border-collapse:collapse}
  th,td{padding:6px;border-bottom:1px solid #ddd;text-align:left}
  .r{text-align:right}
  h1{font-size:18px;margin:0}
</style></head>
<body>
@php($S = \App\Support\Setting::class)
@php($M = \App\Support\Money::class)
<img src="{{ public_path('images/logo.jpg') }}" style="height:70px">
<p style="color:#555">{{ $S::get('company_name') }} @if($S::get('phone')) · {{ $S::get('phone') }} @endif @if($S::get('address')) · {{ $S::get('address') }} @endif</p>
<h1 style="margin-top:8px">Facture {{ $sale->number }} @if($sale->cancelled_at)<span style="color:#c0262d"> — ANNULÉE</span>@endif</h1>
<p>Date : {{ $sale->created_at->format('d/m/Y H:i') }}<br>Client : {{ $sale->customer_name }}@if($sale->seller)<br>Vendeur : {{ $sale->seller->name }}@endif</p>
<table>
  <tr><th>Désignation</th><th class="r">Qté</th><th class="r">Prix unitaire</th><th class="r">Montant</th></tr>
  @foreach ($sale->items as $i)
    <tr><td>{{ $i->name }}</td><td class="r">{{ $i->quantity }}</td><td class="r">{{ $M::fmt($i->unit_price) }}</td><td class="r">{{ $M::fmt($i->quantity * $i->unit_price) }}</td></tr>
  @endforeach
</table>
<p class="r">
  @if ($sale->discount > 0) Remise : −{{ $M::fmt($sale->discount) }}<br> @endif
  <b>Total : {{ $M::fmt($sale->total) }}</b><br>
  @if ($sale->tax > 0) <span style="color:#555">dont TVA : {{ $M::fmt($sale->tax) }}</span><br> @endif
  Versé : {{ $M::fmt($sale->paid) }}
  @if ($sale->balance > 0)<br><b>Reste à payer : {{ $M::fmt($sale->balance) }}</b>@endif
</p>
<p style="text-align:center;margin-top:30px">{{ $S::get('invoice_footer') }}</p>
</body></html>
