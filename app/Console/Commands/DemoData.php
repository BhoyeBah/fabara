<?php
namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\PrintOrder;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\QuoteRequest;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use App\Support\Checkout;
use App\Support\Setting;
use Carbon\Carbon;
use Database\Seeders\CategorySeeder;
use Database\Seeders\UserSeeder;
use DomainException;
use Illuminate\Console\Command;

/**
 * Remplit l'application de données de simulation : 5 articles par sous-menu, clients, fournisseurs, achats,
 * ventes sur 30 jours, commandes d'imprimerie à tous les stades (avec code de suivi), demandes de devis, charges.
 * Tout est marqué « démo » et se supprime d'un coup : php artisan fabara:clear-demo
 */
class DemoData extends Command
{
    protected $signature = 'fabara:demo {--fresh : supprime d\'abord les données de démonstration existantes}';
    protected $description = 'Ajoute des données de démonstration pour simuler tout le logiciel (supprimables avec fabara:clear-demo).';

    private ?int $gerante = null;
    private ?int $agent = null;

    public function handle(): int
    {
        if (Product::where('is_demo', true)->exists()) {
            if (! $this->option('fresh')) {
                $this->warn('Des données de démonstration existent déjà. Utilisez : php artisan fabara:demo --fresh');
                return self::FAILURE;
            }
            $this->call('fabara:clear-demo', ['--force' => true]);
        }
        if (Category::count() === 0) {
            (new CategorySeeder)->run();
        }
        if (User::count() === 0) {
            (new UserSeeder)->run();
        }
        $this->gerante = User::where('role', 'gerante')->value('id');
        $this->agent = User::where('role', 'agent')->value('id');
        mt_srand(2026);

        if (! Setting::get('currency')) {
            Setting::put('currency', 'GNF');
        }

        $this->info('Articles…');
        $products = $this->products();
        $this->info('Clients et fournisseurs…');
        [$customers, $suppliers] = $this->people();
        $this->info('Achats (entrées de stock)…');
        $this->purchases($suppliers);
        $this->info('Ventes de la boutique (30 jours)…');
        $this->sales($customers);
        $this->info('Commandes d\'imprimerie et suivi…');
        $this->printOrders($customers);
        $this->info('Demandes de devis…');
        $this->quotes();
        $this->info('Charges…');
        $this->expenses();

        $this->newLine();
        $this->info('Terminé : ' . $products . ' articles, ' . Sale::count() . ' ventes, ' . PrintOrder::count() . ' commandes d\'imprimerie.');
        $this->line('Suivi de commande sur le site : codes DEMO01, DEMO02, DEMO03 avec le téléphone 620 11 22 01 / 02 / 03.');
        $this->line('Pour tout effacer avant l\'ouverture : php artisan fabara:clear-demo');
        return self::SUCCESS;
    }

    private function products(): int
    {
        $catalogue = require database_path('seeders/demo/catalogue.php');
        $n = 0;
        foreach ($catalogue as $categoryName => $items) {
            $category = Category::where('name', $categoryName)->first();
            if (! $category) {
                $this->warn("Sous-menu introuvable, ignoré : {$categoryName}");
                continue;
            }
            foreach ($items as $i => [$name, $price, $stock, $cost, $description]) {
                $at = now()->subDays(mt_rand(1, 40))->subMinutes(mt_rand(0, 900));
                Product::create([
                    'category_id' => $category->id, 'name' => $name, 'description' => $description, 'price' => $price,
                    'cost_price' => $cost, 'stock' => $stock, 'min_stock' => 3, 'is_active' => true, 'is_demo' => true,
                    'created_at' => $at, 'updated_at' => $at,
                ]);
                $n++;
            }
        }
        return $n;
    }

