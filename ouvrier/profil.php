<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mon Profil - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css"/>
    <style>
        .profil-container { display:grid; grid-template-columns:300px 1fr; gap:32px; max-width:900px; }
        .profil-photo { width:100%; background:var(--noir3); border-radius:var(--radius); overflow:hidden; }
        .profil-photo img { width:100%; height:auto; display:block; }
        .profil-edit { }
        .form-group { margin-bottom:20px; }
        .form-group label { display:block; margin-bottom:8px; font-weight:500; color:var(--blanc); }
        .form-group input, .form-group textarea { width:100%; background:var(--noir3); border:1px solid var(--gris2); color:var(--blanc); padding:12px; border-radius:var(--radius); }
        .form-group textarea { min-height:100px; resize:vertical; }
        .btn-save { background:var(--vert); color:var(--blanc); border:none; padding:12px 24px; border-radius:var(--radius); cursor:pointer; font-weight:500; }
        .btn-save:hover { background:var(--vert-clair); }
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

$ouvrier = $conn->prepare("SELECT * FROM ouvrier WHERE id_ouvrier = ?");
$ouvrier->execute([$ouvrier_id]);
$o = $ouvrier->fetch(PDO::FETCH_ASSOC);

// Calcul de la moyenne des notes
$moyenne = $conn->prepare("SELECT AVG(note) as moyenne FROM candidature WHERE id_ouvrier = ? AND note IS NOT NULL");
$moyenne->execute([$ouvrier_id]);
$stats = $moyenne->fetch(PDO::FETCH_ASSOC);
$moyenne_note = $stats['moyenne'] !== null ? round($stats['moyenne'], 1) : 'Pas encore noté';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $pseudo = trim($_POST['pseudo']);
    $description = trim($_POST['description']);

    // Vérifier si pseudo existe ailleurs
    $check = $conn->prepare("SELECT COUNT(*) FROM ouvrier WHERE pseudo = ? AND id_ouvrier != ?");
    $check->execute([$pseudo, $ouvrier_id]);
    if ($check->fetchColumn() > 0) {
        $erreur = 'Ce pseudo est déjà utilisé.';
    } else {
        $req = $conn->prepare("UPDATE ouvrier SET pseudo = ?, description = ? WHERE id_ouvrier = ?");
        $req->execute([$pseudo, $description, $ouvrier_id]);
        $_SESSION['ouvrier_nom'] = $o['prenom'] . ' ' . $o['nom'];
        $success = 'Profil mis à jour avec succès!';
    }
}
?>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="liste_offres.php">Offres</a></li>
        <li><a href="mes_candidatures.php">Mes Candidatures</a></li>
        <li><a href="logout.php" class="nav-btn">Déconnexion</a></li>
    </ul>
</nav>

<div style="padding: 100px 60px 60px;">
    <h1 style="font-size:32px;margin-bottom:10px;">Mon Profil</h1>
    <p style="color:var(--gris);margin-bottom:32px;">Gérez vos informations</p>

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

    <div class="profil-container">
        <div class="profil-photo">
            <?php if ($o['photo']): ?>
                <img src="data:<?= $o['type_img'] ?>;base64,<?= base64_encode($o['photo']) ?>" alt="Photo"/>
            <?php else: ?>
                <div style="width:100%;height:300px;background:var(--gris2);display:flex;align-items:center;justify-content:center;color:var(--gris);">Pas de photo</div>
            <?php endif; ?>
            <div style="padding:16px;background:var(--noir3);text-align:center;border-top:1px solid var(--gris2);">
                <div style="font-weight:700;margin-bottom:4px;">Évaluation</div>
                <div style="color:var(--vert);font-size:18px;font-weight:700;">⭐ <?= htmlspecialchars($moyenne_note) ?></div>
            </div>
        </div>

        <div class="profil-edit">
            <div style="background:var(--noir3);padding:24px;border-radius:var(--radius);margin-bottom:24px;">
                <h3 style="margin-bottom:16px;">Informations personnelles</h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;margin-bottom:4px;color:var(--gris);font-size:12px;">Nom</label>
                        <div style="font-weight:500;"><?= htmlspecialchars($o['nom']) ?></div>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:4px;color:var(--gris);font-size:12px;">Prénom</label>
                        <div style="font-weight:500;"><?= htmlspecialchars($o['prenom']) ?></div>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <label style="display:block;margin-bottom:4px;color:var(--gris);font-size:12px;">CIN</label>
                        <div style="font-weight:500;"><?= htmlspecialchars($o['CIN']) ?></div>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:4px;color:var(--gris);font-size:12px;">Email</label>
                        <div style="font-weight:500;"><?= htmlspecialchars($o['email']) ?></div>
                    </div>
                </div>
            </div>

            <form method="post" action="profil.php" style="background:var(--noir3);padding:24px;border-radius:var(--radius);">
                <h3 style="margin-bottom:16px;">Modifier mon profil</h3>

                <div class="form-group">
                    <label for="pseudo">Pseudo</label>
                    <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($o['pseudo']) ?>"/>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description"><?= htmlspecialchars($o['description'] ?? '') ?></textarea>
                </div>

                <button type="submit" name="update" class="btn-save">Enregistrer les modifications</button>
            </form>
        </div>
    </div>
</div>

<footer style="margin-top:60px;">
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

</body>
</html>
