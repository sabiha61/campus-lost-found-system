<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require "db.php";

$message = "";
$messageType = "";

$selectedType = $_GET["type"] ?? "";

if (!in_array($selectedType, ["Lost", "Found"], true)) {
    $selectedType = "";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $item_name = trim($_POST["item_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $item_date = $_POST["item_date"] ?? "";
    $post_type = $_POST["post_type"] ?? "";
    $category_id = (int)($_POST["category_id"] ?? 0);
    $user_id = (int)$_SESSION["user_id"];

    if (
        $item_name === "" ||
        $description === "" ||
        $location === "" ||
        $item_date === "" ||
        !in_array($post_type, ["Lost", "Found"], true) ||
        $category_id <= 0
    ) {
        $message = "Please fill in all fields correctly.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO items
            (item_name, description, location, item_date,
             post_type, category_id, user_id, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Active')"
        );

        if ($stmt) {

            $stmt->bind_param(
                "sssssii",
                $item_name,
                $description,
                $location,
                $item_date,
                $post_type,
                $category_id,
                $user_id
            );

            if ($stmt->execute()) {
                $message = "Item report submitted successfully!";
                $messageType = "success";
                $selectedType = $post_type;
            } else {
                $message = "Could not submit the report.";
                $messageType = "error";
            }

            $stmt->close();

        } else {
            $message = "Database query could not be prepared.";
            $messageType = "error";
        }
    }
}

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

    <title>Report an Item | CampusFind</title>

    <link rel="stylesheet" href="style.css">

    <style>
        .report-wrapper {
            max-width: 650px;
            margin: 50px auto;
            padding: 35px;
            background: #ffffff;
            border-radius: 14px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }

        .report-wrapper h1 {
            margin-bottom: 10px;
        }

        .report-wrapper .subtitle {
            margin-bottom: 25px;
            color: #666;
        }

        .report-form label {
            display: block;
            margin: 15px 0 7px;
            font-weight: 600;
        }

        .report-form input,
        .report-form select,
        .report-form textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 7px;
            font-size: 15px;
            box-sizing: border-box;
        }

        .report-form textarea {
            min-height: 110px;
            resize: vertical;
        }

        .report-form button {
            margin-top: 22px;
            border: none;
            cursor: pointer;
        }

        .report-message {
            padding: 13px;
            border-radius: 7px;
            margin: 18px 0;
        }

        .success-message {
            background: #e5f7eb;
            color: #176b35;
        }

        .error-message {
            background: #fde8e8;
            color: #a32222;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="container">

    <div class="report-wrapper">

        <h1>Report a Lost or Found Item</h1>

        <p class="subtitle">
            Help your campus community find lost belongings.
        </p>

        <?php if ($message !== ""): ?>

            <div class="report-message <?= $messageType === "success"
                ? "success-message"
                : "error-message" ?>">

                <?= htmlspecialchars($message) ?>

            </div>

        <?php endif; ?>

        <form method="POST" action="report.php" class="report-form">

            <label for="item_name">Item Name</label>

            <input
                id="item_name"
                type="text"
                name="item_name"
                maxlength="100"
                required
            >

            <label for="post_type">Report Type</label>

            <select id="post_type" name="post_type" required>

                <option value="">Select type</option>

                <option value="Lost"
                    <?= $selectedType === "Lost" ? "selected" : "" ?>>
                    Lost
                </option>

                <option value="Found"
                    <?= $selectedType === "Found" ? "selected" : "" ?>>
                    Found
                </option>

            </select>

            <label for="category_id">Category</label>

            <select id="category_id" name="category_id" required>

                <option value="">Select category</option>

                <?php if ($categories): ?>

                    <?php while ($category = $categories->fetch_assoc()): ?>

                        <option value="<?= (int)$category["category_id"] ?>">
                            <?= htmlspecialchars($category["category_name"]) ?>
                        </option>

                    <?php endwhile; ?>

                <?php endif; ?>

            </select>

            <label for="description">Description</label>

            <textarea
                id="description"
                name="description"
                required
            ></textarea>

            <label for="location">Location</label>

            <input
                id="location"
                type="text"
                name="location"
                maxlength="150"
                required
            >

            <label for="item_date">Date Lost or Found</label>

            <input
                id="item_date"
                type="date"
                name="item_date"
                required
            >

            <button type="submit" class="primary-btn">
                Submit Report
            </button>

        </form>

        <a href="index.php" class="back-link">
            ← Back to Home
        </a>

    </div>

</div>

</body>
</html>