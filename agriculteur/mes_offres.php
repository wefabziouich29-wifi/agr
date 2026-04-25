<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Mes Offres - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css?v=4"/>
    <style>
        .offre-item { background:var(--noir3); border:1px solid var(--gris2); border-radius:var(--radius); padding:20px; margin-bottom:16px; }
        .offre-header { display:flex; justify-content:space-between; align-items:start; margin-bottom:16px; border-bottom:1px solid var(--gris2); padding-bottom:16px; }
        .offre-title { font-weight:700; font-size:18px; }
        .offre-status { padding:6px 12px; border-radius:4px; font-weight:500; font-size:12px; }
        .status-open { background:var(--vert); color:var(--blanc); }
        .status-closed { background:#94a3b8; color:var(--blanc); }
        .offre-actions { display:flex; gap:8px; }
        .btn-small { padding:8px 12px; font-size:12px; border:none; border-radius:4px; cursor:pointer; text-decoration:none; }
        .btn-primary { background:var(--vert); color:var(--blanc); }
        .btn-danger { background:#f87171; color:var(--blanc); }
        .btn-secondary { background:var(--gris2); color:var(--blanc); }
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

// Traiter suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer'])) {
    $offre_id = (int)$_POST['offre_id'];
    
    // Vérifier que l'offre appartient à l'agriculteur et n'a pas de postulants acceptés
    $check = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM offre 
        WHERE id_offre = ? AND id_agriculteur = ?
    ");
    $check->execute([$offre_id, $agri_id]);
    if ($check->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
        $candidatures = $conn->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre = ? AND decision != 'refuse'");
        $candidatures->execute([$offre_id]);
        if ($candidatures->fetchColumn() === 0) {
            $conn->prepare("DELETE FROM offre WHERE id_offre = ?")->execute([$offre_id]);
            $success = 'Offre supprimée avec succès.';
        } else {
            $erreur = 'Impossible de supprimer une offre avec des candidatures.';
        }
    }
}

$offres = $conn->query("
    SELECT o.*, t.libelle as fruit, g.libelle as gouvernorat,
           (SELECT COUNT(*) FROM candidature WHERE id_offre = o.id_offre AND decision = 'accepte') as acceptes,
           (SELECT COUNT(*) FROM candidature WHERE id_offre = o.id_offre AND decision = 'encours') as encours,
           (SELECT COUNT(*) FROM candidature WHERE id_offre = o.id_offre) as total_candidatures
    FROM offre o
    JOIN type_fruit t ON o.id_type_fruit = t.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    WHERE o.id_agriculteur = $agri_id
    ORDER BY o.date_debut DESC
")->fetchAll(PDO::FETCH_ASSOC);
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
    <h1 style="font-size:32px;margin-bottom:10px;">Mes Offres de Récolte</h1>
    <p style="color:var(--gris);margin-bottom:32px;">Gérez vos offres et postulants</p>

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

    <?php if (!empty($offres)): ?>
        <?php foreach ($offres as $o): ?>
            <div class="offre-item">
                <div class="offre-header">
                    <div>
                        <div class="offre-title"><?= htmlspecialchars($o['fruit']) ?> à <?= htmlspecialchars($o['gouvernorat']) ?></div>
                        <small style="color:var(--gris);"><?= htmlspecialchars($o['adresse']) ?></small>
                    </div>
                    <span class="offre-status <?= strtotime($o['date_limite']) > time() ? 'status-open' : 'status-closed' ?>">
                        <?= strtotime($o['date_limite']) > time() ? 'Ouverte' : 'Fermée' ?>
                    </span>
                </div>

                <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:16px;margin:16px 0;font-size:14px;">
                    <div>
                        <div style="color:var(--gris);font-size:12px;margin-bottom:4px;">Période</div>
                        <div><?= date('d/m/Y', strtotime($o['date_debut'])) ?> → <?= date('d/m/Y', strtotime($o['date_fin'])) ?></div>
                    </div>
                    <div>
                        <div style="color:var(--gris);font-size:12px;margin-bottom:4px;">Ouvriers</div>
                        <div><?= $o['acceptes'] ?>/<?= $o['nombre_ouvriers'] ?> engagés</div>
                    </div>
                    <div>
                        <div style="color:var(--gris);font-size:12px;margin-bottom:4px;">Prix à la journée</div>
                        <div style="color:var(--vert);"><?= $o['prix_journee'] ?> DT</div>
                    </div>
                    <div>
                        <div style="color:var(--gris);font-size:12px;margin-bottom:4px;">Candidatures</div>
                        <div><?= $o['total_candidatures'] ?> total (<?= $o['encours'] ?> en attente)</div>
                    </div>
                </div>

                <div class="offre-actions" style="justify-content:space-between;margin-top:16px;">
                    <div style="display:flex;gap:8px;">
                        <a href="voir_postulants.php?offre_id=<?= $o['id_offre'] ?>" class="btn-small btn-primary">Voir postulants (<?= $o['encours'] ?>)</a>
                        <?php if ($o['date_fin'] < date('Y-m-d')): ?>
                            <a href="evaluer_ouvrier.php?offre_id=<?= $o['id_offre'] ?>" class="btn-small btn-secondary">Évaluer</a>
                        <?php endif; ?>
                    </div>
                    <form method="post" action="mes_offres.php" style="display:inline;" onsubmit="return confirm('Confirmer la suppression?');">
                        <input type="hidden" name="offre_id" value="<?= $o['id_offre'] ?>"/>
                        <button type="submit" name="supprimer" class="btn-small btn-danger">Supprimer</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div style="background:var(--noir3);padding:40px;border-radius:var(--radius);text-align:center;color:var(--gris);">
            Vous n'avez pas encore publié d'offres.
            <br/><a href="ajouter_offre.php" style="color:var(--vert);text-decoration:none;font-weight:500;margin-top:8px;display:inline-block;">Créer une offre →</a>
        </div>
    <?php endif; ?>
</div>

<footer style="margin-top:60px;">
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

</body>
</html>
