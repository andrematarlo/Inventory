<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PSHS Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 40px 0;
        }
        .register-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            display: flex;
        }
        .register-image {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                        url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><rect width="100" height="100" fill="%23283593"/></svg>');
            width: 40%;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .register-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
        }
        .register-image-content {
            position: relative;
            color: white;
            text-align: center;
        }
        .register-form {
            width: 60%;
            padding: 40px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 20px;
            border: 1px solid #e1e1e1;
        }
        .form-control:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
            border-color: #667eea;
        }
        .btn-register {
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
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .register-header {
            margin-bottom: 30px;
        }
        .register-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 10px;
        }
        .register-header p {
            color: #718096;
            font-size: 0.95rem;
        }
        @media (max-width: 768px) {
            .register-image {
                display: none;
            }
            .register-form {
                width: 100%;
            }
            .register-container {
                max-width: 500px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-image">
                <div class="register-image-content">
                    <h2 class="mb-4">PSHS Inventory System</h2>
                    <p class="mb-0">Join our team to manage inventory efficiently</p>
                </div>
            </div>
            <div class="register-form">
                <div class="register-header">
                    <h1>Create Account</h1>
                    <p>Please fill in the registration form</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ url('/register') }}" class="mt-4">
                    @csrf
                    <div class="row g-3">
                        <!-- Account Information -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="username" name="Username" 
                                       placeholder="Username" required value="{{ old('Username') }}">
                                <label for="username"><i class="bi bi-person me-2"></i>Username</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="email" name="Email" 
                                       placeholder="Email" required value="{{ old('Email') }}">
                                <label for="email"><i class="bi bi-envelope me-2"></i>Email</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password" 
                                       name="Password" placeholder="Password" required>
                                <label for="password"><i class="bi bi-lock me-2"></i>Password</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="password" class="form-control" id="password_confirmation" 
                                       name="Password_confirmation" placeholder="Confirm Password" required>
                                <label for="password_confirmation"><i class="bi bi-lock-fill me-2"></i>Confirm Password</label>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="firstname" name="FirstName" 
                                       placeholder="First Name" required value="{{ old('FirstName') }}">
                                <label for="firstname">First Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="lastname" name="LastName" 
                                       placeholder="Last Name" required value="{{ old('LastName') }}">
                                <label for="lastname">Last Name</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="gender" name="Gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="Male" {{ old('Gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('Gender') == 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                                <label for="gender">Gender</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="role" name="Role" required>
                                    <option value="">Select Role</option>
                                    <option value="Admin" {{ old('Role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="Employee" {{ old('Role') == 'Employee' ? 'selected' : '' }}>Employee</option>
                                </select>
                                <label for="role">Role</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-floating">
                                <textarea class="form-control" id="address" name="Address" 
                                          placeholder="Address" style="height: 100px" required>{{ old('Address') }}</textarea>
                                <label for="address">Address</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-register mt-4">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
