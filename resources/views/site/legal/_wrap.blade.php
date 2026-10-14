@extends('site.layout')
@section('content')
<div class="max-w-3xl mx-auto px-4 py-12 leading-relaxed text-neutral-700 [&_h1]:text-3xl [&_h1]:font-extrabold [&_h1]:text-neutral-900 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-neutral-900 [&_h2]:mt-8 [&_h2]:mb-2 [&_ul]:list-disc [&_ul]:pl-6 [&_a]:text-fred [&_a]:underline">
  @yield('body')
  <p class="text-sm text-neutral-400 mt-10">Dernière mise à jour : {{ now()->translatedFormat('j F Y') }}</p>
</div>
@endsection
