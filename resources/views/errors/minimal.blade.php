<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>@yield('title')</title>

        @vite('resources/css/app.css')
    </head>
    <body class="antialiased">
        <div class="flex flex-col justify-center items-center gap-y-4 min-h-screen bg-gray-100">
            <div class="flex flex-col justify-center items-center gap-y-4 max-w-[300px]">
                <div class="px-4 text-gray-500 text-6xl font-bold">
                    Erro @yield('code')
                </div>
                <hr class="border-1 w-[200px]"/>
                <div class="ml-4 text-gray-500 uppercase text-center">
                    @yield('message')
                </div>
                <hr class="border-1 w-[200px]"/>
                <a href="/login_from_error" class="bg-gray-300 text-gray-500 w-full items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm">
                    <span class="fi-btn-label">
                        Voltar para Login
                    </span>
                </a>
            </div>
        </div>
    </body>
</html>
