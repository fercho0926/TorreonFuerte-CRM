{{-- Etiquetas de la aplicación instalable (spec 008): color de barra, manifiesto, iconos y registro del service worker. --}}
<meta name="theme-color" content="#0d6efd">
<link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
<link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
<link rel="icon" type="image/png" sizes="32x32" href="{{ asset('iconos/favicon-32.png') }}">
<link rel="apple-touch-icon" href="{{ asset('iconos/apple-touch-180.png') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-title" content="Torreón">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="crm-sw" content="{{ asset('sw.js') }}">
