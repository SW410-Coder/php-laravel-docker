<?php
$config = include 'config.php';

$dbConfig = $config['database'];
$mysqli = new mysqli($dbConfig['host'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$serverConfig = $config['server'];
$arenas = $config['arenas'];


$validFilters = array_keys($arenas);
$filter = isset($_GET['filter']) && in_array($_GET['filter'], $validFilters) ? $_GET['filter'] : 'global_elo';

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

$query = "SELECT uuid, username, global_elo, kills, deaths FROM stats ORDER BY $filter DESC LIMIT $start, $limit";
$result = $mysqli->query($query);

if ($result === false) {
    die("Error in query: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($serverConfig['name']) ?> Practice Stats</title>
    <link rel="stylesheet" href="stylesheet.css">
</head>
<body>

<!-- Header -->
<div class="page-header-wrapper"
     style="background-image: url('header_background.jpg');">
    <div class="page-header-container">
        <div class="page-navigation">
            <nav class="navbar navbar-expand-lg py-3">
                <div class="container container-small">
                    <h1><?= htmlspecialchars($serverConfig['name']) ?></h1>
                    <ul class="navbar-nav w-100 justify-content-around text-uppercase custom-spacing fw-medium fs-4">
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($serverConfig['url']) ?>">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($serverConfig['store_url']) ?>">Store</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($serverConfig['staff_url']) ?>">Staff</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= htmlspecialchars($serverConfig['support_url']) ?>">Support</a>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</div>

<div class="main-content-wrapper">
    <!-- Your original content starts here -->
    <h1>Stats</h1>

    <!-- Arena filter buttons -->
    <div class="arena-filters">
        <?php foreach ($arenas as $arenaKey => $arenaName): ?>
            <button onclick="location.href='?filter=<?= htmlspecialchars($arenaKey) ?>'"><?= htmlspecialchars($arenaName) ?></button>
        <?php endforeach; ?>
    </div>

    <form method="get">
        <label for="filter">Filter by:</label>
        <select name="filter" id="filter" onchange="this.form.submit()">
            <option value="global_elo" <?= $filter == 'global_elo' ? 'selected' : '' ?>>ELO</option>
            <option value="kills" <?= $filter == 'kills' ? 'selected' : '' ?>>Kills</option>
            <option value="deaths" <?= $filter == 'deaths' ? 'selected' : '' ?>>Deaths</option>
        </select>
    </form>
    <table border="1">
        <thead>
        <tr>
            <th>Rank</th>
            <th>Username</th>
            <th>ELO</th>
            <th>Kills</th>
            <th>Deaths</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $rank = $start + 1;
        while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td class="rank <?= $rank == 1 ? 'gold' : ($rank == 2 ? 'silver' : ($rank == 3 ? 'bronze' : '')) ?>">
                    #<?= $rank ?></td>
                <td><img src="https://minotar.net/avatar/<?= htmlspecialchars($row['uuid']) ?>"
                         alt="Player Head"> <?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['global_elo']) ?></td>
                <td><?= htmlspecialchars($row['kills']) ?></td>
                <td><?= htmlspecialchars($row['deaths']) ?></td>
            </tr>
            <?php
            $rank++;
        endwhile; ?>
        </tbody>
    </table>
    <div>
        <?php
        // Pagination links
        $total_results_query = $mysqli->query("SELECT COUNT(*) AS count FROM stats");
        if ($total_results_query === false) {
            die("Error in pagination query: " . $mysqli->error);
        }
        $total_results = $total_results_query->fetch_assoc()['count'];
        $total_pages = ceil($total_results / $limit);

        for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?page=<?= $i ?>&filter=<?= htmlspecialchars($filter) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>
</body>
</html>

<?php
$mysqli->close();
?>
