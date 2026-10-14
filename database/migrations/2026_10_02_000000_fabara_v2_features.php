<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->unsignedInteger('min_stock')->default(3));
        Schema::table('sales', function (Blueprint $t) {
            $t->decimal('tax', 12, 2)->default(0);
            $t->timestamp('cancelled_at')->nullable();
            $t->string('cancel_reason')->nullable();
        });
        Schema::table('print_orders', fn (Blueprint $t) => $t->string('code', 10)->nullable()->unique());

        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('key')->unique();
            $t->text('value')->nullable();
            $t->timestamps();
        });
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id();
            $t->foreignId('product_id')->constrained()->cascadeOnDelete();
            $t->string('type');                       // vente, achat, ajustement, retour
            $t->integer('quantity');                  // positif = entrée, négatif = sortie
            $t->string('note')->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('suppliers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('phone')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('purchases', function (Blueprint $t) {
            $t->id();
            $t->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $t->date('purchased_on');
            $t->string('label');
            $t->decimal('amount', 12, 2);
            $t->decimal('paid', 12, 2)->default(0);
            $t->date('due_date')->nullable();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->unsignedInteger('quantity')->nullable();      // si renseignés : le stock augmente
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('quote_requests', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('phone');
            $t->string('subject');
            $t->text('details')->nullable();
            $t->string('status')->default('nouvelle');       // nouvelle, vue, traitee
            $t->foreignId('print_order_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['quote_requests', 'purchases', 'suppliers', 'stock_movements', 'settings'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('print_orders', fn (Blueprint $t) => $t->dropColumn('code'));
        Schema::table('sales', fn (Blueprint $t) => $t->dropColumn(['tax', 'cancelled_at', 'cancel_reason']));
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn('min_stock'));
    }
};
