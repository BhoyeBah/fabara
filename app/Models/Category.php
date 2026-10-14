<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    public function products() { return $this->hasMany(Product::class); }

    /** Petit pictogramme pour les tuiles du site (aucune photo n'est inventée). */
    public function icon(): string
    {
        $n = mb_strtolower($this->name);
        $map = [
            'femme' => '👗', 'homme' => '👔', 'tunique' => '🧵', 'cosm' => '🧴', 'chauss' => '👟', 'bijou' => '⌚', 'accessoire' => '📱', 'sac' => '👜',
            'cachet' => '🔖', 'carnet' => '🧾', 'carte' => '💳', 'badge' => '🪪', 'tableau' => '🖼️', 'éventail' => '🪭', 'étiquette' => '🏷️',
            'bâche' => '🚩', 'autocollant' => '✨', 'calendrier' => '📅', 'offset' => '📰', 'shirt' => '👕', 'porte-clé' => '🔑', 'pochette' => '👝',
            'uv' => '💡', 'bloc' => '📝',
        ];
        foreach ($map as $k => $e) {
            if (str_contains($n, $k)) {
                return $e;
            }
        }
        return $this->menu === 'boutique' ? '🛍️' : '🖨️';
    }

    public function thumb(): ?string
    {
        return $this->products()->where('is_active', true)->whereNotNull('image')->first()?->image_url;
    }
}
