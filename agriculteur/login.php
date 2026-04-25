<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Uber-Cueillette -- Connexion Agriculteur</title>
    <link rel="stylesheet" href="../css/style.css?v=4"/>
</head>
<body>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="../index.php">Accueil</a></li>
        <li><a href="login.php">Agriculteur</a></li>
        <li><a href="../ouvrier/login.php">Ouvrier</a></li>
    </ul>
</nav>

<?php
session_start();
if (isset($_SESSION['agriculteur_id'])) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider'])) {
    require_once '../config/connexion.php';
    $pseudo = trim($_POST['pseudo']);
    $mdp    = trim($_POST['password']);
    $req = $conn->prepare("SELECT * FROM agriculteur WHERE pseudo=? AND password=?");
    $req->execute([$pseudo, md5($mdp)]);
    $agri = $req->fetch(PDO::FETCH_ASSOC);
    if ($agri) {
        $_SESSION['agriculteur_id']  = $agri['id_agriculteur'];
        $_SESSION['agriculteur_nom'] = $agri['prenom'] . ' ' . $agri['nom'];
        header('Location: dashboard.php');
        exit;
    } else {
        $erreur = 'Pseudo ou mot de passe incorrect.';
    }
}
?>

<div class="page-form">
    <div class="form-box">
        <h2>Connexion</h2>
        <p class="subtitle">Acces a votre espace agriculteur</p>

        <?php if (!empty($erreur)): ?>
            <div class="alert-error"><?= htmlspecialchars($erreur) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label for="pseudo">Pseudo</label>
                <input type="text" id="pseudo" name="pseudo" placeholder="Votre pseudo"/>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Votre mot de passe"/>
            </div>
            <button type="submit" name="valider" class="btn-submit">Se connecter</button>
        </form>

        <div class="divider"><span>ou</span></div>

        <div class="form-link">
            Pas encore de compte ? <a href="inscription.php">S'inscrire ici</a>
        </div>
        <div class="form-link" style="margin-top:10px;">
            <a href="../index.php" style="color:var(--gris);font-size:13px;">&larr; Retour a l'accueil</a>
        </div>
    </div>
</div>

<footer>
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

</body>
</html>
