@php($menu = $p->category?->menu ?? 'boutique')
@php($out = $p->stock !== null && $p->stock <= 0)
@php($low = ! $out && $p->stock !== null && $p->isLow())
@php($isNew = $p->created_at && $p->created_at->gt(now()->subDays(14)))
<a href="{{ route('product', $p) }}" class="lift reveal group flex flex-col bg-white border border-neutral-200 rounded-2xl overflow-hidden">
  <div class="zoom relative overflow-hidden">
    @if ($p->image_url)
      <img src="{{ $p->image_url }}" alt="{{ $p->name }}" loading="lazy" class="w-full aspect-[4/3] object-cover {{ $out ? 'opacity-60 grayscale' : '' }}">
    @else
      <div class="w-full aspect-[4/3] grid place-items-center {{ $menu === 'boutique' ? 'bg-gradient-to-br from-pink-50 to-pink-100' : 'bg-gradient-to-br from-red-50 to-orange-100' }}">
        <span class="text-5xl">{{ $p->category?->icon() }}</span>
      </div>
    @endif
    <div class="absolute top-2 left-2 flex flex-col gap-1 items-start">
      @if ($out)<span class="bg-neutral-900 text-white text-[11px] font-semibold px-2 py-1 rounded-full">Épuisé</span>
      @elseif ($low)<span class="bg-fyellow text-neutral-900 text-[11px] font-semibold px-2 py-1 rounded-full">Dernières pièces</span>
      @elseif ($isNew)<span class="bg-fgreen text-white text-[11px] font-semibold px-2 py-1 rounded-full">Nouveau</span>@endif
    </div>
  </div>
  <div class="p-4 flex flex-col gap-1 flex-1">
    <span class="text-xs text-neutral-500">{{ $p->category?->name }}</span>
    <b class="leading-snug line-clamp-2">{{ $p->name }}</b>
    <div class="mt-auto pt-2 flex items-end justify-between">
      <span class="font-extrabold text-lg {{ $menu === 'boutique' ? 'text-fpink' : 'text-fred' }}">
        {{ $p->price ? ($menu === 'imprimerie' ? 'dès ' : '') . \App\Support\Money::fmt($p->price) : 'Sur devis' }}
      </span>
      <span class="text-sm text-neutral-400 group-hover:text-neutral-900 transition">Voir →</span>
    </div>
  </div>
</a>
