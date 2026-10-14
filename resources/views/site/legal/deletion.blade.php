@extends('site.legal._wrap')
@section('title', 'Suppression de compte — Fabara')
@section('body')
<h1>Supprimer mon compte et mes données</h1>
<p>Vous pouvez demander la suppression de votre compte {{ $company ?? 'Fabara' }} et des données associées à tout moment.</p>

<h2>Option 1 : vous pouvez vous connecter</h2>
<p><a href="{{ route('login') }}">Connectez-vous</a>, ouvrez « Mon compte » puis « Supprimer mon compte et mes données ». La suppression est immédiate (même chose dans l’application).</p>

<h2>Option 2 : vous ne pouvez pas vous connecter</h2>
<form method="POST" action="{{ route('deletion.request') }}" class="grid gap-3 mt-3 not-prose">
  @csrf
  <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
  <input name="email" type="email" required placeholder="Email de votre compte" class="border border-neutral-300 rounded-xl px-4 py-3 outline-none focus:border-fred">
  <input name="phone" placeholder="Téléphone (facultatif)" class="border border-neutral-300 rounded-xl px-4 py-3 outline-none focus:border-fred">
  @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
  <button class="bg-red-600 hover:bg-red-700 text-white rounded-xl px-6 py-3 font-semibold justify-self-start">Demander la suppression</button>
</form>
<p class="mt-3">Nous traitons la demande sous 30 jours maximum.</p>

<h2>Ce qui est supprimé</h2>
<ul>
  <li>Votre compte (nom, email, téléphone, mot de passe).</li>
  <li>Vos demandes de devis et vos informations de contact.</li>
</ul>
<h2>Ce qui est conservé</h2>
<ul><li>Les factures et commandes déjà réalisées, sans votre nom ni votre téléphone (obligation comptable).</li></ul>
@endsection
