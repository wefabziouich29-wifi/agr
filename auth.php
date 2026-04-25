<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login / Sign Up - Uber-Cueillette</title>
    <link rel="stylesheet" href="css/style.css?v=4"/>
    <style>
        .auth-wrap {
            width: 100%;
            max-width: 820px;
            margin: 0 auto;
            background: var(--noir3);
            border: 1px solid var(--gris2);
            border-radius: 20px;
            padding: 32px;
        }

        .auth-title {
            font-size: 34px;
            margin-bottom: 10px;
        }

        .auth-subtitle {
            color: var(--gris);
            margin-bottom: 26px;
            line-height: 1.6;
        }

        .flow-line {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 26px;
        }

        .flow-step {
            border: 1px solid var(--gris2);
            background: var(--noir2);
            border-radius: 999px;
            padding: 8px 14px;
            font-size: 13px;
            color: var(--gris);
        }

        .flow-arrow {
            color: var(--gris);
            align-self: center;
            font-size: 13px;
        }

        .auth-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }

        .auth-choice {
            position: relative;
            border: 1px solid var(--gris2);
            border-radius: 14px;
            background: var(--noir2);
            padding: 16px;
            transition: border var(--transition), box-shadow var(--transition);
            cursor: pointer;
        }

        .auth-choice:hover {
            border-color: var(--vert);
        }

        .auth-choice input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .auth-choice strong {
            display: block;
            margin-bottom: 6px;
            font-size: 16px;
        }

        .auth-choice small {
            color: var(--gris);
            line-height: 1.4;
        }

        .auth-choice.active {
            border-color: var(--vert-clair);
            box-shadow: 0 0 0 2px rgba(106,176,76,0.2);
        }

        .auth-role-wrap {
            margin-bottom: 24px;
        }

        .auth-role-wrap label {
            display: block;
            margin-bottom: 8px;
            color: rgba(255,255,255,0.75);
            font-size: 14px;
            font-weight: 500;
        }

        .auth-role-wrap select {
            width: 100%;
            max-width: 320px;
            background: var(--noir2);
            border: 1px solid var(--gris2);
            color: var(--blanc);
            border-radius: var(--radius);
            padding: 12px 14px;
            font-size: 14px;
        }

        .auth-role-wrap select:focus {
            outline: none;
            border-color: var(--vert-clair);
            box-shadow: 0 0 0 3px rgba(106,176,76,0.15);
        }

        .auth-note {
            margin-top: 12px;
            color: var(--gris);
            font-size: 13px;
        }

        .auth-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .auth-back {
            color: var(--gris);
            text-decoration: none;
            font-size: 14px;
        }

        .auth-back:hover {
            color: var(--blanc);
        }

        .auth-error {
            background: rgba(231,76,60,0.12);
            border: 1px solid rgba(231,76,60,0.35);
            color: #e74c3c;
            border-radius: var(--radius);
            padding: 12px 14px;
            margin-bottom: 16px;
            font-size: 14px;
        }

        @media (max-width: 900px) {
            .auth-wrap {
                padding: 22px;
            }

            .auth-title {
                font-size: 28px;
            }

            .auth-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php
session_start();

if (isset($_SESSION['agriculteur_id'])) {
    header('Location: agriculteur/dashboard.php');
    exit;
}

if (isset($_SESSION['ouvrier_id'])) {
    header('Location: ouvrier/dashboard.php');
    exit;
}

$allowed_modes = ['login', 'signup'];
$allowed_roles = ['agriculteur', 'ouvrier'];

$mode = $_GET['mode'] ?? 'login';
$role = $_GET['role'] ?? 'agriculteur';
$error = '';

if (!in_array($mode, $allowed_modes, true)) {
    $mode = 'login';
}

if (!in_array($role, $allowed_roles, true)) {
    $role = 'agriculteur';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['continuer'])) {
    $mode = $_POST['mode'] ?? 'login';
    $role = $_POST['role'] ?? 'agriculteur';

    if (!in_array($mode, $allowed_modes, true) || !in_array($role, $allowed_roles, true)) {
        $error = 'Veuillez choisir un parcours valide.';
    } else {
        $destinations = [
            'agriculteur' => [
                'login' => 'agriculteur/login.php',
                'signup' => 'agriculteur/inscription.php'
            ],
            'ouvrier' => [
                'login' => 'ouvrier/login.php',
                'signup' => 'ouvrier/inscription.php'
            ]
        ];

        header('Location: ' . $destinations[$role][$mode]);
        exit;
    }
}

