@extends('site.layout')
@section('title', 'Recherche — Fabara')
@section('content')
<div class="max-w-7xl mx-auto px-4 pt-8">
  <h1 class="text-3xl font-extrabold">{{ $term !== '' ? 'Résultats pour « ' . $term . ' »' : 'Rechercher' }}</h1>
  <form method="GET" action="{{ route('search') }}" class="flex gap-2 mt-4 max-w-xl">
    <input type="search" name="q" value="{{ $term }}" placeholder="Article ou service…" class="border border-neutral-300 rounded-xl px-4 py-2.5 flex-1 outline-none focus:border-neutral-900">
    <button class="bg-neutral-900 text-white rounded-xl px-5 py-2.5 font-medium">Rechercher</button>
  </form>
  @if ($term !== '')
    <p class="text-sm text-neutral-500 mt-4">{{ $products->count() }} résultat{{ $products->count() > 1 ? 's' : '' }}</p>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6 mt-4">
      @forelse ($products as $p) @include('site._card', ['p' => $p])
      @empty
        <div class="col-span-full text-center py-16 border border-dashed border-neutral-300 rounded-2xl">
          <p class="font-semibold">Aucun résultat</p>
          <p class="text-neutral-500 mt-1">Essayez un autre mot, ou décrivez-nous votre besoin dans une demande de devis.</p>
          <a href="{{ url('/') }}#devis" class="inline-block mt-4 bg-fred text-white px-5 py-2.5 rounded-xl font-semibold">Demander un devis</a>
        </div>
      @endforelse
    </div>
  @endif
</div>
@endsection
