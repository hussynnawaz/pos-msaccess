<?php
require_once __DIR__ . '/../bootstrap.php';
$session = new SessionManager();
$session->start();
if ($session->isLoggedIn()) {
    header('Location: /admin');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malik Tuc Shop - Point of Sale</title>
    <meta name="description" content="Malik Tuc Shop Point of Sale System — Manage sales, inventory, and reports for your fuel, snacks, and convenience store.">
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
            overflow: hidden;
        }

        /* ── Deep purple/slate animated gradient — avoids teal that clashes with logo ── */
        .hero-bg {
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
            background: rgba(168, 85, 247, 0.08);
            animation: float linear infinite;
        }

        .particle:nth-child(1) { width: 300px; height: 300px; top: -80px; left: -60px;  animation-duration: 20s; background: rgba(168, 85, 247, 0.07); }
        .particle:nth-child(2) { width: 200px; height: 200px; top: 60%;  right: -40px; animation-duration: 25s; background: rgba(139, 92, 246, 0.06); }
        .particle:nth-child(3) { width: 150px; height: 150px; bottom: -30px; left: 40%; animation-duration: 18s; background: rgba(192, 132, 252, 0.05); }
        .particle:nth-child(4) { width: 100px; height: 100px; top: 30%;  left: 20%;   animation-duration: 22s; background: rgba(217, 70, 239, 0.04); }
        .particle:nth-child(5) { width: 250px; height: 250px; top: 10%;  right: 15%;  animation-duration: 28s; background: rgba(99, 102, 241, 0.05); }

        @keyframes float {
            0%   { transform: translateY(0) rotate(0deg) scale(1);   opacity: 0.6; }
            33%  { transform: translateY(-30px) rotate(120deg) scale(1.05); opacity: 0.8; }
            66%  { transform: translateY(15px) rotate(240deg) scale(0.95); opacity: 0.6; }
            100% { transform: translateY(0) rotate(360deg) scale(1);   opacity: 0.6; }
        }

        /* ── Main content container ── */
        .content-wrapper {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 2rem;
        }

        /* ── Glass card ── */
        .glass-card {
            background: rgba(255, 255, 255, 0.06);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 2rem;
            padding: 3rem 2.5rem;
            max-width: 520px;
            width: 100%;
            text-align: center;
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

        /* ── Logo image — white bg pill so logo is always readable ── */
        .logo-container {
            margin-bottom: 1.5rem;
            animation: logoEntry 1s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
        }

        .logo-container img {
            width: 280px;
            max-width: 100%;
            height: auto;
            background: #ffffff;
            border-radius: 1.25rem;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }

        .logo-container img:hover {
            transform: scale(1.04);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.35);
        }

        @keyframes logoEntry {
            from { opacity: 0; transform: scale(0.8); }
            to   { opacity: 1; transform: scale(1); }
        }

        /* ── Tagline ── */
        .tagline {
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: rgba(192, 132, 252, 0.85);
            margin-bottom: 2rem;
            animation: fadeUp 0.8s ease 0.4s both;
        }

        /* ── Divider ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            animation: fadeUp 0.8s ease 0.5s both;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(168, 85, 247, 0.3), transparent);
        }

        .divider span {
            font-size: 0.7rem;
            letter-spacing: 0.15em;
            color: rgba(255, 255, 255, 0.35);
            text-transform: uppercase;
            white-space: nowrap;
        }

        /* ── Info section ── */
        .info-section {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            animation: fadeUp 0.8s ease 0.6s both;
        }

        .info-section h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: #e2e8f0;
            margin: 0 0 0.5rem 0;
        }

        .info-section p {
            font-size: 0.875rem;
            color: rgba(203, 213, 225, 0.8);
            margin: 0;
            line-height: 1.6;
        }

        /* ── Feature pills ── */
        .features {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
            animation: fadeUp 0.8s ease 0.7s both;
        }

        .feature-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(168, 85, 247, 0.1);
            border: 1px solid rgba(168, 85, 247, 0.2);
            border-radius: 999px;
            padding: 0.4rem 0.9rem;
            font-size: 0.75rem;
            color: rgba(192, 132, 252, 0.9);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .feature-pill:hover {
            background: rgba(168, 85, 247, 0.2);
            border-color: rgba(168, 85, 247, 0.4);
            transform: translateY(-2px);
        }

        .feature-pill svg {
            width: 14px;
            height: 14px;
            flex-shrink: 0;
        }

        /* ── CTA Button — warm amber/orange to complement the logo ── */
        .cta-button {
            display: block;
            width: 100%;
            padding: 1rem 1.5rem;
            background: linear-gradient(135deg, #f59e0b, #d97706, #b45309);
            background-size: 200% 200%;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            border: none;
            border-radius: 0.875rem;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow:
                0 4px 16px rgba(245, 158, 11, 0.3),
                0 1px 3px rgba(0, 0, 0, 0.2);
            animation: fadeUp 0.8s ease 0.8s both;
            position: relative;
            overflow: hidden;
        }

        .cta-button::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 0%, rgba(255,255,255,0.15) 50%, transparent 100%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }

        .cta-button:hover {
            background-position: 100% 0;
            transform: translateY(-2px);
            box-shadow:
                0 8px 28px rgba(245, 158, 11, 0.45),
                0 2px 6px rgba(0, 0, 0, 0.25);
        }

        .cta-button:hover::before {
            transform: translateX(100%);
        }

        .cta-button:active {
            transform: translateY(0);
        }

        /* ── Footer ── */
        .footer {
            margin-top: 2rem;
            font-size: 0.7rem;
            color: rgba(148, 163, 184, 0.4);
            letter-spacing: 0.05em;
            animation: fadeUp 0.8s ease 0.9s both;
        }

        /* ── Utility animation ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ── Responsive ── */
        @media (max-width: 480px) {
            .glass-card {
                padding: 2rem 1.5rem;
                border-radius: 1.5rem;
            }
            .logo-container img {
                width: 220px;
            }
        }
    </style>
</head>
<body>

    <!-- Animated background -->
    <div class="hero-bg"></div>

    <!-- Floating particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Main content -->
    <div class="content-wrapper">
        <div class="glass-card">

            <!-- Large shop logo on white background for visibility -->
            <div class="logo-container">
                <img src="/public/assets/images/malik-tuc-shop.png" alt="Malik Tuc Shop — Fuel, Snacks, Convenience">
            </div>

            <!-- Tagline -->
            <p class="tagline">Fuel &bull; Snacks &bull; Convenience</p>

            <!-- Divider -->
            <div class="divider">
                <span>Point of Sale System</span>
            </div>

            <!-- Info card -->
            <div class="info-section">
                <h2>Welcome Back</h2>
                <p>Your complete business management dashboard — sales, inventory, suppliers, and reports all in one place.</p>
            </div>

            <!-- Feature pills -->
            <div class="features">
                <span class="feature-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    Sales
                </span>
                <span class="feature-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                    Inventory
                </span>
                <span class="feature-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Reports
                </span>
                <span class="feature-pill">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                    Suppliers
                </span>
            </div>

            <!-- CTA -->
            <a href="/login" class="cta-button" id="admin-login-btn">
                Admin Login &rarr;
            </a>

            <!-- Footer -->
            <p class="footer">&copy; 2026 Malik Tuc Shop. All rights reserved.</p>

        </div>
    </div>

</body>
</html>
