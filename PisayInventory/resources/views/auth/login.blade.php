<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PSHS Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
        }
        .login-image {
            background: url('{{ asset("images/inventory-bg.jpg") }}') center/cover;
            width: 50%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
        }
        .login-image-content {
            position: relative;
            color: white;
            text-align: center;
        }
        .login-form {
            width: 50%;
            padding: 40px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 20px;
            border: 1px solid #e1e1e1;
            margin-bottom: 20px;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
            border-color: #667eea;
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 20px;
            width: 100%;
            color: white;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .login-header {
            margin-bottom: 30px;
        }
        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #718096;
            font-size: 0.95rem;
        }
        @media (max-width: 768px) {
            .login-image {
                display: none;
            }
            .login-form {
                width: 100%;
            }
            .login-container {
                max-width: 400px;
            }
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .form-floating {
            margin-bottom: 20px;
        }
        .form-floating label {
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-image">
                <div class="login-image-content">
                    <h2 class="mb-4">PSHS Inventory System</h2>
                    <p class="mb-0">Efficiently manage your inventory with our comprehensive system</p>
                </div>
            </div>
            <div class="login-form">
                <div class="login-header">
                    <h1>Welcome Back!</h1>
                    <p>Please sign in to continue</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ url('/login') }}">
                    @csrf
                    <div class="form-floating">
                        <input type="text" class="form-control" id="username" name="Username" 
                               placeholder="Username" required value="{{ old('Username') }}">
                        <label for="username"><i class="bi bi-person me-2"></i>Username</label>
                    </div>

                    <div class="form-floating">
                        <input type="password" class="form-control" id="password" 
                               name="Password" placeholder="Password" required>
                        <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                    </button>

                    <div class="text-center mt-4">
                        <p class="text-muted">
                            Don't have an account? 
                            <a href="{{ route('register') }}" class="text-decoration-none">Register here</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
