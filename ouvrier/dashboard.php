<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard Ouvrier - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css"/>
</head>
<body>

<?php
session_start();
if (!isset($_SESSION['ouvrier_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/connexion.php';
$ouvrier_id = $_SESSION['ouvrier_id'];
?>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="liste_offres.php">Offres</a></li>
        <li><a href="mes_candidatures.php">Mes Candidatures</a></li>
        <li><a href="mes_chantiers.php">Mes Chantiers</a></li>
        <li><a href="profil.php">Mon Profil</a></li>
        <li><a href="logout.php" class="nav-btn">Déconnexion</a></li>
    </ul>
</nav>

<div style="padding: 100px 60px 60px;">
    <h1 style="font-size:32px;margin-bottom:10px;">Bienvenue, <?= htmlspecialchars($_SESSION['ouvrier_nom']) ?></h1>
    <p style="color:var(--gris);margin-bottom:40px;">Tableau de bord ouvrier</p>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-bottom:40px;">
        <?php
        $stats = $conn->query("
            SELECT 
                (SELECT COUNT(*) FROM candidature WHERE id_ouvrier = $ouvrier_id) as total_candidatures,
                (SELECT COUNT(*) FROM candidature WHERE id_ouvrier = $ouvrier_id AND decision = 'accepte') as acceptes,
                (SELECT COUNT(*) FROM candidature WHERE id_ouvrier = $ouvrier_id AND decision = 'refuse') as refuses
        ")->fetch(PDO::FETCH_ASSOC);
        ?>
        <div style="background:var(--noir3);padding:24px;border-radius:var(--radius);border-left:4px solid var(--vert);text-align:center;">
            <div style="font-size:28px;font-weight:700;color:var(--vert);"><?= $stats['total_candidatures'] ?></div>
            <div style="color:var(--gris);font-size:14px;margin-top:8px;">Candidatures</div>
        </div>
        <div style="background:var(--noir3);padding:24px;border-radius:var(--radius);border-left:4px solid #4ade80;text-align:center;">
            <div style="font-size:28px;font-weight:700;color:#4ade80;"><?= $stats['acceptes'] ?></div>
            <div style="color:var(--gris);font-size:14px;margin-top:8px;">Acceptées</div>
        </div>
        <div style="background:var(--noir3);padding:24px;border-radius:var(--radius);border-left:4px solid #f87171;text-align:center;">
            <div style="font-size:28px;font-weight:700;color:#f87171;"><?= $stats['refuses'] ?></div>
            <div style="color:var(--gris);font-size:14px;margin-top:8px;">Refusées</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">
        <div>
            <h2 style="margin-bottom:16px;">Dernières offres disponibles</h2>
            <div style="background:var(--noir3);border-radius:var(--radius);overflow:hidden;">
                <?php
                $offres = $conn->query("
                    SELECT o.*, t.libelle as fruit, g.libelle as gouvernorat
                    FROM offre o
                    JOIN type_fruit t ON o.id_type_fruit = t.id_type_fruit
                    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
                    WHERE o.date_limite > NOW()
                    ORDER BY o.date_limite ASC
                    LIMIT 3
                ")->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($offres)) {
                    foreach ($offres as $o) {
                        echo '<div style="padding:16px;border-bottom:1px solid var(--gris2);">';
                        echo '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">';
                        echo '<div><strong>' . htmlspecialchars($o['fruit']) . '</strong> à ' . htmlspecialchars($o['gouvernorat']) . '</div>';
                        echo '<span style="color:var(--vert);font-weight:700;">' . $o['prix_journee'] . ' DT/j</span>';
                        echo '</div>';
                        echo '<small style="color:var(--gris);">Du ' . date('d/m/Y', strtotime($o['date_debut'])) . ' au ' . date('d/m/Y', strtotime($o['date_fin'])) . '</small>';
                        echo '</div>';
                    }
                } else {
                    echo '<div style="padding:20px;text-align:center;color:var(--gris);">Aucune offre disponible</div>';
                }
                ?>
            </div>
            <a href="liste_offres.php" style="display:block;margin-top:16px;color:var(--vert);text-decoration:none;font-weight:500;">Voir toutes les offres →</a>
        </div>

        <div>
            <h2 style="margin-bottom:16px;">Mes candidatures en cours</h2>
            <div style="background:var(--noir3);border-radius:var(--radius);overflow:hidden;">
                <?php
                $candidatures = $conn->query("
                    SELECT c.*, o.id_offre, t.libelle as fruit, g.libelle as gouvernorat
                    FROM candidature c
                    JOIN offre o ON c.id_offre = o.id_offre
                    JOIN type_fruit t ON o.id_type_fruit = t.id_type_fruit
                    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
                    WHERE c.id_ouvrier = $ouvrier_id AND c.decision = 'encours'
                    LIMIT 3
                ")->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($candidatures)) {
                    foreach ($candidatures as $c) {
                        echo '<div style="padding:16px;border-bottom:1px solid var(--gris2);">';
                        echo '<div><strong>' . htmlspecialchars($c['fruit']) . '</strong></div>';
                        echo '<small style="color:var(--gris);">Candidature du ' . date('d/m/Y', strtotime($c['date_candidature'])) . '</small>';
                        echo '<div style="margin-top:8px;"><span style="background:var(--vert);color:var(--blanc);padding:4px 8px;border-radius:4px;font-size:12px;">En cours</span></div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div style="padding:20px;text-align:center;color:var(--gris);">Aucune candidature en cours</div>';
                }
                ?>
            </div>
            <a href="mes_candidatures.php" style="display:block;margin-top:16px;color:var(--vert);text-decoration:none;font-weight:500;">Voir mes candidatures →</a>
        </div>
    </div>
</div>

<footer style="margin-top:60px;">
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

</body>
</html>
