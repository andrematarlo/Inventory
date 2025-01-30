<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - PSHS Inventory</title>
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .wave-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, #0061ff, #60efff);
            clip-path: polygon(0 0, 100% 0, 100% 35%, 0 65%);
            z-index: -1;
        }

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(
                45deg,
                transparent,
                rgba(255, 255, 255, 0.1),
                transparent
            );
            transform: rotate(45deg);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) rotate(45deg); }
            100% { transform: translateX(100%) rotate(45deg); }
        }

        .form-control {
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            font-weight: 400;
        }

        .form-control:focus {
            background-color: #fff;
            box-shadow: 0 0 0 2px #0061ff;
        }

        .btn-login {
            background: #0061ff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            width: 100%;
        }

        .btn-login:hover {
            background: #0056e0;
            transform: translateY(-2px);
        }

        .login-header {
            color: #0061ff;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .form-check-input:checked {
            background-color: #0061ff;
            border-color: #0061ff;
        }

        .forgot-link {
            color: #0061ff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            font-size: 0.9rem;
        }

        .forgot-link:hover {
            color: #0056e0;
        }

        .input-group-text {
            border: none;
            background-color: #f8f9fa;
        }

        .password-toggle {
            border: none;
            background-color: #f8f9fa;
            padding: 12px 16px;
            color: #6c757d;
            cursor: pointer;
            transition: color 0.3s;
        }

        .password-toggle:hover {
            color: #0061ff;
        }

        .error-message {
            background-color: #fff3f3;
            color: #dc3545;
            padding: 8px;
            border-radius: 6px;
            font-size: 0.875rem;
            margin-top: 4px;
        }

        .form-label {
            font-weight: 500;
            font-size: 0.9rem;
        }

        .form-check-label {
            font-size: 0.9rem;
        }

        ::placeholder {
            font-weight: 300;
            opacity: 0.7;
        }

        .divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 1.5rem 0;
        }

        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #e0e0e0;
        }

        .divider span {
            padding: 0 1rem;
            color: #6c757d;
            font-size: 0.9rem;
            font-weight: 400;
        }

        .btn-register {
            background: transparent;
            border: 2px solid #0061ff;
            color: #0061ff;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            width: 100%;
        }

        .btn-register:hover {
            background: rgba(0, 97, 255, 0.1);
            transform: translateY(-2px);
        }

        .buttons-container {
            display: flex;
            gap: 12px;
            margin-bottom: 1.5rem;
        }

        .form-select {
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            font-weight: 400;
            font-family: 'Poppins', sans-serif !important;
            cursor: pointer;
        }

        .form-select:focus {
            background-color: #fff;
            box-shadow: 0 0 0 2px #0061ff;
            border: none;
        }

        /* Custom arrow icon for the select */
        .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Cpath fill='%236c757d' d='M8 10.5l-4-4h8l-4 4z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
        }
    </style>
</head>
<body>
    <div class="wave-bg"></div>
    <div class="container">
        <div class="row min-vh-100 align-items-center justify-content-center">
            <div class="col-11 col-md-8 col-lg-4">
                <div class="login-container p-4 p-md-5">
                    <h2 class="login-header text-center mb-4">Sign In</h2>
                    
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        
                        <!-- Role Selection -->
                        <div class="mb-4">
                            <label class="form-label text-muted mb-2">Select Role</label>
                            <select class="form-select" name="role" required>
                                <option value="" selected disabled>Choose your role</option>
                                <option value="student">Student</option>
                                <option value="staff">Staff</option>
                            </select>
                            @error('role')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <!-- Email Input -->
                        <div class="mb-4">
                            <label class="form-label text-muted mb-2">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                placeholder="name@example.com" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password Input -->
                        <div class="mb-4">
                            <label class="form-label text-muted mb-2">Password</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" 
                                    name="password" placeholder="Enter password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <i class="bi bi-eye-fill"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember">
                                <label class="form-check-label text-muted" for="remember_me">
                                    Keep me signed in
                                </label>
                            </div>
                        </div>

                        <!-- Buttons Container -->
                        <div class="buttons-container">
                            <button type="submit" class="btn btn-login text-white">
                                Sign In
                            </button>
                            <a href="{{ route('register') }}" class="btn btn-register">
                                Register
                            </a>
                        </div>

                        <!-- Forgot Password -->
                        @if (Route::has('password.request'))
                            <div class="text-center">
                                <a href="{{ route('password.request') }}" class="forgot-link">
                                    Forgot password?
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
