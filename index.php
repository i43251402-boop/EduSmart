<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>EduSmart | Next-Gen Learning Platform</title>
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
      background: #0a0c15;
      color: #fff;
      overflow-x: hidden;
    }

    /* Animated Gradient Background */
    .bg-gradient {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 20% 30%, #1e2a4a, #0a0c15 80%);
      z-index: -2;
    }

    .bg-gradient::before {
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

    /* Blob moving */
    .blob {
      position: fixed;
      width: 40vw;
      height: 40vw;
      background: radial-gradient(circle, rgba(249,115,22,0.3), rgba(250,204,21,0.1));
      border-radius: 62% 38% 72% 28% / 45% 32% 68% 55%;
      filter: blur(70px);
      z-index: -1;
      animation: blobMove 18s infinite alternate;
    }

    @keyframes blobMove {
      0% { transform: translate(10%, 10%) scale(1); }
      100% { transform: translate(-10%, -10%) scale(1.2); }
    }

    /* Navbar Modern */
    .navbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1.2rem 6%;
      backdrop-filter: blur(12px);
      background: rgba(10, 12, 21, 0.6);
      border-bottom: 1px solid rgba(255,255,255,0.08);
      position: relative;
      z-index: 100;
    }

    .logo {
      font-size: 1.8rem;
      font-weight: 800;
      background: linear-gradient(135deg, #facc15, #f97316);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
      letter-spacing: -0.5px;
    }

    .nav-links {
      display: flex;
      gap: 2rem;
      align-items: center;
    }

    .nav-links a {
      text-decoration: none;
      color: #e2e8f0;
      font-weight: 500;
      transition: 0.2s;
      font-size: 1rem;
    }

    .nav-links a:hover {
      color: #facc15;
    }

    .btn-login-nav {
      background: linear-gradient(95deg, #f97316, #ea580c);
      padding: 0.6rem 1.6rem;
      border-radius: 40px;
      color: white !important;
      box-shadow: 0 4px 12px rgba(249,115,22,0.3);
    }

    /* Hero Section */
    .hero {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 5rem 8%;
      gap: 3rem;
      flex-wrap: wrap;
      position: relative;
    }

    .hero-content {
      flex: 1;
      min-width: 300px;
    }

    .badge {
      display: inline-block;
      background: rgba(249,115,22,0.2);
      backdrop-filter: blur(4px);
      padding: 0.3rem 1rem;
      border-radius: 40px;
      font-size: 0.8rem;
      font-weight: 600;
      color: #facc15;
      margin-bottom: 1.5rem;
      border: 1px solid rgba(250,204,21,0.3);
    }

    .hero-content h1 {
      font-size: 3.8rem;
      font-weight: 800;
      line-height: 1.2;
      margin-bottom: 1.5rem;
      background: linear-gradient(to right, #fff, #facc15);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    .hero-content p {
      font-size: 1.2rem;
      color: #94a3b8;
      margin-bottom: 2rem;
      line-height: 1.5;
    }

    .cta-group {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
    }

    .btn-primary {
      background: linear-gradient(95deg, #f97316, #ea580c);
      padding: 0.9rem 2rem;
      border-radius: 60px;
      text-decoration: none;
      color: white;
      font-weight: 700;
      transition: all 0.3s;
      box-shadow: 0 8px 20px rgba(249,115,22,0.4);
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-primary:hover {
      transform: translateY(-4px);
      box-shadow: 0 15px 30px rgba(249,115,22,0.6);
      gap: 12px;
    }

    .btn-outline {
      background: transparent;
      border: 1px solid rgba(255,255,255,0.3);
      padding: 0.9rem 2rem;
      border-radius: 60px;
      text-decoration: none;
      color: white;
      font-weight: 600;
      transition: 0.2s;
    }

    .btn-outline:hover {
      background: rgba(255,255,255,0.1);
      border-color: #facc15;
    }

    /* Ilustrasi Hero */
    .hero-illustration {
      flex: 1;
      display: flex;
      justify-content: center;
      position: relative;
    }

    .floating-card {
      background: rgba(30, 35, 55, 0.6);
      backdrop-filter: blur(15px);
      border-radius: 48px;
      padding: 1.5rem;
      border: 1px solid rgba(255,215,0,0.3);
      animation: floatCard 4s infinite ease-in-out;
      transform-style: preserve-3d;
    }

    @keyframes floatCard {
      0% { transform: translateY(0px) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(2deg); }
      100% { transform: translateY(0px) rotate(0deg); }
    }

    .floating-card i {
      font-size: 5rem;
      color: #facc15;
      margin: 0 1rem;
    }

    .floating-card span {
      font-size: 1.2rem;
      font-weight: 600;
    }

    /* Stats Section */
    .stats {
      display: flex;
      justify-content: space-around;
      flex-wrap: wrap;
      gap: 2rem;
      padding: 3rem 8%;
      background: rgba(0,0,0,0.3);
      backdrop-filter: blur(8px);
      margin: 2rem 0;
    }

    .stat-item {
      text-align: center;
    }

    .stat-number {
      font-size: 2.5rem;
      font-weight: 800;
      background: linear-gradient(135deg, #facc15, #f97316);
      -webkit-background-clip: text;
      background-clip: text;
      color: transparent;
    }

    /* Features */
    .features {
      padding: 5rem 6%;
      text-align: center;
    }

    .section-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
    }

    .section-desc {
      color: #94a3b8;
      max-width: 600px;
      margin: 0 auto 3rem;
    }

    .feature-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 2rem;
    }

    .feature-card {
      background: rgba(20, 25, 45, 0.6);
      backdrop-filter: blur(12px);
      border-radius: 32px;
      padding: 2rem;
      transition: all 0.4s;
      border: 1px solid rgba(255,255,255,0.1);
      position: relative;
      overflow: hidden;
    }

    .feature-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,215,0,0.2), transparent);
      transition: 0.6s;
    }

    .feature-card:hover::before {
      left: 100%;
    }

    .feature-card:hover {
      transform: translateY(-12px);
      border-color: #facc15;
      background: rgba(30, 35, 65, 0.8);
    }

    .feature-icon {
      font-size: 3rem;
      color: #facc15;
      margin-bottom: 1.5rem;
    }

    .feature-card h3 {
      font-size: 1.5rem;
      margin-bottom: 0.8rem;
    }

    .feature-card p {
      color: #cbd5e1;
    }

    /* CTA Banner */
    .cta-banner {
      margin: 3rem 6%;
      background: linear-gradient(135deg, rgba(249,115,22,0.2), rgba(250,204,21,0.1));
      border-radius: 48px;
      padding: 3rem;
      text-align: center;
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255,215,0,0.3);
    }

    .cta-banner h2 {
      font-size: 2rem;
      margin-bottom: 1rem;
    }

    /* Footer */
    footer {
      text-align: center;
      padding: 2rem;
      border-top: 1px solid rgba(255,255,255,0.05);
      color: #64748b;
    }

    @media (max-width: 768px) {
      .hero-content h1 { font-size: 2.5rem; }
      .navbar { flex-direction: column; gap: 1rem; }
      .floating-card i { font-size: 3rem; }
    }
  </style>
