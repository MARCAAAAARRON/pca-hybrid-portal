<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approval | PCA Hybridization Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Sora', sans-serif;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image: url("/images/coconut_farm.png");
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center bottom;
            opacity: 0.45;
            z-index: -10;
            pointer-events: none;
        }
        .circle-top-right {
            position: absolute;
            top: -350px;
            right: -350px;
            width: 800px;
            height: 800px;
            border-radius: 50%;
            border: 120px solid #0b9e4f;
            background-color: #d4e122;
            opacity: 0.9;
            z-index: -5;
        }
        .circle-top-right::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300px;
            height: 300px;
            background-color: white;
            border-radius: 50%;
            transform: translate(-50%, -50%);
        }
        .circle-bottom-left {
            position: absolute;
            bottom: -300px;
            left: -300px;
            width: 700px;
            height: 700px;
            border-radius: 50%;
            background-color: #0b9e4f;
            opacity: 0.9;
            z-index: -5;
        }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen relative overflow-hidden">
    <div class="circle-top-right"></div>
    <div class="circle-bottom-left"></div>

    <div class="bg-white p-10 rounded-2xl shadow-2xl max-w-lg w-full text-center border border-gray-100 relative z-10 mx-4">
        <img src="/images/PCA_Logo.png" alt="PCA Logo" class="w-28 h-28 mx-auto mb-6">
        
        <h1 class="text-3xl font-extrabold text-[#0b9e4f] mb-4">Account Pending Approval</h1>
        
        <p class="text-gray-600 mb-8 leading-relaxed">
            Thank you for registering! Your account has been created successfully but is currently waiting for Superadmin approval. 
            Once approved, you will be able to access the dashboard.
        </p>

        <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
            @csrf
            <button type="submit" class="w-full bg-[#0b9e4f] hover:bg-[#098a44] text-white font-bold py-3 px-4 rounded-xl transition duration-200 shadow-lg shadow-green-200">
                Back to Login
            </button>
        </form>
    </div>
</body>
</html>
