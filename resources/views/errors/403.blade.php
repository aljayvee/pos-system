<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            border-radius: 24px;
            max-width: 500px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container px-4">
        <div class="card error-card border-0 shadow-lg text-center p-5">
            <div class="mb-4">
                <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 100px; height: 100px;">
                    <i class="fas fa-user-lock fa-3x"></i>
                </div>
            </div>
            
            <h2 class="fw-bold text-dark mb-2">Access Denied</h2>
            <h5 class="text-secondary mb-4">Code 403: Unauthorized</h5>
            
            <p class="text-muted mb-4 fs-5">
                You do not have permission to access this resource. 
                <br>
                Please contact the administrator for assistance.
            </p>

            <div class="d-grid gap-2 col-md-8 mx-auto">
                <a href="{{ url()->previous() == url()->current() ? url('/') : url()->previous() }}" class="btn btn-primary rounded-pill fw-bold py-3 shadow-sm">
                    <i class="fas fa-arrow-left me-2"></i> Go Back
                </a>
            </div>
            
            <div class="mt-4 pt-3 border-top">
                <small class="text-muted">Current Role: <span class="badge bg-secondary rounded-pill">{{ ucfirst(auth()->user()->role ?? 'Guest') }}</span></small>
            </div>
        </div>
    </div>
</body>
</html>
