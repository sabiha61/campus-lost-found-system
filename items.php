<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require "db.php";

$search = trim($_GET["search"] ?? "");
$post_type = $_GET["post_type"] ?? "";
$category_id = (int)($_GET["category_id"] ?? 0);

if (!in_array($post_type, ["Lost", "Found"], true)) {
    $post_type = "";
}

$sql = "SELECT items.*, categories.category_name
        FROM items
        LEFT JOIN categories
        ON items.category_id = categories.category_id
        WHERE LOWER(items.status) != 'returned'";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND (
        items.item_name LIKE ?
        OR items.description LIKE ?
        OR items.location LIKE ?
    )";

    $keyword = "%" . $search . "%";

    $params[] = $keyword;
    $params[] = $keyword;
    $params[] = $keyword;

    $types .= "sss";
}

if ($post_type !== "") {
    $sql .= " AND items.post_type = ?";
    $params[] = $post_type;
    $types .= "s";
}

if ($category_id > 0) {
    $sql .= " AND items.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}

$sql .= " ORDER BY items.created_at DESC";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database query error.");
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$items = $stmt->get_result();

$categories = $conn->query(
    "SELECT category_id, category_name
     FROM categories
     ORDER BY category_name"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Lost & Found Items | CampusFind</title>

    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container items-page">

    <h1>Lost & Found Items</h1>

    <form method="GET" action="items.php" class="items-filter">

        <input
            type="text"
            name="search"
            placeholder="Search item name or description..."
            value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
        >

        <select name="post_type">
            <option value="">All Items</option>

            <option value="Lost"
                <?= $post_type === "Lost" ? "selected" : "" ?>>
                Lost
            </option>

            <option value="Found"
                <?= $post_type === "Found" ? "selected" : "" ?>>
                Found
            </option>
        </select>

        <select name="category_id">
            <option value="0">All Categories</option>

            <?php if ($categories): ?>

                <?php while ($category = $categories->fetch_assoc()): ?>

                    <option
                        value="<?= (int)$category["category_id"] ?>"
                        <?= $category_id === (int)$category["category_id"] ? "selected" : "" ?>
                    >
                        <?= htmlspecialchars($category["category_name"], ENT_QUOTES, 'UTF-8') ?>
                    </option>

                <?php endwhile; ?>

            <?php endif; ?>
        </select>

        <button type="submit">Search</button>

        <a href="items.php" class="reset-btn">Reset</a>

    </form>

    <?php if ($items->num_rows > 0): ?>

        <div class="item-grid">

            <?php while ($item = $items->fetch_assoc()): ?>

                <div class="item-card">

                    <span class="item-badge <?= $item["post_type"] === "Lost" ? "lost-badge" : "found-badge" ?>">
                        <?= htmlspecialchars($item["post_type"], ENT_QUOTES, 'UTF-8') ?>
                    </span>

                    <h2>
                        <?= htmlspecialchars($item["item_name"], ENT_QUOTES, 'UTF-8') ?>
                    </h2>

                    <p>
                        <strong>Category:</strong>
                        <?= htmlspecialchars($item["category_name"] ?? "N/A", ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p>
                        <strong>Description:</strong>
                        <?= nl2br(htmlspecialchars($item["description"], ENT_QUOTES, 'UTF-8')) ?>
                    </p>

                    <p>
                        <strong>Location:</strong>
                        <?= htmlspecialchars($item["location"], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p>
                        <strong>Date:</strong>
                        <?= htmlspecialchars($item["item_date"], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                    <p>
                        <strong>Status:</strong>
                        <?= htmlspecialchars($item["status"], ENT_QUOTES, 'UTF-8') ?>
                    </p>

                </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>

        <div class="empty-state">
            <h3>No Items Found</h3>
            <p>Try another keyword or change your filters.</p>
        </div>

    <?php endif; ?>

    <p style="margin-top: 30px;">
        <a href="index.php" class="outline-btn">← Back to Home</a>
    </p>

</div>

</body>
</html>