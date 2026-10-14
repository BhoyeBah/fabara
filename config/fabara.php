<?php
// Valeurs par défaut. Elles se modifient ensuite dans l'admin : Paramètres.
return [
    'whatsapp' => env('FABARA_WHATSAPP', ''),   // avec indicatif, sans + ni espaces
    'currency' => env('FABARA_CURRENCY', ''),   // ex : GNF (vide = aucun suffixe)
    'tax_rate' => env('FABARA_TAX_RATE', 0),    // TVA en % (0 = pas de TVA ; les prix saisis sont alors TTC)
];
