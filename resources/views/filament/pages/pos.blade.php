<x-filament-panels::page>
@php($lines = $this->getLines())
@php($sub = array_sum(array_column($lines, 'amount')))
@php($disc = min($this->discountValue(), $sub))
@php($total = max(0, $sub - $disc))
@php($m = fn ($v) => \App\Support\Money::fmt($v))
@php($inCart = collect($lines)->mapWithKeys(fn ($l) => [$l['p']->id => $l['qty']]))
@php($paidValue = ($paid === null || $paid === '') ? null : (float) $paid)
<style>
.pos{padding-bottom:120px}
.pos-search{width:100%;border:1px solid #e3e3e8;border-radius:14px;padding:14px 16px 14px 44px;font-size:1rem;background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' fill='none' stroke='%239ca3af' stroke-width='2' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cpath d='m21 21-4.3-4.3'/%3E%3C/svg%3E") no-repeat 14px center;outline:none}
.pos-search:focus{border-color:#d6196b;box-shadow:0 0 0 3px rgba(214,25,107,.12)}
.pos-tabs{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.pos-tab{border:1px solid #e3e3e8;background:#fff;border-radius:999px;padding:8px 18px;font-weight:700;font-size:.9rem;transition:all .15s}
.pos-tab.on{background:#171717;color:#fff;border-color:#171717}
.pos-subs{display:flex;gap:8px;overflow-x:auto;padding:10px 0 4px;scrollbar-width:none}
.pos-subs::-webkit-scrollbar{display:none}
.pos-sub{flex:none;border:1px solid #e3e3e8;background:#fff;border-radius:999px;padding:6px 14px;font-size:.85rem;white-space:nowrap}
.pos-sub.on{background:#d6196b;border-color:#d6196b;color:#fff}
.pos-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:14px;margin-top:10px}
.pos-tile{position:relative;text-align:left;border:1px solid #ececf0;border-radius:16px;padding:10px;background:#fff;cursor:pointer;display:flex;flex-direction:column;gap:4px;transition:transform .15s,box-shadow .15s,border-color .15s}
.pos-tile:hover:not(:disabled){transform:translateY(-3px);box-shadow:0 14px 26px -14px rgba(0,0,0,.35);border-color:#d6196b}
.pos-tile:active:not(:disabled){transform:scale(.97)}
.pos-tile:disabled{opacity:.5;cursor:not-allowed}
.pos-tile.has{border-color:#0a8f3c;box-shadow:0 0 0 2px rgba(10,143,60,.18)}
.pos-img{width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:11px}
.pos-ph{width:100%;aspect-ratio:1/1;border-radius:11px;display:grid;place-items:center;font-size:42px;background:linear-gradient(135deg,rgba(214,25,107,.09),rgba(246,196,0,.16))}
.pos-name{font-weight:700;font-size:.9rem;line-height:1.25;min-height:2.3em}
.pos-price{color:#d6196b;font-weight:800;font-size:1.02rem}
.pos-stock{font-size:.74rem;color:#6b7280}
.pos-badge{position:absolute;top:6px;right:6px;background:#0a8f3c;color:#fff;font-weight:800;border-radius:999px;min-width:28px;height:28px;display:grid;place-items:center;padding:0 8px;font-size:.85rem;box-shadow:0 4px 10px rgba(10,143,60,.4)}
.pos-line{display:grid;grid-template-columns:1fr auto auto;gap:10px;align-items:center;padding:10px 0;border-bottom:1px solid #f1f1f4}
.pos-q{display:flex;align-items:center;gap:8px}
.pos-q button{border:1px solid #e3e3e8;border-radius:10px;width:36px;height:36px;font-size:1.2rem;font-weight:800;background:#fafafa}
.pos-label{display:block;font-size:.78rem;font-weight:700;color:#6b7280;margin:12px 0 6px;text-transform:uppercase;letter-spacing:.04em}
.pos-in{width:100%;border:1px solid #e3e3e8;border-radius:12px;padding:11px 14px;background:#fff;outline:none}
.pos-methods{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}
.pos-method{border:1px solid #e3e3e8;border-radius:12px;padding:10px;font-weight:700;background:#fff}
.pos-method.on{border-color:#d6196b;background:rgba(214,25,107,.08);color:#d6196b}
.pos-hint{color:#c0262d;font-size:.85rem;margin-top:6px}
/* Barre fixe en bas : toujours visible */
.pos-bar{position:fixed;left:0;right:0;bottom:0;z-index:60;background:#fff;border-top:3px solid #0a8f3c;box-shadow:0 -10px 30px -12px rgba(0,0,0,.35);padding:10px 16px;display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap}
.pos-bar .info{display:flex;flex-direction:column;line-height:1.15}
.pos-bar .info small{color:#6b7280;font-weight:700}
.pos-bar .info strong{font-size:1.9rem;font-weight:900}
.pos-bar .acts{display:flex;gap:10px;align-items:center;flex:1;justify-content:flex-end}
.pos-see{border:1px solid #d1d5db;border-radius:12px;padding:12px 16px;font-weight:700;background:#fff}
.pos-go{border-radius:14px;padding:15px 24px;font-size:1.1rem;font-weight:900;color:#fff;background:linear-gradient(135deg,#0a8f3c,#16a34a);box-shadow:0 10px 20px -10px rgba(10,143,60,.8);flex:1;max-width:420px}
.pos-go:disabled{background:#d1d5db;box-shadow:none;cursor:not-allowed}
.pos-sheet{position:fixed;left:0;right:0;bottom:84px;z-index:59;max-height:calc(100vh - 170px);overflow-y:auto;background:#fff;border-top:1px solid #e5e7eb;border-radius:20px 20px 0 0;box-shadow:0 -14px 40px -14px rgba(0,0,0,.4);padding:16px 18px;max-width:640px;margin:0 auto}
.pos-ok{position:fixed;inset:0;z-index:80;background:rgba(0,0,0,.55);display:grid;place-items:center;padding:16px}
.pos-ok > div{background:#fff;border-radius:24px;padding:28px;max-width:420px;width:100%;text-align:center}
.pos-ok .big{display:block;background:#0a8f3c;color:#fff;font-weight:900;font-size:1.2rem;border-radius:14px;padding:16px;text-decoration:none;margin-top:18px}
.pos-ok .sec{display:block;width:100%;border:1px solid #d1d5db;border-radius:14px;padding:14px;font-weight:700;margin-top:10px}
</style>

<div class="pos" x-data="{ open: false }">
    <input type="search" class="pos-search" placeholder="Chercher un article…" wire:model.live.debounce.250ms="search" autofocus>
    <div class="pos-tabs">
        @foreach (['tous' => 'Tout', 'boutique' => '🛍️ Boutique', 'imprimerie' => '🖨️ Imprimerie'] as $k => $label)
            <button type="button" class="pos-tab {{ $menu === $k ? 'on' : '' }}" wire:click="setMenu('{{ $k }}')">{{ $label }}</button>
        @endforeach
    </div>
    @php($cats = $this->getCategories())
    @if ($cats->count() > 1)
        <div class="pos-subs">
            <button type="button" class="pos-sub {{ $category === null ? 'on' : '' }}" wire:click="setCategory(null)">Tous</button>
            @foreach ($cats as $c)
                <button type="button" class="pos-sub {{ $category === $c->id ? 'on' : '' }}" wire:click="setCategory({{ $c->id }})">{{ $c->icon() }} {{ $c->name }}</button>
            @endforeach
        </div>
    @endif

    <div class="pos-grid">
        @forelse ($this->getProducts() as $p)
            @php($out = $p->stock !== null && $p->stock <= 0)
            @php($qty = $inCart[$p->id] ?? 0)
            <button type="button" class="pos-tile {{ $qty ? 'has' : '' }}" wire:click="add({{ $p->id }})" wire:key="p-{{ $p->id }}" @disabled($out)>
                @if ($qty)<span class="pos-badge">{{ $qty }}</span>@endif
                @if ($p->image_url)
                    <img class="pos-img" src="{{ $p->image_url }}" alt="" loading="lazy">
                @else
                    <div class="pos-ph">{{ $p->category?->icon() ?? '🛍️' }}</div>
                @endif
                <span class="pos-name">{{ $p->name }}</span>
                <span class="pos-price">{{ $m($p->price) }}</span>
                <span class="pos-stock">{{ $out ? 'Épuisé' : ($p->stock === null ? 'Disponible' : 'Stock : ' . $p->stock) }}</span>
            </button>
        @empty
            <p style="color:#6b7280;grid-column:1/-1;padding:24px 0">Aucun article trouvé. L’administrateur doit d’abord ajouter les articles avec leur prix (menu Articles).</p>
        @endforelse
    </div>

    {{-- Détail du panier (s'ouvre au-dessus de la barre) --}}
    <div class="pos-sheet" x-show="open" x-cloak x-transition @click.outside="open = false">
        <div style="display:flex;justify-content:space-between;align-items:center"><b style="font-size:1.1rem">🧺 Ce que le client a pris</b><button type="button" @click="open = false" style="font-size:1.4rem;padding:0 8px">✕</button></div>
        @forelse ($lines as $l)
            <div class="pos-line" wire:key="l-{{ $l['p']->id }}">
                <div><b>{{ $l['p']->name }}</b><br><small style="color:#6b7280">{{ $m($l['p']->price) }} l’unité</small></div>
                <div class="pos-q">
                    <button type="button" wire:click="dec({{ $l['p']->id }})" aria-label="Moins">−</button>
                    <b style="min-width:22px;text-align:center">{{ $l['qty'] }}</b>
                    <button type="button" wire:click="add({{ $l['p']->id }})" aria-label="Plus">+</button>
                </div>
                <b style="min-width:84px;text-align:right">{{ $m($l['amount']) }}</b>
            </div>
        @empty
            <p style="text-align:center;color:#9ca3af;padding:20px 0">Cliquez sur les articles pour les ajouter.</p>
        @endforelse

        @if ($disc > 0)<div style="display:flex;justify-content:space-between;margin-top:8px;color:#6b7280"><span>Remise</span><span>− {{ $m($disc) }}</span></div>@endif

        <span class="pos-label">Nom du client (facultatif)</span>
        <input class="pos-in" placeholder="Client comptoir" wire:model="customer">

        <span class="pos-label">Paiement</span>
        <div class="pos-methods">
            @foreach (['especes' => '💵 Espèces', 'mobile_money' => '📱 Mobile money', 'virement' => '🏦 Virement', 'carte' => '💳 Carte'] as $k => $label)
                <button type="button" class="pos-method {{ $method === $k ? 'on' : '' }}" wire:click="setMethod('{{ $k }}')">{{ $label }}</button>
            @endforeach
        </div>

        <span class="pos-label">Montant versé <span style="text-transform:none;font-weight:400">(vide = tout payé)</span></span>
        <input class="pos-in" type="number" min="0" inputmode="numeric" placeholder="{{ $m($total) }}" wire:model.live="paid">
        @if ($paidValue !== null && $paidValue < $total)
            <p class="pos-hint">Reste à payer : <b>{{ $m($total - $paidValue) }}</b> (crédit)</p>
        @endif

        @if ($this->isAdmin())
            <span class="pos-label">Remise (administrateur)</span>
            <input class="pos-in" type="number" min="0" inputmode="numeric" placeholder="0" wire:model.live="discount">
        @endif
        @if (count($lines))<button type="button" wire:click="clear" style="margin-top:12px;color:#6b7280;text-decoration:underline">Vider le panier</button>@endif
    </div>

    {{-- BARRE FIXE : total + bouton, toujours visibles --}}
    <div class="pos-bar">
        <div class="info"><small>{{ $this->cartCount() }} article(s) — TOTAL</small><strong>{{ $m($total) }}</strong></div>
        <div class="acts">
            <button type="button" class="pos-see" @click="open = !open">🧺 Voir / modifier</button>
            <button type="button" class="pos-go" wire:click="complete" wire:loading.attr="disabled" @disabled(! count($lines)) @click="open = false">✔ Valider et générer le reçu</button>
        </div>
    </div>
</div>

@if ($lastSaleId)
    <div class="pos-ok">
        <div>
            <div style="font-size:48px">✅</div>
            <h2 style="font-size:1.4rem;font-weight:900;margin-top:6px">Vente {{ $lastNumber }} enregistrée</h2>
            <a class="big" href="{{ route('receipt', $lastSaleId) }}?print=1" target="_blank">🖨️ Imprimer le reçu</a>
            <a class="sec" href="{{ route('invoice', $lastSaleId) }}" target="_blank">Facture A4</a>
            <button type="button" class="sec" wire:click="newSale">➕ Nouvelle vente</button>
        </div>
    </div>
@endif
</x-filament-panels::page>
