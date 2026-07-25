<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Not Found</title>
    <link rel="stylesheet" href="/public/assets/css/tailwind.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@700;900&display=swap');
        .font-404 { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-[#e9eef5] flex items-center justify-center overflow-hidden">
    <div class="text-center">
        <div class="relative w-[420px] h-[340px] mx-auto">
            <!-- Clouds -->
            <svg class="absolute top-4 left-6 w-20 h-12 opacity-30" viewBox="0 0 80 40" fill="none">
                <path d="M20 36C10 36 2 28 2 20C2 12 10 4 20 4C22 1 28 0 34 0C44 0 52 6 54 10C56 7 62 5 68 5C78 5 80 14 76 20C80 25 78 36 68 36H20Z" fill="#c5d3e0"/>
            </svg>
            <svg class="absolute top-2 right-16 w-14 h-9 opacity-25" viewBox="0 0 80 40" fill="none">
                <path d="M20 36C10 36 2 28 2 20C2 12 10 4 20 4C22 1 28 0 34 0C44 0 52 6 54 10C56 7 62 5 68 5C78 5 80 14 76 20C80 25 78 36 68 36H20Z" fill="#c5d3e0"/>
            </svg>
            <svg class="absolute top-10 right-6 w-10 h-7 opacity-20" viewBox="0 0 80 40" fill="none">
                <path d="M20 36C10 36 2 28 2 20C2 12 10 4 20 4C22 1 28 0 34 0C44 0 52 6 54 10C56 7 62 5 68 5C78 5 80 14 76 20C80 25 78 36 68 36H20Z" fill="#c5d3e0"/>
            </svg>

            <!-- Sparkles -->
            <svg class="absolute top-14 left-32 w-3 h-3 text-white opacity-70" viewBox="0 0 12 12" fill="currentColor">
                <path d="M6 0L7 5L12 6L7 7L6 12L5 7L0 6L5 5Z"/>
            </svg>
            <svg class="absolute top-6 right-28 w-2.5 h-2.5 text-white opacity-50" viewBox="0 0 12 12" fill="currentColor">
                <path d="M6 0L7 5L12 6L7 7L6 12L5 7L0 6L5 5Z"/>
            </svg>
            <svg class="absolute top-20 left-20 w-2 h-2 text-white opacity-40" viewBox="0 0 12 12" fill="currentColor">
                <path d="M6 0L7 5L12 6L7 7L6 12L5 7L0 6L5 5Z"/>
            </svg>

            <!-- Character (paper document) standing on 404 -->
            <svg class="absolute bottom-[100px] left-1/2 -translate-x-1/2 w-28 h-32 z-10" viewBox="0 0 112 128" fill="none">
                <!-- Left leg -->
                <line x1="38" y1="82" x2="32" y2="108" stroke="#b0c4d8" stroke-width="4" stroke-linecap="round"/>
                <ellipse cx="30" cy="112" rx="8" ry="5" fill="#f5f7fa" stroke="#b0c4d8" stroke-width="2"/>
                <!-- Right leg -->
                <line x1="62" y1="82" x2="68" y2="108" stroke="#b0c4d8" stroke-width="4" stroke-linecap="round"/>
                <ellipse cx="70" cy="112" rx="8" ry="5" fill="#f5f7fa" stroke="#b0c4d8" stroke-width="2"/>
                <!-- Body -->
                <rect x="18" y="10" width="64" height="76" rx="4" fill="#f5f7fa" stroke="#b0c4d8" stroke-width="2.5"/>
                <!-- Folded corner -->
                <path d="M66 10L82 26H70C67.8 26 66 24.2 66 22V10Z" fill="#e2e8f0"/>
                <path d="M66 10V22C66 24.2 67.8 26 70 26H82" stroke="#b0c4d8" stroke-width="2.5" fill="none"/>
                <!-- Face -->
                <circle cx="42" cy="48" r="3.5" fill="#64748b"/>
                <circle cx="60" cy="48" r="3.5" fill="#64748b"/>
                <path d="M42 60C42 60 46 66 60 60" stroke="#64748b" stroke-width="3" stroke-linecap="round" fill="none"/>
                <!-- Left arm raised -->
                <line x1="18" y1="50" x2="0" y2="32" stroke="#b0c4d8" stroke-width="4" stroke-linecap="round"/>
                <circle cx="-2" cy="30" r="5" fill="#f5f7fa" stroke="#b0c4d8" stroke-width="2.5"/>
                <!-- Right arm raised -->
                <line x1="82" y1="50" x2="100" y2="36" stroke="#b0c4d8" stroke-width="4" stroke-linecap="round"/>
                <circle cx="102" cy="34" r="5" fill="#f5f7fa" stroke="#b0c4d8" stroke-width="2.5"/>
            </svg>

            <!-- 404 Large Text -->
            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex items-end">
                <span class="font-404 font-900 text-[140px] leading-none tracking-tight text-[#b0c4d8] select-none">404</span>
            </div>

            <!-- Ground Lines -->
            <svg class="absolute bottom-0 left-0 w-full h-8" viewBox="0 0 420 32" fill="none" preserveAspectRatio="none">
                <line x1="0" y1="8" x2="420" y2="8" stroke="#b0c4d8" stroke-width="1.5" stroke-dasharray="8 5"/>
                <line x1="0" y1="20" x2="420" y2="20" stroke="#b0c4d8" stroke-width="1" stroke-dasharray="6 4" opacity="0.5"/>
            </svg>
        </div>

        <!-- Message -->
        <p class="text-[#8497ab] text-sm mt-2 mb-6">Oops! Sorry, we could not find the page</p>
        <a href="/" class="inline-block bg-blue-500 hover:bg-blue-600 text-white font-medium py-2.5 px-8 rounded-lg transition-colors">Go Home</a>
    </div>
</body>
</html>
