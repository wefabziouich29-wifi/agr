<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mes Candidatures - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css"/>
    <style>
        .candidature-item { background:var(--noir3); border:1px solid var(--gris2); border-radius:var(--radius); padding:20px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; }
        .candidature-info { flex:1; }
        .candidature-status { padding:6px 12px; border-radius:4px; font-weight:500; font-size:12px; }
        .status-encours { background:var(--vert); color:var(--blanc); }
        .status-accepte { background:#4ade80; color:var(--blanc); }
        .status-refuse { background:#f87171; color:var(--blanc); }
    </style>
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

$candidatures = $conn->query("
    SELECT c.*, o.id_offre, t.libelle as fruit, g.libelle as gouvernorat, a.prenom as agri_prenom, a.nom as agri_nom
    FROM candidature c
    JOIN offre o ON c.id_offre = o.id_offre
    JOIN type_fruit t ON o.id_type_fruit = t.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    JOIN agriculteur a ON o.id_agriculteur = a.id_agriculteur
    WHERE c.id_ouvrier = $ouvrier_id
    ORDER BY c.date_candidature DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="liste_offres.php">Offres</a></li>
        <li><a href="mes_chantiers.php">Mes Chantiers</a></li>
        <li><a href="profil.php">Mon Profil</a></li>
        <li><a href="logout.php" class="nav-btn">Déconnexion</a></li>
    </ul>
</nav>

<div style="padding: 100px 60px 60px;">
    <h1 style="font-size:32px;margin-bottom:10px;">Mes Candidatures</h1>
    <p style="color:var(--gris);margin-bottom:32px;">Suivi de vos postulations</p>

    <?php if (isset($_GET['success'])): ?>
        <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:16px;border-radius:var(--radius);margin-bottom:24px;">
            ✓ Vous avez postulé avec succès à cette offre!
        </div>
    <?php endif; ?>

    <?php if (!empty($candidatures)): ?>
        <?php foreach ($candidatures as $c): ?>
            <div class="candidature-item">
                <div class="candidature-info">
                    <div style="font-weight:700;font-size:16px;margin-bottom:8px;">
                        <?= htmlspecialchars($c['fruit']) ?> à <?= htmlspecialchars($c['gouvernorat']) ?>
                    </div>
                    <div style="color:var(--gris);font-size:13px;margin-bottom:4px;">
                        Chez <?= htmlspecialchars($c['agri_prenom'] . ' ' . $c['agri_nom']) ?>
                    </div>
                    <small style="color:var(--gris);">Candidature du <?= date('d/m/Y', strtotime($c['date_candidature'])) ?></small>
                </div>
                <span class="candidature-status status-<?= $c['decision'] ?>">
                    <?php
                        $status_labels = [
                            'encours' => 'En cours',
                            'accepte' => 'Acceptée',
                            'refuse' => 'Refusée'
                        ];
                        echo $status_labels[$c['decision']] ?? 'Inconnue';
                    ?>
                </span>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="background:var(--noir3);padding:40px;border-radius:var(--radius);text-align:center;color:var(--gris);">
            Vous n'avez pas encore postulé à des offres.
        </div>
    <?php endif; ?>
</div>

<footer style="margin-top:60px;">
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

</body>
</html>
