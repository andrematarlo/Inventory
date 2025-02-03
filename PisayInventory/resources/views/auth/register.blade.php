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
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .page-layout {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            max-width: 1400px;
            height: 100vh;
            padding: 2rem 4rem;
        }
        .background-content {
            width: 45%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            height: 90vh;
            padding-top: 4rem;
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
            margin-bottom: 3rem;
        }
        .register-container {
            width: 45%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            height: 90vh;
            overflow-y: auto;
        }
        .container {
            width: auto;
            padding: 0;
        }
        .register-image {
            display: none;
        }
        .register-form {
            width: 100%;
            padding: 0;
        }
        .form-group {
            margin-bottom: 0.5rem;
            position: relative;
        }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.5rem 0.75rem;
            height: 40px;
            color: white;
            width: 100%;
            font-size: 0.9rem;
        }
        .form-select {
            padding-right: 2rem;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 16px 12px;
        }
        .form-select option {
            background-color: #764ba2;
            color: white;
            padding: 8px;
        }
        .form-control::placeholder,
        .form-select::placeholder {
            color: rgba(255, 255, 255, 0.8);
            opacity: 1;
        }
        .form-label {
            display: none;
        }
        .row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin: 0;
        }
        .col-6 {
            flex: 0 0 calc(50% - 0.25rem);
            max-width: calc(50% - 0.25rem);
        }
        .col-12 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        textarea.form-control {
            min-height: 35px !important;
            height: 35px !important;
            resize: none;
        }
        .btn-register {
            width: auto;
            padding: 0.4rem 1.5rem;
            margin: 0.5rem auto 0;
            display: block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        .btn-register:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        .register-header {
            margin-bottom: 0.75rem;
            text-align: center;
        }
        .register-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: white !important;
            margin-bottom: 5px;
        }
        .register-header p {
            color: rgba(255, 255, 255, 0.8) !important;
            font-size: 0.85rem;
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
        .features-grid {
            display: flex;
            flex-direction: column;
            gap: 1rem;
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
        }
        .feature-icon svg {
            width: 28px;
            height: 28px;
            color: white;
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
        .register-header h1,
        .form-label {
            color: white !important;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.6) !important;
        }
        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: rgba(255, 255, 255, 0.8) !important;
        }
        .form-select option {
            background-color: #667eea;
            color: white;
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.1);
            border: 1px solid rgba(220, 53, 69, 0.2);
            color: white;
            margin-bottom: 1rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.85rem;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .form-floating > label {
            padding: 0.5rem 0.75rem;
            color: rgba(255, 255, 255, 0.8) !important;
        }
        .form-floating > .form-control,
        .form-floating > .form-select {
            height: calc(2.5rem + 2px);
            padding: 0.5rem 0.75rem;
            line-height: 1.25;
            background: rgba(255, 255, 255, 0.1);
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label,
        .form-floating > .form-select ~ label {
            opacity: 1;
            transform: scale(0.85) translateY(-1rem) translateX(0.15rem);
            background: transparent;
            padding: 0 0.5rem;
        }
        .form-floating > textarea.form-control {
            height: 100px;
        }
        .row.g-3 {
            margin: 0;
        }
        .col-md-6 {
            padding: 0.5rem;
        }
        .col-12 {
            padding: 0.5rem;
        }
        .sign-in-link {
            text-align: center;
            margin-top: 0.75rem;
            font-size: 0.85rem;
        }
        .sign-in-link a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .sign-in-link a:hover {
            color: white;
        }
        /* Update select styling */
        .form-select {
            background-color: white !important;
            color: #000000 !important;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
        /* Style for the dropdown options */
        .form-select option {
            background-color: white !important;
            color: #000000 !important;
            padding: 8px;
        }
        /* Style for the placeholder/default option */
        .form-select option[value=""][disabled] {
            color: rgba(0, 0, 0, 0.6) !important;
        }
        /* Update the dropdown arrow color to black */
        .form-select {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23000000' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
        }
        /* Optional: Add container for button to ensure proper centering */
        .button-container {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-top: 0.75rem;
        }
        .features-wrapper {
            display: none;
        }
        /* Hide scrollbar but keep functionality */
        .register-container::-webkit-scrollbar {
            display: none;
        }
        .register-container {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
</head>
<body>
    <div class="page-layout">
        <div class="background-content">
            <h1>PSHS Inventory Management System</h1>
            <p>Efficiently manage and track your school's resources with our modern inventory system.</p>
            
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

        <div class="register-container">
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

            <form method="POST" action="{{ url('/register') }}">
                @csrf
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <input type="text" class="form-control" name="Username" id="Username" placeholder="Username">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <input type="email" class="form-control" name="Email" id="Email" placeholder="Email">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <input type="password" class="form-control" id="password" 
                                   name="Password" placeholder="Password" required>
                            <label class="form-label">Password</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <input type="password" class="form-control" id="password_confirmation" 
                                   name="Password_confirmation" placeholder="Confirm Password" required>
                            <label class="form-label">Confirm Password</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <input type="text" class="form-control" id="firstname" name="FirstName" 
                                   placeholder="First Name" required value="{{ old('FirstName') }}">
                            <label class="form-label">First Name</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <input type="text" class="form-control" id="lastname" name="LastName" 
                                   placeholder="Last Name" required value="{{ old('LastName') }}">
                            <label class="form-label">Last Name</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <select class="form-select" id="gender" name="Gender" required>
                                <option value="" disabled selected>Select Gender</option>
                                <option value="Male" {{ old('Gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('Gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <select class="form-select" id="role" name="Role" required>
                                <option value="" disabled selected>Select Role</option>
                                <option value="Admin" {{ old('Role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                                <option value="Employee" {{ old('Role') == 'Employee' ? 'selected' : '' }}>Employee</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <textarea class="form-control" id="address" name="Address" 
                                      placeholder="Address" required>{{ old('Address') }}</textarea>
                            <label class="form-label">Address</label>
                        </div>
                    </div>
                </div>

                <div class="button-container">
                    <button type="submit" class="btn btn-register">
                        <i class="bi bi-person-plus me-2"></i>REGISTER
                    </button>

                    <div class="sign-in-link">
                        <a href="{{ route('login') }}">Already have an account? Sign In</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
