<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Waiting for Approval - POS System</title>
    
    {{-- PWA Manifest --}}
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#e0f2fe">

    {{-- Fonts & Icons --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    {{-- Premium UI --}}
    @vite(['resources/css/premium-ui.css'])

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    <style>
        .wait-container {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Ambient Bubbles */
        .bubble {
            position: absolute;
            background: linear-gradient(135deg, #a5b4fc 0%, #c4b5fd 100%);
            border-radius: 50%;
            opacity: 0.6;
            filter: blur(60px);
            z-index: -1;
            animation: float 8s ease-in-out infinite;
        }
        .bubble-1 { width: 300px; height: 300px; top: -100px; left: -100px; }
        .bubble-2 { width: 250px; height: 250px; bottom: 50px; right: -50px; animation-delay: -4s; background: linear-gradient(135deg, #67e8f9 0%, #93c5fd 100%); }

        .wait-card {
            width: 100%;
            max-width: 450px;
            padding: 40px;
            background: rgba(255, 255, 255, 0.7);
            text-align: center;
        }

        .pulse-icon {
            font-size: 3rem;
            color: #4f46e5;
            animation: pulse 2s infinite;
            margin-bottom: 1.5rem;
            display: inline-block;
        }

        @keyframes pulse {
            0% { transform: scale(0.95); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
            100% { transform: scale(0.95); opacity: 1; }
        }
        
        .progress-bar-custom {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin: 20px 0;
            position: relative;
        }
        
        .progress-value {
            height: 100%;
            background: linear-gradient(90deg, #4f46e5, #818cf8);
            width: 0%;
            transition: width 0.1s linear;
        }
    </style>
</head>
<body>

    <div class="wait-container">
        <div class="bubble bubble-1"></div>
        <div class="bubble bubble-2"></div>

        <div class="glass-panel wait-card float-card">
            <div class="pulse-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            
            <h3 class="fw-bold mb-2">Verification Required</h3>
            <p class="text-muted mb-4">Please check your email or main device to approve this login attempt.</p>

            <div class="p-3 bg-white rounded-3 shadow-sm mb-4 border d-inline-block">
                <h2 class="mb-0 fw-bold font-monospace text-primary tracking-wider" id="code-display">
                    {{ session('device_code', 'Checking...') }}
                </h2>
                <small class="text-muted d-block mt-1">DEVICE CODE</small>
            </div>

            <div class="progress-bar-custom">
                <div class="progress-value" id="progress"></div>
            </div>

            <p class="small text-muted mb-0">Redirecting automatically once approved...</p>
        </div>
    </div>

    <script>
        // Check status every 3 seconds
        let checkInterval = setInterval(checkStatus, 3000);
        let progress = 0;
        
        // Progress bar animation
        setInterval(() => {
            progress += 1; // 1% every 30ms = 3s loop roughly
            if(progress > 100) progress = 0;
            document.getElementById('progress').style.width = progress + '%';
        }, 30);

        function checkStatus() {
            axios.get('{{ route('auth.consent.check') }}')
                .then(response => {
                     if (response.data.status === 'approved') {
                         clearInterval(checkInterval);
                         window.location.href = "{{ route('admin.dashboard') }}";
                     } else if (response.data.status === 'rejected') {
                         clearInterval(checkInterval);
                         alert('Login Request Rejected.');
                         window.location.href = '{{ route('login') }}';
                     }
                })
                .catch(error => console.log('Checking status...'));
        }
    </script>
</body>
</html>