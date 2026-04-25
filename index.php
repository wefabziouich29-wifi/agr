<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Uber-Cueillette</title>
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #0f0f0f; color: #fff; }

        /* ---- HERO ---- */
        .hero {
            position: relative;
            height: 100vh;
            min-height: 600px;
            background: url('images/hero.jpg') center center / cover no-repeat;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to bottom,
                rgba(0,0,0,0.25) 0%,
                rgba(0,0,0,0.45) 60%,
                rgba(0,0,0,0.82) 100%
            );
        }

        /* ---- NAV dans le hero ---- */
        nav {
            position: absolute;
            top: 0; left: 0; right: 0;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 28px 60px;
        }

        .logo {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            color: #fff;
            text-decoration: none;
            letter-spacing: 1px;
        }

        .logo span { color: #7ec850; }

        .nav-menu {
            display: flex;
            gap: 10px;
            list-style: none;
        }

        .nav-menu a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 20px;
            border-radius: 30px;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.3s ease;
            letter-spacing: 0.4px;
        }

        .nav-menu a:hover {
            background: rgba(126,200,80,0.15);
            border-color: #7ec850;
            color: #7ec850;
        }

        .nav-menu .nav-cta {
            background: #4a7c3f;
            border-color: #4a7c3f;
            color: #fff;
        }

        .nav-menu .nav-cta:hover {
            background: #5d9e50;
            border-color: #5d9e50;
        }

        /* ---- TEXTE dans le hero (en bas) ---- */
        .hero-bottom {
            position: absolute;
            bottom: 60px;
            left: 60px;
            right: 60px;
            z-index: 5;
        }

        .hero-tag {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #7ec850;
            border: 1px solid #7ec850;
            padding: 5px 16px;
            border-radius: 30px;
            margin-bottom: 20px;
        }

        .hero-title {
            font-family: 'Playfair Display', serif;
            font-size: clamp(38px, 6vw, 80px);
            font-weight: 700;
            line-height: 1.1;
            color: #fff;
            max-width: 700px;
        }

        .hero-title span { color: #7ec850; }

        /* ---- SECTION INTRO ---- */
        .intro-section {
            background: #0f0f0f;
            padding: 80px 60px;
            text-align: center;
        }

        .intro-section p {
            font-size: clamp(16px, 2vw, 20px);
            color: rgba(255,255,255,0.65);
            line-height: 1.8;
            max-width: 720px;
            margin: 0 auto 60px;
            font-weight: 300;
        }

        /* ---- BOUTONS ESPACES ---- */
        .espaces {
            display: flex;
            gap: 24px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .espace-btn {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 6px;
            padding: 28px 40px;
            border-radius: 16px;
            text-decoration: none;
            min-width: 240px;
            transition: all 0.35s ease;
            border: 1px solid transparent;
        }

        .espace-btn-agri {
            background: #111;
            border-color: #2a2a2a;
        }

        .espace-btn-agri:hover {
            background: #1a2e16;
            border-color: #4a7c3f;
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(74,124,63,0.25);
        }

        .espace-btn-ouv {
            background: #111;
            border-color: #2a2a2a;
        }

        .espace-btn-ouv:hover {
            background: #1a2e16;
            border-color: #4a7c3f;
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(74,124,63,0.25);
        }

        .espace-label {
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #7ec850;
        }

        .espace-btn-ouv .espace-label { color: #7ec850; }

        .espace-title {
            font-family: 'Playfair Display', serif;
            font-size: 22px;
            font-weight: 700;
            color: #fff;
        }

        .espace-desc {
            font-size: 13px;
            color: rgba(255,255,255,0.45);
            line-height: 1.5;
            text-align: left;
        }

        .espace-arrow {
            margin-top: 16px;
            font-size: 13px;
            font-weight: 600;
            color: #7ec850;
            letter-spacing: 0.5px;
        }

        .espace-btn-ouv .espace-arrow { color: #7ec850; }

        /* ---- FOOTER ---- */
        footer {
            background: #090909;
            border-top: 1px solid rgba(255,255,255,0.05);
            padding: 28px 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        footer p {
            font-size: 13px;
            color: rgba(255,255,255,0.3);
        }

        /* ---- RESPONSIVE ---- */
        @media (max-width: 768px) {
            nav { padding: 20px 24px; }
            .hero-bottom { left: 24px; right: 24px; bottom: 40px; }
            .intro-section { padding: 60px 24px; }
            .espaces { flex-direction: column; align-items: center; }
            .espace-btn { min-width: 100%; max-width: 340px; }
            footer { flex-direction: column; gap: 8px; text-align: center; padding: 24px; }
        }
    </style>
</head>
<body>

<!-- ===== HERO avec NAV dedans ===== -->
<section class="hero">
    <div class="hero-overlay"></div>

    <nav>
        <a href="index.php" class="logo">Uber<span>Cueillette</span></a>
        <ul class="nav-menu">
            <li><a href="index.php">Accueil</a></li>
            <li><a href="auth.php" class="nav-cta">Login / Sign Up</a></li>
        </ul>
    </nav>

    <div class="hero-bottom">
        <div class="hero-tag">Tunisie -- Saison de Recolte 2026</div>
        <h1 class="hero-title">
            La Recolte<br/>
            <span>Connectee</span>
        </h1>
    </div>
</section>

<!-- ===== INTRO + BOUTONS ===== -->
<section class="intro-section">
    <p>
        Uber-Cueillette met en relation les agriculteurs tunisiens
        et les ouvriers de recolte. Publiez vos offres, postulez,
        gerez vos chantiers -- tout en un seul endroit.
    </p>

    <div class="espaces">

        <a href="auth.php?mode=signup&role=agriculteur" class="espace-btn espace-btn-agri">
            <span class="espace-label">Agriculteur</span>
            <span class="espace-title">Vous etes agriculteur ?</span>
            <span class="espace-desc">Publiez vos offres, gerez les candidatures et evaluez vos ouvriers.</span>
            <span class="espace-arrow">Acceder &rarr;</span>
        </a>

        <a href="auth.php?mode=signup&role=ouvrier" class="espace-btn espace-btn-ouv">
            <span class="espace-label">Ouvrier</span>
            <span class="espace-title">Vous cherchez du travail ?</span>
            <span class="espace-desc">Consultez les offres disponibles et postulez en un seul clic.</span>
            <span class="espace-arrow">Acceder &rarr;</span>
        </a>

    </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
</footer>

</body>
</html>
