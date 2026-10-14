@extends('site.layout')
@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden bg-gradient-to-br from-white via-white to-pink-50">
  <div class="max-w-7xl mx-auto px-4 py-12 md:py-20 grid lg:grid-cols-2 gap-10 items-center">
    <div class="reveal">
      <span class="inline-flex items-center gap-2 bg-red-50 text-fred text-xs font-semibold px-3 py-1.5 rounded-full">● Boutique &amp; Imprimerie</span>
      <h1 class="text-4xl md:text-6xl font-extrabold leading-[1.05] mt-5 tracking-tight">
        Habillez-vous.<br>Imprimez.<br><span class="text-fred">Faites-vous remarquer.</span>
      </h1>
      <p class="text-lg text-neutral-600 mt-5 max-w-xl">Mode, cosmétiques, chaussures, bijoux et accessoires en boutique. Cartes de visite, bâches, T-shirts, cachets et bien plus à l’imprimerie.</p>
      <div class="flex flex-wrap gap-3 mt-8">
        <a href="#boutique" class="bg-fpink hover:opacity-90 text-white px-6 py-3.5 rounded-xl font-semibold shadow-lg shadow-pink-200 transition">Voir la boutique</a>
        <a href="#imprimerie" class="bg-fred hover:opacity-90 text-white px-6 py-3.5 rounded-xl font-semibold shadow-lg shadow-red-200 transition">Nos services d’impression</a>
        <a href="#devis" class="border border-neutral-300 hover:border-neutral-900 px-6 py-3.5 rounded-xl font-semibold transition">Demander un devis</a>
      </div>
      <dl class="grid grid-cols-3 gap-4 mt-10 max-w-lg">
        <div><dt class="text-2xl font-extrabold">{{ $totals['boutique'] + $totals['imprimerie'] }}+</dt><dd class="text-sm text-neutral-500">articles et services</dd></div>
        <div><dt class="text-2xl font-extrabold">{{ count($menus['imprimerie'] ?? []) }}</dt><dd class="text-sm text-neutral-500">métiers d’impression</dd></div>
        <div><dt class="text-2xl font-extrabold">24/7</dt><dd class="text-sm text-neutral-500">suivi en ligne</dd></div>
      </dl>
    </div>
    <div class="grid grid-cols-2 gap-4 reveal">
      <a href="#boutique" class="lift col-span-2 rounded-3xl p-8 text-white relative overflow-hidden" style="background:linear-gradient(135deg,#d6196b,#f0558f)">
        <span class="text-6xl opacity-90">🛍️</span>
        <h2 class="text-2xl font-extrabold mt-3">La Boutique</h2>
        <p class="opacity-90">{{ $totals['boutique'] > 0 ? $totals['boutique'] . ' articles' : 'Bientôt disponible' }} · Femme, homme, tunique MakiSall, cosmétiques…</p>
      </a>
      <a href="#imprimerie" class="lift rounded-3xl p-6 text-white" style="background:linear-gradient(135deg,#d7141a,#f0603a)">
        <span class="text-4xl">🖨️</span>
        <h2 class="text-xl font-extrabold mt-2">L’Imprimerie</h2>
        <p class="text-sm opacity-90">Sur mesure, rapide, soigné.</p>
      </a>
      <a href="{{ route('track') }}" class="lift rounded-3xl p-6 text-white" style="background:linear-gradient(135deg,#0a8f3c,#2fb86a)">
        <span class="text-4xl">📦</span>
        <h2 class="text-xl font-extrabold mt-2">Suivi de commande</h2>
        <p class="text-sm opacity-90">Avec votre code.</p>
      </a>
    </div>
  </div>
</section>

{{-- Raccourcis --}}
<section class="border-y border-neutral-200 bg-white">
  <div class="max-w-7xl mx-auto px-4 py-4 flex gap-2 overflow-x-auto no-scrollbar">
    @foreach (['boutique', 'imprimerie'] as $k)
      @foreach ($menus[$k] ?? [] as $c)
        <a href="{{ route('category', [$k, $c->slug]) }}" class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-full border border-neutral-200 hover:border-neutral-900 text-sm transition">
          <span>{{ $c->icon() }}</span>{{ $c->name }}
        </a>
      @endforeach
    @endforeach
  </div>
