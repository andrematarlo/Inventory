<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register - PSHS Inventory</title>
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
            background-color: #f8f9fa;
            min-height: 100vh;
            overflow-x: hidden;
        }

        input, button, a, label, div, select {
            font-family: 'Poppins', sans-serif !important;
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

        .register-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .form-control, .form-select {
            border: none;
            border-radius: 8px;
            padding: 12px 16px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            font-weight: 400;
        }

        .form-control:focus, .form-select:focus {
            background-color: #fff;
            box-shadow: 0 0 0 2px #0061ff;
        }

        .btn-register {
            background: #0061ff;
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            color: white;
            width: 100%;
        }

        .btn-register:hover {
            background: #0056e0;
            transform: translateY(-2px);
        }

        .login-link {
            color: #0061ff;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
            font-size: 0.9rem;
        }

        .login-link:hover {
            color: #0056e0;
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
            color: #6c757d;
            margin-bottom: 0.5rem;
        }

        .register-header {
            color: #0061ff;
            font-size: 2rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="wave-bg"></div>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="register-container">
                    <h2 class="register-header">Create Account</h2>
                    
                    <form method="POST" action="{{ route('register') }}">
                        @csrf

                        <!-- Role Selection -->
                        <div class="mb-3">
                            <label class="form-label">Select Role</label>
                            <select class="form-select" name="role" required>
                                <option value="" selected disabled>Choose your role</option>
                                <option value="student" {{ old('role') == 'student' ? 'selected' : '' }}>Student</option>
                                <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                            </select>
                            @error('role')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Name -->
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" required autofocus autocomplete="name">
                            @error('full_name')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" required autocomplete="username">
                            @error('email')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required autocomplete="new-password">
                            @error('password')
                                <div class="error-message">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div class="mb-4">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="password_confirmation" required autocomplete="new-password">
                        </div>

                        <div class="mb-4">
                            <button type="submit" class="btn btn-register">
                                Register
                            </button>
                        </div>

                        <div class="text-center">
                            <a class="login-link" href="{{ route('login') }}">
                                Already have an account? Sign in
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
