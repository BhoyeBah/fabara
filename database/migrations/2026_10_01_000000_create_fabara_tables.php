<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('agent');
            $t->boolean('is_active')->default(true);
            $t->string('phone')->nullable();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id();
            $t->string('menu');                       // boutique | imprimerie
            $t->string('name');
            $t->string('slug')->unique();
            $t->unsignedInteger('position')->default(0);
            $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id();
            $t->foreignId('category_id')->constrained()->cascadeOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->decimal('price', 12, 2)->nullable();      // vide = sur devis
            $t->decimal('cost_price', 12, 2)->default(0);
            $t->integer('stock')->nullable();             // vide = illimité / service
            $t->string('image')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('phone')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('sales', function (Blueprint $t) {
            $t->id();
            $t->string('number')->unique();
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->string('customer_name')->default('Client comptoir');
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('subtotal', 12, 2)->default(0);
            $t->decimal('discount', 12, 2)->default(0);
            $t->decimal('total', 12, 2)->default(0);
            $t->decimal('paid', 12, 2)->nullable();
            $t->string('method')->default('especes');
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('sale_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name');
            $t->unsignedInteger('quantity')->default(1);
            $t->decimal('unit_price', 12, 2)->default(0);
            $t->decimal('cost_price', 12, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('print_orders', function (Blueprint $t) {
            $t->id();
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->string('customer_name');
            $t->string('phone')->nullable();
            $t->string('title');
            $t->unsignedInteger('quantity')->default(1);
            $t->decimal('price', 12, 2)->default(0);
            $t->decimal('deposit', 12, 2)->default(0);
            $t->decimal('material_cost', 12, 2)->default(0);
            $t->string('status')->default('nouvelle');
            $t->date('due_date')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('sale_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('expenses', function (Blueprint $t) {
            $t->id();
            $t->date('spent_on');
            $t->string('label');
            $t->string('category')->default('Autre');
            $t->decimal('amount', 12, 2);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['expenses', 'print_orders', 'sale_items', 'sales', 'customers', 'products', 'categories'] as $t) {
            Schema::dropIfExists($t);
        }
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn(['role', 'is_active', 'phone']));
    }
};
