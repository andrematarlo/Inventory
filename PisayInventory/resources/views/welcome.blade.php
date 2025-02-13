<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PSHS Inventory System</title>
        <link rel="icon" href="{{ asset('images/pisaylogo.png') }}" type="image/x-icon">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
                font-family: 'Poppins', sans-serif;
            }
            body {
                background-image: url("{{ asset('images/pshsbackground.jpg') }}");
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-attachment: fixed;
                min-height: 100vh;
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
            .glass-nav {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
            .glass-card {
                background: rgba(255, 255, 255, 0.1);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 15px;
                transition: transform 0.3s ease;
            }
            .glass-card:hover {
                transform: translateY(-5px);
            }
            .btn {
                padding: 10px 20px;
                border-radius: 8px;
                transition: all 0.3s ease;
                text-decoration: none;
            }
            .btn-primary {
                background: rgba(255, 255, 255, 0.2);
                color: white;
            }
            .btn-primary:hover {
                background: rgba(255, 255, 255, 0.3);
            }
            .btn-outline {
                border: 1px solid rgba(255, 255, 255, 0.3);
                color: white;
            }
            .btn-outline:hover {
                background: rgba(255, 255, 255, 0.1);
            }
        </style>
        
        <!-- Vite assets -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <!-- Navigation -->
        <nav style="position: fixed; width: 100%; padding: 20px 0; z-index: 1000;" class="glass-nav">
            <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center;">
                <div style="color: white; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <img src="{{ asset('images/pisaylogo.png') }}" alt="PSHS Logo" style="height: 35px; width: auto;">
                    <span>PSHS-CVisC</span>
                </div>
                
                @if (Route::has('login'))
                    <div style="display: flex; gap: 20px;">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-outline">Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-outline">Log in</a>
                        @endauth
                    </div>
                @endif
            </div>
        </nav>

        <!-- Hero Section --> 
        <div style="padding: 140px 20px 20px; text-align: center;">
            <div style="max-width: 800px; margin: 0 auto;">
                <h1 style="font-size: 48px; color: white; font-weight: 700; margin-bottom: 20px;">
                    <span style="display: block;">Inventory<br>Management System</span>
                   
                </h1>
                <p style="color: rgba(255, 255, 255, 0.8); font-size: 18px; line-height: 1.6;">
                    Efficiently manage and track your school's resources with our modern inventory system.
                </p>
            </div>
        </div>

        <!-- Features Section -->
        <div style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-bottom: 60px;">
                <!-- Feature 1 -->
                <div class="glass-card" style="padding: 20px;">
                    <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                        <svg style="width: 30px; height: 30px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <h3 style="color: white; font-size: 20px; margin-bottom: 10px;">Easy Tracking</h3>
                    <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.4; font-size: 14px;">
                        Keep track of all your inventory items with our intuitive system.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="glass-card" style="padding: 20px;">
                    <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                        <svg style="width: 30px; height: 30px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 style="color: white; font-size: 20px; margin-bottom: 10px;">Real-time Updates</h3>
                    <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.4; font-size: 14px;">
                        Get instant updates on inventory changes and movements.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="glass-card" style="padding: 20px;">
                    <div style="width: 50px; height: 50px; background: rgba(255, 255, 255, 0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 15px;">
                        <svg style="width: 30px; height: 30px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <h3 style="color: white; font-size: 20px; margin-bottom: 10px;">Detailed Reports</h3>
                    <p style="color: rgba(255, 255, 255, 0.8); line-height: 1.4; font-size: 14px;">
                        Generate comprehensive reports for better decision making.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer style="text-align: center; padding: 10px; color: rgba(255, 255, 255, 0.8); position: fixed; bottom: 0; width: 100%; background: rgba(255, 255, 255, 0.1);">
            © {{ date('Y') }} PSHS-CVisC Inventory System. All rights reserved.
        </footer>
    </body>
</html>
