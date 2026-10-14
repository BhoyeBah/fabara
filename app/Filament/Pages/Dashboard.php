<?php
namespace App\Filament\Pages;

use App\Enums\OrderStatus;
use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\PrintOrderResource;
use App\Filament\Resources\ProductResource;
use App\Filament\Resources\QuoteRequestResource;
use App\Filament\Resources\SaleResource;
use App\Filament\Widgets;
use App\Models\PrintOrder;
use App\Models\Product;
use App\Models\QuoteRequest;
use App\Models\Sale;
use App\Support\Money;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Contracts\Support\Htmlable;

/** Page d'accueil de chaque interface : simple, avec les gros boutons utiles et ce qu'il faut traiter. */
class Dashboard extends BaseDashboard
{
    protected static ?string $title = 'Accueil';
    protected static ?string $navigationLabel = 'Accueil';
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.pages.home';

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    /** Graphiques affichés selon le rôle (chacun ne voit que ce qui le concerne). */
    public function getWidgets(): array
    {
        return match ($this->role()) {
            'admin' => [
                Widgets\SalesTrend::class, Widgets\SalesByMenu::class, Widgets\PaymentMethods::class,
                Widgets\TopProducts::class, Widgets\OrdersByStatus::class, Widgets\ResultByMonth::class,
            ],
            'gerante' => [Widgets\SalesTrend::class, Widgets\TopProducts::class],
            'agent' => [Widgets\OrdersByStatus::class, Widgets\OrdersDue::class],
            default => [],
        };
    }

    public function getColumns(): int|string|array
    {
        return ['default' => 1, 'lg' => 2];
    }

    /** Rôle « affiché » : celui de l'interface ouverte (un admin dans /atelier voit la vue atelier). */
    public function role(): ?string
    {
        $real = auth()->user()?->role?->value;
        $view = ['admin' => 'admin', 'caisse' => 'gerante', 'atelier' => 'agent'][Filament::getCurrentPanel()?->getId() ?? ''] ?? $real;
        if ($real !== 'admin' && $view !== $real) {
            return $real;
        }
        return $view;
    }

    // ---------------------------------------------------------------- actions de l'atelier
    public function advance(int $id): void
    {
        if (! in_array($this->role(), ['admin', 'agent'], true)) {
            return;
        }
        $order = PrintOrder::find($id);
        if (! $order || ! $order->status->next()) {
            return;
        }
        $order->advance();
        Notification::make()->title('Commande ' . $order->code . ' : ' . $order->fresh()->status->getLabel())->success()->send();
    }

    public function deliver(int $id): void
    {
        if (! in_array($this->role(), ['admin', 'agent'], true)) {
            return;
        }
        $order = PrintOrder::find($id);
        if (! $order || $order->status !== OrderStatus::Prete) {
            return;
        }
        if ($order->price <= 0) {
            Notification::make()->title('Le prix de cette commande doit d’abord être fixé par l’administrateur.')->warning()->send();
            return;
        }
        $sale = $order->deliver();
        Notification::make()->title('Commande livrée. Facture ' . $sale->number . ' créée.')->success()->send();
    }

