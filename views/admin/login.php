<?php
require_once __DIR__ . '/../../bootstrap.php';

$session = new SessionManager();
$session->start();
if ($session->isLoggedIn()) {
    header('Location: /admin');
    exit;
}
$csrf = new CsrfProtection();
$token = $csrf->generateToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — Malik Tuc Shop</title>
    <meta name="description" content="Sign in to the Malik Tuc Shop admin dashboard to manage your sales, inventory, and reports.">
    <link rel="stylesheet" href="/public/assets/css/tailwind.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── Deep purple/slate background — matches home page ── */
        .login-bg {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #1a1025 0%, #1e1b3a 25%, #2d1f4e 50%, #1a1035 75%, #0f0d1a 100%);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
            z-index: 0;
        }

        @keyframes gradientShift {
            0%   { background-position: 0% 50%; }
            50%  { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* ── Floating ambient orbs ── */
        .particles {
            position: fixed;
            inset: 0;
            z-index: 1;
            pointer-events: none;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            border-radius: 50%;
            animation: float linear infinite;
        }

        .particle:nth-child(1) { width: 280px; height: 280px; top: -70px;  left: -50px;  animation-duration: 20s; background: rgba(168, 85, 247, 0.07); }
        .particle:nth-child(2) { width: 180px; height: 180px; top: 55%;   right: -30px; animation-duration: 25s; background: rgba(139, 92, 246, 0.06); }
        .particle:nth-child(3) { width: 140px; height: 140px; bottom: -20px; left: 35%; animation-duration: 18s; background: rgba(192, 132, 252, 0.05); }
        .particle:nth-child(4) { width: 220px; height: 220px; top: 15%;   right: 20%;  animation-duration: 28s; background: rgba(99, 102, 241, 0.05); }

        @keyframes float {
            0%   { transform: translateY(0) rotate(0deg) scale(1);   opacity: 0.6; }
            33%  { transform: translateY(-30px) rotate(120deg) scale(1.05); opacity: 0.8; }
            66%  { transform: translateY(15px) rotate(240deg) scale(0.95); opacity: 0.6; }
            100% { transform: translateY(0) rotate(360deg) scale(1);   opacity: 0.6; }
        }

        /* ── Main wrapper ── */
        .login-wrapper {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        /* ── Glass card ── */
        .login-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            padding: 2.75rem 2.5rem 2.5rem;
            max-width: 440px;
            width: 100%;
            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.5),
                0 0 80px rgba(168, 85, 247, 0.06),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            animation: cardEntry 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
        }

        @keyframes cardEntry {
            from { opacity: 0; transform: translateY(40px) scale(0.96); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* ── Logo ── */
        .logo-area {
            text-align: center;
            margin-bottom: 1.75rem;
            animation: logoEntry 1s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
        }

        .logo-area img {
            width: 180px;
            max-width: 100%;
            height: auto;
            background: #ffffff;
            border-radius: 1rem;
            padding: 0.85rem 1rem;
            box-shadow: 0 6px 24px rgba(0, 0, 0, 0.25);
            transition: transform 0.35s ease;
        }

        .logo-area img:hover { transform: scale(1.04); }

        @keyframes logoEntry {
            from { opacity: 0; transform: scale(0.85); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* ── Header text ── */
        .login-header {
            text-align: center;
            margin-bottom: 1.75rem;
            animation: fadeUp 0.8s ease 0.3s both;
        }

        .login-header h1 {
            font-size: 1.5rem;
            font-weight: 800;
            color: #f1f5f9;
            margin: 0 0 0.35rem 0;
        }

        .login-header p {
            font-size: 0.875rem;
            color: rgba(203, 213, 225, 0.65);
            margin: 0;
        }

        /* ── Error message ── */
        .error-msg {
            display: none;
            margin-bottom: 1.25rem;
            padding: 0.75rem 1rem;
            background: rgba(239, 68, 68, 0.12);
            border: 1px solid rgba(239, 68, 68, 0.25);
            border-radius: 0.75rem;
            color: #fca5a5;
            font-size: 0.8125rem;
            font-weight: 500;
            animation: shake 0.4s ease;
        }

        .error-msg.visible { display: block; }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%  { transform: translateX(-6px); }
            40%  { transform: translateX(6px); }
            60%  { transform: translateX(-4px); }
            80%  { transform: translateX(4px); }
        }

        /* ── Form ── */
        .login-form {
            animation: fadeUp 0.8s ease 0.4s both;
        }

        .field-group {
            margin-bottom: 1.25rem;
        }

        .field-group label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            color: rgba(203, 213, 225, 0.8);
            margin-bottom: 0.5rem;
            letter-spacing: 0.02em;
        }

        .field-input {
            width: 100%;
            padding: 0.8rem 1rem;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 0.75rem;
            color: #f1f5f9;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: all 0.25s ease;
            outline: none;
        }

        .field-input::placeholder {
            color: rgba(148, 163, 184, 0.5);
        }

        .field-input:focus {
            border-color: rgba(168, 85, 247, 0.5);
            box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.12);
            background: rgba(255, 255, 255, 0.08);
        }

        /* ── Remember / Forgot row ── */
        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            animation: fadeUp 0.8s ease 0.5s both;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
            font-size: 0.8125rem;
            color: rgba(203, 213, 225, 0.7);
        }

        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #a855f7;
            cursor: pointer;
        }

        .forgot-link {
            font-size: 0.8125rem;
            color: rgba(192, 132, 252, 0.85);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            color: #c084fc;
        }

        /* ── Submit button — warm amber CTA ── */
        .submit-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 0.9rem 1.5rem;
            background: linear-gradient(135deg, #f59e0b, #d97706, #b45309);
            background-size: 200% 200%;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            border: none;
            border-radius: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow:
                0 4px 16px rgba(245, 158, 11, 0.3),
                0 1px 3px rgba(0, 0, 0, 0.2);
            animation: fadeUp 0.8s ease 0.55s both;
            position: relative;
            overflow: hidden;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.15) 50%, transparent 100%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .submit-btn:hover {
            background-position: 100% 0;
            transform: translateY(-2px);
            box-shadow:
                0 8px 28px rgba(245, 158, 11, 0.45),
                0 2px 6px rgba(0, 0, 0, 0.25);
        }

        .submit-btn:hover::before {
            transform: translateX(100%);
        }

        .submit-btn:active { transform: translateY(0); }

        .submit-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .submit-btn .spinner {
            display: none;
            width: 20px;
            height: 20px;
            margin-left: 0.5rem;
            border: 2.5px solid rgba(255,255,255,0.3);
            border-top-color: #fff;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* ── Divider ── */
        .back-link-area {
            text-align: center;
            margin-top: 1.75rem;
            animation: fadeUp 0.8s ease 0.65s both;
        }

        .back-link-area a {
            font-size: 0.8125rem;
            color: rgba(203, 213, 225, 0.5);
            text-decoration: none;
            transition: color 0.2s;
        }

        .back-link-area a:hover {
            color: rgba(192, 132, 252, 0.9);
        }

        /* ── Footer ── */
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.7rem;
            color: rgba(148, 163, 184, 0.35);
            letter-spacing: 0.05em;
            animation: fadeUp 0.8s ease 0.7s both;
        }

        /* ── Utility ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 480px) {
            .login-card {
                padding: 2rem 1.5rem;
                border-radius: 1.5rem;
            }
            .logo-area img { width: 140px; }
        }
    </style>
</head>
<body>

    <!-- Background -->
    <div class="login-bg"></div>

    <!-- Ambient particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Content -->
    <div class="login-wrapper">
        <div class="login-card">

            <!-- Logo -->
            <div class="logo-area">
                <img src="/public/assets/images/malik-tuc-shop.png" alt="Malik Tuc Shop">
            </div>

            <!-- Header -->
            <div class="login-header">
                <h1>Sign in to Dashboard</h1>
                <p>Welcome back! Enter your credentials below.</p>
            </div>

            <!-- Error (hidden by default) -->
            <div id="error-message" class="error-msg"></div>

            <!-- Login form -->
            <form id="loginForm" class="login-form" autocomplete="on">
                <input type="hidden" name="csrf_token" id="csrf_token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="field-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="field-input"
                        placeholder="Enter your username"
                        required
                        autocomplete="username"
                    >
                </div>

                <div class="field-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="field-input"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <div class="options-row">
                    <label class="remember-label">
                        <input type="checkbox" name="remember">
                        Remember for 30 days
                    </label>
                    <a href="#" class="forgot-link">Forgot password</a>
                </div>

                <button type="submit" id="submitBtn" class="submit-btn">
                    <span id="btnText">Sign in</span>
                    <div id="spinner" class="spinner"></div>
                </button>
            </form>

            <!-- Back link -->
            <div class="back-link-area">
                <a href="/">&larr; Back to Home</a>
            </div>

            <!-- Footer -->
            <p class="login-footer">&copy; 2026 Malik Tuc Shop. All rights reserved.</p>

        </div>
    </div>

    <script>
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        const csrfToken = document.getElementById('csrf_token').value;
        const errorDiv = document.getElementById('error-message');
        const btnText = document.getElementById('btnText');
        const spinner = document.getElementById('spinner');
        const submitBtn = document.getElementById('submitBtn');

        errorDiv.classList.remove('visible');

        btnText.textContent = 'Signing in…';
        spinner.style.display = 'block';
        submitBtn.disabled = true;

        try {
            const response = await fetch('/api/login.php', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, password, csrf_token: csrfToken })
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = data.redirect;
            } else {
                errorDiv.textContent = data.message;
                errorDiv.classList.add('visible');
            }
        } catch (error) {
            errorDiv.textContent = 'Network error. Please try again.';
            errorDiv.classList.add('visible');
        } finally {
            btnText.textContent = 'Sign in';
            spinner.style.display = 'none';
            submitBtn.disabled = false;
        }
    });
    </script>
</body>
</html>