</section>

@if ($latest->count())
  <section class="max-w-7xl mx-auto px-4 pt-14">
    <div class="flex items-end justify-between mb-6">
      <div><p class="text-fred text-sm font-semibold uppercase tracking-wider">À découvrir</p><h2 class="text-3xl font-extrabold">Derniers articles</h2></div>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
      @foreach ($latest as $p) @include('site._card', ['p' => $p]) @endforeach
    </div>
  </section>
@endif

@foreach (['boutique' => ['Boutique', 'text-fpink', 'from-pink-50 to-pink-100', 'Mode, beauté et accessoires'], 'imprimerie' => ['Imprimerie', 'text-fred', 'from-red-50 to-orange-100', 'Tous vos travaux d’impression et de personnalisation']] as $k => [$label, $accent, $grad, $tagline])
  <section id="{{ $k }}" class="max-w-7xl mx-auto px-4 pt-16 scroll-mt-24">
    <p class="{{ $accent }} text-sm font-semibold uppercase tracking-wider">{{ $tagline }}</p>
    <h2 class="text-3xl md:text-4xl font-extrabold mb-6">{{ $label }}</h2>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
      @foreach ($menus[$k] ?? [] as $c)
        <a href="{{ route('category', [$k, $c->slug]) }}" class="lift reveal bg-white border border-neutral-200 rounded-2xl overflow-hidden group">
          <div class="zoom">
            @if ($t = $c->thumb())
              <img src="{{ $t }}" alt="{{ $c->name }}" loading="lazy" class="w-full h-36 object-cover">
            @else
              <div class="w-full h-36 grid place-items-center bg-gradient-to-br {{ $grad }}"><span class="text-5xl">{{ $c->icon() }}</span></div>
            @endif
          </div>
          <div class="p-4">
            <p class="font-bold leading-snug">{{ $c->name }}</p>
            <p class="text-xs text-neutral-500 mt-1">{{ $c->products_count > 0 ? $c->products_count . ' article' . ($c->products_count > 1 ? 's' : '') : 'Bientôt disponible' }}</p>
          </div>
        </a>
      @endforeach
    </div>
  </section>
@endforeach

{{-- Comment ça marche --}}
<section class="max-w-7xl mx-auto px-4 pt-20">
  <div class="text-center max-w-2xl mx-auto mb-8">
    <p class="text-fred text-sm font-semibold uppercase tracking-wider">Simple et rapide</p>
    <h2 class="text-3xl md:text-4xl font-extrabold">Comment commander</h2>
  </div>
  <div class="grid md:grid-cols-3 gap-5">
    @foreach ([['1', '🔎', 'Choisissez', 'Parcourez la boutique ou les services d’imprimerie.'], ['2', '💬', 'Contactez-nous', 'Écrivez-nous sur WhatsApp ou envoyez une demande de devis.'], ['3', '📦', 'Recevez', 'Nous confirmons le prix et la disponibilité. Suivez votre commande avec son code.']] as [$n, $e, $t, $d])
      <div class="reveal bg-white border border-neutral-200 rounded-2xl p-6 relative">
        <span class="absolute -top-4 left-6 w-9 h-9 rounded-full bg-fred text-white grid place-items-center font-bold shadow-md">{{ $n }}</span>
        <div class="text-4xl mt-2">{{ $e }}</div>
        <h3 class="text-lg font-bold mt-3">{{ $t }}</h3>
        <p class="text-neutral-600 mt-1">{{ $d }}</p>
      </div>
    @endforeach
  </div>
  <div class="reveal mt-10 rounded-3xl p-8 md:p-10 text-white flex flex-wrap items-center justify-between gap-4" style="background:linear-gradient(135deg,#171717,#3a3a3a)">
    <div>
      <h3 class="text-2xl font-extrabold">Une commande d’imprimerie en cours ?</h3>
      <p class="text-neutral-300 mt-1">Suivez son avancement avec votre code et votre numéro de téléphone.</p>
    </div>
    <a href="{{ route('track') }}" class="bg-white text-neutral-900 px-6 py-3 rounded-xl font-semibold hover:bg-neutral-100 transition">Suivre ma commande →</a>
  </div>
</section>
@endsection
