<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Ajouter une Offre - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css"/>
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

$fruits = $conn->query("SELECT * FROM type_fruit ORDER BY libelle")->fetchAll(PDO::FETCH_ASSOC);
$gouvernorats = $conn->query("SELECT * FROM gouvernorat ORDER BY libelle")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $fruit = (int)$_POST['fruit'];
    $gouvernorat = (int)$_POST['gouvernorat'];
    $adresse = trim($_POST['adresse']);
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $nombre_ouvriers = (int)$_POST['nombre_ouvriers'];
    $prix_journee = (float)$_POST['prix_journee'];
    $date_limite = $_POST['date_limite'];

    $erreurs = [];
    if (!$fruit) $erreurs[] = 'Veuillez sélectionner un type de fruit.';
    if (!$gouvernorat) $erreurs[] = 'Veuillez sélectionner un gouvernorat.';
    if (!$adresse) $erreurs[] = 'L\'adresse est obligatoire.';
    if (!$date_debut) $erreurs[] = 'La date de début est obligatoire.';
    if (!$date_fin) $erreurs[] = 'La date de fin est obligatoire.';
    if ($date_fin <= $date_debut) $erreurs[] = 'La date de fin doit être après la date de début.';
    if ($nombre_ouvriers < 1) $erreurs[] = 'Le nombre d\'ouvriers doit être au moins 1.';
    if ($prix_journee <= 0) $erreurs[] = 'Le prix à la journée doit être positif.';
    if (!$date_limite) $erreurs[] = 'La date limite de candidature est obligatoire.';
    if ($date_limite > $date_debut) $erreurs[] = 'La date limite doit être avant la date de début.';

    if (empty($erreurs)) {
        $req = $conn->prepare(
            "INSERT INTO offre (id_type_fruit, id_gouvernorat, adresse, date_debut, date_fin, nombre_ouvriers, prix_journee, date_limite, id_agriculteur)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $req->execute([$fruit, $gouvernorat, $adresse, $date_debut, $date_fin, $nombre_ouvriers, $prix_journee, $date_limite, $agri_id]);
        $success = 'Offre créée avec succès!';
    }
}
?>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="mes_offres.php">Mes Offres</a></li>
        <li><a href="profil.php">Mon Profil</a></li>
        <li><a href="logout.php" class="nav-btn">Déconnexion</a></li>
    </ul>
</nav>

<div style="padding: 100px 60px 60px;">
    <h1 style="font-size:32px;margin-bottom:10px;">Créer une Offre de Récolte</h1>
    <p style="color:var(--gris);margin-bottom:32px;">Publiez une nouvelle offre pour trouver des ouvriers</p>

    <div style="max-width:700px;">
        <?php if (!empty($erreurs)): ?>
            <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:16px;border-radius:var(--radius);margin-bottom:24px;">
                <?php foreach ($erreurs as $e): echo '-- ' . htmlspecialchars($e) . '<br/>'; endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div style="background:#dcfce7;border:1px solid #86efac;color:#166534;padding:16px;border-radius:var(--radius);margin-bottom:24px;">
                ✓ <?= htmlspecialchars($success) ?> <a href="mes_offres.php" style="color:#166534;font-weight:600;">Voir mes offres</a>
            </div>
        <?php else: ?>

        <form method="post" action="ajouter_offre.php" style="background:var(--noir3);padding:32px;border-radius:var(--radius);">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label for="fruit">Type de Fruit *</label>
                    <select id="fruit" name="fruit" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($fruits as $f): ?>
                            <option value="<?= $f['id_type_fruit'] ?>" <?= isset($_POST['fruit']) && $_POST['fruit'] == $f['id_type_fruit'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($f['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="gouvernorat">Gouvernorat *</label>
                    <select id="gouvernorat" name="gouvernorat" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($gouvernorats as $g): ?>
                            <option value="<?= $g['id_gouvernorat'] ?>" <?= isset($_POST['gouvernorat']) && $_POST['gouvernorat'] == $g['id_gouvernorat'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g['libelle']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="adresse">Adresse du site de récolte *</label>
                <input type="text" id="adresse" name="adresse" placeholder="Ex: Rue principale, Sfax" value="<?= htmlspecialchars($_POST['adresse'] ?? '') ?>" required/>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label for="date_debut">Date de début *</label>
                    <input type="date" id="date_debut" name="date_debut" value="<?= $_POST['date_debut'] ?? '' ?>" required/>
                </div>
                <div class="form-group">
                    <label for="date_fin">Date de fin *</label>
                    <input type="date" id="date_fin" name="date_fin" value="<?= $_POST['date_fin'] ?? '' ?>" required/>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label for="nombre_ouvriers">Nombre d'ouvriers demandés *</label>
                    <input type="number" id="nombre_ouvriers" name="nombre_ouvriers" min="1" value="<?= $_POST['nombre_ouvriers'] ?? '' ?>" required/>
                </div>
                <div class="form-group">
                    <label for="prix_journee">Prix à la journée (DT) *</label>
                    <input type="number" id="prix_journee" name="prix_journee" step="0.01" min="0" value="<?= $_POST['prix_journee'] ?? '' ?>" required/>
                </div>
            </div>

            <div class="form-group">
                <label for="date_limite">Date limite de candidature *</label>
                <input type="date" id="date_limite" name="date_limite" value="<?= $_POST['date_limite'] ?? '' ?>" required/>
                <small style="color:var(--gris);display:block;margin-top:4px;">Après cette date, l'offre sera fermée aux nouvelles candidatures.</small>
            </div>

            <button type="submit" name="ajouter" class="btn-submit">Publier l'offre</button>
        </form>

        <?php endif; ?>
    </div>
</div>

<footer style="margin-top:60px;">
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

<style>
    select, input[type="date"], input[type="number"], input[type="text"] {
        background:var(--noir2);
        border:1px solid var(--gris2);
        color:var(--blanc);
        padding:12px;
        border-radius:var(--radius);
        font-family:inherit;
        font-size:14px;
    }
    select:focus, input:focus {
        outline:none;
        border-color:var(--vert);
        background:var(--noir2);
    }
</style>

</body>
</html>
