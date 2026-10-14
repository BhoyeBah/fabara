@extends('site.layout')
@section('title', 'Créer un compte — Fabara')
@section('content')
<div class="max-w-md mx-auto px-4 py-14">
  <form method="POST" action="{{ url('/inscription') }}" class="bg-white border border-neutral-200 rounded-3xl shadow-sm p-8">
    @csrf
    <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
    <div class="text-center mb-6">
      <h1 class="text-2xl font-extrabold">Créer mon compte</h1>
      <p class="text-neutral-500 text-sm mt-1">Suivez vos commandes et gérez vos informations.</p>
    </div>
    @php $f = 'mt-1 w-full border border-neutral-300 rounded-xl px-4 py-3 outline-none focus:border-fred'; @endphp
    <label class="block mb-3"><span class="text-sm font-medium">Nom complet</span><input name="name" value="{{ old('name') }}" required class="{{ $f }}"></label>
    @error('name')<p class="text-sm text-red-600 mb-2">{{ $message }}</p>@enderror
    <label class="block mb-3"><span class="text-sm font-medium">Email</span><input name="email" type="email" value="{{ old('email') }}" required class="{{ $f }}"></label>
    @error('email')<p class="text-sm text-red-600 mb-2">{{ $message }}</p>@enderror
    <label class="block mb-3"><span class="text-sm font-medium">Téléphone (facultatif)</span><input name="phone" value="{{ old('phone') }}" inputmode="tel" class="{{ $f }}">
      <span class="text-xs text-neutral-500">Sert à retrouver vos commandes d’imprimerie.</span></label>
    <label class="block mb-3"><span class="text-sm font-medium">Mot de passe (8 caractères minimum)</span><input name="password" type="password" required class="{{ $f }}"></label>
    @error('password')<p class="text-sm text-red-600 mb-2">{{ $message }}</p>@enderror
    <label class="block mb-4"><span class="text-sm font-medium">Confirmer le mot de passe</span><input name="password_confirmation" type="password" required class="{{ $f }}"></label>
    <label class="text-sm flex items-start gap-2 mb-2"><input type="checkbox" name="terms" value="1" class="rounded mt-1" required>
      <span>J’ai lu et j’accepte les <a href="{{ route('terms') }}" target="_blank" class="text-fred underline">conditions d’utilisation</a> et la <a href="{{ route('privacy') }}" target="_blank" class="text-fred underline">politique de confidentialité</a>.</span></label>
    @error('terms')<p class="text-sm text-red-600 mb-2">{{ $message }}</p>@enderror
    <button class="mt-3 w-full bg-fred hover:bg-red-700 text-white rounded-xl py-3 font-semibold transition">Créer mon compte</button>
    <p class="text-center text-sm text-neutral-600 mt-5">Déjà un compte ? <a href="{{ route('login') }}" class="text-fred font-semibold">Se connecter</a></p>
  </form>
</div>
@endsection
