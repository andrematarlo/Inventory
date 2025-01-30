<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .login-btn {
            position: relative;
            overflow: hidden;
            z-index: 1;
        }
        
        .login-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
            z-index: -1;
        }
        
        .login-btn:hover::before {
            left: 100%;
        }
        
        .quote-container {
            animation: fadeIn 1.5s ease-out;
        }
        
        @keyframes fadeIn {
            from { 
                opacity: 0; 
                transform: translateY(20px); 
            }
            to { 
                opacity: 1; 
                transform: translateY(0); 
            }
        }
        
        .quote-text {
            color: #8BB9FE;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .system-title {
            color: #fff;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
    </style>
</head>
<body>
    <!-- Login Button -->
    <div class="position-absolute top-0 end-0 m-4">
        <a href="/login" class="btn btn-outline-light login-btn px-4 py-2">
            <i class="bi bi-box-arrow-in-right me-2"></i>Login
        </a>
    </div>

    <!-- Center Quote Section -->
    <div class="container d-flex align-items-center justify-content-center min-vh-100">
        <div class="quote-container text-center p-4">
            <h1 class="system-title display-4 fw-bold mb-4">
                Welcome to PSHS Inventory System
            </h1>
            <blockquote class="quote-text fs-3 fst-italic mb-4">
                "Organization is not just about managing things, but about making life simpler and more efficient."
            </blockquote>
            <p class="text-light-emphasis small opacity-75">
                PSHS Inventory Management System
            </p>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"></script>
</body>
</html>