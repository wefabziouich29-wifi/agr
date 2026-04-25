<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mon Profil - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css"/>
    <style>
        .form-group { margin-bottom:20px; }
        .form-group label { display:block; margin-bottom:8px; font-weight:500; }
        .form-group input, .form-group textarea { width:100%; background:var(--noir3); border:1px solid var(--gris2); color:var(--blanc); padding:12px; border-radius:var(--radius); font-family:inherit; }
        .btn-save { background:var(--vert); color:var(--blanc); border:none; padding:12px 24px; border-radius:var(--radius); cursor:pointer; font-weight:500; }
        .btn-save:hover { background:var(--vert-clair); }
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

$agriculteur = $conn->prepare("SELECT * FROM agriculteur WHERE id_agriculteur = ?");
$agriculteur->execute([$agri_id]);
$a = $agriculteur->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $pseudo = trim($_POST['pseudo']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);

    // Vérifier si pseudo existe ailleurs
    $check = $conn->prepare("SELECT COUNT(*) FROM agriculteur WHERE (pseudo = ? OR email = ?) AND id_agriculteur != ?");
    $check->execute([$pseudo, $email, $agri_id]);
    if ($check->fetchColumn() > 0) {
        $erreur = 'Ce pseudo ou email est déjà utilisé.';
    } else {
        $req = $conn->prepare("UPDATE agriculteur SET pseudo = ?, email = ?, adresse = ? WHERE id_agriculteur = ?");
        $req->execute([$pseudo, $email, $adresse, $agri_id]);
        $success = 'Profil mis à jour avec succès!';
    }
}
?>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="mes_offres.php">Mes Offres</a></li>
        <li><a href="logout.php" class="nav-btn">Déconnexion</a></li>
    </ul>
</nav>

<div style="padding: 100px 60px 60px;max-width:700px;">
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

    <div style="background:var(--noir3);padding:24px;border-radius:var(--radius);margin-bottom:32px;">
        <h3 style="margin-bottom:16px;">Informations personnelles</h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div>
                <label style="display:block;margin-bottom:4px;color:var(--gris);font-size:12px;">Nom</label>
                <div style="font-weight:500;"><?= htmlspecialchars($a['nom']) ?></div>
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;color:var(--gris);font-size:12px;">Prénom</label>
                <div style="font-weight:500;"><?= htmlspecialchars($a['prenom']) ?></div>
            </div>
            <div>
                <label style="display:block;margin-bottom:4px;color:var(--gris);font-size:12px;">CIN</label>
                <div style="font-weight:500;"><?= htmlspecialchars($a['CIN']) ?></div>
            </div>
        </div>
    </div>

    <form method="post" action="profil.php" style="background:var(--noir3);padding:24px;border-radius:var(--radius);">
        <h3 style="margin-bottom:16px;">Modifier mes informations</h3>

        <div class="form-group">
            <label for="pseudo">Pseudo</label>
            <input type="text" id="pseudo" name="pseudo" value="<?= htmlspecialchars($a['pseudo']) ?>" required/>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($a['email']) ?>" required/>
        </div>

        <div class="form-group">
            <label for="adresse">Adresse</label>
            <input type="text" id="adresse" name="adresse" value="<?= htmlspecialchars($a['adresse']) ?>"/>
        </div>

        <button type="submit" name="update" class="btn-save">Enregistrer les modifications</button>
    </form>
</div>

<footer style="margin-top:60px;">
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

</body>
</html>
