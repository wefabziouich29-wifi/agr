<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Évaluer les ouvriers - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css"/>
    <style>
        .ouvrier-eval { background:var(--noir3); border:1px solid var(--gris2); border-radius:var(--radius); padding:24px; margin-bottom:24px; }
        .eval-form { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px; }
        .eval-form textarea, .eval-form input { background:var(--noir2); border:1px solid var(--gris2); color:var(--blanc); padding:12px; border-radius:var(--radius); font-family:inherit; }
        .eval-form textarea { grid-column:1/-1; }
        .btn-save { background:var(--vert); color:var(--blanc); border:none; padding:10px 20px; border-radius:var(--radius); cursor:pointer; font-weight:500; grid-column:1/-1; }
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
$offre_id = isset($_GET['offre_id']) ? (int)$_GET['offre_id'] : 0;

// Vérifier que l'offre appartient à l'agriculteur
$offre = $conn->prepare("SELECT * FROM offre WHERE id_offre = ? AND id_agriculteur = ?");
$offre->execute([$offre_id, $agri_id]);
$o = $offre->fetch(PDO::FETCH_ASSOC);

if (!$o || strtotime($o['date_fin']) > time()) {
    header('Location: mes_offres.php');
    exit;
}

// Traiter les évaluations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['evaluer'])) {
    $candidature_id = (int)$_POST['candidature_id'];
    $note = (int)$_POST['note'];
    $commentaire = trim($_POST['commentaire']);
    $remuneration = (float)$_POST['remuneration'];

    if ($note < 0 || $note > 10) {
        $erreur = 'La note doit être entre 0 et 10.';
    } else {
        $req = $conn->prepare("UPDATE candidature SET note = ?, commentaire = ?, remuneration = ? WHERE id_candidature = ?");
        $req->execute([$note, $commentaire, $remuneration, $candidature_id]);
        $success = 'Évaluation enregistrée!';
    }
}

$ouvriers = $conn->query("
    SELECT c.*, ou.prenom, ou.nom
    FROM candidature c
    JOIN ouvrier ou ON c.id_ouvrier = ou.id_ouvrier
    WHERE c.id_offre = $offre_id AND c.decision = 'accepte'
    ORDER BY ou.prenom, ou.nom
")->fetchAll(PDO::FETCH_ASSOC);

$fruit = $conn->prepare("SELECT * FROM type_fruit WHERE id_type_fruit = ?");
$fruit->execute([$o['id_type_fruit']]);
$f = $fruit->fetch(PDO::FETCH_ASSOC);
?>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="mes_offres.php">Mes Offres</a></li>
        <li><a href="logout.php" class="nav-btn">Déconnexion</a></li>
    </ul>
</nav>

<div style="padding: 100px 60px 60px;">
    <h1 style="font-size:32px;margin-bottom:10px;">Évaluer les ouvriers</h1>
    <p style="color:var(--gris);margin-bottom:32px;">Chantier <?= htmlspecialchars($f['libelle']) ?> - Du <?= date('d/m/Y', strtotime($o['date_debut'])) ?> au <?= date('d/m/Y', strtotime($o['date_fin'])) ?></p>

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

    <?php if (!empty($ouvriers)): ?>
        <?php foreach ($ouvriers as $ou): ?>
            <div class="ouvrier-eval">
                <div style="font-weight:700;font-size:18px;margin-bottom:16px;"><?= htmlspecialchars($ou['prenom'] . ' ' . $ou['nom']) ?></div>

                <?php if ($ou['note'] !== null): ?>
                    <div style="background:var(--noir2);padding:16px;border-radius:4px;margin-bottom:16px;">
                        <div style="color:var(--gris);font-size:12px;margin-bottom:4px;">Évaluation précédente</div>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:12px;">
                            <div>
                                <div style="font-weight:700;color:#fbbf24;">⭐ <?= $ou['note'] ?>/10</div>
                            </div>
                            <div>
                                <div style="color:var(--vert);">Rémunération: <?= $ou['remuneration'] ?> DT</div>
                            </div>
                        </div>
                        <?php if ($ou['commentaire']): ?>
                            <div style="font-size:13px;"><?= htmlspecialchars($ou['commentaire']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="margin-bottom:16px;">
                        <button onclick="document.getElementById('edit_<?= $ou['id_candidature'] ?>').style.display = 'grid'" style="background:var(--gris2);color:var(--blanc);border:none;padding:8px 16px;border-radius:4px;cursor:pointer;">Modifier</button>
                    </div>
                    <div id="edit_<?= $ou['id_candidature'] ?>" class="eval-form" style="display:none;">
                        <form method="post" action="evaluer_ouvrier.php?offre_id=<?= $offre_id ?>" style="display:contents;">
                            <input type="hidden" name="candidature_id" value="<?= $ou['id_candidature'] ?>"/>
                            <input type="number" name="note" min="0" max="10" value="<?= $ou['note'] ?>" placeholder="Note (0-10)" required/>
                            <input type="number" name="remuneration" step="0.01" min="0" value="<?= $ou['remuneration'] ?>" placeholder="Rémunération (DT)" required/>
                            <textarea name="commentaire" placeholder="Commentaires..."><?= htmlspecialchars($ou['commentaire'] ?? '') ?></textarea>
                            <button type="submit" name="evaluer" class="btn-save">Mettre à jour l'évaluation</button>
                        </form>
                    </div>
                <?php else: ?>
                    <form method="post" action="evaluer_ouvrier.php?offre_id=<?= $offre_id ?>" class="eval-form">
                        <input type="hidden" name="candidature_id" value="<?= $ou['id_candidature'] ?>"/>
                        <input type="number" name="note" min="0" max="10" placeholder="Note (0-10)" required/>
                        <input type="number" name="remuneration" step="0.01" min="0" placeholder="Rémunération (DT)" required/>
                        <textarea name="commentaire" placeholder="Commentaires sur le travail..."></textarea>
                        <button type="submit" name="evaluer" class="btn-save">Enregistrer l'évaluation</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="background:var(--noir3);padding:40px;border-radius:var(--radius);text-align:center;color:var(--gris);">
            Aucun ouvrier à évaluer pour cette offre.
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
