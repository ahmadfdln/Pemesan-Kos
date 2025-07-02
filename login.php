<?php include 'koneksi.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Heaven Indekos</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f0f2f5; /* Light gray background color */
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="flex flex-col md:flex-row bg-white rounded-lg shadow-xl w-full max-w-4xl mx-auto overflow-hidden transform transition duration-500 hover:scale-[1.01]">
        <!-- Login Form Section (Left Column on larger screens) -->
        <div class="md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Login Akun</h2>
            <form action="proses_login.php" method="POST" class="space-y-4">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <input 
                        type="text" 
                        name="username" 
                        id="username"
                        placeholder="Username" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out"
                    >
                </div>
                <div>
                    <label for="password" class="sr-only">Kata Sandi</label>
                    <input 
                        type="password" 
                        name="password" 
                        id="password"
                        placeholder="Kata Sandi" 
                        required 
                        class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 ease-in-out"
                    >
                </div>
                <button 
                    type="submit" 
                    class="w-full bg-blue-600 text-white font-semibold py-3 rounded-md hover:bg-blue-700 transition duration-300 ease-in-out shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
                >
                    Login
                </button>
            </form>
            <p class="text-center text-gray-600 mt-6">
                Belum punya akun? 
                <a href="registrasi.php" class="text-blue-600 hover:text-blue-800 font-medium transition duration-300 ease-in-out">Daftar di sini</a>
            </p>
        </div>

        <!-- Image Section (Right Column on larger screens) -->
        <div class="md:w-1/2 bg-blue-600 flex items-center justify-center p-6 md:p-12">
            <img 
                src="image/bg.jpg" 
                alt="Gambar Kos Heaven Indekos" 
                class="rounded-lg shadow-lg w-full h-auto object-cover max-h-96 md:max-h-full"
            >
        </div>
    </div>
</body>
</html>
