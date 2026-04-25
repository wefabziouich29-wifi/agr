<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Offres de Récolte - Uber-Cueillette</title>
    <link rel="stylesheet" href="../css/style.css?v=4"/>
    <style>
        .filters { display:grid; grid-template-columns:repeat(3, 1fr); gap:16px; margin-bottom:32px; }
        .filters select, .filters input { background:var(--noir3); border:1px solid var(--gris2); color:var(--blanc); padding:10px; border-radius:var(--radius); }
        .offers-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:24px; }
        .offer-card { background:var(--noir3); border:1px solid var(--gris2); border-radius:var(--radius); padding:24px; transition:all var(--transition); }
        .offer-card:hover { border-color:var(--vert); }
        .offer-header { display:flex; justify-content:space-between; align-items:start; margin-bottom:16px; }
        .offer-fruit { font-weight:700; font-size:16px; }
        .offer-price { color:var(--vert); font-weight:700; font-size:18px; }
        .offer-info { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin:16px 0; font-size:13px; color:var(--gris); }
        .btn-postuler { background:var(--vert); color:var(--blanc); border:none; padding:10px 20px; border-radius:var(--radius); cursor:pointer; font-weight:500; margin-top:16px; width:100%; }
        .btn-postuler:hover { background:var(--vert-clair); }
        .already-applied { background:#94a3b8; cursor:default; }
        .already-applied:hover { background:#94a3b8; }
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

// Récupérer listes pour filtres
$fruits_raw = $conn->query("SELECT id_type_fruit, libelle FROM type_fruit ORDER BY libelle")->fetchAll(PDO::FETCH_ASSOC);
$gouvernorats_raw = $conn->query("SELECT id_gouvernorat, libelle FROM gouvernorat ORDER BY libelle")->fetchAll(PDO::FETCH_ASSOC);

$normalize_label = static function ($label) {
    $label = trim((string)$label);
    return function_exists('mb_strtolower') ? mb_strtolower($label, 'UTF-8') : strtolower($label);
};

$fruits = [];
$fruit_groups = [];
$fruit_id_to_group = [];
foreach ($fruits_raw as $fruit_row) {
    $label = trim((string)$fruit_row['libelle']);
    if ($label === '') {
        continue;
    }

    $id = (int)$fruit_row['id_type_fruit'];
    $key = $normalize_label($label);

    if (!isset($fruit_groups[$key])) {
        $fruit_groups[$key] = [];
        $fruits[] = [
            'id_type_fruit' => $id,
            'libelle' => $label
        ];
    }

    $fruit_groups[$key][] = $id;
    $fruit_id_to_group[$id] = $key;
}

$gouvernorats = [];
$gouvernorat_groups = [];
$gouvernorat_id_to_group = [];
foreach ($gouvernorats_raw as $gouvernorat_row) {
    $label = trim((string)$gouvernorat_row['libelle']);
    if ($label === '') {
        continue;
    }

    $id = (int)$gouvernorat_row['id_gouvernorat'];
    $key = $normalize_label($label);

    if (!isset($gouvernorat_groups[$key])) {
        $gouvernorat_groups[$key] = [];
        $gouvernorats[] = [
            'id_gouvernorat' => $id,
            'libelle' => $label
        ];
    }

    $gouvernorat_groups[$key][] = $id;
    $gouvernorat_id_to_group[$id] = $key;
}

// Filtres
$fruit_filter = isset($_GET['fruit']) ? (int)$_GET['fruit'] : 0;
$gouvernorat_filter = isset($_GET['gouvernorat']) ? (int)$_GET['gouvernorat'] : 0;
$prix_filter = isset($_GET['prix']) ? (int)$_GET['prix'] : 0;

$fruit_filter_ids = [];
if ($fruit_filter > 0) {
    if (isset($fruit_id_to_group[$fruit_filter])) {
        $fruit_filter_ids = $fruit_groups[$fruit_id_to_group[$fruit_filter]];
    } else {
        $fruit_filter_ids = [$fruit_filter];
    }
}

$gouvernorat_filter_ids = [];
if ($gouvernorat_filter > 0) {
    if (isset($gouvernorat_id_to_group[$gouvernorat_filter])) {
        $gouvernorat_filter_ids = $gouvernorat_groups[$gouvernorat_id_to_group[$gouvernorat_filter]];
    } else {
        $gouvernorat_filter_ids = [$gouvernorat_filter];
    }
}

$query = "
    SELECT o.*, t.libelle as fruit, g.libelle as gouvernorat, 
           (SELECT COUNT(*) FROM candidature WHERE id_offre = o.id_offre AND decision = 'accepte') as acceptes
    FROM offre o
    JOIN type_fruit t ON o.id_type_fruit = t.id_type_fruit
    JOIN gouvernorat g ON o.id_gouvernorat = g.id_gouvernorat
    WHERE o.date_limite > NOW()
";

if (!empty($fruit_filter_ids)) {
    $query .= " AND o.id_type_fruit IN (" . implode(',', array_map('intval', $fruit_filter_ids)) . ")";
}
if (!empty($gouvernorat_filter_ids)) {
    $query .= " AND o.id_gouvernorat IN (" . implode(',', array_map('intval', $gouvernorat_filter_ids)) . ")";
}
if ($prix_filter > 0) $query .= " AND o.prix_journee <= $prix_filter";

$query .= " ORDER BY o.date_limite ASC";
$offres = $conn->query($query)->fetchAll(PDO::FETCH_ASSOC);

// Récupérer mes candidatures
$mes_candidatures = $conn->query("SELECT id_offre FROM candidature WHERE id_ouvrier = $ouvrier_id")->fetchAll(PDO::FETCH_ASSOC);
$offre_ids_postulees = array_map(fn($c) => $c['id_offre'], $mes_candidatures);
?>

<nav>
    <a href="../index.php" class="nav-logo">Uber<span>Cueillette</span></a>
    <ul class="nav-links">
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="liste_offres.php">Offres</a></li>
        <li><a href="mes_candidatures.php">Mes Candidatures</a></li>
        <li><a href="mes_chantiers.php">Mes Chantiers</a></li>
        <li><a href="profil.php">Mon Profil</a></li>
        <li><a href="logout.php" class="nav-btn">Déconnexion</a></li>
    </ul>
</nav>

<div style="padding: 100px 60px 60px;">
    <h1 style="font-size:32px;margin-bottom:10px;">Offres de Récolte</h1>
    <p style="color:var(--gris);margin-bottom:32px;">Trouvez et postulez aux offres qui vous intéressent</p>

    <form method="get" action="liste_offres.php">
        <div class="filters">
            <select name="fruit">
                <option value="0">-- Tous les fruits --</option>
                <?php foreach ($fruits as $f): ?>
                    <option value="<?= $f['id_type_fruit'] ?>" <?= $fruit_filter == $f['id_type_fruit'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($f['libelle']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="gouvernorat">
                <option value="0">-- Tous les gouvernorats --</option>
                <?php foreach ($gouvernorats as $g): ?>
                    <option value="<?= $g['id_gouvernorat'] ?>" <?= $gouvernorat_filter == $g['id_gouvernorat'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['libelle']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div style="display:flex;gap:8px;">
                <input type="number" name="prix" placeholder="Prix max (DT/jour)" value="<?= $prix_filter ?: '' ?>"/>
                <button type="submit" style="background:var(--vert);color:var(--blanc);border:none;padding:10px 20px;border-radius:var(--radius);cursor:pointer;font-weight:500;">Filtrer</button>
            </div>
        </div>
    </form>

    <div class="offers-grid">
        <?php if (!empty($offres)): ?>
            <?php foreach ($offres as $o): ?>
                <div class="offer-card">
                    <div class="offer-header">
                        <span class="offer-fruit"><?= htmlspecialchars($o['fruit']) ?></span>
                        <span class="offer-price"><?= $o['prix_journee'] ?> DT</span>
                    </div>
                    <div style="color:var(--gris);font-size:13px;"><?= htmlspecialchars($o['gouvernorat']) ?> • <?= htmlspecialchars($o['adresse']) ?></div>
                    
                    <div class="offer-info">
                        <div>📅 Début: <?= date('d/m/Y', strtotime($o['date_debut'])) ?></div>
                        <div>📅 Fin: <?= date('d/m/Y', strtotime($o['date_fin'])) ?></div>
                        <div>👥 Ouvriers: <?= $o['nombre_ouvriers'] ?></div>
                        <div>✓ Acceptés: <?= $o['acceptes'] ?>/<?= $o['nombre_ouvriers'] ?></div>
                    </div>

                    <small style="color:#fbbf24;">Limite de candidature: <?= date('d/m/Y', strtotime($o['date_limite'])) ?></small>

                    <?php if (in_array($o['id_offre'], $offre_ids_postulees)): ?>
                        <button class="btn-postuler already-applied" disabled>✓ Vous avez postuler</button>
                    <?php elseif ($o['acceptes'] >= $o['nombre_ouvriers']): ?>
                        <button class="btn-postuler" style="background:#94a3b8;" disabled>Offre complète</button>
                    <?php else: ?>
                        <form method="post" action="postuler.php" style="margin-top:16px;">
                            <input type="hidden" name="offre_id" value="<?= $o['id_offre'] ?>"/>
                            <button type="submit" class="btn-postuler">Postuler</button>
                        </form>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--gris);">
                Aucune offre disponible avec ces critères.
            </div>
        <?php endif; ?>
    </div>
</div>

<footer style="margin-top:60px;">
    <p>&copy; 2026 UberCueillette -- ISG Tunis</p>
    <p>Projet Web2</p>
</footer>

</body>
</html>
