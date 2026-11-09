<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'MI Raudlatul Ulum')</title>
    <x-site-branding />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="screen-shell"><x-site-header /><x-site-navbar /><main>@yield('content')</main><x-site-footer /></body>
</html>
