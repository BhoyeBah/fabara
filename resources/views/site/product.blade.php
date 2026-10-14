@extends('site.layout')
@section('title', e($product->name) . ' — Fabara')
@section('description', e(\Illuminate\Support\Str::limit(strip_tags((string) $product->description) ?: $product->name . ' chez Fabara Impress & Boutique.', 150)))
@php($menu = $product->category->menu)
@php($wa = \App\Support\Setting::whatsapp())
@php($out = $product->stock !== null && $product->stock <= 0)
@php($accent = $menu === 'boutique' ? 'text-fpink' : 'text-fred')
@section('content')
<div class="max-w-7xl mx-auto px-4">
  <p class="pt-6 text-sm text-neutral-500">
    <a href="{{ route('home') }}" class="hover:underline">Accueil</a> ›
    <a href="{{ route('category', [$menu, $product->category->slug]) }}" class="hover:underline">{{ $product->category->name }}</a> ›
    <span class="text-neutral-900">{{ $product->name }}</span>
  </p>

  <div class="grid md:grid-cols-2 gap-8 lg:gap-14 mt-6">
    <div class="rounded-3xl overflow-hidden border border-neutral-200 bg-neutral-50 self-start">
      @if ($product->image_url)
        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full aspect-square object-cover {{ $out ? 'opacity-60 grayscale' : '' }}">
      @else
        <div class="w-full aspect-square grid place-items-center {{ $menu === 'boutique' ? 'bg-gradient-to-br from-pink-50 to-pink-100' : 'bg-gradient-to-br from-red-50 to-orange-100' }}">
          <span class="text-8xl">{{ $product->category->icon() }}</span>
        </div>
      @endif
    </div>

    <div class="md:sticky md:top-28 self-start">
      <a href="{{ route('category', [$menu, $product->category->slug]) }}" class="inline-block text-xs font-semibold px-3 py-1 rounded-full bg-neutral-100 hover:bg-neutral-200">{{ $product->category->name }}</a>
      <h1 class="text-3xl md:text-4xl font-extrabold mt-3 leading-tight">{{ $product->name }}</h1>
      <p class="text-4xl font-extrabold mt-5 {{ $accent }}">
        {{ $product->price ? ($menu === 'imprimerie' ? 'à partir de ' : '') . \App\Support\Money::fmt($product->price) : 'Prix sur devis' }}
      </p>

      @if ($menu === 'boutique')
        <p class="mt-3 inline-flex items-center gap-2 font-medium {{ $out ? 'text-neutral-500' : ($product->isLow() ? 'text-amber-600' : 'text-fgreen') }}">
          <span class="w-2.5 h-2.5 rounded-full {{ $out ? 'bg-neutral-400' : ($product->isLow() ? 'bg-amber-500' : 'bg-fgreen') }}"></span>
          {{ $out ? 'Épuisé pour le moment' : ($product->isLow() ? 'Dernières pièces disponibles' : 'En stock') }}
        </p>
      @endif

      @if ($product->description)<p class="mt-6 text-neutral-700 leading-relaxed whitespace-pre-line">{{ $product->description }}</p>@endif

      @unless ($out)
        <div class="mt-8 flex flex-wrap gap-3">
          <a class="text-white px-7 py-4 rounded-xl font-semibold shadow-lg transition hover:opacity-90 {{ $menu === 'boutique' ? 'bg-fpink shadow-pink-200' : 'bg-fred shadow-red-200' }}"
             @if ($wa) href="https://wa.me/{{ $wa }}?text={{ rawurlencode('Bonjour, je suis intéressé(e) par : ' . $product->name . ' (' . $product->category->name . ')') }}" target="_blank" rel="noopener"
             @else href="{{ url('/') }}#devis" @endif>
            {{ $menu === 'imprimerie' ? 'Demander un devis' : ($wa ? 'Commander sur WhatsApp' : 'Nous contacter') }}
          </a>
          <a href="{{ url('/') }}#devis" class="px-7 py-4 rounded-xl font-semibold border border-neutral-300 hover:border-neutral-900 transition">Poser une question</a>
        </div>
      @endunless

      <ul class="mt-8 grid gap-3 text-sm text-neutral-600 border-t border-neutral-200 pt-6">
        <li class="flex gap-3"><span>✓</span> Prix confirmé à la commande</li>
        <li class="flex gap-3"><span>✓</span> {{ $menu === 'imprimerie' ? 'Suivi de votre commande en ligne avec un code' : 'Retrait en boutique ou livraison à convenir' }}</li>
      </ul>
    </div>
  </div>

  @if ($related->count())
    <h2 class="text-2xl font-extrabold mt-16 mb-5">Dans la même catégorie</h2>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
      @foreach ($related as $p) @include('site._card', ['p' => $p]) @endforeach
    </div>
  @endif
</div>
@endsection
