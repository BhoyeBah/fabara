@extends('site.layout')
@section('title', 'Mon compte — Fabara')
@section('content')
@php $f = 'mt-1 w-full border border-neutral-300 rounded-xl px-4 py-3 outline-none focus:border-fred'; $card = 'bg-white border border-neutral-200 rounded-3xl shadow-sm p-6 md:p-8'; @endphp
<div class="max-w-4xl mx-auto px-4 py-12 grid gap-6">
  <div>
    <h1 class="text-3xl font-extrabold">Mon compte</h1>
    <p class="text-neutral-500">{{ $u->name }} · {{ $u->email }}</p>
  </div>

  @if ($staff)
    <div class="{{ $card }}">
      <p class="text-neutral-700">Vous êtes connecté avec un compte de l’équipe ({{ $u->role->getLabel() }}).</p>
      <a href="{{ $u->role->home() }}" class="inline-block mt-4 bg-fred text-white rounded-xl px-6 py-3 font-semibold">Ouvrir mon interface</a>
      <p class="text-sm text-neutral-500 mt-3">Les comptes de l’équipe sont gérés par l’administrateur.</p>
    </div>
  @else
  <div class="grid md:grid-cols-2 gap-6">
    <div class="{{ $card }}">
      <h2 class="font-bold text-lg mb-3">Mes commandes d’imprimerie</h2>
      @forelse ($orders as $o)
        <div class="flex justify-between gap-3 py-2 border-b border-neutral-100 text-sm">
          <span><b>{{ $o->code }}</b> — {{ $o->title }}</span>
          <span class="text-neutral-600 whitespace-nowrap">{{ $o->status instanceof \BackedEnum ? $o->status->getLabel() : $o->status }}</span>
        </div>
      @empty
        <p class="text-sm text-neutral-500">Aucune commande liée à votre téléphone. <a href="{{ route('track') }}" class="text-fred">Suivre avec un code</a></p>
      @endforelse
    </div>
    <div class="{{ $card }}">
      <h2 class="font-bold text-lg mb-3">Mes demandes de devis</h2>
      @forelse ($quotes as $q)
        <div class="flex justify-between gap-3 py-2 border-b border-neutral-100 text-sm"><span>{{ $q->subject }}</span><span class="text-neutral-500 whitespace-nowrap">{{ $q->created_at?->format('d/m/Y') }}</span></div>
      @empty
        <p class="text-sm text-neutral-500">Aucune demande. <a href="{{ url('/') }}#devis" class="text-fred">Demander un devis</a></p>
      @endforelse
    </div>
  </div>

  <form method="POST" action="{{ route('account.update') }}" class="{{ $card }} grid gap-3">
    @csrf @method('PUT')
    <h2 class="font-bold text-lg">Mes informations</h2>
    <div class="grid sm:grid-cols-3 gap-3">
      <label class="block"><span class="text-sm font-medium">Nom</span><input name="name" value="{{ old('name', $u->name) }}" required class="{{ $f }}"></label>
      <label class="block"><span class="text-sm font-medium">Email</span><input name="email" type="email" value="{{ old('email', $u->email) }}" required class="{{ $f }}"></label>
      <label class="block"><span class="text-sm font-medium">Téléphone</span><input name="phone" value="{{ old('phone', $u->phone) }}" class="{{ $f }}"></label>
    </div>
    @if ($errors->hasAny(['name','email','phone']))<p class="text-sm text-red-600">{{ $errors->first('name') ?: $errors->first('email') ?: $errors->first('phone') }}</p>@endif
    <div><button class="bg-fred text-white rounded-xl px-6 py-3 font-semibold">Enregistrer</button></div>
  </form>

  <form method="POST" action="{{ route('account.password') }}" class="{{ $card }} grid gap-3">
    @csrf @method('PUT')
    <h2 class="font-bold text-lg">Changer mon mot de passe</h2>
    <div class="grid sm:grid-cols-3 gap-3">
      <label class="block"><span class="text-sm font-medium">Mot de passe actuel</span><input name="current_password" type="password" required class="{{ $f }}"></label>
      <label class="block"><span class="text-sm font-medium">Nouveau (8 caractères min.)</span><input name="password" type="password" required class="{{ $f }}"></label>
      <label class="block"><span class="text-sm font-medium">Confirmer</span><input name="password_confirmation" type="password" required class="{{ $f }}"></label>
    </div>
    @if ($errors->hasAny(['current_password','password']))<p class="text-sm text-red-600">{{ $errors->first('current_password') ?: $errors->first('password') }}</p>@endif
    <div><button class="border border-neutral-300 hover:border-neutral-900 rounded-xl px-6 py-3 font-semibold">Modifier</button></div>
  </form>

  <div class="{{ $card }}">
    <h2 class="font-bold text-lg">Mes données</h2>
    <p class="text-sm text-neutral-600 mt-1">Vous pouvez télécharger une copie de vos données personnelles à tout moment.</p>
    <a href="{{ route('account.export') }}" class="inline-block mt-3 border border-neutral-300 hover:border-neutral-900 rounded-xl px-6 py-3 font-semibold">Télécharger mes données</a>
  </div>

  <form method="POST" action="{{ route('account.destroy') }}" class="{{ $card }} border-red-200 grid gap-3" onsubmit="return confirm('Supprimer définitivement votre compte et vos données ?')">
    @csrf @method('DELETE')
    <h2 class="font-bold text-lg text-red-700">Supprimer mon compte et mes données</h2>
    <p class="text-sm text-neutral-600">Votre compte, vos demandes de devis et vos informations de contact sont effacés définitivement. Les factures et commandes déjà réalisées sont conservées de façon anonyme (obligation comptable). Action irréversible.</p>
    <label class="block max-w-sm"><span class="text-sm font-medium">Votre mot de passe</span><input name="password" type="password" required class="{{ $f }}"></label>
    <label class="text-sm flex items-center gap-2"><input type="checkbox" name="confirm" value="1" class="rounded"> Je confirme vouloir supprimer mon compte.</label>
    @if ($errors->hasAny(['password','confirm']))<p class="text-sm text-red-600">{{ $errors->first('password') ?: $errors->first('confirm') }}</p>@endif
    <div><button class="bg-red-600 hover:bg-red-700 text-white rounded-xl px-6 py-3 font-semibold">Supprimer définitivement</button></div>
  </form>
  @endif

  <form method="POST" action="{{ route('logout') }}">@csrf <button class="text-sm text-neutral-600 underline">Me déconnecter</button></form>
</div>
@endsection
