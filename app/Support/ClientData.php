<?php
namespace App\Support;

use App\Models\Customer;
use App\Models\PrintOrder;
use App\Models\QuoteRequest;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/** Données personnelles d'un client : consultation, export et suppression (conformité RGPD / Google Play). */
class ClientData
{
    public static function last8(?string $p): string
    {
        return substr(preg_replace('/\D/', '', (string) $p), -8);
    }

    private static function samePhone($rows, string $key)
    {
        return $rows->filter(fn ($r) => $key !== '' && strlen($key) >= 6 && self::last8($r->phone) === $key)->values();
    }

    public static function quotes(User $u)
    {
        $k = self::last8($u->phone);
        return self::samePhone(QuoteRequest::whereNotNull('phone')->latest()->limit(500)->get(), $k);
    }

    public static function orders(User $u)
    {
        $k = self::last8($u->phone);
        return self::samePhone(PrintOrder::whereNotNull('phone')->latest()->limit(500)->get(), $k);
    }

    public static function export(User $u): array
    {
        return [
            'exporte_le' => now()->toIso8601String(),
            'compte' => ['nom' => $u->name, 'email' => $u->email, 'telephone' => $u->phone, 'cree_le' => $u->created_at?->toIso8601String(), 'conditions_acceptees_le' => $u->terms_accepted_at?->toIso8601String()],
            'demandes_de_devis' => self::quotes($u)->map(fn ($q) => ['date' => $q->created_at?->toDateString(), 'objet' => $q->subject, 'details' => $q->details, 'statut' => $q->status])->all(),
            'commandes_imprimerie' => self::orders($u)->map(fn ($o) => ['code' => $o->code, 'titre' => $o->title, 'statut' => $o->status instanceof \BackedEnum ? $o->status->value : $o->status, 'prix' => $o->price, 'date' => $o->created_at?->toDateString()])->all(),
        ];
    }

    /** Supprime le compte et les données personnelles ; les pièces comptables sont conservées sans identité. */
    public static function erase(User $u): void
    {
        DB::transaction(function () use ($u) {
            $k = self::last8($u->phone);
            if ($k !== '' && strlen($k) >= 6) {
                self::quotes($u)->each->delete();
                self::orders($u)->each(fn ($o) => $o->forceFill(['customer_name' => 'Client supprimé', 'phone' => null, 'customer_id' => null, 'notes' => null])->save());
                Customer::whereNotNull('phone')->get()->filter(fn ($c) => self::last8($c->phone) === $k)->each(function ($c) {
                    Sale::where('customer_id', $c->id)->update(['customer_name' => 'Client supprimé', 'customer_id' => null]);
                    $c->delete();
                });
            }
            $u->tokens()->delete();
            $u->delete();
        });
    }
}
