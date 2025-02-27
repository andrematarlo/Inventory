<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PSHS Inventory System</title>
    <link rel="icon" href="{{ asset('images/pisaylogo.png') }}" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-image: url("{{ asset('images/pshsbackground.jpg') }}");
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 1rem;
            overflow-x: hidden;
            overflow-y: auto;
        }
        /* Add overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5); /* dark overlay for better readability */
            z-index: -1;
        }

        .page-layout {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 1400px;
            min-height: calc(100vh - 2rem);
            padding: 1rem;
            gap: 2rem;
            flex-wrap: wrap;
        }

        .background-content {
            flex: 1;
            min-width: 300px;
            max-width: 600px;
            padding: 1rem;
        }

        .background-content h1 {
            font-size: 3.2rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
            color: white;
        }

        .background-content p {
            font-size: 1.1rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0;
        }

        .login-container {
            flex: 1;
            min-width: 300px;
            max-width: 450px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-control {
            width: 100%;
            max-width: 100%;
            min-height: 40px;
            padding: 0.5rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            color: white;
            margin-bottom: 1rem;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        .form-label {
            color: white;
        }

        .btn-login {
            min-height: 40px;
            padding: 0.5rem 1.5rem;
            width: auto;
            min-width: 120px;
            display: block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .btn-login:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.95rem;
        }

        .features-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 3rem;
        }

        .feature-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .feature-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .feature-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .feature-item:hover .feature-icon {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .feature-item:hover svg {
            transform: scale(1.1);
            color: rgba(255, 255, 255, 1);
        }

        .feature-icon svg {
            width: 28px;
            height: 28px;
            color: white;
            transition: all 0.3s ease;
        }

        .feature-item:hover h3,
        .feature-item:hover p {
            transform: translateX(5px);
        }

        .feature-item h3,
        .feature-item p {
            transition: all 0.3s ease;
        }

        .feature-item h3 {
            font-size: 1.1rem;
            margin-bottom: 0.35rem;
            color: white;
        }

        .feature-item p {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0;
        }

        .button-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: white;
            margin-bottom: 1rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
            border-radius: 8px;
        }

        /* Add responsive breakpoints */
        @media (max-width: 1200px) {
            .page-layout {
                max-width: 100%;
                padding: 2rem;
            }

            .background-content h1 {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 992px) {
            .page-layout {
                padding: 1.5rem;
                gap: 1.5rem;
            }

            .background-content h1 {
                font-size: 2rem;
            }

            .feature-item {
                padding: 0.75rem 1rem;
            }

            .feature-icon {
                width: 40px;
                height: 40px;
            }

            .feature-icon svg {
                width: 24px;
                height: 24px;
            }
        }

        @media (max-width: 768px) {
            .page-layout {
                flex-direction: column;
                gap: 1rem;
                padding: 0.5rem;
            }

            .background-content,
            .login-container {
                width: 100%;
                max-width: 100%;
                padding: 1rem;
            }

            .background-content {
                text-align: center;
            }

            .background-content h1 {
                font-size: 2rem !important;
            }

            .features-grid {
                margin-top: 1rem;
                gap: 0.5rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 0.5rem;
            }

            .login-container {
                padding: 1rem;
            }

            .background-content h1 {
                font-size: 1.5rem !important;
            }

            .feature-item {
                padding: 0.5rem;
            }

            .login-header h1 {
                font-size: 1.75rem;
            }

            .login-header p {
                font-size: 0.9rem;
            }

            .form-control {
                padding: 0.5rem 0.75rem;
            }
        }

        /* Add orientation specific styles */
        @media (max-height: 600px) and (orientation: landscape) {
            .page-layout {
                height: auto;
                min-height: 100vh;
                padding: 1rem;
            }

            .features-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }

            .background-content h1 {
                font-size: 1.8rem;
            }
        }

        /* Maintain aspect ratio for logo */
        .login-header img {
            max-width: 70px;
            height: auto;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="page-layout">
        <div class="background-content">
            <h1 style="font-size: 48px; color: white; font-weight: 700; margin-bottom: 20px;">
                <span style="display: block;">PSHS-CVisC Inventory</span>
                <span style="display: block;">Management System</span>
            </h1>
            <p style="color: rgba(255, 255, 255, 0.8); font-size: 18px; line-height: 1.6;">
                Efficiently manage and track your school's resources with our modern inventory system.
            </p>
            
            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Easy Tracking</h3>
                        <p>Keep track of all inventory items</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Real-time Updates</h3>
                        <p>Instant inventory changes</p>
                    </div>
                </div>
                
                <div class="feature-item">
                    <div class="feature-icon">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3>Detailed Reports</h3>
                        <p>Comprehensive analytics</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-container">
            <div class="login-header">
                <div style="margin-bottom: 20px;">
                    <img src="{{ asset('images/pisaylogo.png') }}" alt="PSHS Logo" style="height: 70px; width: auto;">
                </div>
                <h1>Welcome Back!</h1>
                <p>Please sign in to continue</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3">
                    <label for="Username" class="form-label">Username</label>
                    <input type="text" 
                           class="form-control @error('Username') is-invalid @enderror" 
                           name="Username" 
                           value="{{ old('Username') }}" 
                           required 
                           autofocus>
                    @error('Username')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="Password" class="form-label">Password</label>
                    <input type="password" 
                           class="form-control @error('Password') is-invalid @enderror" 
                           name="Password" 
                           required>
                    @error('Password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <div class="button-container">
                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
