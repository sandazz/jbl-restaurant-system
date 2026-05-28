<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') - Restaurant BYOB</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f1f5f9 0%, #e9eef5 100%);
        }

        /* Sidebar */
        .sidebar {
            background: #fff;
            box-shadow: 2px 0 12px rgba(0,0,0,0.06);
            border-right: 1px solid #f1f5f9;
        }
        .active-nav {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #991b1b;
            border-left: 3px solid #dc2626;
            font-weight: 700;
        }
        .main-content { margin-left: 256px; }
        @media (max-width: 1024px) { .main-content { margin-left: 0; } }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Navbar -->
    @include('layouts.navbar')

    <div class="flex" style="padding-top:67px;">
        <!-- Sidebar -->
        <x-sidebar :modules="$modules ?? []" />

        <!-- Main Content -->
        <div class="flex-1 main-content px-6 py-8 w-full lg:w-auto">
            @yield('content')
        </div>
    </div>

    @yield('scripts')
</body>
</html>
