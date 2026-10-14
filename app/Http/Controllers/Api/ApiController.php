<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PrintOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use App\Support\Checkout;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ApiController extends Controller
{
    private function need(Request $r, array $roles): void
    {
        abort_unless(in_array($r->user()->role->value, $roles, true) && $r->user()->is_active, 403);
    }

    public function categories()
    {
        return Category::with(['products' => fn ($q) => $q->where('is_active', true)
            ->select(['id', 'category_id', 'name', 'description', 'price', 'stock', 'image'])])->orderBy('position')->get();
    }

    public function login(Request $r)
    {
        $r->validate(['email' => 'required|email', 'password' => 'required']);
        $u = User::where('email', $r->email)->first();
        if (! $u || ! $u->is_active || ! Hash::check($r->password, $u->password)) {
            return response()->json(['message' => 'Identifiants incorrects.'], 422);
        }
        return ['token' => $u->createToken($r->input('device', 'app'))->plainTextToken, 'name' => $u->name, 'role' => $u->role->value];
    }

    public function me(Request $r) { return ['name' => $r->user()->name, 'email' => $r->user()->email, 'role' => $r->user()->role->value]; }

    public function exportAccount(Request $r)
    {
        $this->need($r, ['client']);
        return \App\Support\ClientData::export($r->user());
    }

    public function deleteAccount(Request $r)
    {
        $this->need($r, ['client']);
        $r->validate(['password' => 'required|current_password:sanctum']);
        \App\Support\ClientData::erase($r->user());
        return ['ok' => true];
    }

    public function logout(Request $r) { $r->user()->currentAccessToken()->delete(); return ['ok' => true]; }

    public function sales(Request $r)
    {
        $this->need($r, ['admin', 'gerante']);
        return Sale::active()->with('items')->latest()->limit(100)->get();
    }

    public function storeSale(Request $r)
    {
        $this->need($r, ['admin', 'gerante']);
        $d = $r->validate([
            'customer_name' => 'nullable|string|max:255', 'method' => 'nullable|in:especes,mobile_money,virement,carte',
            'discount' => 'nullable|numeric|min:0', 'paid' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id', 'items.*.quantity' => 'required|integer|min:1',
        ]);
        // Les prix viennent du catalogue : tout prix envoyé par l'application est ignoré.
        $lines = [];
        foreach ($d['items'] as $i) {
            $lines[$i['product_id']] = ($lines[$i['product_id']] ?? 0) + $i['quantity'];
        }
        try {
            $sale = Checkout::make(
                $lines, $d['customer_name'] ?? 'Client comptoir', $d['method'] ?? 'especes',
                isset($d['paid']) ? (float) $d['paid'] : null,
                $r->user()->role->value === 'admin' ? (float) ($d['discount'] ?? 0) : 0.0,
                $r->user()->id
            );
        } catch (DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
        return response()->json($sale->load('items'), 201);
    }

    public function printOrders(Request $r)
    {
        $this->need($r, ['admin', 'agent']);
        return PrintOrder::where('status', '!=', 'livree')->latest()->get();
    }

    public function advance(Request $r, PrintOrder $order)
    {
        $this->need($r, ['admin', 'agent']);
        $order->advance();
        return $order->fresh();
    }

    public function deliver(Request $r, PrintOrder $order)
    {
        $this->need($r, ['admin', 'agent']);
        if ($order->status->value !== 'prete' || $order->price <= 0) {
            return response()->json(['message' => 'La commande doit être « Prête » et avoir un prix.'], 422);
        }
        return $order->deliver()->load('items');
    }
}