$mode_title = $mode === 'signup' ? 'Sign Up' : 'Login';
$mode_note = $mode === 'signup'
    ? 'Creer compte puis completer votre profil.'
    : 'Se connecter avec un compte existant.';
?>

<nav>
    <a href="index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="index.php">Accueil</a></li>
        <li><a href="auth.php" class="nav-btn">Login / Sign Up</a></li>
    </ul>
</nav>

<main class="page-shell page-shell--form">
    <section class="auth-wrap">
        <h1 class="auth-title">Login / Sign Up</h1>
        <p class="auth-subtitle">
            Flow de l'app: Accueil -> Login/Sign Up -> Nouvel utilisateur ? -> Choisir role -> Connexion.
        </p>

        <div class="flow-line" aria-hidden="true">
            <span class="flow-step">Accueil</span>
            <span class="flow-arrow">-></span>
            <span class="flow-step">Login / Sign Up</span>
            <span class="flow-arrow">-></span>
            <span class="flow-step">Choisir role</span>
            <span class="flow-arrow">-></span>
            <span class="flow-step">Connexion reussie</span>
        </div>

        <?php if ($error !== ''): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="auth.php">
            <div class="form-group" style="margin-bottom:16px;">
                <label style="font-size:15px;">Nouvel utilisateur ?</label>
            </div>

            <div class="auth-grid">
                <label class="auth-choice <?= $mode === 'signup' ? 'active' : '' ?>" id="choice-signup">
                    <input type="radio" name="mode" value="signup" <?= $mode === 'signup' ? 'checked' : '' ?>/>
                    <strong>Oui - Sign Up</strong>
                    <small>Creer compte, choisir le role puis completer le profil.</small>
                </label>

                <label class="auth-choice <?= $mode === 'login' ? 'active' : '' ?>" id="choice-login">
                    <input type="radio" name="mode" value="login" <?= $mode === 'login' ? 'checked' : '' ?>/>
                    <strong>Non - Login</strong>
                    <small>Se connecter directement avec un compte existant.</small>
                </label>
            </div>

            <div class="auth-role-wrap">
                <label for="role">Choisir role: Agriculteur / Ouvrier</label>
                <select id="role" name="role" required>
                    <option value="agriculteur" <?= $role === 'agriculteur' ? 'selected' : '' ?>>Agriculteur</option>
                    <option value="ouvrier" <?= $role === 'ouvrier' ? 'selected' : '' ?>>Ouvrier</option>
                </select>
                <p class="auth-note">
                    Parcours actuel: <strong id="mode-title"><?= htmlspecialchars($mode_title) ?></strong>.
                    <span id="mode-note"><?= htmlspecialchars($mode_note) ?></span>
                </p>
            </div>

            <div class="auth-actions">
                <button type="submit" name="continuer" class="btn-submit" style="width:auto;padding:12px 26px;">Continuer</button>
                <a href="index.php" class="auth-back">Retour a l'accueil</a>
            </div>
        </form>
    </section>
</main>

<footer>
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

<script>
    (function () {
        var radios = document.querySelectorAll('input[name="mode"]');
        var signupCard = document.getElementById('choice-signup');
        var loginCard = document.getElementById('choice-login');
        var modeTitle = document.getElementById('mode-title');
        var modeNote = document.getElementById('mode-note');

        function refresh() {
            var selected = document.querySelector('input[name="mode"]:checked');
            var mode = selected ? selected.value : 'login';

            signupCard.classList.toggle('active', mode === 'signup');
            loginCard.classList.toggle('active', mode === 'login');

            if (mode === 'signup') {
                modeTitle.textContent = 'Sign Up';
                modeNote.textContent = 'Creer compte puis completer votre profil.';
            } else {
                modeTitle.textContent = 'Login';
                modeNote.textContent = 'Se connecter avec un compte existant.';
            }
        }

        radios.forEach(function (radio) {
            radio.addEventListener('change', refresh);
        });

        refresh();
    })();
</script>

</body>
</html>