    private function people(): array
    {
        $customers = [];
        $list = [
            ['Mariama Diallo', '620 11 22 01'], ['Mamadou Bah', '620 11 22 02'], ['Aïssatou Camara', '620 11 22 03'],
            ['Ibrahima Sylla', '621 33 44 55'], ['Fatoumata Barry', '622 55 66 77'], ['Ousmane Soumah', '623 77 88 99'],
            ['Kadiatou Keïta', '624 12 34 56'], ['Mohamed Touré', '625 98 76 54'],
        ];
        foreach ($list as [$name, $phone]) {
            $customers[] = Customer::create(['name' => $name, 'phone' => $phone, 'notes' => '[démo]']);
        }
        $suppliers = [];
        foreach ([['Grossiste textile (démo)', '628 10 20 30'], ['Importateur cosmétiques (démo)', '628 40 50 60'], ['Fournitures d’imprimerie (démo)', '628 70 80 90']] as [$name, $phone]) {
            $suppliers[] = Supplier::create(['name' => $name, 'phone' => $phone, 'notes' => '[démo]']);
        }
        return [$customers, $suppliers];
    }

    private function product(string $name): ?Product
    {
        return Product::where('is_demo', true)->where('name', $name)->first();
    }

    private function purchases(array $s): void
    {
        $rows = [
            [$s[0], -26, 'Tuniques MakiSall classiques', 'Tunique MakiSall classique', 10, 1700000, 1700000, null],
            [$s[1], -18, 'Crèmes hydratantes au lait de riz', 'Crème hydratante au lait de riz', 20, 960000, 500000, 10],
            [$s[0], -12, 'Robes longues en wax', 'Robe longue en wax', 8, 880000, 400000, -3],
            [$s[2], -9, 'Encre et papier pour l’imprimerie', null, null, 650000, 650000, null],
            [$s[0], -4, 'Baskets homme', 'Baskets homme', 10, 1700000, 0, 20],
        ];
        foreach ($rows as [$supplier, $days, $label, $productName, $qty, $amount, $paid, $due]) {
            $product = $productName ? $this->product($productName) : null;
            $at = now()->addDays($days);
            $p = Purchase::create([
                'supplier_id' => $supplier->id, 'purchased_on' => $at->toDateString(), 'label' => $label, 'amount' => $amount, 'paid' => $paid,
                'due_date' => $due === null ? null : now()->addDays($due)->toDateString(),
                'product_id' => $product?->id, 'quantity' => $qty, 'notes' => '[démo]', 'created_at' => $at, 'updated_at' => $at,
            ]);
            StockMovement::where('product_id', $product?->id)->where('type', 'achat')->whereNull('sale_id')->where('created_at', '>', now()->subMinute())
                ->update(['created_at' => $at, 'updated_at' => $at, 'user_id' => $this->gerante]);
        }
    }

