<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waiting for Approval</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            Another session is active. Please approve this login on your other device.
        </p>

        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 mb-6">
            <div class="bg-blue-600 h-2.5 rounded-full" style="width: 100%; animation: progress 60s linear;"></div>
        </div>

        <button onclick="sendForceEmail()" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-2 px-4 rounded mb-4 text-sm">
            I cannot access my other device
        </button>

        <form action="{{ route('logout') }}" method="POST">
             @csrf
             <button type="submit" class="text-xs text-gray-500 hover:underline">Cancel Request</button>
        </form>
    </div>

    <script>
        const requestId = "{{ $request_id }}"; // Passed from controller

        // 1. Poll Status
        const poller = setInterval(() => {
            axios.get('{{ route("auth.consent.check") }}', { params: { request_id: requestId } })
                .then(response => {
                    if (response.data.status === 'approved') {
                        window.location.href = response.data.redirect;
                    } else if (response.data.status === 'denied') {
                        clearInterval(poller);
                        alert('Login Request Denied.');
                        window.location.href = '/login';
                    }
                });
        }, 2000);

        // 2. Send Email Function
        function sendForceEmail() {
            Swal.fire({
                title: 'Send Recovery Email?',
                text: "We will send a secure link to your registered email to force a login.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, send it'
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.post('{{ route("auth.force.email") }}', { 
                        _token: '{{ csrf_token() }}',
                        request_id: requestId 
                    })
                    .then(res => {
                        Swal.fire('Sent!', res.data.message, 'success');
                    })
                    .catch(err => {
                        Swal.fire('Error', 'Could not send email.', 'error');
                    });
                }
            });
        }
    </script>
</body>
</html>