    // ---------------------------------------------------------------- données affichées
    public function overview(): array
    {
        $role = $this->role();
        $name = trim((string) strtok((string) auth()->user()?->name, ' '));
        $d = [
            'role' => $role,
            'greeting' => 'Bonjour' . ($name !== '' ? ', ' . $name : ''),
            'date' => ucfirst(now()->translatedFormat('l j F Y')),
            'accent' => ['admin' => '#d7141a', 'gerante' => '#d6196b', 'agent' => '#0a8f3c'][$role] ?? '#d7141a',
            'actions' => [], 'stats' => [], 'todo' => [],
        ];

        if (in_array($role, ['admin', 'gerante'], true)) {
            $today = Sale::active()->whereDate('created_at', today());
            $month = Sale::active()->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            $due = Sale::active()->whereColumn('paid', '<', 'total');
            $d['stats'] = [
                ['Ventes aujourd’hui', (clone $today)->count(), 'ventes'],
                ['Encaissé aujourd’hui', Money::fmt((clone $today)->sum('paid')), 'en caisse'],
                ['Chiffre d’affaires du mois', Money::fmt((clone $month)->sum('total')), now()->translatedFormat('F')],
                ['Reste à encaisser', Money::fmt((clone $due)->selectRaw('COALESCE(SUM(total - paid),0) as d')->value('d')), (clone $due)->count() . ' vente(s)'],
            ];
            $low = Product::where('is_active', true)->whereNotNull('stock')->whereColumn('stock', '<=', 'min_stock')->orderBy('stock')->limit(8)->get();
            $d['low'] = $low;
            $d['recent'] = Sale::with('items')->latest()->limit(8)->get();
            $d['quotes'] = QuoteRequest::where('status', 'nouvelle')->latest()->limit(5)->get();
            $d['quotesCount'] = QuoteRequest::where('status', 'nouvelle')->count();
        }

        if (in_array($role, ['admin', 'agent'], true)) {
            $d['late'] = PrintOrder::whereNotNull('due_date')->whereDate('due_date', '<', today())->where('status', '!=', 'livree')->count();
            $d['noPrice'] = PrintOrder::where('price', '<=', 0)->where('status', '!=', 'livree')->count();
            $d['quotesCount'] = QuoteRequest::where('status', 'nouvelle')->count();
        }

        if ($role === 'agent') {
            $board = [];
            foreach ([OrderStatus::Nouvelle, OrderStatus::EnCours, OrderStatus::Prete] as $status) {
                $board[$status->value] = PrintOrder::where('status', $status->value)->orderByRaw('due_date IS NULL')->orderBy('due_date')->orderBy('id')->limit(30)->get();
            }
            $d['board'] = $board;
            $d['delivered'] = PrintOrder::where('status', 'livree')->latest('updated_at')->limit(5)->get();
            $d['stats'] = [
                ['À commencer', $board['nouvelle']->count(), 'nouvelles commandes'],
                ['En fabrication', $board['en_cours']->count(), 'en cours'],
                ['Prêtes à remettre', $board['prete']->count(), 'à livrer'],
                ['En retard', $d['late'], 'à traiter vite'],
            ];
        }

        // Gros boutons
        $try = fn (callable $f) => rescue($f, null, false);
        if ($role === 'admin') {
            $d['actions'] = array_values(array_filter([
                ['🛒', 'Vendre', 'Ouvrir la caisse', $try(fn () => Pos::getUrl())],
                ['➕', 'Ajouter des articles', 'Prix, photos et stock', $try(fn () => ProductResource::getUrl('index', ['action' => 'create']))],
                ['🖨️', 'Commandes imprimerie', 'Suivre et fixer les prix', $try(fn () => PrintOrderResource::getUrl())],
                ['📥', 'Demandes clients', ($d['quotesCount'] ?? 0) . ' nouvelle(s)', $try(fn () => QuoteRequestResource::getUrl())],
                ['📊', 'Comptabilité', 'Chiffres du mois', $try(fn () => Accounting::getUrl())],
                ['🧾', 'Ventes et factures', 'Historique', $try(fn () => SaleResource::getUrl())],
            ], fn ($a) => $a[3]));
        } elseif ($role === 'gerante') {
            $d['actions'] = array_values(array_filter([
                ['🛒', 'Nouvelle vente', 'Cliquer sur les articles', $try(fn () => Pos::getUrl())],
                ['🧾', 'Ventes du jour', 'Reçus et factures', $try(fn () => SaleResource::getUrl())],
                ['📥', 'Demandes clients', ($d['quotesCount'] ?? 0) . ' nouvelle(s)', $try(fn () => QuoteRequestResource::getUrl())],
                ['📦', 'Articles et stock', 'Consulter', $try(fn () => ProductResource::getUrl())],
            ], fn ($a) => $a[3]));
        } else {
            $d['actions'] = array_values(array_filter([
                ['➕', 'Nouvelle commande', 'Enregistrer un travail', $try(fn () => PrintOrderResource::getUrl('index', ['action' => 'create']))],
                ['🖨️', 'Toutes les commandes', 'Liste et recherche', $try(fn () => PrintOrderResource::getUrl())],
                ['📥', 'Demandes clients', ($d['quotesCount'] ?? 0) . ' nouvelle(s)', $try(fn () => QuoteRequestResource::getUrl())],
                ['👥', 'Clients', 'Carnet de clients', $try(fn () => CustomerResource::getUrl())],
            ], fn ($a) => $a[3]));
        }

        return $d;
    }
}
