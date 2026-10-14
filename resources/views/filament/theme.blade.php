<style>
    :root{--fb-line:#ececf0}
    html{scroll-behavior:smooth}
    body,.fi-body{font-feature-settings:"cv11","ss01";-webkit-font-smoothing:antialiased}

    /* Fond clair, cartes blanches */
    .fi-body,.fi-layout{background:#f6f7f9!important}
    .fi-main-ctn{background:transparent}
    .fi-main{padding-top:1.25rem}

    /* Menu latéral */
    .fi-sidebar{background:#fff!important;border-right:1px solid var(--fb-line)!important;box-shadow:none!important}
    .fi-sidebar-header{background:#fff!important;border-bottom:1px solid var(--fb-line)!important}
    .fi-sidebar-nav{padding-top:1rem}
    .fi-sidebar-group-label{font-size:.66rem!important;letter-spacing:.09em;text-transform:uppercase;color:#9ca3af!important;font-weight:700}
    .fi-sidebar-item-button{border-radius:12px!important;padding:.6rem .75rem!important;transition:background-color .15s ease,transform .1s ease}
    .fi-sidebar-item-button:hover{background:#f3f4f6!important}
    .fi-sidebar-item-active>.fi-sidebar-item-button,.fi-sidebar-item.fi-active>.fi-sidebar-item-button{background:rgba(var(--primary-500),.12)!important}
    .fi-sidebar-item-active .fi-sidebar-item-label,.fi-sidebar-item.fi-active .fi-sidebar-item-label{font-weight:700;color:rgb(var(--primary-700))!important}

    /* Barre du haut */
    .fi-topbar{background:rgba(255,255,255,.85)!important;backdrop-filter:blur(10px);border-bottom:1px solid var(--fb-line)!important;box-shadow:none!important}
    .fi-topbar nav{backdrop-filter:blur(8px)}

    /* Titres */
    .fi-header-heading{font-weight:800!important;letter-spacing:-.015em;font-size:1.75rem!important}

    /* Cartes, tableaux, widgets */
    .fi-section,.fi-ta-ctn,.fi-wi-stats-overview-stat,.fi-fo-wizard,.fi-in-section{background:#fff!important;border:1px solid var(--fb-line)!important;border-radius:18px!important;box-shadow:0 1px 2px rgba(0,0,0,.03),0 10px 28px -16px rgba(0,0,0,.14)!important;--tw-ring-shadow:0 0 #0000!important}
    .fi-wi-stats-overview-stat{transition:transform .2s ease,box-shadow .2s ease}
    .fi-wi-stats-overview-stat:hover{transform:translateY(-2px);box-shadow:0 14px 30px -14px rgba(0,0,0,.25)!important}
    .fi-ta-header-cell{font-size:.7rem!important;letter-spacing:.05em;text-transform:uppercase;color:#6b7280!important;background:#fafafb}
    .fi-ta-row{transition:background-color .15s ease}
    .fi-ta-row:hover{background:#fafafb}
    .fi-ta-cell{padding-top:.2rem;padding-bottom:.2rem}

    /* Boutons et champs */
    .fi-btn{border-radius:12px!important;font-weight:700;transition:transform .12s ease,box-shadow .2s ease,filter .2s ease}
    .fi-btn:active{transform:scale(.97)}
    .fi-btn-color-primary:not(.fi-outlined){box-shadow:0 8px 18px -10px rgba(var(--primary-600),.9)}
    .fi-input-wrp,.fi-select-input{border-radius:12px!important}
    .fi-badge{font-weight:700;border-radius:999px!important}
    .fi-modal-window{border-radius:22px!important;box-shadow:0 30px 60px -20px rgba(0,0,0,.35)!important}
    .fi-no-notifications,.fi-ta-empty-state{padding-top:2rem;padding-bottom:2rem}
    .fi-ta-empty-state-icon-ctn{background:#f3f4f6}

    /* Notifications */
    .fi-no-notification{border-radius:16px!important;box-shadow:0 18px 40px -18px rgba(0,0,0,.35)!important}

    /* Page de connexion */
    .fi-simple-layout{background:radial-gradient(1000px 500px at 85% -10%,rgba(246,196,0,.18),transparent 60%),radial-gradient(900px 500px at -10% 110%,rgba(214,25,107,.12),transparent 60%),#fff!important}
    .fi-simple-main{border-radius:26px!important;border:1px solid var(--fb-line);box-shadow:0 30px 70px -30px rgba(0,0,0,.3)!important;padding:2.25rem!important}
    .fi-simple-header-heading{font-weight:800!important;letter-spacing:-.02em}

    /* Apparition douce de la page */
    .fi-main{animation:fabara-in .22s ease both}
    @keyframes fabara-in{from{opacity:.4;transform:translateY(5px)}to{opacity:1;transform:none}}
    ::selection{background:rgba(var(--primary-500),.25)}
    @media (prefers-reduced-motion:reduce){*{animation:none!important;transition:none!important}}
</style>
