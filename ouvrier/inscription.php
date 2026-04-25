<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Uber-Cueillette -- Inscription Ouvrier</title>
    <link rel="stylesheet" href="../css/style.css"/>
</head>
<body>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="../index.php">Accueil</a></li>
        <li><a href="login.php">Connexion</a></li>
        <li><a href="../agriculteur/login.php">Agriculteur</a></li>
    </ul>
</nav>

<?php
session_start();
if (isset($_SESSION['ouvrier_id'])) {
    header('Location: dashboard.php');
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider'])) {
    require_once '../config/connexion.php';

    $nom     = trim($_POST['nom']);
    $prenom  = trim($_POST['prenom']);
    $cin     = trim($_POST['CIN']);
    $email   = trim($_POST['email']);
    $pseudo  = trim($_POST['pseudo']);
    $mdp     = trim($_POST['password']);
    $description = trim($_POST['description']);

    $erreurs = [];
    if ($nom === '')    $erreurs[] = 'Le nom est obligatoire.';
    if ($prenom === '') $erreurs[] = 'Le prenom est obligatoire.';
    if (!preg_match('/^\d{8}$/', $cin))             $erreurs[] = 'Le CIN doit contenir exactement 8 chiffres.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))  $erreurs[] = 'Email invalide.';
    if (!preg_match('/^[a-zA-Z]+$/', $pseudo))       $erreurs[] = 'Le pseudo doit contenir uniquement des lettres.';
    if (!preg_match('/^[a-zA-Z0-9]{7,}[$#]$/', $mdp)) $erreurs[] = 'Mot de passe invalide -- 8+ caracteres, finit par $ ou #';
    
    // Vérifier upload photo
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $erreurs[] = 'La photo d\'identité est obligatoire.';
    } else {
        $file = $_FILES['photo'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file['type'], $allowed_types)) {
            $erreurs[] = 'Format de photo invalide (JPG, JPEG, PNG).';
        }
        if ($file['size'] > 5000000) {
            $erreurs[] = 'La photo ne doit pas dépasser 5 MB.';
        }
    }

    if (empty($erreurs)) {
        $chk = $conn->prepare("SELECT COUNT(*) FROM ouvrier WHERE pseudo=? OR CIN=? OR email=?");
        $chk->execute([$pseudo, $cin, $email]);
        if ($chk->fetchColumn() > 0) {
            $erreurs[] = 'Pseudo, CIN ou email deja utilise.';
        }
    }

    if (empty($erreurs)) {
        $photo_data = file_get_contents($_FILES['photo']['tmp_name']);
        $photo_name = $_FILES['photo']['name'];
        $photo_type = $_FILES['photo']['type'];
        $photo_size = $_FILES['photo']['size'];

        $req = $conn->prepare(
            "INSERT INTO ouvrier (nom, prenom, CIN, email, pseudo, password, description, imgName, taille, type_img, photo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $req->execute([$nom, $prenom, $cin, $email, $pseudo, md5($mdp), $description, $photo_name, $photo_size, $photo_type, $photo_data]);
        $msg_ok = true;
    }
}
?>

<div class="page-form">
    <div class="form-box" style="max-width:560px;">
        <h2>Inscription Ouvrier</h2>
        <p class="subtitle">Creez votre compte et commencez a travailler</p>

        <?php if (!empty($erreurs)): ?>
            <div class="alert-error">
                <?php foreach ($erreurs as $e): echo '-- ' . htmlspecialchars($e) . '<br/>'; endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($msg_ok)): ?>
            <div class="alert-success">
                Inscription reussie ! <a href="login.php" style="color:var(--vert-clair);font-weight:600;">Se connecter maintenant</a>
            </div>
        <?php else: ?>

        <form method="post" action="inscription.php" enctype="multipart/form-data" onsubmit="return validerFormOuvrier()">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label for="nom">Nom *</label>
                    <input type="text" id="nom" name="nom"
                           value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                           placeholder="Votre nom"/>
                    <span class="error-msg"></span>
                </div>
                <div class="form-group">
                    <label for="prenom">Prenom *</label>
                    <input type="text" id="prenom" name="prenom"
                           value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>"
                           placeholder="Votre prenom"/>
                    <span class="error-msg"></span>
                </div>
            </div>

            <div class="form-group">
                <label for="CIN">Numero CIN * (8 chiffres)</label>
                <input type="text" id="CIN" name="CIN" maxlength="8"
                       value="<?= htmlspecialchars($_POST['CIN'] ?? '') ?>"
                       placeholder="12345678"/>
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="votre@email.com"/>
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="photo">Photo d'identité * (JPG, PNG - max 5MB)</label>
                <input type="file" id="photo" name="photo" accept="image/*" onchange="apercuPhoto(this)"/>
                <img id="photo-preview" style="display:none;width:100px;height:100px;margin-top:10px;border-radius:8px;"/>
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="description">Description (expérience, niveau, etc.)</label>
                <textarea id="description" name="description" placeholder="Décrivez votre expérience..." style="min-height:80px;">
<?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label for="pseudo">Pseudo * (lettres uniquement)</label>
                <input type="text" id="pseudo" name="pseudo"
                       value="<?= htmlspecialchars($_POST['pseudo'] ?? '') ?>"
                       placeholder="ex: JeanDurand"/>
                <span class="error-msg"></span>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe * (8+ caracteres, finit par $ ou #)</label>
                <input type="password" id="password" name="password"
                       value="<?= htmlspecialchars($_POST['password'] ?? '') ?>"
                       placeholder="ex: Password123#"/>
                <span class="error-msg"></span>
            </div>

            <button type="submit" name="valider" class="btn-submit">S'inscrire</button>
        </form>

        <?php endif; ?>

        <div class="divider"><span>ou</span></div>

        <div class="form-link">
            Deja inscrit ? <a href="login.php">Se connecter ici</a>
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

<script src="../js/validation.js"></script>

</body>
</html>
