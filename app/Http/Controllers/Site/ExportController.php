<?php
namespace App\Http\Controllers\Site;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Sale;
use App\Support\Report;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/** Exports comptables réservés à l'administrateur. */
class ExportController extends Controller
{
    private function guard(): void
    {
        abort_unless(Auth::user()?->is_active && Auth::user()->role === Role::Admin, 403);
    }

    public function csv(Request $r, string $type)
    {
        $this->guard();
        $month = $r->query('month') ?: null;
        [$from, $to] = Report::range($month);

        $rows = match ($type) {
            'ventes' => array_merge(
                [['Numéro', 'Date', 'Client', 'Sous-total', 'Remise', 'TVA incluse', 'Total', 'Versé', 'Reste', 'Paiement', 'Annulée']],
                Sale::whereBetween('created_at', [$from, $to])->orderBy('created_at')->get()->map(fn ($s) => [
                    $s->number, $s->created_at->format('d/m/Y H:i'), $s->customer_name, $s->subtotal, $s->discount, $s->tax,
                    $s->total, $s->paid, $s->balance, $s->method, $s->cancelled_at ? 'oui' : '',
                ])->all()
            ),
            'charges' => array_merge(
                [['Date', 'Libellé', 'Catégorie', 'Montant']],
                Expense::whereBetween('spent_on', [$from->toDateString(), $to->toDateString()])->orderBy('spent_on')->get()
                    ->map(fn ($e) => [$e->spent_on->format('d/m/Y'), $e->label, $e->category, $e->amount])->all()
            ),
            'achats' => array_merge(
                [['Date', 'Fournisseur', 'Libellé', 'Montant', 'Payé', 'Reste', 'Échéance']],
                Purchase::with('supplier')->whereBetween('purchased_on', [$from->toDateString(), $to->toDateString()])->orderBy('purchased_on')->get()
                    ->map(fn ($p) => [$p->purchased_on->format('d/m/Y'), $p->supplier?->name, $p->label, $p->amount, $p->paid, $p->balance, $p->due_date?->format('d/m/Y')])->all()
            ),
        };

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");                         // pour que Excel lise bien les accents
            foreach ($rows as $row) {
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, "fabara-$type-" . ($month ?: 'tout') . '.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function template()
    {
        $this->guard();
        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['sous_menu', 'nom', 'prix', 'stock', 'cout', 'description', 'image'], ';');
            fputcsv($out, ['Sacs', 'Nom de l’article', '', '', '', '', ''], ';');
            fclose($out);
        }, 'modele-articles.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function pdf(Request $r)
    {
        $this->guard();
        $month = $r->query('month') ?: null;
        return Pdf::loadView('pdf.report', ['r' => Report::build($month)])->stream('rapport-' . ($month ?: 'tout') . '.pdf');
    }
}
