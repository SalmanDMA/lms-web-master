<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="api-token" content="{{ session('token') }}">
    <title>{{ $title }} - {{ config('app.name') }}</title>
    @vite(['resources/sass/app.scss'])
    <link rel="shortcut icon" href="{{ asset('/assets/static/images/logo/favicon.svg') }}" type="image/x-icon">
    <link rel="shortcut icon" href="{{ asset('/assets/static/images/logo/favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="{{ asset('/assets/css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/js/choices.js/public/assets/styles/choices.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/css/table-datatable-jquery.css') }}">
    <link rel="stylesheet" href="{{ asset('/assets/js/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">

    <script src="{{ asset('/assets/js/choices.js/public/assets/scripts/choices.min.js') }}"></script>
    <script src="{{ asset('/assets/js/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('/assets/js/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('/assets/js/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('/assets/js/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/34.2.0/classic/ckeditor.js"></script>
</head>

<body>
    <div id="app">
        <div id="sidebar">
            @include('layouts.partials.sidebar')
        </div>
        <div id="main">
            <header class="mb-3">
                <a href="#" class="burger-btn d-block d-xl-none">
                    <i class="bi bi-justify fs-3"></i>
                </a>
            </header>
            {{ $slot }}
            @include('layouts.partials.footer')
        </div>
    </div>

    @vite(['resources/js/app.js'])
</body>

</html>
