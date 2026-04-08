<?php
session_start();
require_once 'classes/User.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $roleForm = $_POST['role'] ?? '';

    if (User::login($username, $password)) {
        // Login berhasil, session sudah diset di User::login
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Username atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | EduSmart - Platform Pendidikan Digital</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background: #0a0c15;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
        }

        /* Background animasi (sama dengan home) */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at 20% 30%, #1e2a4a, #0a0c15 80%);
            z-index: -2;
        }

        .animated-bg::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: conic-gradient(from 0deg, #f97316, #facc15, #f97316, #facc15);
            animation: rotateBg 20s linear infinite;
            opacity: 0.15;
            z-index: -1;
        }

        @keyframes rotateBg {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Blob bergerak */
        .blob {
            position: fixed;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(249,115,22,0.25), rgba(250,204,21,0.08));
            border-radius: 62% 38% 72% 28% / 45% 32% 68% 55%;
            filter: blur(80px);
            z-index: -1;
            animation: blobMove 20s infinite alternate;
        }

        @keyframes blobMove {
            0% { transform: translate(10%, 10%) scale(1); }
            100% { transform: translate(-10%, -10%) scale(1.2); }
        }

        /* Elemen pendidikan animasi (buku, gelar, laptop) */
        .edu-float {
            position: fixed;
            font-size: 2rem;
            opacity: 0.2;
            z-index: -1;
            pointer-events: none;
            animation: floatEdu 15s infinite ease-in-out;
        }

        @keyframes floatEdu {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(5deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }

        /* Login card */
        .login-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            padding: 1rem;
            position: relative;
            z-index: 2;
        }

        .login-card {
            background: rgba(15, 20, 35, 0.65);
            backdrop-filter: blur(20px);
            border-radius: 56px;
            padding: 2.5rem 2rem;
            width: 100%;
            max-width: 460px;
            border: 1px solid rgba(255,215,0,0.3);
            box-shadow: 0 30px 50px rgba(0,0,0,0.4);
            animation: slideFade 0.7s cubic-bezier(0.2, 0.9, 0.4, 1.2);
            transition: all 0.4s ease;
        }

        .login-card:hover {
            transform: translateY(-5px);
            border-color: #fbbf24;
            box-shadow: 0 40px 60px rgba(0,0,0,0.5);
        }

        @keyframes slideFade {
            0% { opacity: 0; transform: translateY(40px) scale(0.96);}
            100% { opacity: 1; transform: translateY(0) scale(1);}
        }

        .login-title {
            font-size: 2.2rem;
            text-align: center;
            background: linear-gradient(135deg, #fff, #facc15);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 0.5rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .login-title i {
            background: none;
            -webkit-background-clip: unset;
            background-clip: unset;
            color: #facc15;
            font-size: 2rem;
        }

        .login-sub {
            text-align: center;
            color: #cbd5e6;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .input-group {
            margin-bottom: 1.2rem;
        }

        .input-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #f1f5f9;
            font-size: 0.9rem;
        }

        .input-group label i {
            margin-right: 8px;
            color: #facc15;
        }

        .input-group input {
            width: 100%;
            padding: 0.9rem 1.2rem;
            background: rgba(255,255,255,0.08);
            border: 1.5px solid rgba(255,255,255,0.2);
            border-radius: 60px;
            font-size: 1rem;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: white;
            transition: 0.25s;
        }

        .input-group input:focus {
            outline: none;
            border-color: #facc15;
            background: rgba(255,255,255,0.15);
            box-shadow: 0 0 0 3px rgba(250,204,21,0.2);
        }

        .forgot-password {
            text-align: right;
            margin: -0.5rem 0 1.2rem 0;
        }

        .forgot-password a {
            color: #facc15;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: 0.2s;
        }

        .forgot-password a:hover {
            text-decoration: underline;
            color: #f97316;
        }

        .role-group {
            margin: 1.2rem 0 1.5rem;
        }

        .role-label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.8rem;
            color: #f1f5f9;
            font-size: 0.9rem;
        }

        .role-options {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
        }

        .role-option {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.08);
            padding: 0.6rem 1.2rem;
            border-radius: 60px;
            cursor: pointer;
            transition: 0.2s;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .role-option:hover {
            background: rgba(250,204,21,0.2);
            border-color: #facc15;
        }

        .role-option input {
            accent-color: #f97316;
            width: 18px;
            height: 18px;
            margin: 0;
            cursor: pointer;
        }

        .role-option span {
            color: white;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(95deg, #f97316, #ea580c);
            border: none;
            border-radius: 60px;
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 6px 14px rgba(249,115,22,0.3);
        }

        .btn-login:hover {
            transform: translateY(-3px) scale(1.02);
            background: linear-gradient(95deg, #facc15, #f97316);
            box-shadow: 0 12px 28px rgba(250,204,21,0.5);
        }

        .login-footer {
            text-align: center;
            margin-top: 1.8rem;
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .login-footer a {
            color: #facc15;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .error-message {
            background: rgba(220,38,38,0.2);
            padding: 0.7rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
            text-align: center;
            color: #fecaca;
            border-left: 4px solid #ef4444;
        }

        @media (max-width: 500px) {
            .login-card {
                padding: 1.8rem 1.2rem;
            }
            .role-options {
                gap: 1rem;
            }
            .role-option {
                padding: 0.4rem 1rem;
            }
            .login-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <!-- Background animasi -->
    <div class="animated-bg"></div>
    <div class="blob" style="top: -20%; left: -10%;"></div>
    <div class="blob" style="bottom: -10%; right: -5%; width: 60vw; height: 60vw;"></div>

    <!-- Elemen pendidikan animasi (dekoratif) -->
    <div class="edu-float" style="top: 15%; left: 5%; animation-duration: 12s;"><i class="fas fa-book-open"></i></div>
    <div class="edu-float" style="bottom: 20%; right: 8%; animation-duration: 14s; animation-delay: 2s;"><i class="fas fa-graduation-cap"></i></div>
    <div class="edu-float" style="top: 40%; right: 15%; animation-duration: 18s; animation-delay: 1s;"><i class="fas fa-laptop-code"></i></div>
    <div class="edu-float" style="bottom: 35%; left: 10%; animation-duration: 16s; animation-delay: 3s;"><i class="fas fa-chalkboard-teacher"></i></div>

    <div class="login-wrapper">
        <div class="login-card">
            <h1 class="login-title">
                <i class="fas fa-graduation-cap"></i> 
                Masuk ke EduSmart
            </h1>
            <p class="login-sub">Silakan login dengan akun Anda</p>

            <?php if ($error): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="input-group">
                    <label><i class="fas fa-user"></i> Username / NIM / NIDN</label>
                    <input type="text" name="username" placeholder="Masukkan username atau NIM" required>
                </div>

                <div class="input-group">
                    <label><i class="fas fa-lock"></i> Password</label>
                    <input type="password" name="password" placeholder="********" required>
                </div>

                <div class="forgot-password">
                    <a href="#"><i class="fas fa-question-circle"></i> Lupa Password?</a>
                </div>

                <div class="role-group">
                    <label class="role-label">Pilih Peran:</label>
                    <div class="role-options">
                        <label class="role-option">
                            <input type="radio" name="role" value="mahasiswa" checked> 
                            <span><i class="fas fa-user-graduate"></i> Mahasiswa</span>
                        </label>
                        <label class="role-option">
                            <input type="radio" name="role" value="dosen"> 
                            <span><i class="fas fa-chalkboard-user"></i> Dosen</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-arrow-right"></i> Login
                </button>
            </form>

            <div class="login-footer">
                Belum punya akun? <a href="#">Hubungi Administrator</a>
            </div>
        </div>
    </div>
</body>
</html>