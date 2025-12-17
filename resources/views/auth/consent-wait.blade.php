<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting for Approval</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body class="bg-gray-100 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg max-w-md w-full text-center">
        <div class="animate-pulse mb-6">
            <div class="h-16 w-16 bg-blue-100 text-blue-500 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
        </div>
        
        <h2 class="text-xl font-bold mb-2">Verification Required</h2>
        <p class="text-gray-600 mb-6">
            We detected another active session on this account. <br>
            <strong>Please check your other device to approve this login.</strong>
        </p>

        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-4">
            <div class="bg-blue-600 h-2.5 rounded-full" style="width: 100%; animation: progress 60s linear;"></div>
        </div>

        <a href="/login" class="text-sm text-gray-500 hover:underline">Cancel Request</a>
    </div>

    <script>
        // Check status every 2 seconds
        setInterval(() => {
            axios.get('{{ route("auth.consent.check") }}')
                .then(response => {
                    if (response.data.status === 'approved') {
                        window.location.href = response.data.redirect;
                    } else if (response.data.status === 'denied') {
                        alert('Login Request Denied.');
                        window.location.href = '/login';
                    }
                })
                .catch(error => console.error(error));
        }, 2000);
    </script>
</body>
</html>