<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title') - {{ config('app.name') }}</title>
    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
    <style>
        .svg-icon path {
            fill: #FFFFFF;
        }
    </style>

</head>

<body>
    <div class="flex flex-col">
        @include('student.layout.header')
        @include('student.components.navigation')
        <main class="bg-gray-100 flex flex-col gap-5 items-center h-full p-4">
            @yield('content')
        </main>
    </div>
    @stack('js')
    <script>
        const navAccount = document.getElementById('nav-account');
        const navMenu = document.getElementById('nav-account-menu');

        // Toggle the dropdown on button click
        navAccount.addEventListener('click', function(event) {
            event.stopPropagation(); // Ensure it only toggles on the button click
            navMenu.classList.toggle('hidden'); // Toggle the dropdown visibility
        });

        // Close the dropdown if clicked outside of button or dropdown menu
        document.addEventListener('click', function(event) {
            if (!navAccount.contains(event.target) && !navMenu.contains(event.target)) {
                navMenu.classList.add('hidden'); // Close the dropdown if clicked outside
            }
        });
    </script>

</body>

</html>