</head>
<body>

<div class="bg-gradient"></div>
<div class="blob" style="top: 10%; left: -10%;"></div>
<div class="blob" style="bottom: 0; right: -5%; width: 50vw; height: 50vw; animation-delay: -5s;"></div>

<nav class="navbar">
  <div class="logo">EduSmart</div>
  <div class="nav-links">
    <a href="#">Beranda</a>
    <a href="#fitur">Fitur</a>
    <a href="#tentang">Tentang</a>
    <a href="login.php" class="btn-login-nav">Login</a>
  </div>
</nav>

<section class="hero">
  <div class="hero-content">
    <span class="badge"><i class="fas fa-rocket"></i> Platform Masa Depan</span>
    <h1>Belajar Tanpa Batas,<br> Raih <span style="color:#facc15;">Mimpi</span> Digital</h1>
    <p>EduSmart menghubungkan dosen dan mahasiswa dalam ekosistem belajar interaktif. Kelola nilai, tugas, dan progres dengan pengalaman modern.</p>
    <div class="cta-group">
      <a href="login.php" class="btn-primary"><i class="fas fa-arrow-right"></i> Mulai Sekarang</a>
      <a href="#fitur" class="btn-outline"><i class="fas fa-play"></i> Lihat Demo</a>
    </div>
  </div>
  <div class="hero-illustration">
    <div class="floating-card">
      <i class="fas fa-graduation-cap"></i>
      <i class="fas fa-laptop-code"></i>
      <i class="fas fa-chart-line"></i>
      <div><span>+10.000</span> Students</div>
    </div>
  </div>
</section>

<div class="stats">
  <div class="stat-item"><div class="stat-number">150+</div><div>Mata Kuliah</div></div>
  <div class="stat-item"><div class="stat-number">50+</div><div>Dosen Ahli</div></div>
  <div class="stat-item"><div class="stat-number">98%</div><div>Kepuasan</div></div>
</div>

<section id="fitur" class="features">
  <h2 class="section-title">Fitur Unggulan <i class="fas fa-crown" style="color:#facc15;"></i></h2>
  <p class="section-desc">Didesain untuk memberikan pengalaman belajar terbaik bagi dosen dan mahasiswa.</p>
  <div class="feature-grid">
    <div class="feature-card"><i class="fas fa-chalkboard-user feature-icon"></i><h3>Manajemen Kelas</h3><p>Buat kelas, bagikan materi, dan nilai tugas dengan mudah.</p></div>
    <div class="feature-card"><i class="fas fa-chart-simple feature-icon"></i><h3>Analitik Akademik</h3><p>Pantau IPK, grafik perkembangan, dan rekomendasi.</p></div>
    <div class="feature-card"><i class="fas fa-mobile-alt feature-icon"></i><h3>Akses Mobile</h3><p>Belajar kapan saja, di mana saja dengan antarmuka responsif.</p></div>
    <div class="feature-card"><i class="fas fa-shield-alt feature-icon"></i><h3>Keamanan Data</h3><p>Enkripsi end-to-end dan privasi terjamin.</p></div>
  </div>
</section>

<div class="cta-banner">
  <h2>Siap meningkatkan pengalaman belajarmu?</h2>
  <p>Bergabunglah dengan ribuan pengguna aktif di EduSmart.</p>
  <a href="login.php" class="btn-primary" style="margin-top: 1.5rem; display: inline-block;">Daftar / Login →</a>
</div>

<footer>
  © 2025 EduSmart – Revolutionizing Education
</footer>

<script>
  // Simple scroll reveal
  const cards = document.querySelectorAll('.feature-card');
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.style.opacity = '1';
        entry.target.style.transform = 'translateY(0)';
      }
    });
  }, { threshold: 0.1 });
  cards.forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(30px)';
    card.style.transition = 'all 0.6s ease';
    observer.observe(card);
  });
</script>
</body>
</html>