    /** Donne à une vente sa date, son numéro dans l'ordre du jour, son client. */
    private function stamp(Sale $sale, Carbon $at, ?Customer $customer = null): void
    {
        $prefix = 'F-' . $at->format('ymd') . '-';
        $n = Sale::where('id', '!=', $sale->id)->where('number', 'like', $prefix . '%')->count() + 1;
        while (Sale::where('id', '!=', $sale->id)->where('number', $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT))->exists()) {
            $n++;
        }
        $sale->forceFill([
            'number' => $prefix . str_pad((string) $n, 4, '0', STR_PAD_LEFT), 'created_at' => $at, 'updated_at' => $at,
            'customer_id' => $customer?->id, 'notes' => '[démo]',
        ])->save();
        $sale->items()->update(['created_at' => $at, 'updated_at' => $at]);
        StockMovement::where('sale_id', $sale->id)->update(['created_at' => $at, 'updated_at' => $at, 'user_id' => $this->gerante]);
    }

    private function sales(array $customers): void
    {
        $pool = Product::where('is_demo', true)->where('price', '>', 0)->with('category')->get();
        $shop = $pool->filter(fn ($p) => $p->category->menu === 'boutique' && ($p->stock ?? 0) >= 4)->values();
        $print = $pool->filter(fn ($p) => $p->category->menu === 'imprimerie' && $p->price <= 150000)->values();
        $methods = ['especes', 'especes', 'especes', 'mobile_money', 'mobile_money', 'virement', 'carte'];
        $created = 0;

        for ($i = 0; $i < 36; $i++) {
            $lines = [];
            for ($k = mt_rand(1, 3); $k > 0; $k--) {
                $source = mt_rand(1, 100) <= 80 ? $shop : $print;
                if ($source->isNotEmpty()) {
                    $p = $source[mt_rand(0, $source->count() - 1)];
                    $lines[$p->id] = mt_rand(1, 2);
                }
            }
            $customer = mt_rand(0, 1) ? $customers[mt_rand(0, count($customers) - 1)] : null;
            $total = 0;
            foreach ($lines as $id => $q) {
                $total += $pool->firstWhere('id', $id)->price * $q;
            }
            $paid = ($customer && $i % 9 === 4) ? round($total * 0.6, -3) : null;   // quelques ventes à crédit
            try {
                $sale = Checkout::make($lines, $customer?->name ?? 'Client comptoir', $methods[mt_rand(0, count($methods) - 1)], $paid, 0, $this->gerante);
            } catch (DomainException) {
                continue;
            }
            $this->stamp($sale, now()->subDays(mt_rand(0, 29))->setTime(mt_rand(8, 19), mt_rand(0, 59)), $customer);
            if (++$created === 7) {
                $sale->fresh()->cancel('Erreur de saisie (démo)');
            }
        }
    }

    private function printOrders(array $customers): void
    {
        // [client, article, quantité, statut, il y a (jours), échéance (jours), acompte %, code]
        $rows = [
            [0, 'Cartes de visite x500', 1, 'en_cours', 2, 2, 50, 'DEMO01'],
            [1, 'Bâche d’enseigne 3 x 1 m', 1, 'prete', 5, 0, 50, 'DEMO02'],
            [2, 'T-shirts personnalisés lot x20', 1, 'nouvelle', 0, 7, 30, 'DEMO03'],
            [3, 'Flyers A5 x1000', 1, 'en_cours', 3, 3, 50, null],
            [4, 'Cachet automatique rond', 2, 'prete', 1, 0, 100, null],
            [5, 'Tableau photo sur toile 50 x 70', 1, 'en_cours', 4, 4, 40, null],
            [6, 'Lot d’éventails x100', 1, 'nouvelle', 1, 10, 30, null],
            [7, 'Polo brodé logo', 5, 'en_cours', 2, 5, 50, null],
            [0, 'Calendrier mural 2027', 10, 'nouvelle', 0, 14, 20, null],
            [3, 'Stylos personnalisés x50', 1, 'prete', 6, -1, 50, null],
            [1, 'Carnet de factures A5 duplicata', 3, 'livree', 12, -9, 100, null],
            [5, 'Cartes de visite x100', 2, 'livree', 20, -17, 100, null],
            [2, 'Badge PVC avec photo', 30, 'en_cours', 3, 4, 50, null],
        ];
        foreach ($rows as [$ci, $productName, $qty, $status, $ago, $due, $depositPct, $code]) {
            $product = $this->product($productName);
            $customer = $customers[$ci];
            if (! $product) {
                continue;
            }
            $at = now()->subDays($ago)->setTime(mt_rand(8, 17), mt_rand(0, 59));
            $price = $product->price * $qty;
            $order = PrintOrder::create([
                'customer_id' => $customer->id, 'customer_name' => $customer->name, 'phone' => $customer->phone,
                'title' => $qty > 1 ? $qty . ' × ' . $product->name : $product->name, 'quantity' => $qty, 'product_id' => $product->id,
                'deposit' => round($price * $depositPct / 100, -3), 'material_cost' => $product->cost_price * $qty,
                'status' => OrderStatus::from($status === 'livree' ? 'en_cours' : $status), 'due_date' => now()->addDays($due)->toDateString(),
                'notes' => '[démo] Commande de simulation.', 'user_id' => $this->agent, 'code' => $code,
                'created_at' => $at, 'updated_at' => $at,
            ]);
            if ($status === 'livree') {
                $order->update(['deposit' => $order->price]);
                $sale = $order->deliver();
                $this->stamp($sale->fresh(), $at->copy()->addDays(max(1, $ago - 2 > 0 ? 2 : 1)), $customer);
            }
        }
        // Un travail sur mesure sans prix : l'administrateur doit le fixer (visible dans les alertes).
        $at = now()->subHours(5);
        PrintOrder::create([
            'customer_id' => $customers[4]->id, 'customer_name' => $customers[4]->name, 'phone' => $customers[4]->phone,
            'title' => 'Habillage complet d’un véhicule (devis à établir)', 'quantity' => 1, 'price' => 0, 'status' => OrderStatus::Nouvelle,
            'due_date' => now()->addDays(15)->toDateString(), 'notes' => '[démo] Prix à fixer par l’administrateur.', 'user_id' => $this->agent,
            'created_at' => $at, 'updated_at' => $at,
        ]);
    }

    private function quotes(): void
    {
        $rows = [
            ['Aminata Condé', '622 00 11 22', '200 flyers A5 couleur', 'Pour l’ouverture de mon salon, livraison souhaitée vendredi.', 'nouvelle', 0],
            ['Sékou Traoré', '623 44 55 66', 'Bâche 4 x 2 m pour mon restaurant', 'Visuel prêt, fichier à envoyer par WhatsApp.', 'nouvelle', 1],
            ['Association Jeunesse Unie', '624 77 88 99', '50 T-shirts imprimés', 'Logo sur la poitrine et dans le dos, tailles variées.', 'nouvelle', 2],
            ['Hadja Bangoura', '625 10 20 30', 'Carnets de factures pour ma boutique', 'Trois carnets duplicata avec mon logo.', 'vue', 3],
            ['Cabinet Konaté', '626 11 12 13', 'Cartes de visite et papier à en-tête', 'Pour cinq associés.', 'traitee', 6],
            ['Mory Camara', '627 21 22 23', '100 porte-clés personnalisés', 'Cadeaux pour un mariage.', 'traitee', 9],
        ];
        foreach ($rows as $i => [$name, $phone, $subject, $details, $status, $ago]) {
            $at = now()->subDays($ago)->subHours(mt_rand(0, 6));
            $q = QuoteRequest::create([
                'name' => $name, 'phone' => $phone, 'subject' => $subject, 'details' => '[démo] ' . $details, 'status' => $status,
                'created_at' => $at, 'updated_at' => $at,
            ]);
            if ($i === 4) {
                $q->toPrintOrder();       // transformée en commande : visible côté imprimerie
                $q->update(['created_at' => $at, 'updated_at' => $at]);
            }
        }
    }

    private function expenses(): void
    {
        $rows = [
            ['Loyer de la boutique', 'Loyer', 2000000, 12], ['Électricité', 'Électricité', 350000, 10], ['Internet et téléphone', 'Internet', 150000, 9],
            ['Salaire de la gérante', 'Salaires', 1500000, 8], ['Salaire de l’agent imprimerie', 'Salaires', 1200000, 8],
            ['Transport de marchandises', 'Transport', 180000, 6], ['Publicité en ligne', 'Publicité', 250000, 5],
            ['Entretien de la machine d’impression', 'Entretien', 300000, 3], ['Fournitures de bureau', 'Fournitures', 90000, 2],
        ];
        foreach ($rows as [$label, $category, $amount, $ago]) {
            $at = now()->subDays($ago);
            Expense::create(['spent_on' => $at->toDateString(), 'label' => $label . ' (démo)', 'category' => $category, 'amount' => $amount, 'created_at' => $at, 'updated_at' => $at]);
        }
    }
}
