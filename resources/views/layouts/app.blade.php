<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cafe POS System</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    
    <style>
        * { font-family: 'Inter', sans-serif; }
        .bg-majoo { background-color: #00a991; }
        .text-majoo { color: #00a991; }
        .border-majoo { border-color: #00a991; }
        .spinner {
            border: 3px solid rgba(0,169,145,0.2);
            border-top-color: #00a991;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .stat-card { transition: transform 0.2s ease; }
        .stat-card:hover { transform: translateY(-2px); }
    </style>
    
    <script>
        tailwind.config = {
            theme: { extend: { colors: { majoo: '#00a991' } } }
        }
    </script>
</head>
<body class="bg-gray-50">
    <div id="app">
        <!-- Loading -->
        <div id="loading" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
            <div class="bg-white rounded-xl p-6 flex items-center gap-3">
                <div class="spinner"></div>
                <span>Memuat...</span>
            </div>
        </div>

        <!-- Login -->
        <div id="login-container" class="min-h-screen flex items-center justify-center bg-gradient-to-br from-majoo to-teal-700 p-4">
            <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
                <div class="text-center mb-8">
                    <div class="w-16 h-16 bg-majoo rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fa-solid fa-store text-white text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Cafe POS System</h2>
                    <p class="text-gray-500 text-sm mt-2">Silakan login untuk melanjutkan</p>
                </div>
                
                <div id="login-error" class="bg-red-50 text-red-600 p-3 rounded-lg mb-4 text-sm hidden"></div>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" placeholder="admin@cafe.com" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-majoo focus:border-majoo outline-none">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                        <input type="password" id="password" placeholder="password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-majoo focus:border-majoo outline-none">
                    </div>
                    
                    <button onclick="login()" class="w-full bg-majoo text-white py-2 rounded-lg font-semibold hover:bg-opacity-90 transition">
                        Login
                    </button>
                </div>
                
                <p class="text-xs text-gray-400 text-center mt-6">Demo: admin@cafe.com / password</p>
            </div>
        </div>

        <!-- Main App -->
        <div id="main-container" class="hidden">
            <div class="flex h-screen overflow-hidden">
                <!-- Sidebar -->
                <aside id="sidebar" class="w-64 bg-white shadow-lg flex flex-col fixed md:relative z-30 h-full -translate-x-full md:translate-x-0 transition-transform">
                    <div class="p-5 border-b">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-majoo rounded-lg flex items-center justify-center">
                                <i class="fa-solid fa-utensils text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-xl font-bold">Cafe POS</h1>
                                <p class="text-xs text-gray-500">Management System</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto py-4" id="sidebar-menu"></div>
                    
                    <div class="p-4 border-t">
                        <div class="flex items-center gap-3">
                            <i class="fa-regular fa-circle-user"></i>
                            <span id="sidebar-user-role" class="capitalize text-sm"></span>
                        </div>
                    </div>
                </aside>

                <!-- Main Content -->
                <div class="flex-1 flex flex-col overflow-hidden">
                    <nav class="bg-white shadow-sm border-b px-6 py-3 flex justify-between items-center">
                        <button onclick="toggleSidebar()" class="md:hidden text-gray-600">
                            <i class="fa-solid fa-bars text-xl"></i>
                        </button>
                        
                        <div class="flex items-center gap-4">
                            <span id="user-name" class="font-semibold"></span>
                            <span id="user-role" class="text-sm text-gray-500 capitalize"></span>
                            <button onclick="logout()" class="text-red-500">
                                <i class="fa-solid fa-right-from-bracket"></i>
                            </button>
                        </div>
                    </nav>

                    <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                        <div id="page-content">
                            <div class="flex justify-center items-center h-64">
                                <div class="spinner"></div>
                            </div>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/pos-app.js') }}"></script>
</body>
</html>