@extends('site.legal._wrap')
@section('title', 'Politique de confidentialité — Fabara')
@section('body')
@php $co = $company ?? 'Fabara'; $mail = \App\Support\Setting::get('email'); $tel = \App\Support\Setting::get('phone'); @endphp
<h1>Politique de confidentialité</h1>
<p>{{ $co }} (« nous ») exploite ce site et l’application associée. Cette page explique quelles données nous collectons, pourquoi, et comment vous les contrôlez.</p>

<h2>Données collectées</h2>
<ul>
  <li><b>Compte client</b> : nom, email, mot de passe (stocké chiffré), téléphone (facultatif), date d’acceptation des conditions.</li>
  <li><b>Demandes de devis et commandes d’imprimerie</b> : nom, téléphone, description du travail demandé.</li>
  <li><b>Achats en boutique</b> : montants et articles, sans lien avec votre compte sauf si vous le demandez.</li>
  <li><b>Données techniques</b> : cookie de session nécessaire à la connexion. Aucune publicité, aucun traceur tiers.</li>
</ul>

<h2>Pourquoi</h2>
<p>Pour créer et sécuriser votre compte, répondre à vos demandes de devis, fabriquer et suivre vos commandes, établir les factures et vous contacter à ce sujet. Nous ne vendons ni ne louons vos données.</p>

<h2>Partage</h2>
<p>Vos données ne sont accessibles qu’à notre équipe (administration, caisse, atelier). Elles ne sont transmises à aucun tiers, sauf obligation légale.</p>

<h2>Durée de conservation</h2>
<p>Votre compte est conservé tant que vous ne le supprimez pas. Les factures sont gardées sans identité apparente après suppression, pour respecter nos obligations comptables.</p>

<h2>Vos droits</h2>
<ul>
  <li><b>Accès et copie</b> : « Mon compte » → « Télécharger mes données ».</li>
  <li><b>Correction</b> : « Mon compte » → « Mes informations ».</li>
  <li><b>Suppression</b> : « Mon compte » → « Supprimer mon compte et mes données », ou sans vous connecter via la page <a href="{{ route('deletion') }}">Suppression de compte</a>. La suppression dans l’application fait de même.</li>
</ul>

<h2>Sécurité</h2>
<p>Mots de passe chiffrés, connexion protégée contre les tentatives répétées, accès de l’équipe limité selon le rôle.</p>

<h2>Enfants</h2>
<p>Le service ne s’adresse pas aux moins de 13 ans et nous ne collectons pas sciemment leurs données.</p>

<h2>Contact</h2>
<p>@if ($mail) Email : <a href="mailto:{{ $mail }}">{{ $mail }}</a>@endif @if ($tel) · Téléphone : {{ $tel }}@endif @if (! $mail && ! $tel) Coordonnées indiquées en bas de page. @endif</p>
@endsection
