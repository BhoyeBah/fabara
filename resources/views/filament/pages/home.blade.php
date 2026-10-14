<x-filament-panels::page>
@php($d = $this->overview())
@php($m = fn ($v) => \App\Support\Money::fmt($v))
<style>
.hm{--ac:{{ $d['accent'] }};display:grid;gap:22px}
.hm-hero{border-radius:22px;padding:26px 28px;color:#fff;background:linear-gradient(135deg,var(--ac),color-mix(in srgb,var(--ac) 55%,#f6c400));display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap}
.hm-hero h2{font-size:1.7rem;font-weight:800;line-height:1.15}
.hm-hero p{opacity:.9;margin-top:2px}
.hm-cta{background:#fff;color:var(--ac);font-weight:800;border-radius:14px;padding:14px 22px;box-shadow:0 8px 20px -8px rgba(0,0,0,.35);transition:transform .15s}
.hm-cta:hover{transform:translateY(-2px)}
.hm-actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:14px}
.hm-act{background:#fff;border:1px solid #ececf0;border-radius:18px;padding:18px;display:flex;gap:14px;align-items:center;transition:all .18s;text-decoration:none;color:inherit}
.hm-act:hover{transform:translateY(-3px);box-shadow:0 14px 28px -14px rgba(0,0,0,.28);border-color:var(--ac)}
.hm-ico{width:50px;height:50px;border-radius:14px;display:grid;place-items:center;font-size:25px;background:color-mix(in srgb,var(--ac) 11%,#fff);flex:none}
.hm-act b{display:block;font-size:1rem}
.hm-act small{color:#6b7280}
.hm-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px}
.hm-stat{background:#fff;border:1px solid #ececf0;border-radius:18px;padding:18px}
.hm-stat small{color:#6b7280;font-size:.8rem}
.hm-stat b{display:block;font-size:1.65rem;font-weight:800;margin-top:2px;line-height:1.2}
.hm-stat span{font-size:.78rem;color:#9ca3af}
.hm-cols{display:grid;grid-template-columns:1fr;gap:18px}
@media(min-width:1100px){.hm-cols{grid-template-columns:1.4fr 1fr}}
.hm-card{background:#fff;border:1px solid #ececf0;border-radius:18px;padding:20px}
.hm-card h3{font-weight:800;font-size:1.05rem;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center}
.hm-row{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f1f1f4}
.hm-row:last-child{border-bottom:0}
.hm-row small{color:#6b7280}
.hm-pill{font-size:.72rem;font-weight:700;border-radius:999px;padding:3px 10px;white-space:nowrap}
.hm-ok{background:#dcfce7;color:#166534}.hm-warn{background:#fef3c7;color:#92400e}.hm-bad{background:#fee2e2;color:#991b1b}.hm-gray{background:#f1f1f4;color:#4b5563}
.hm-todo{display:flex;flex-wrap:wrap;gap:10px}
.hm-chip{display:inline-flex;gap:8px;align-items:center;border-radius:999px;padding:9px 16px;font-weight:700;font-size:.9rem;text-decoration:none}
.hm-empty{color:#9ca3af;text-align:center;padding:18px 0}
.hm-link{color:var(--ac);font-weight:700;font-size:.85rem;text-decoration:none}
/* Tableau des commandes (atelier) */
.hm-board{display:grid;grid-template-columns:1fr;gap:16px}
@media(min-width:900px){.hm-board{grid-template-columns:repeat(3,1fr)}}
.hm-col{background:#f6f7f9;border-radius:18px;padding:14px;min-height:140px}
.hm-col h3{font-weight:800;display:flex;justify-content:space-between;align-items:center;margin:2px 4px 12px}
.hm-count{background:#fff;border-radius:999px;padding:2px 10px;font-size:.8rem}
.hm-order{background:#fff;border:1px solid #ececf0;border-radius:14px;padding:14px;margin-bottom:10px}
.hm-order .t{font-weight:800;line-height:1.25}
.hm-order .m{color:#6b7280;font-size:.85rem;margin-top:3px}
.hm-code{font-family:ui-monospace,Menlo,monospace;background:#f1f1f4;border-radius:6px;padding:1px 7px;font-size:.78rem;letter-spacing:.06em}
.hm-btn{width:100%;margin-top:10px;border-radius:12px;padding:11px;font-weight:800;color:#fff;background:var(--ac);transition:filter .15s,transform .1s}
.hm-btn:hover{filter:brightness(1.07)}.hm-btn:active{transform:scale(.98)}
.hm-btn.alt{background:#111827}
</style>

<div class="hm">
    {{-- Bandeau d'accueil --}}
    <div class="hm-hero">
        <div>
            <h2>{{ $d['greeting'] }} 👋</h2>
            <p>{{ $d['date'] }}</p>
        </div>
        @if ($d['role'] === 'agent')
            @if (! empty($d['actions'][0]))<a class="hm-cta" href="{{ $d['actions'][0][3] }}" wire:navigate>＋ Nouvelle commande</a>@endif
        @else
            <a class="hm-cta" href="{{ \App\Filament\Pages\Pos::getUrl() }}" wire:navigate>🛒 Nouvelle vente</a>
        @endif
    </div>

    {{-- À traiter --}}
    @php($late = $d['late'] ?? 0)
    @php($noPrice = $d['noPrice'] ?? 0)
    @php($qc = $d['quotesCount'] ?? 0)
    @php($lowCount = isset($d['low']) ? $d['low']->count() : 0)
    @if ($late || $noPrice || $qc || $lowCount)
        <div class="hm-todo">
            @if ($qc)<span class="hm-chip hm-warn">📥 {{ $qc }} demande(s) client à traiter</span>@endif
            @if ($late)<span class="hm-chip hm-bad">⏰ {{ $late }} commande(s) en retard</span>@endif
            @if ($noPrice && $d['role'] === 'admin')<span class="hm-chip hm-warn">💰 {{ $noPrice }} commande(s) sans prix : à fixer</span>@endif
            @if ($lowCount)<span class="hm-chip hm-bad">📦 {{ $lowCount }} article(s) bientôt en rupture</span>@endif
        </div>
    @endif

    {{-- Gros boutons --}}
    <div class="hm-actions">
        @foreach ($d['actions'] as [$icon, $label, $hint, $url])
            <a class="hm-act" href="{{ $url }}" wire:navigate>
                <span class="hm-ico">{{ $icon }}</span>
                <span><b>{{ $label }}</b><small>{{ $hint }}</small></span>
            </a>
        @endforeach
    </div>

    {{-- Chiffres --}}
    <div class="hm-stats">
        @foreach ($d['stats'] as [$label, $value, $hint])
            <div class="hm-stat"><small>{{ $label }}</small><b>{{ $value }}</b><span>{{ $hint }}</span></div>
        @endforeach
    </div>


    {{-- Graphiques (admin et gérante) --}}
    @if ($d['role'] !== 'agent' && count($this->getVisibleWidgets()))
        <div>
            <h3 style="font-weight:800;font-size:1.15rem;margin:4px 2px 12px">📈 Vos chiffres en un coup d’œil</h3>
            <x-filament-widgets::widgets :columns="$this->getColumns()" :data="$this->getWidgetData()" :widgets="$this->getVisibleWidgets()" />
        </div>
    @endif

    @if ($d['role'] === 'agent')
        {{-- Tableau des commandes --}}
        <div class="hm-board">
            @foreach (['nouvelle' => ['À commencer', '🆕'], 'en_cours' => ['En fabrication', '🛠️'], 'prete' => ['Prêtes à remettre', '✅']] as $key => [$title, $emoji])
                <div class="hm-col">
                    <h3><span>{{ $emoji }} {{ $title }}</span><span class="hm-count">{{ $d['board'][$key]->count() }}</span></h3>
                    @forelse ($d['board'][$key] as $o)
                        @php($isLate = $o->due_date && $o->due_date->isPast() && ! $o->due_date->isToday())
                        <div class="hm-order" wire:key="o-{{ $o->id }}">
                            <div style="display:flex;justify-content:space-between;gap:8px;align-items:start">
                                <span class="t">{{ $o->title }}</span>
                                <span class="hm-code">{{ $o->code }}</span>
                            </div>
                            <div class="m">{{ $o->customer_name }}@if ($o->phone) · <a href="tel:{{ preg_replace('/\s+/', '', $o->phone) }}" style="color:inherit;text-decoration:underline">{{ $o->phone }}</a>@endif</div>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:8px">
                                @if ($o->due_date)<span class="hm-pill {{ $isLate ? 'hm-bad' : 'hm-gray' }}">{{ $isLate ? 'En retard · ' : 'Pour le ' }}{{ $o->due_date->format('d/m') }}</span>@endif
                                @if ($o->price > 0)<span class="hm-pill hm-gray">{{ $m($o->price) }}</span>
                                @else<span class="hm-pill hm-warn">Prix à fixer par l’admin</span>@endif
                                @if ($o->price > 0 && $o->deposit < $o->price)<span class="hm-pill hm-warn">Reste {{ $m($o->price - $o->deposit) }}</span>@endif
                            </div>
                            @if ($o->status->next())
                                <button type="button" class="hm-btn" wire:click="advance({{ $o->id }})" wire:loading.attr="disabled">
                                    {{ $o->status->value === 'nouvelle' ? 'Commencer le travail' : 'Marquer comme prête' }}
                                </button>
                            @elseif ($o->status->value === 'prete')
                                <button type="button" class="hm-btn alt" wire:click="deliver({{ $o->id }})" wire:confirm="Remettre cette commande au client et créer la facture ?" wire:loading.attr="disabled">
                                    Remettre au client et facturer
                                </button>
                            @endif
                        </div>
                    @empty
                        <p class="hm-empty">Rien ici pour le moment.</p>
                    @endforelse
                </div>
            @endforeach
        </div>


    {{-- Graphiques (agent) --}}
    @if (count($this->getVisibleWidgets()))
        <div>
            <h3 style="font-weight:800;font-size:1.15rem;margin:4px 2px 12px">📈 Vos chiffres en un coup d’œil</h3>
            <x-filament-widgets::widgets :columns="$this->getColumns()" :data="$this->getWidgetData()" :widgets="$this->getVisibleWidgets()" />
        </div>
    @endif

        @if ($d['delivered']->count())
            <div class="hm-card">
                <h3>Dernières commandes livrées</h3>
                @foreach ($d['delivered'] as $o)
                    <div class="hm-row"><span><b>{{ $o->title }}</b><br><small>{{ $o->customer_name }}</small></span>
                        @if ($o->sale_id)<a class="hm-link" href="{{ route('invoice', $o->sale_id) }}" target="_blank">Facture</a>@endif</div>
                @endforeach
            </div>
        @endif
    @else
        {{-- Admin et gérante --}}
        <div class="hm-cols">
            <div class="hm-card">
                <h3><span>Dernières ventes</span><a class="hm-link" href="{{ \App\Filament\Resources\SaleResource::getUrl() }}" wire:navigate>Tout voir →</a></h3>
                @forelse ($d['recent'] as $s)
                    <div class="hm-row">
                        <span><b>{{ $s->customer_name }}</b><br><small>{{ $s->number }} · {{ $s->created_at->format('d/m H:i') }}</small></span>
                        <span style="text-align:right">
                            <b>{{ $m($s->total) }}</b><br>
                            @if ($s->cancelled_at)<span class="hm-pill hm-gray">Annulée</span>
                            @elseif ($s->paid < $s->total)<span class="hm-pill hm-warn">Reste {{ $m($s->total - $s->paid) }}</span>
                            @else<span class="hm-pill hm-ok">Payée</span>@endif
                            <a class="hm-link" href="{{ route('receipt', $s) }}" target="_blank" style="margin-left:6px">Reçu</a>
                        </span>
                    </div>
                @empty
                    <p class="hm-empty">Aucune vente pour le moment. Cliquez sur « Nouvelle vente ».</p>
                @endforelse
            </div>

            <div style="display:grid;gap:18px;align-content:start">
                <div class="hm-card">
                    <h3>Stock bas</h3>
                    @forelse ($d['low'] as $p)
                        <div class="hm-row"><span><b>{{ $p->name }}</b></span><span class="hm-pill {{ $p->stock <= 0 ? 'hm-bad' : 'hm-warn' }}">{{ $p->stock <= 0 ? 'Épuisé' : 'Reste ' . $p->stock }}</span></div>
                    @empty
                        <p class="hm-empty">✅ Tous les articles sont bien approvisionnés.</p>
                    @endforelse
                </div>
                <div class="hm-card">
                    <h3>Demandes de clients</h3>
                    @forelse ($d['quotes'] as $q)
                        <div class="hm-row"><span><b>{{ $q->subject }}</b><br><small>{{ $q->name }} · {{ $q->phone }}</small></span><span class="hm-pill hm-warn">Nouveau</span></div>
                    @empty
                        <p class="hm-empty">Aucune nouvelle demande.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</div>
</x-filament-panels::page>
