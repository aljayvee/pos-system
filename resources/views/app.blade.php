<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <link rel="icon" href="{{ asset('images/favicon.png') }}" type="image/png">

    {{-- We no longer output $pageTitle here directly because Inertia handles document titles with

    <Head> component --}}

        {{-- CSS Libraries --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        <style>
            button:focus {
                outline: none !important;
            }

            html {
                background-color: #f1f5f9;
                min-height: 100vh;
                height: 100%;
            }

            body {
                zoom: 80%;
                background-color: #f1f5f9;
                height: 125vh;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                margin: 0;
            }
        </style>

        {{-- Inertia Asset Handling --}}
        @vite(['resources/css/app.css', 'resources/css/premium-ui.css', 'resources/js/app.js'])
        @inertiaHead
    </head>

<body class="bg-light">
    @inertia

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>