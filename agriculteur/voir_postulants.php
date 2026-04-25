<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Postulants - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css?v=4"/>
    <style>
        .postulant-card { background:var(--noir3); border:1px solid var(--gris2); border-radius:var(--radius); padding:24px; margin-bottom:16px; display:flex; gap:24px; }
        .postulant-photo { width:120px; height:120px; border-radius:var(--radius); overflow:hidden; flex-shrink:0; background:var(--gris2); display:flex; align-items:center; justify-content:center; }
        .postulant-photo img { width:100%; height:100%; object-fit:cover; }
        .postulant-info { flex:1; }
        .postulant-actions { display:flex; gap:8px; }
        .btn-accept { background:#4ade80; color:var(--blanc); border:none; padding:8px 16px; border-radius:4px; cursor:pointer; font-weight:500; }
        .btn-reject { background:#f87171; color:var(--blanc); border:none; padding:8px 16px; border-radius:4px; cursor:pointer; font-weight:500; }
        .rating { font-weight:700; color:#fbbf24; }
    </style>
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
$offre_id = isset($_GET['offre_id']) ? (int)$_GET['offre_id'] : 0;

// Vérifier que l'offre appartient à l'agriculteur
$offre = $conn->prepare("SELECT * FROM offre WHERE id_offre = ? AND id_agriculteur = ?");
$offre->execute([$offre_id, $agri_id]);
$o = $offre->fetch(PDO::FETCH_ASSOC);

if (!$o) {
    header('Location: mes_offres.php');
    exit;
}

// Traiter acceptation/refus
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accepter'])) {
        $candidature_id = (int)$_POST['candidature_id'];
        
        // Vérifier que le nombre d'ouvriers n'est pas atteint
        $acceptes = $conn->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre = ? AND decision = 'accepte'");
        $acceptes->execute([$offre_id]);
        if ($acceptes->fetchColumn() < $o['nombre_ouvriers']) {
            $conn->prepare("UPDATE candidature SET decision = 'accepte' WHERE id_candidature = ?")->execute([$candidature_id]);
            $success = 'Candidature acceptée!';
        } else {
            $erreur = 'Vous avez atteint le nombre d\'ouvriers requis.';
        }
    } elseif (isset($_POST['refuser'])) {
        $candidature_id = (int)$_POST['candidature_id'];
        $conn->prepare("UPDATE candidature SET decision = 'refuse' WHERE id_candidature = ?")->execute([$candidature_id]);
        $success = 'Candidature refusée.';
    }
}

$postulants = $conn->query("
    SELECT c.*, ou.*, 
           (SELECT AVG(note) FROM candidature WHERE id_ouvrier = ou.id_ouvrier AND note IS NOT NULL) as moyenne_note
    FROM candidature c
    JOIN ouvrier ou ON c.id_ouvrier = ou.id_ouvrier
    WHERE c.id_offre = $offre_id
    ORDER BY c.decision, c.date_candidature DESC
")->fetchAll(PDO::FETCH_ASSOC);

$fruits = $conn->prepare("SELECT * FROM type_fruit WHERE id_type_fruit = ?");
$fruits->execute([$o['id_type_fruit']]);
$fruit = $fruits->fetch(PDO::FETCH_ASSOC);
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
    <h1 style="font-size:32px;margin-bottom:10px;">Postulants pour l'offre</h1>
    <p style="color:var(--gris);margin-bottom:32px;"><?= htmlspecialchars($fruit['libelle']) ?> - Du <?= date('d/m/Y', strtotime($o['date_debut'])) ?> au <?= date('d/m/Y', strtotime($o['date_fin'])) ?></p>

    <?php if (isset($success)): ?>
        <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:16px;border-radius:var(--radius);margin-bottom:24px;">
            ✓ <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($erreur)): ?>
        <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:16px;border-radius:var(--radius);margin-bottom:24px;">
            ✗ <?= htmlspecialchars($erreur) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($postulants)): ?>
        <?php 
        $en_attente = array_filter($postulants, fn($p) => $p['decision'] === 'encours');
        $acceptes_list = array_filter($postulants, fn($p) => $p['decision'] === 'accepte');
        $refuses_list = array_filter($postulants, fn($p) => $p['decision'] === 'refuse');
        ?>

        <?php if (!empty($en_attente)): ?>
            <h2 style="margin:32px 0 16px;font-size:20px;">En attente (<?= count($en_attente) ?>)</h2>
            <?php foreach ($en_attente as $p): ?>
                <div class="postulant-card">
                    <div class="postulant-photo">
                        <?php if ($p['photo']): ?>
                            <img src="data:<?= $p['type_img'] ?>;base64,<?= base64_encode($p['photo']) ?>" alt="Photo"/>
                        <?php else: ?>
                            <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--gris);">Pas de photo</div>
                        <?php endif; ?>
                    </div>
                    <div class="postulant-info">
                        <div style="font-weight:700;font-size:18px;margin-bottom:8px;"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></div>
                        <div style="color:var(--gris);margin-bottom:4px;">CIN: <?= htmlspecialchars($p['CIN']) ?></div>
                        <div style="color:var(--gris);margin-bottom:4px;">Email: <?= htmlspecialchars($p['email']) ?></div>
                        <?php if ($p['description']): ?>
                            <div style="margin:8px 0;font-size:13px;"><?= htmlspecialchars($p['description']) ?></div>
                        <?php endif; ?>
                        <div style="margin-top:8px;color:var(--gris);font-size:12px;">
                            Moyenne: <span class="rating"><?= $p['moyenne_note'] ? round($p['moyenne_note'], 1) . '/10' : 'N/A' ?></span>
                        </div>
                    </div>
                    <div class="postulant-actions" style="flex-direction:column;justify-content:center;">
                        <form method="post" action="voir_postulants.php?offre_id=<?= $offre_id ?>" style="display:inline;">
                            <input type="hidden" name="candidature_id" value="<?= $p['id_candidature'] ?>"/>
                            <button type="submit" name="accepter" class="btn-accept">✓ Accepter</button>
                        </form>
                        <form method="post" action="voir_postulants.php?offre_id=<?= $offre_id ?>" style="display:inline;">
                            <input type="hidden" name="candidature_id" value="<?= $p['id_candidature'] ?>"/>
                            <button type="submit" name="refuser" class="btn-reject">✗ Refuser</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($acceptes_list)): ?>
            <h2 style="margin:32px 0 16px;font-size:20px;color:#4ade80;">Acceptés (<?= count($acceptes_list) ?>)</h2>
            <?php foreach ($acceptes_list as $p): ?>
                <div class="postulant-card">
                    <div class="postulant-photo">
                        <?php if ($p['photo']): ?>
                            <img src="data:<?= $p['type_img'] ?>;base64,<?= base64_encode($p['photo']) ?>" alt="Photo"/>
                        <?php else: ?>
                            <div>Pas de photo</div>
                        <?php endif; ?>
                    </div>
                    <div class="postulant-info">
                        <div style="font-weight:700;font-size:18px;margin-bottom:8px;"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></div>
                        <div style="color:var(--gris);margin-bottom:4px;">CIN: <?= htmlspecialchars($p['CIN']) ?></div>
                        <div style="color:var(--gris);margin-bottom:4px;">Email: <?= htmlspecialchars($p['email']) ?></div>
                        <div style="margin-top:8px;color:var(--gris);font-size:12px;">
                            Moyenne: <span class="rating"><?= $p['moyenne_note'] ? round($p['moyenne_note'], 1) . '/10' : 'N/A' ?></span>
                        </div>
                    </div>
                    <div style="align-self:center;">
                        <span style="background:#4ade80;color:var(--blanc);padding:6px 12px;border-radius:4px;font-weight:500;font-size:12px;">Accepté</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($refuses_list)): ?>
            <h2 style="margin:32px 0 16px;font-size:20px;color:#f87171;">Refusés (<?= count($refuses_list) ?>)</h2>
            <?php foreach ($refuses_list as $p): ?>
                <div class="postulant-card" style="opacity:0.6;">
                    <div class="postulant-photo">
                        <?php if ($p['photo']): ?>
                            <img src="data:<?= $p['type_img'] ?>;base64,<?= base64_encode($p['photo']) ?>" alt="Photo"/>
                        <?php else: ?>
                            <div>Pas de photo</div>
                        <?php endif; ?>
                    </div>
                    <div class="postulant-info">
                        <div style="font-weight:700;font-size:18px;margin-bottom:8px;"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></div>
                        <div style="color:var(--gris);margin-bottom:4px;">CIN: <?= htmlspecialchars($p['CIN']) ?></div>
                    </div>
                    <div style="align-self:center;">
                        <span style="background:#f87171;color:var(--blanc);padding:6px 12px;border-radius:4px;font-weight:500;font-size:12px;">Refusé</span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php else: ?>
        <div style="background:var(--noir3);padding:40px;border-radius:var(--radius);text-align:center;color:var(--gris);">
            Aucun postulant pour cette offre.
        </div>
    <?php endif; ?>

    <div style="margin-top:32px;">
        <a href="mes_offres.php" style="color:var(--vert);text-decoration:none;font-weight:500;">← Retour à mes offres</a>
    </div>
</div>

<footer style="margin-top:60px;">
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

</body>
</html>
