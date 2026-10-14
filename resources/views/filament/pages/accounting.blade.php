<x-filament-panels::page>
    @php($r = $this->getReport())
    @php($m = fn ($v) => \App\Support\Money::fmt($v))
    <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
        <label for="mois"><b>Période</b></label>
        <input id="mois" type="month" wire:model.live="month" style="border:1px solid #ccc;border-radius:8px;padding:6px 10px">
        <small>(vide = depuis le début)</small>
        <x-filament::button tag="a" color="gray" size="sm" target="_blank" :href="route('report.pdf', ['month' => $month])">Rapport PDF</x-filament::button>
        <x-filament::button tag="a" color="gray" size="sm" :href="route('export', ['type' => 'ventes', 'month' => $month])">Ventes (Excel/CSV)</x-filament::button>
        <x-filament::button tag="a" color="gray" size="sm" :href="route('export', ['type' => 'charges', 'month' => $month])">Charges (CSV)</x-filament::button>
        <x-filament::button tag="a" color="gray" size="sm" :href="route('export', ['type' => 'achats', 'month' => $month])">Achats (CSV)</x-filament::button>
    </div>

    <x-filament::section heading="Résultat de la période">
        <table style="width:100%;border-collapse:collapse">
            @foreach ([
                ['Chiffre d’affaires (' . $r['sales_count'] . ' ventes)', $r['ca']],
                ['dont TVA collectée', $r['tax']],
                ['Chiffre d’affaires hors taxe', $r['ht']],
                ['Coût des articles vendus', -$r['cost']],
                ['Marge brute', $r['gross']],
                ['Charges (loyer, salaires…)', -$r['charges_total']],
            ] as [$label, $v])
                <tr><td style="padding:6px 0;border-bottom:1px solid #eee">{{ $label }}</td><td style="text-align:right;border-bottom:1px solid #eee">{{ $m($v) }}</td></tr>
            @endforeach
            <tr><td style="padding:8px 0"><b>Bénéfice net</b></td><td style="text-align:right"><b style="color:{{ $r['net'] >= 0 ? '#0a8f3c' : '#c0262d' }}">{{ $m($r['net']) }}</b></td></tr>
        </table>
    </x-filament::section>

    <x-filament::section heading="Argent à recevoir et à payer (à ce jour)">
        <table style="width:100%">
            <tr><td>Encaissé sur la période</td><td style="text-align:right">{{ $m($r['collected']) }}</td></tr>
            <tr><td>Créances clients (reste à encaisser)</td><td style="text-align:right;color:#c0262d">{{ $m($r['receivables']) }}</td></tr>
            <tr><td>Dettes fournisseurs (reste à payer)</td><td style="text-align:right;color:#c0262d">{{ $m($r['supplier_debt']) }}</td></tr>
            <tr><td>Achats fournisseurs de la période (information)</td><td style="text-align:right">{{ $m($r['purchases']) }}</td></tr>
        </table>
    </x-filament::section>

    <x-filament::section heading="Ventes par menu">
        <table style="width:100%">
            <tr><td>Boutique</td><td style="text-align:right">{{ $m($r['by_menu']['boutique']) }}</td></tr>
            <tr><td>Imprimerie</td><td style="text-align:right">{{ $m($r['by_menu']['imprimerie']) }}</td></tr>
        </table>
    </x-filament::section>

    <x-filament::section heading="Charges par catégorie">
        <table style="width:100%">
            @forelse ($r['charges'] as $cat => $v)
                <tr><td>{{ $cat }}</td><td style="text-align:right">{{ $m($v) }}</td></tr>
            @empty
                <tr><td>Aucune charge sur la période.</td></tr>
            @endforelse
        </table>
    </x-filament::section>

    <x-filament::section heading="Articles les plus vendus">
        <table style="width:100%">
            @forelse ($r['top'] as $name => $row)
                <tr><td>{{ $name }}</td><td style="text-align:right">{{ $row['qty'] }} ×</td><td style="text-align:right">{{ $m($row['amount']) }}</td></tr>
            @empty
                <tr><td>Aucune vente sur la période.</td></tr>
            @endforelse
        </table>
    </x-filament::section>
</x-filament-panels::page>
