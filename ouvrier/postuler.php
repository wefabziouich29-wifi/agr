<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Postuler - Uber-Cueillette</title>
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['offre_id'])) {
    $offre_id = (int)$_POST['offre_id'];

    // Vérifier si l'offre existe et n'est pas clôturée
    $offre = $conn->prepare("SELECT * FROM offre WHERE id_offre = ?");
    $offre->execute([$offre_id]);
    $o = $offre->fetch(PDO::FETCH_ASSOC);

    if (!$o) {
        $erreur = "Offre introuvable.";
    } elseif (strtotime($o['date_limite']) < time()) {
        $erreur = "Cette offre n'accepte plus de candidatures.";
    } else {
        // Vérifier si l'ouvrier a déjà postulé
        $check = $conn->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre = ? AND id_ouvrier = ?");
        $check->execute([$offre_id, $ouvrier_id]);
        if ($check->fetchColumn() > 0) {
            $erreur = "Vous avez déjà postulé pour cette offre.";
        } else {
            // Vérifier si l'offre n'est pas complète
            $acceptes = $conn->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre = ? AND decision = 'accepte'");
            $acceptes->execute([$offre_id]);
            if ($acceptes->fetchColumn() >= $o['nombre_ouvriers']) {
                $erreur = "Cette offre a atteint le nombre d'ouvriers requis.";
            } else {
                // Ajouter la candidature
                $req = $conn->prepare("INSERT INTO candidature (id_offre, id_ouvrier, decision) VALUES (?, ?, 'encours')");
                $req->execute([$offre_id, $ouvrier_id]);
                header('Location: mes_candidatures.php?success=1');
                exit;
            }
        }
    }
}
?>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="liste_offres.php">Offres</a></li>
        <li><a href="mes_candidatures.php">Mes Candidatures</a></li>
        <li><a href="mes_chantiers.php">Mes Chantiers</a></li>
        <li><a href="logout.php" class="nav-btn">Déconnexion</a></li>
    </ul>
</nav>

<div style="padding: 100px 60px 60px;">
    <?php if (isset($erreur)): ?>
        <div style="background:#fee2e2;border:1px solid #fecaca;color:#991b1b;padding:16px;border-radius:var(--radius);margin-bottom:24px;">
            <?= htmlspecialchars($erreur) ?>
        </div>
        <a href="liste_offres.php" class="btn-submit" style="display:inline-block;margin-top:16px;">Retour aux offres</a>
    <?php endif; ?>
</div>

<footer>
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
</footer>

</body>
</html>
