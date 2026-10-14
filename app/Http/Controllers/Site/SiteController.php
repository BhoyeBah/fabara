<?php
namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function home()
    {
        $menus = Category::withCount(['products' => fn ($q) => $q->where('is_active', true)])->orderBy('position')->get()->groupBy('menu');
        $latest = Product::with('category')->where('is_active', true)->latest()->take(8)->get();
        $totals = [
            'boutique' => Product::where('is_active', true)->whereHas('category', fn ($query) => $query->where('menu', 'boutique'))->count(),
            'imprimerie' => Product::where('is_active', true)->whereHas('category', fn ($query) => $query->where('menu', 'imprimerie'))->count(),
        ];
        return view('site.home', compact('menus', 'latest', 'totals'));
    }

    public function search(Request $r)
    {
        $term = trim((string) $r->query('q'));
        $products = collect();
        if (mb_strlen($term) >= 2) {
            $products = Product::with('category')->where('is_active', true)
                ->where(fn ($query) => $query->where('name', 'like', '%' . $term . '%')->orWhere('description', 'like', '%' . $term . '%'))
                ->orderBy('name')->limit(60)->get();
        }
        return view('site.search', compact('term', 'products'));
    }

    public function category(Request $r, string $menu, Category $category)
    {
        abort_unless($category->menu === $menu, 404);
        $siblings = Category::where('menu', $menu)->withCount(['products' => fn ($query) => $query->where('is_active', true)])->orderBy('position')->get();
        $q = $category->products()->with('category')->where('is_active', true);
        if ($s = trim((string) $r->query('q'))) {
            $q->where('name', 'like', '%' . $s . '%');
        }
        match ($r->query('tri')) {
            'prix_asc' => $q->orderBy('price'),
            'prix_desc' => $q->orderByDesc('price'),
            default => $q->latest(),
        };
        $products = $q->get();
        return view('site.category', compact('menu', 'category', 'products', 'siblings'));
    }

    public function product(Product $product)
    {
        abort_unless($product->is_active, 404);
        $product->load('category');
        $related = Product::with('category')->where('is_active', true)->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)->latest()->take(4)->get();
        return view('site.product', compact('product', 'related'));
    }
}
