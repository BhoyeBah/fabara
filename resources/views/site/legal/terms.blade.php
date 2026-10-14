@extends('site.legal._wrap')
@section('title', 'Conditions d’utilisation — Fabara')
@section('body')
@php $co = $company ?? 'Fabara'; @endphp
<h1>Conditions d’utilisation</h1>
<p>En créant un compte ou en utilisant ce site, vous acceptez les conditions suivantes.</p>

<h2>Le service</h2>
<p>Le site de {{ $co }} présente nos articles de boutique et nos services d’imprimerie, permet de demander un devis, de suivre une commande et de gérer son compte.</p>

<h2>Compte</h2>
<ul>
  <li>Vous fournissez des informations exactes et gardez votre mot de passe confidentiel.</li>
  <li>Vous pouvez supprimer votre compte à tout moment depuis « Mon compte » ou la page <a href="{{ route('deletion') }}">Suppression de compte</a>.</li>
  <li>Nous pouvons suspendre un compte utilisé de façon abusive.</li>
</ul>

<h2>Devis et commandes</h2>
<p>Une demande de devis n’est pas une commande. Le prix et le délai sont confirmés par nous avant fabrication. Les articles de boutique se règlent et se retirent en magasin, dans la limite des stocks.</p>

<h2>Prix et disponibilité</h2>
<p>Les prix et stocks affichés peuvent changer. Les photos sont indicatives.</p>

<h2>Données personnelles</h2>
<p>Leur traitement est décrit dans notre <a href="{{ route('privacy') }}">politique de confidentialité</a>.</p>

<h2>Responsabilité</h2>
<p>Nous faisons au mieux pour la disponibilité du site sans garantie d’accès continu. Vous restez responsable des contenus (logos, textes) que vous nous confiez pour impression.</p>
@endsection
