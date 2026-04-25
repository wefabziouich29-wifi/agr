<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mes Chantiers - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css"/>
    <style>
        .chantier-card { background:var(--noir3); border:1px solid var(--gris2); border-radius:var(--radius); padding:24px; margin-bottom:16px; }
        .chantier-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:1px solid var(--gris2); padding-bottom:16px; }
        .rating { color:#fbbf24; font-weight:700; font-size:18px; }
        .chantier-info { display:grid; grid-template-columns:repeat(2, 1fr); gap:16px; margin:16px 0; }
        .info-item { }
        .info-label { color:var(--gris); font-size:12px; text-transform:uppercase; }
        .info-value { font-weight:600; margin-top:4px; }
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

$chantiers = $conn->query("
    SELECT c.*, o.*, t.libelle as fruit, g.libelle as gouvernorat, a.prenom as agri_prenom, a.nom as agri_nom
    FROM candidature c
    JOIN offre o ON c.id_offre = o.id_offre
    JOIN type_fruit t ON o.id_type_fruit = t.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    JOIN agriculteur a ON o.id_agriculteur = a.id_agriculteur
    WHERE c.id_ouvrier = $ouvrier_id AND c.decision = 'accepte' AND o.date_fin < NOW()
    ORDER BY o.date_fin DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Calcul des gains totaux
$total_gains = 0;
if (!empty($chantiers)) {
    foreach ($chantiers as $ch) {
        if ($ch['remuneration']) {
            $total_gains += $ch['remuneration'];
        }
    }
}
?>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="liste_offres.php">Offres</a></li>
        <li><a href="mes_candidatures.php">Mes Candidatures</a></li>
        <li><a href="profil.php">Mon Profil</a></li>
        <li><a href="logout.php" class="nav-btn">Déconnexion</a></li>
    </ul>
</nav>

<div style="padding: 100px 60px 60px;">
    <h1 style="font-size:32px;margin-bottom:10px;">Mes Chantiers</h1>
    <p style="color:var(--gris);margin-bottom:32px;">Historique de vos chantiers et gains</p>

    <div style="background:var(--noir3);border:1px solid var(--gris2);border-radius:var(--radius);padding:24px;margin-bottom:32px;text-align:center;">
        <div style="font-size:12px;color:var(--gris);text-transform:uppercase;margin-bottom:8px;">Gains totaux</div>
        <div style="font-size:32px;font-weight:700;color:var(--vert);"><?= number_format($total_gains, 2) ?> DT</div>
    </div>

    <?php if (!empty($chantiers)): ?>
        <?php foreach ($chantiers as $ch): ?>
            <div class="chantier-card">
                <div class="chantier-header">
                    <div>
                        <div style="font-weight:700;font-size:18px;"><?= htmlspecialchars($ch['fruit']) ?></div>
                        <small style="color:var(--gris);"><?= htmlspecialchars($ch['gouvernorat']) ?></small>
                    </div>
                    <div style="text-align:right;">
                        <div class="rating">⭐ <?= $ch['note'] !== null ? $ch['note'] . '/10' : '—' ?></div>
                        <small style="color:var(--gris);">Note reçue</small>
                    </div>
                </div>

                <div class="chantier-info">
                    <div class="info-item">
                        <div class="info-label">Période</div>
                        <div class="info-value"><?= date('d/m/Y', strtotime($ch['date_debut'])) ?> - <?= date('d/m/Y', strtotime($ch['date_fin'])) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Rémunération</div>
                        <div class="info-value" style="color:var(--vert);"><?= $ch['remuneration'] ?? '—' ?> DT</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Agriculteur</div>
                        <div class="info-value"><?= htmlspecialchars($ch['agri_prenom'] . ' ' . $ch['agri_nom']) ?></div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Lieu</div>
                        <div class="info-value"><?= htmlspecialchars($ch['adresse']) ?></div>
                    </div>
                </div>

                <?php if ($ch['commentaire']): ?>
                    <div style="background:var(--noir2);border-left:3px solid var(--accent);padding:12px;border-radius:4px;margin-top:16px;">
                        <div style="color:var(--gris);font-size:12px;margin-bottom:4px;">Commentaire de l'agriculteur:</div>
                        <div><?= htmlspecialchars($ch['commentaire']) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="background:var(--noir3);padding:40px;border-radius:var(--radius);text-align:center;color:var(--gris);">
            Vous n'avez pas encore terminé de chantiers.
        </div>
    <?php endif; ?>
</div>

<footer style="margin-top:60px;">
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

</body>
</html>
