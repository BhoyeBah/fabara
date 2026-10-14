<?php
namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\PrintOrder;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\QuoteRequest;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClearDemo extends Command
{
    protected $signature = 'fabara:clear-demo {--force : sans confirmation}';
    protected $description = 'Supprime toutes les données de démonstration (articles, ventes, commandes, devis, achats, charges). Vos propres données ne sont pas touchées.';

    public function handle(): int
    {
        $products = Product::where('is_demo', true)->get();
        $demoOrders = PrintOrder::where('notes', 'like', '[démo]%');
        $saleIds = Sale::where('notes', 'like', '[démo]%')->pluck('id')
            ->merge((clone $demoOrders)->whereNotNull('sale_id')->pluck('sale_id'))->unique()->values();
        $counts = [
            'articles' => $products->count(), 'ventes' => $saleIds->count(), 'commandes' => (clone $demoOrders)->count(),
            'demandes de devis' => QuoteRequest::where('details', 'like', '[démo]%')->count(),
            'clients' => Customer::where('notes', 'like', '[démo]%')->count(),
            'fournisseurs' => Supplier::where('notes', 'like', '[démo]%')->count(),
            'achats' => Purchase::where('notes', 'like', '[démo]%')->count(),
            'charges' => Expense::where('label', 'like', '% (démo)')->count(),
        ];
        if (array_sum($counts) === 0) {
            $this->info('Aucune donnée de démonstration.');
            return self::SUCCESS;
        }
        $this->table(['Type', 'Nombre'], collect($counts)->map(fn ($n, $k) => [$k, $n])->values()->all());
        if (! $this->option('force') && ! $this->confirm('Supprimer ces données de démonstration ?')) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($products, $saleIds) {
            QuoteRequest::where('details', 'like', '[démo]%')->delete();
            PrintOrder::where('notes', 'like', '[démo]%')->delete();
            StockMovement::whereIn('sale_id', $saleIds)->delete();
            Sale::whereIn('id', $saleIds)->delete();
            Customer::where('notes', 'like', '[démo]%')->delete();
            Purchase::where('notes', 'like', '[démo]%')->delete();
            Supplier::where('notes', 'like', '[démo]%')->delete();
            Expense::where('label', 'like', '% (démo)')->delete();
            foreach ($products as $p) {
                if ($p->image) {
                    Storage::disk('public')->delete($p->image);
                }
                $p->delete();
            }
        });
        $this->info('Données de démonstration supprimées.');
        return self::SUCCESS;
    }
}
