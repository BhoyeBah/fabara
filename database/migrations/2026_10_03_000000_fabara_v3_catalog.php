<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // is_demo : articles d'exemple, supprimables d'un coup avec « php artisan fabara:clear-demo »
        Schema::table('products', function (Blueprint $t) {
            $t->boolean('is_demo')->default(false);
            $t->boolean('is_featured')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('products', fn (Blueprint $t) => $t->dropColumn(['is_demo', 'is_featured']));
    }
};
