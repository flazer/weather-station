<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="robots" content="noindex,nofollow"/>

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        <script src="{{ mix('js/app.js') }}" defer></script>

        <!-- Styles -->
        <link rel="stylesheet" href="{{mix('css/app.css')}}">
    </head>
    <body>
        <div id="app" class="h-100">
            <main class="h-100">
                @yield('content')
            </main>
            <footer>
                <a href="https://frozendonkey.com" target="_blank"></a>
            </footer>
        </div>
    </body>
</html>