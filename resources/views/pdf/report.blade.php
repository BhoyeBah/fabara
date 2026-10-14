<!doctype html>
<html lang="fr"><head><meta charset="utf-8">
<style>
  body{font-family:'DejaVu Sans',sans-serif;font-size:12px;color:#111}
  table{width:100%;border-collapse:collapse;margin-bottom:14px}
  td,th{padding:5px;border-bottom:1px solid #ddd;text-align:left}
  .r{text-align:right} h2{font-size:14px;margin:14px 0 4px}
</style></head>
<body>
@php($M = \App\Support\Money::class)
<img src="{{ public_path('images/logo.jpg') }}" style="height:60px">
<h1 style="font-size:18px">Rapport comptable — {{ $r['month'] ?: 'depuis le début' }}</h1>
<h2>Résultat</h2>
<table>
  <tr><td>Chiffre d’affaires ({{ $r['sales_count'] }} ventes)</td><td class="r">{{ $M::fmt($r['ca']) }}</td></tr>
  <tr><td>dont TVA collectée</td><td class="r">{{ $M::fmt($r['tax']) }}</td></tr>
  <tr><td>Chiffre d’affaires hors taxe</td><td class="r">{{ $M::fmt($r['ht']) }}</td></tr>
  <tr><td>Coût des articles vendus</td><td class="r">−{{ $M::fmt($r['cost']) }}</td></tr>
  <tr><td>Marge brute</td><td class="r">{{ $M::fmt($r['gross']) }}</td></tr>
  <tr><td>Charges</td><td class="r">−{{ $M::fmt($r['charges_total']) }}</td></tr>
  <tr><td><b>Bénéfice net</b></td><td class="r"><b>{{ $M::fmt($r['net']) }}</b></td></tr>
</table>
<h2>À recevoir et à payer (à ce jour)</h2>
<table>
  <tr><td>Encaissé sur la période</td><td class="r">{{ $M::fmt($r['collected']) }}</td></tr>
  <tr><td>Créances clients</td><td class="r">{{ $M::fmt($r['receivables']) }}</td></tr>
  <tr><td>Dettes fournisseurs</td><td class="r">{{ $M::fmt($r['supplier_debt']) }}</td></tr>
  <tr><td>Achats fournisseurs de la période</td><td class="r">{{ $M::fmt($r['purchases']) }}</td></tr>
</table>
<h2>Ventes par menu</h2>
<table>
  <tr><td>Boutique</td><td class="r">{{ $M::fmt($r['by_menu']['boutique']) }}</td></tr>
  <tr><td>Imprimerie</td><td class="r">{{ $M::fmt($r['by_menu']['imprimerie']) }}</td></tr>
</table>
<h2>Charges par catégorie</h2>
<table>
  @forelse ($r['charges'] as $cat => $v)<tr><td>{{ $cat }}</td><td class="r">{{ $M::fmt($v) }}</td></tr>@empty<tr><td>Aucune.</td></tr>@endforelse
</table>
<h2>Articles les plus vendus</h2>
<table>
  @forelse ($r['top'] as $name => $row)<tr><td>{{ $name }}</td><td class="r">{{ $row['qty'] }} ×</td><td class="r">{{ $M::fmt($row['amount']) }}</td></tr>@empty<tr><td>Aucune vente.</td></tr>@endforelse
</table>
<p style="color:#777">Édité le {{ now()->format('d/m/Y H:i') }}</p>
</body></html>
