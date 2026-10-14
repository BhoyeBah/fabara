@extends('site.layout')
@section('title', 'Connexion — Fabara')
@section('content')
<div class="max-w-md mx-auto px-4 py-14">
  @auth
    <div class="bg-white border border-neutral-200 rounded-3xl shadow-sm p-8 text-center">
      <img src="{{ asset('images/logo.jpg') }}" alt="Fabara" class="h-14 mx-auto">
      <h1 class="text-2xl font-extrabold mt-4">Vous êtes connecté</h1>
      <p class="text-neutral-600 mt-2">{{ auth()->user()->name }} — {{ auth()->user()->role->getLabel() }}</p>
      <a href="{{ auth()->user()->role->home() }}" class="mt-6 block w-full bg-fred hover:bg-red-700 text-white rounded-xl py-3 font-semibold transition">Continuer</a>
      <form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf
        <button class="w-full border border-neutral-300 hover:border-neutral-900 rounded-xl py-3 font-semibold transition">Changer de compte</button>
      </form>
    </div>
  @else
  <form method="POST" action="{{ url('/connexion') }}" class="bg-white border border-neutral-200 rounded-3xl shadow-sm p-8">
    @csrf
    <div class="text-center mb-6">
      <img src="{{ asset('images/logo.jpg') }}" alt="Fabara" class="h-14 mx-auto">
      <h1 class="text-2xl font-extrabold mt-4">Connexion</h1>
      <p class="text-neutral-500 text-sm mt-1">Clients et équipe : entrez vos identifiants, vous serez dirigé vers votre espace.</p>
    </div>
    <label class="block mb-3"><span class="text-sm font-medium">Email</span>
      <input name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-1 w-full border border-neutral-300 rounded-xl px-4 py-3 outline-none focus:border-fred"></label>
    <label class="block mb-3"><span class="text-sm font-medium">Mot de passe</span>
      <input name="password" type="password" required class="mt-1 w-full border border-neutral-300 rounded-xl px-4 py-3 outline-none focus:border-fred"></label>
    @error('email')<p class="text-sm text-red-600 mb-3">{{ $message }}</p>@enderror
    <label class="text-sm flex items-center gap-2 mb-5"><input type="checkbox" name="remember" value="1" class="rounded"> Rester connecté</label>
    <button class="w-full bg-fred hover:bg-red-700 text-white rounded-xl py-3 font-semibold transition">Se connecter</button>
    <p class="text-center text-sm text-neutral-600 mt-5">Pas encore de compte ? <a href="{{ route('register') }}" class="text-fred font-semibold">Créer un compte</a></p>
  </form>
  @endauth
</div>
@endsection
