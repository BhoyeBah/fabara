<?php
namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Support\Checkout;
use DomainException;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/** Caisse : on clique sur les articles pris par le client, puis on génère le reçu. */
class Pos extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Caisse (vendre)';
    protected static ?string $title = 'Caisse';
    protected static ?int $navigationSort = 0;
    protected static ?string $navigationGroup = 'Ventes';
    protected static string $view = 'filament.pages.pos';

    public array $cart = [];            // [id de l'article => quantité]
    public string $search = '';
    public string $menu = 'tous';
    public ?int $category = null;       // sous-menu choisi (vide = tous)
    public string $customer = 'Client comptoir';
    public string $method = 'especes';
    public $paid = null;
    public $discount = 0;
    public ?int $lastSaleId = null;
    public ?string $lastNumber = null;

    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role?->value, ['admin', 'gerante'], true);
    }

    public function isAdmin(): bool
    {
        return auth()->user()?->role?->value === 'admin';
    }

    public function getProducts()
    {
        $q = Product::with('category')->where('is_active', true)->where('price', '>', 0);
        if (in_array($this->menu, ['boutique', 'imprimerie'], true)) {
            $m = $this->menu;
            $q->whereHas('category', fn ($c) => $c->where('menu', $m));
        }
        if ($this->category) {
            $q->where('category_id', $this->category);
        }
        if (trim($this->search) !== '') {
            $q->where('name', 'like', '%' . trim($this->search) . '%');
        }
        return $q->orderBy('name')->limit(80)->get();
    }

    public function setMenu(string $menu): void
    {
        $this->menu = in_array($menu, ['boutique', 'imprimerie'], true) ? $menu : 'tous';
        $this->category = null;
    }

    public function setCategory(?int $id): void
    {
        $this->category = $id ?: null;
    }

    /** Sous-menus qui ont au moins un article vendable (pour les boutons de filtre). */
    public function getCategories()
    {
        $q = Category::whereHas('products', fn ($p) => $p->where('is_active', true)->where('price', '>', 0))->orderBy('position');
        if (in_array($this->menu, ['boutique', 'imprimerie'], true)) {
            $q->where('menu', $this->menu);
        }
        return $q->get();
    }

    public function cartCount(): int
    {
        return (int) array_sum($this->cart);
    }

    public function setMethod(string $method): void
    {
        $this->method = in_array($method, ['especes', 'mobile_money', 'virement', 'carte'], true) ? $method : 'especes';
    }

    public function add(int $id): void
    {
        $p = Product::where('is_active', true)->find($id);
        if (! $p || ! $p->price) {
            return;
        }
        $n = ($this->cart[$id] ?? 0) + 1;
        if ($p->stock !== null && $n > $p->stock) {
            Notification::make()->title('Stock insuffisant : il en reste ' . $p->stock . '.')->warning()->send();
            return;
        }
        $this->cart[$id] = $n;
    }

    public function dec(int $id): void
    {
        if (! isset($this->cart[$id])) {
            return;
        }
        $this->cart[$id]--;
        if ($this->cart[$id] <= 0) {
            unset($this->cart[$id]);
        }
    }

    public function remove(int $id): void
    {
        unset($this->cart[$id]);
    }

    public function newSale(): void
    {
        $this->lastSaleId = null;
        $this->lastNumber = null;
    }

    public function clear(): void
    {
        $this->cart = [];
    }

    public function getLines(): array
    {
        if (! $this->cart) {
            return [];
        }
        $products = Product::whereIn('id', array_keys($this->cart))->get()->keyBy('id');
        $out = [];
        foreach ($this->cart as $id => $qty) {
            if ($p = $products->get($id)) {
                $out[] = ['p' => $p, 'qty' => (int) $qty, 'amount' => (float) $p->price * (int) $qty];
            }
        }
        return $out;
    }

    public function discountValue(): float
    {
        return $this->isAdmin() ? max(0, (float) $this->discount) : 0.0;
    }

    public function complete(): void
    {
        if (! $this->cart) {
            Notification::make()->title('Cliquez d’abord sur les articles pris par le client.')->warning()->send();
            return;
        }
        try {
            $sale = Checkout::make(
                $this->cart,
                $this->customer,
                $this->method,
                ($this->paid === null || $this->paid === '') ? null : (float) $this->paid,
                $this->discountValue(),
                auth()->id()
            );
        } catch (DomainException $e) {
            Notification::make()->title($e->getMessage())->danger()->send();
            return;
        }

        $this->lastSaleId = $sale->id;
        $this->lastNumber = $sale->number;
        $this->cart = [];
        $this->paid = null;
        $this->discount = 0;
        $this->customer = 'Client comptoir';

        // On ouvre directement le reçu (même onglet : fiable sur Safari), avec un bouton pour revenir à la caisse.
        $this->redirect(route('receipt', ['sale' => $sale, 'back' => static::getUrl()]));
    }
}
