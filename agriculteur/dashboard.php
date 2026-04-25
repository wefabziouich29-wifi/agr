<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Dashboard Agriculteur - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css?v=4"/>
</head>
<body>

<?php
session_start();
if (!isset($_SESSION['agriculteur_id'])) {
    header('Location: login.php');
    exit;
}
require_once '../config/connexion.php';
$agri_id = $_SESSION['agriculteur_id'];
?>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="mes_offres.php">Mes Offres</a></li>
        <li><a href="ajouter_offre.php" class="nav-btn">+ Nouvelle Offre</a></li>
        <li><a href="profil.php">Mon Profil</a></li>
        <li><a href="logout.php" class="nav-btn">Déconnexion</a></li>
    </ul>
</nav>

<div style="padding: 100px 60px 60px;">
    <h1 style="font-size:32px;margin-bottom:10px;">Bienvenue, <?= htmlspecialchars($_SESSION['agriculteur_nom']) ?></h1>
    <p style="color:var(--gris);margin-bottom:40px;">Tableau de bord agriculteur</p>

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-bottom:40px;">
        <?php
        $stats = $conn->query("
            SELECT 
                (SELECT COUNT(*) FROM offre WHERE id_agriculteur = $agri_id) as total_offres,
                (SELECT COUNT(*) FROM candidature c JOIN offre o ON c.id_offre = o.id_offre WHERE o.id_agriculteur = $agri_id AND c.decision = 'accepte') as ouvriers_acceptes,
                (SELECT COUNT(*) FROM offre WHERE id_agriculteur = $agri_id AND date_fin < NOW()) as chantiers_termines
        ")->fetch(PDO::FETCH_ASSOC);
        ?>
        <div style="background:var(--noir3);padding:24px;border-radius:var(--radius);border-left:4px solid var(--vert);text-align:center;">
            <div style="font-size:28px;font-weight:700;color:var(--vert);"><?= $stats['total_offres'] ?></div>
            <div style="color:var(--gris);font-size:14px;margin-top:8px;">Offres publiées</div>
        </div>
        <div style="background:var(--noir3);padding:24px;border-radius:var(--radius);border-left:4px solid #4ade80;text-align:center;">
            <div style="font-size:28px;font-weight:700;color:#4ade80;"><?= $stats['ouvriers_acceptes'] ?></div>
            <div style="color:var(--gris);font-size:14px;margin-top:8px;">Ouvriers engagés</div>
        </div>
        <div style="background:var(--noir3);padding:24px;border-radius:var(--radius);border-left:4px solid var(--accent);text-align:center;">
            <div style="font-size:28px;font-weight:700;color:var(--accent);"><?= $stats['chantiers_termines'] ?></div>
            <div style="color:var(--gris);font-size:14px;margin-top:8px;">Chantiers terminés</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:32px;">
        <div>
            <h2 style="margin-bottom:16px;">Vos offres en cours</h2>
            <div style="background:var(--noir3);border-radius:var(--radius);overflow:hidden;">
                <?php
                $offres = $conn->query("
                    SELECT o.*, t.libelle as fruit, g.libelle as gouvernorat,
                           (SELECT COUNT(*) FROM candidature WHERE id_offre = o.id_offre AND decision = 'accepte') as acceptes,
                           (SELECT COUNT(*) FROM candidature WHERE id_offre = o.id_offre AND decision = 'encours') as encours
                    FROM offre o
                    JOIN type_fruit t ON o.id_type_fruit = t.id_type_fruit
                    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
                    WHERE o.id_agriculteur = $agri_id AND o.date_fin >= NOW()
                    ORDER BY o.date_limite ASC
                    LIMIT 5
                ")->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($offres)) {
                    foreach ($offres as $o) {
                        echo '<div style="padding:16px;border-bottom:1px solid var(--gris2);">';
                        echo '<div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">';
                        echo '<strong>' . htmlspecialchars($o['fruit']) . ' (' . htmlspecialchars($o['gouvernorat']) . ')</strong>';
                        echo '<span style="color:var(--vert);font-size:12px;font-weight:700;">' . $o['acceptes'] . '/' . $o['nombre_ouvriers'] . ' ouvriers</span>';
                        echo '</div>';
                        echo '<small style="color:var(--gris);">Candidatures en attente: ' . $o['encours'] . '</small><br/>';
                        echo '<small style="color:var(--gris);">Limite: ' . date('d/m/Y', strtotime($o['date_limite'])) . '</small>';
                        echo '</div>';
                    }
                } else {
                    echo '<div style="padding:20px;text-align:center;color:var(--gris);">Aucune offre en cours</div>';
                }
                ?>
            </div>
        </div>

        <div>
            <h2 style="margin-bottom:16px;">Dernières candidatures</h2>
            <div style="background:var(--noir3);border-radius:var(--radius);overflow:hidden;">
                <?php
                $candidatures = $conn->query("
                    SELECT c.*, o.id_offre, t.libelle as fruit, ou.prenom, ou.nom
                    FROM candidature c
                    JOIN offre o ON c.id_offre = o.id_offre
                    JOIN type_fruit t ON o.id_type_fruit = t.id_type_fruit
                    JOIN ouvrier ou ON c.id_ouvrier = ou.id_ouvrier
                    WHERE o.id_agriculteur = $agri_id AND c.decision = 'encours'
                    ORDER BY c.date_candidature DESC
                    LIMIT 5
                ")->fetchAll(PDO::FETCH_ASSOC);

                if (!empty($candidatures)) {
                    foreach ($candidatures as $c) {
                        echo '<div style="padding:16px;border-bottom:1px solid var(--gris2);">';
                        echo '<div><strong>' . htmlspecialchars($c['prenom'] . ' ' . $c['nom']) . '</strong></div>';
                        echo '<small style="color:var(--gris);">Pour ' . htmlspecialchars($c['fruit']) . '</small><br/>';
                        echo '<small style="color:var(--gris);">Le ' . date('d/m/Y', strtotime($c['date_candidature'])) . '</small>';
                        echo '</div>';
                    }
                } else {
                    echo '<div style="padding:20px;text-align:center;color:var(--gris);">Aucune candidature en attente</div>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<footer style="margin-top:60px;">
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

</body>
</html>
