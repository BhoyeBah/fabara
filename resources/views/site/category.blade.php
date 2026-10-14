@extends('site.layout')
@section('title', e($category->name) . ' — Fabara')
@section('content')
@php($accent = $menu === 'boutique' ? 'text-fpink' : 'text-fred')
<div class="max-w-7xl mx-auto px-4">
  <p class="pt-6 text-sm text-neutral-500"><a href="{{ route('home') }}" class="hover:underline">Accueil</a> › <a href="{{ route('home') }}#{{ $menu }}" class="hover:underline capitalize">{{ $menu }}</a> › <span class="text-neutral-900">{{ $category->name }}</span></p>

  <div class="mt-4 rounded-3xl p-6 md:p-8 flex items-center gap-5 {{ $menu === 'boutique' ? 'bg-gradient-to-r from-pink-50 to-white' : 'bg-gradient-to-r from-red-50 to-white' }}">
    <span class="text-5xl md:text-6xl">{{ $category->icon() }}</span>
    <div>
      <h1 class="text-3xl md:text-4xl font-extrabold">{{ $category->name }}</h1>
      <p class="text-neutral-600 mt-1">{{ $products->count() }} article{{ $products->count() > 1 ? 's' : '' }} disponible{{ $products->count() > 1 ? 's' : '' }}</p>
    </div>
  </div>

  <div class="grid lg:grid-cols-[16rem_1fr] gap-8 mt-8">
    <aside class="hidden lg:block">
      <h2 class="font-bold mb-3 capitalize">{{ $menu }}</h2>
      <nav class="grid gap-1 text-sm">
        @foreach ($siblings as $s)
          <a href="{{ route('category', [$menu, $s->slug]) }}" class="flex items-center justify-between gap-2 px-3 py-2 rounded-lg {{ $s->id === $category->id ? 'bg-neutral-900 text-white' : 'hover:bg-neutral-100' }}">
            <span class="flex items-center gap-2"><span>{{ $s->icon() }}</span>{{ $s->name }}</span>
            <span class="text-xs opacity-60">{{ $s->products_count }}</span>
          </a>
        @endforeach
      </nav>
    </aside>

    <div>
      <form method="GET" class="flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Rechercher dans {{ $category->name }}…" class="border border-neutral-300 rounded-xl px-4 py-2.5 flex-1 min-w-[200px] outline-none focus:border-neutral-900">
        <select name="tri" onchange="this.form.submit()" class="border border-neutral-300 rounded-xl px-4 py-2.5 bg-white">
          <option value="">Plus récents</option>
          <option value="prix_asc" @selected(request('tri') === 'prix_asc')>Prix croissant</option>
          <option value="prix_desc" @selected(request('tri') === 'prix_desc')>Prix décroissant</option>
        </select>
        <button class="bg-neutral-900 text-white rounded-xl px-5 py-2.5 font-medium">Rechercher</button>
      </form>

      <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6 mt-6">
        @forelse ($products as $p)
          @include('site._card', ['p' => $p])
        @empty
          <div class="col-span-full text-center py-16 border border-dashed border-neutral-300 rounded-2xl">
            <p class="text-4xl">{{ $category->icon() }}</p>
            <p class="font-semibold mt-3">Aucun article pour le moment</p>
            <p class="text-neutral-500 mt-1">Contactez-nous : nous pouvons répondre à votre besoin.</p>
            <a href="{{ url('/') }}#devis" class="inline-block mt-4 bg-fred text-white px-5 py-2.5 rounded-xl font-semibold">Demander un devis</a>
          </div>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection
