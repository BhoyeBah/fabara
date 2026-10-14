<!doctype html>
<html lang="fr" class="scroll-smooth">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', \App\Support\Setting::get('company_name') . ' — Impress & Boutique')</title>
<meta name="description" content="@yield('description', 'Fabara Impress & Boutique : vêtements, cosmétiques, chaussures, bijoux, accessoires et sacs, et tous vos travaux d’impression et de personnalisation.')">
<meta name="theme-color" content="#d7141a">
<link rel="icon" href="{{ asset('images/logo.jpg') }}">
@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
  @vite('resources/css/app.css')
@else
  {{-- Repli si "npm run build" n'a pas été lancé : Tailwind via CDN (à éviter en production) --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <script>tailwind.config={theme:{extend:{colors:{fred:'#d7141a',fpink:'#d6196b',fgreen:'#0a8f3c',fyellow:'#f6c400'}}}}</script>
@endif
<style>
  html{-webkit-text-size-adjust:100%}
  body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;background:#fff;color:#171717}
  .brand-line{height:4px;background:linear-gradient(90deg,#d7141a,#f6c400,#0a8f3c)}
  .lift{transition:transform .25s ease, box-shadow .25s ease, border-color .25s ease}
  .lift:hover{transform:translateY(-4px);box-shadow:0 14px 30px -12px rgba(0,0,0,.22)}
  .zoom img{transition:transform .5s ease}
  .lift:hover .zoom img{transform:scale(1.06)}
  .reveal{opacity:0;transform:translateY(16px);transition:opacity .6s ease, transform .6s ease}
  .reveal.in{opacity:1;transform:none}
  .mega{opacity:0;visibility:hidden;transform:translateY(8px);transition:all .18s ease}
  .has-mega:hover .mega,.has-mega:focus-within .mega{opacity:1;visibility:visible;transform:none}
  .no-scrollbar::-webkit-scrollbar{display:none}.no-scrollbar{scrollbar-width:none}
  @media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}.reveal{opacity:1;transform:none}}
  :focus-visible{outline:2px solid #d7141a;outline-offset:2px}
  .flash{animation:flash .5s ease}
  @keyframes flash{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:none}}
</style>
</head>
<body class="antialiased">
@php($menus = \App\Models\Category::orderBy('position')->get()->groupBy('menu'))
@php($wa = \App\Support\Setting::whatsapp())
@php($company = \App\Support\Setting::get('company_name'))

<div class="bg-neutral-900 text-white text-xs">
  <div class="max-w-7xl mx-auto px-4 py-2 flex flex-wrap items-center justify-between gap-2">
    <span>Boutique &amp; Imprimerie — commandez sur WhatsApp ou demandez un devis</span>
    <span class="hidden sm:flex items-center gap-4">
      @if (\App\Support\Setting::get('phone'))<span>☎ {{ \App\Support\Setting::get('phone') }}</span>@endif
      <a href="{{ route('track') }}" class="hover:underline">Suivre ma commande</a>
    </span>
  </div>
</div>

<header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-neutral-200">
  <div class="max-w-7xl mx-auto px-4 h-16 flex items-center gap-3 lg:gap-6">
    <button type="button" id="burger" aria-label="Menu" class="lg:hidden w-10 h-10 grid place-items-center rounded-lg border border-neutral-200">☰</button>
    <a href="{{ route('home') }}" class="shrink-0"><img src="{{ asset('images/logo.jpg') }}" alt="{{ $company }} Impress et Boutique" class="h-11 w-auto"></a>

    <nav class="hidden lg:flex items-center gap-1 text-sm font-medium">
      @foreach (['boutique' => 'Boutique', 'imprimerie' => 'Imprimerie'] as $k => $label)
        <div class="has-mega relative">
          <a href="{{ route('home') }}#{{ $k }}" class="px-4 py-2 rounded-full hover:bg-neutral-100 inline-flex items-center gap-1">{{ $label }} <span class="text-[10px]">▾</span></a>
          <div class="mega absolute left-0 top-full pt-2 w-[34rem] z-50">
            <div class="bg-white rounded-2xl border border-neutral-200 shadow-xl p-3 grid grid-cols-2 gap-1">
              @foreach ($menus[$k] ?? [] as $c)
                <a class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-neutral-100" href="{{ route('category', [$k, $c->slug]) }}"><span class="text-lg">{{ $c->icon() }}</span><span>{{ $c->name }}</span></a>
              @endforeach
            </div>
          </div>
        </div>
      @endforeach
      <a href="{{ url('/') }}#devis" class="px-4 py-2 rounded-full hover:bg-neutral-100">Devis</a>
      <a href="{{ route('track') }}" class="px-4 py-2 rounded-full hover:bg-neutral-100">Suivi de commande</a>
    </nav>

    <form action="{{ route('search') }}" method="GET" class="flex-1 max-w-md ml-auto hidden md:block">
      <label class="relative block">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-neutral-400">🔍</span>
        <input type="search" name="q" value="{{ request()->routeIs('search') ? request('q') : '' }}" placeholder="Rechercher un article ou un service…"
               class="w-full rounded-full bg-neutral-100 border border-transparent focus:bg-white focus:border-neutral-300 pl-10 pr-4 py-2.5 text-sm outline-none transition">
      </label>
    </form>
    @auth
      <a href="{{ auth()->user()->role->home() }}" class="ml-auto md:ml-0 text-xs font-semibold text-white bg-fred hover:bg-red-700 rounded-full px-4 py-2 whitespace-nowrap">{{ auth()->user()->role === \App\Enums\Role::Client ? 'Mon compte' : 'Mon interface' }}</a>
    @else
      <div class="ml-auto md:ml-0 flex items-center gap-2 whitespace-nowrap">
        <a href="{{ route('login') }}" class="text-xs font-semibold border border-neutral-300 hover:border-neutral-900 rounded-full px-3 py-2">Connexion</a>
        <a href="{{ route('register') }}" class="hidden sm:inline text-xs font-semibold text-white bg-fred hover:bg-red-700 rounded-full px-3 py-2">Créer un compte</a>
      </div>
    @endauth
  </div>

  {{-- Menu mobile --}}
  <div id="drawer" class="hidden lg:hidden border-t border-neutral-200 bg-white max-h-[75vh] overflow-auto">
    <form action="{{ route('search') }}" method="GET" class="p-4 pb-2">
      <input type="search" name="q" placeholder="Rechercher…" class="w-full rounded-full bg-neutral-100 px-4 py-2.5 text-sm outline-none">
    </form>
    @foreach (['boutique' => 'Boutique', 'imprimerie' => 'Imprimerie'] as $k => $label)
      <details class="px-4 py-1 border-b border-neutral-100">
        <summary class="py-3 font-semibold cursor-pointer list-none flex justify-between">{{ $label }} <span>▾</span></summary>
        <div class="pb-3 grid gap-1">
          @foreach ($menus[$k] ?? [] as $c)
            <a class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-neutral-100" href="{{ route('category', [$k, $c->slug]) }}"><span>{{ $c->icon() }}</span>{{ $c->name }}</a>
          @endforeach
        </div>
      </details>
    @endforeach
    <a href="{{ url('/') }}#devis" class="block px-4 py-3 font-semibold border-b border-neutral-100">Demander un devis</a>
    <a href="{{ route('track') }}" class="block px-4 py-3 font-semibold border-b border-neutral-100">Suivre ma commande</a>
    @auth
      <a href="{{ auth()->user()->role->home() }}" class="block px-4 py-3 font-semibold">{{ auth()->user()->role === \App\Enums\Role::Client ? 'Mon compte' : 'Mon interface' }}</a>
    @else
      <a href="{{ route('login') }}" class="block px-4 py-3 font-semibold border-b border-neutral-100">Connexion</a>
      <a href="{{ route('register') }}" class="block px-4 py-3 font-semibold text-fred">Créer un compte</a>
    @endauth
  </div>
  <div class="brand-line"></div>
</header>

@if (session('status'))<div class="flash bg-green-50 text-green-800 border-b border-green-200 text-center px-4 py-3 text-sm">✓ {{ session('status') }}</div>@endif
<main>@yield('content')</main>

<section id="devis" class="bg-neutral-50 border-t border-neutral-200 mt-16">
  <div class="max-w-7xl mx-auto px-4 py-14 grid lg:grid-cols-2 gap-10 items-start">
    <div class="reveal">
      <p class="text-fred font-semibold text-sm uppercase tracking-wider">Imprimerie sur mesure</p>
      <h2 class="text-3xl md:text-4xl font-extrabold mt-2">Demandez votre devis</h2>
      <p class="text-neutral-600 mt-3 max-w-md">Cartes de visite, bâches, T-shirts, cachets, badges… Décrivez votre besoin : nous revenons vers vous avec un prix et un délai.</p>
      <ul class="mt-6 space-y-3 text-neutral-700">
        <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-fgreen text-white grid place-items-center text-xs">✓</span> Réponse avec prix et délai</li>
        <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-fgreen text-white grid place-items-center text-xs">✓</span> Un code pour suivre votre commande en ligne</li>
        <li class="flex gap-3"><span class="w-6 h-6 rounded-full bg-fgreen text-white grid place-items-center text-xs">✓</span> Envoi de votre logo ou visuel par WhatsApp</li>
      </ul>
    </div>
    <div class="bg-white border border-neutral-200 rounded-3xl shadow-sm p-6 md:p-8 reveal">
      @if (session('success'))<p class="flash bg-green-50 text-green-800 border border-green-200 rounded-xl px-4 py-3 mb-4">✓ {{ session('success') }}</p>@endif
      @if ($errors->any() && old('subject') !== null)<p class="bg-red-50 text-red-700 rounded-xl px-4 py-3 mb-4">Merci de remplir votre nom, votre téléphone et votre demande.</p>@endif
      <form method="POST" action="{{ route('quote.store') }}" class="grid gap-3">
        @csrf
        <input type="text" name="website" tabindex="-1" autocomplete="off" style="position:absolute;left:-9999px" aria-hidden="true">
        <div class="grid sm:grid-cols-2 gap-3">
          <label class="block"><span class="text-sm font-medium">Votre nom</span>
            <input name="name" id="rn" value="{{ old('name') }}" oninput="wl()" required class="mt-1 w-full border border-neutral-300 rounded-xl px-4 py-3 focus:border-fred outline-none"></label>
          <label class="block"><span class="text-sm font-medium">Téléphone</span>
            <input name="phone" id="rp" value="{{ old('phone') }}" required inputmode="tel" class="mt-1 w-full border border-neutral-300 rounded-xl px-4 py-3 focus:border-fred outline-none"></label>
        </div>
        <label class="block"><span class="text-sm font-medium">Travail ou article souhaité</span>
          <input name="subject" id="rj" value="{{ old('subject') }}" oninput="wl()" placeholder="Ex : 200 flyers A5 couleur" required class="mt-1 w-full border border-neutral-300 rounded-xl px-4 py-3 focus:border-fred outline-none"></label>
        <label class="block"><span class="text-sm font-medium">Détails (facultatif)</span>
          <textarea name="details" id="rt" rows="3" oninput="wl()" placeholder="Couleurs, format, date souhaitée…" class="mt-1 w-full border border-neutral-300 rounded-xl px-4 py-3 focus:border-fred outline-none">{{ old('details') }}</textarea></label>
        <div class="flex flex-wrap gap-3 pt-1">
          <button class="bg-fred hover:bg-red-700 text-white px-6 py-3 rounded-xl font-semibold transition">Envoyer ma demande</button>
          <a id="wa" target="_blank" rel="noopener" class="border border-neutral-300 hover:border-neutral-900 px-6 py-3 rounded-xl font-semibold transition">Ou par WhatsApp</a>
        </div>
      </form>
    </div>
  </div>
</section>

<footer class="bg-neutral-900 text-neutral-300">
  <div class="brand-line"></div>
  <div class="max-w-7xl mx-auto px-4 py-12 grid sm:grid-cols-2 lg:grid-cols-4 gap-8 text-sm">
    <div class="lg:col-span-1">
      <img src="{{ asset('images/logo.jpg') }}" alt="{{ $company }}" class="h-12 w-auto bg-white rounded-lg p-1">
      <p class="mt-4 text-neutral-400">Boutique de mode et accessoires, et imprimerie : tout pour vous habiller, vous équiper et faire connaître votre activité.</p>
    </div>
    <div>
      <h3 class="text-white font-semibold mb-3">Boutique</h3>
      <ul class="space-y-2">@foreach (($menus['boutique'] ?? collect())->take(8) as $c)<li><a class="hover:text-white" href="{{ route('category', ['boutique', $c->slug]) }}">{{ $c->name }}</a></li>@endforeach</ul>
    </div>
    <div>
      <h3 class="text-white font-semibold mb-3">Imprimerie</h3>
      <ul class="space-y-2">@foreach (($menus['imprimerie'] ?? collect())->take(8) as $c)<li><a class="hover:text-white" href="{{ route('category', ['imprimerie', $c->slug]) }}">{{ $c->name }}</a></li>@endforeach</ul>
    </div>
    <div>
      <h3 class="text-white font-semibold mb-3">Contact</h3>
      <ul class="space-y-2">
        @if (\App\Support\Setting::get('phone'))<li>☎ {{ \App\Support\Setting::get('phone') }}</li>@endif
        @if (\App\Support\Setting::get('address'))<li>📍 {{ \App\Support\Setting::get('address') }}</li>@endif
        @if ($wa)<li><a class="hover:text-white" href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener">💬 WhatsApp</a></li>@endif
        <li><a class="hover:text-white" href="{{ url('/') }}#devis">Demander un devis</a></li>
        <li><a class="hover:text-white" href="{{ route('track') }}">Suivre ma commande</a></li>
      </ul>
    </div>
  </div>
  <div class="border-t border-neutral-800 text-xs text-neutral-500">
    <div class="max-w-7xl mx-auto px-4 py-4 flex flex-wrap gap-x-5 gap-y-2 justify-between">
      <span>© {{ date('Y') }} {{ $company }} — Impress &amp; Boutique. Tous droits réservés.</span>
      <span class="flex flex-wrap gap-x-4 gap-y-1">
        <a class="hover:text-white" href="{{ route('privacy') }}">Confidentialité</a>
        <a class="hover:text-white" href="{{ route('terms') }}">Conditions</a>
        <a class="hover:text-white" href="{{ route('deletion') }}">Suppression de compte</a>
      </span>
    </div>
  </div>
</footer>

@if ($wa)
<a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" aria-label="Écrire sur WhatsApp"
   class="fixed bottom-5 right-5 z-40 bg-fgreen hover:bg-green-700 text-white rounded-full pl-4 pr-5 py-3 font-semibold shadow-xl transition flex items-center gap-2">💬 <span class="hidden sm:inline">WhatsApp</span></a>
@endif
<script>
const WA = @json($wa);
function wl(){
  const g = id => (document.getElementById(id)||{}).value || '';
  const t = encodeURIComponent('Bonjour, je suis ' + g('rn') + '. Je souhaite : ' + g('rj') + '. ' + g('rt'));
  const a = document.getElementById('wa');
  if (!a) return;
  if (WA) { a.href = 'https://wa.me/' + WA + '?text=' + t; } else { a.style.display = 'none'; }
}
wl();
document.getElementById('burger')?.addEventListener('click', () => document.getElementById('drawer').classList.toggle('hidden'));
// Apparition en douceur au défilement
const els = document.querySelectorAll('.reveal');
if ('IntersectionObserver' in window) {
  const io = new IntersectionObserver((entries) => entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } }), { threshold: .08 });
  els.forEach(el => io.observe(el));
} else { els.forEach(el => el.classList.add('in')); }
</script>
</body>
</html>
