<?php
session_start();
require "db.php";

// Check whether the user is logged in.
$isLoggedIn = isset($_SESSION["user_id"]);

// Search is available only to logged-in users.
$search = $isLoggedIn ? trim($_GET["search"] ?? "") : "";
$searchPattern = "%" . $search . "%";

// Default values for guests.
$lostItems = null;
$foundItems = null;
$queryError = false;
$firstSearchResult = true;

// Load items only when the user is logged in.
if ($isLoggedIn) {

    // Lost items query
    $lostQuery = $conn->prepare(
        "SELECT item_id, item_name, description, location, item_date, status
         FROM items
         WHERE post_type = 'Lost'
           AND LOWER(status) != 'returned'
           AND (
                item_name LIKE ?
                OR description LIKE ?
                OR location LIKE ?
           )
         ORDER BY created_at DESC"
    );

    if ($lostQuery) {
        $lostQuery->bind_param(
            "sss",
            $searchPattern,
            $searchPattern,
            $searchPattern
        );

        if ($lostQuery->execute()) {
            $lostItems = $lostQuery->get_result();
        } else {
            $queryError = true;
        }
    } else {
        $queryError = true;
    }

    // Found items query
    $foundQuery = $conn->prepare(
        "SELECT item_id, item_name, description, location, item_date, status
         FROM items
         WHERE post_type = 'Found'
           AND (
                item_name LIKE ?
                OR description LIKE ?
                OR location LIKE ?
           )
         ORDER BY created_at DESC"
    );

    if ($foundQuery) {
        $foundQuery->bind_param(
            "sss",
            $searchPattern,
            $searchPattern,
            $searchPattern
        );

        if ($foundQuery->execute()) {
            $foundItems = $foundQuery->get_result();
        } else {
            $queryError = true;
        }
    } else {
        $queryError = true;
    }
}

// Links according to login status.
$itemsLink = $isLoggedIn ? "#lost-items" : "login.php";
$foundLink = $isLoggedIn ? "#found-items" : "login.php";
$reportLink = $isLoggedIn ? "report.php" : "login.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Lost &amp; Found</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <!-- Header -->
    <header class="header">
        <div class="container navbar">

            <a href="index.php" class="logo">
                Campus<span>Find</span>
            </a>

            <nav class="nav-links">
               <a href="index.php" class="active">Home</a>
               <a href="#about">About</a>

               <a href="items.php">Browse All Items</a>

               <a href="<?= htmlspecialchars($itemsLink, ENT_QUOTES, 'UTF-8') ?>">
                   Lost Items
               </a>

                 <a href="<?= htmlspecialchars($foundLink, ENT_QUOTES, 'UTF-8') ?>">
                  Found Items
              </a>
            </nav>

            <div class="nav-buttons">

                <?php if ($isLoggedIn): ?>

                    <span class="login-btn">
                        Hi,
                        <?= htmlspecialchars($_SESSION["name"] ?? "Student", ENT_QUOTES, 'UTF-8') ?>
                    </span>

                    <a href="logout.php" class="register-btn">
                        Logout
                    </a>

                <?php else: ?>

                    <a href="login.php" class="login-btn">
                        Login
                    </a>

                    <a href="register.php" class="register-btn">
                        Register
                    </a>

                <?php endif; ?>

            </div>

        </div>
    </header>

    <main>

        <!-- Hero Section -->
        <section class="hero">
            <div class="container hero-content">

                <span class="hero-tag">
                    YOUR CAMPUS, YOUR COMMUNITY
                </span>

                <h1>
                    Lost Something?<br>
                    <span>Let's Find It Together.</span>
                </h1>

                <p>
                    Lost your ID card, phone, books, or other belongings?
                    Our campus lost and found platform helps students
                    report lost items and share found belongings easily.
                </p>

                <!-- Search Section -->

                <?php if ($isLoggedIn): ?>

                    <form action="index.php#search-results"
                          method="GET"
                          class="home-search">

                        <span class="search-icon">⌕</span>

                        <input
                            type="text"
                            name="search"
                            placeholder="What are you looking for?"
                            value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>"
                            required
                        >

                        <button type="submit">
                            Search →
                        </button>

                        <?php if ($search !== ""): ?>

                            <a href="index.php" class="clear-search">
                                Clear
                            </a>

                        <?php endif; ?>

                    </form>

                <?php else: ?>

                    <div class="hero-buttons">

                        <a href="login.php" class="primary-btn">
                            Login to Search Items
                        </a>

                        <a href="register.php" class="secondary-btn">
                            Create Account
                        </a>

                    </div>

                <?php endif; ?>

              <?php if ($isLoggedIn): ?>

    <div class="hero-buttons">

        <a href="report.php" class="primary-btn">
            Report an Item
        </a>

        <a href="#found-items" class="secondary-btn">
            Explore Found Items
        </a>

    </div>

<?php endif; ?>

                <div class="hero-note">
                    <span class="note-dot"></span>
                    Helping students reconnect with their belongings
                </div>

            </div>
        </section>

        <!-- Features Section -->
        <section class="features section-padding" id="about">

            <div class="container">

                <div class="section-heading">

                    <span class="eyebrow">HOW IT WORKS</span>

                    <h2>A Smarter Way to Find Your Belongings</h2>

                    <p>
                        A simple and organized platform that connects
                        students and helps return lost belongings.
                    </p>

                </div>

                <div class="feature-grid">

                    <article class="feature-card">

                        <div class="feature-icon lost-icon">⌕</div>

                        <h3>Report Lost Items</h3>

                        <p>
                            Report belongings you have lost on campus
                            with their details and last known location.
                        </p>

                        <a href="<?= htmlspecialchars($reportLink, ENT_QUOTES, 'UTF-8') ?>">
                            Report a lost item →
                        </a>

                    </article>

                    <article class="feature-card">

                        <div class="feature-icon found-icon">✓</div>

                        <h3>Share Found Items</h3>

                        <p>
                            Help other students by sharing details of
                            belongings you have found on campus.
                        </p>

                        <a href="<?= htmlspecialchars($reportLink, ENT_QUOTES, 'UTF-8') ?>">
                            Share a found item →
                        </a>

                    </article>

                    <article class="feature-card">

                        <div class="feature-icon safe-icon">⌕</div>

                        <h3>Find Your Belongings</h3>

                        <p>
                            Browse lost and found reports to identify
                            items that may belong to you.
                        </p>

                        <a href="<?= htmlspecialchars($foundLink, ENT_QUOTES, 'UTF-8') ?>">
                            Explore items →
                        </a>

                    </article>

                </div>

            </div>

        </section>

        <!-- Lost Items Section -->
        <section class="items-section section-padding" id="lost-items">

            <div class="container">

                <div class="items-heading">

                    <div>

                        <span class="eyebrow">NEED HELP?</span>

                        <h2>Lost Items</h2>

                        <p>
                            Check lost item reports or let others know
                            what you have misplaced.
                        </p>

                    </div>

                <?php if ($isLoggedIn): ?>
                     <a href="report.php" class="outline-btn">
                      + Report Lost Item
                     </a>
                <?php endif; ?>

                </div>

                <div class="item-grid">

                    <?php if (!$isLoggedIn): ?>

                        <div class="empty-state">

                            <h3>Login Required</h3>

                            <p>
                                Please log in to view lost item reports.
                            </p>

                            <a href="login.php" class="primary-btn">
                                Login
                            </a>

                        </div>

                    <?php elseif ($queryError): ?>

                        <div class="empty-state">

                            <h3>Unable to Load Items</h3>

                            <p>
                                Something went wrong while loading the items.
                                Please try again.
                            </p>

                        </div>

                    <?php elseif ($lostItems && $lostItems->num_rows > 0): ?>

                        <?php while ($item = $lostItems->fetch_assoc()): ?>

                            <article
                                class="item-card"
                                <?php if ($search !== "" && $firstSearchResult): ?>
                                    id="search-results"
                                <?php endif; ?>
                            >

                                <?php
                                if ($search !== "" && $firstSearchResult) {
                                    $firstSearchResult = false;
                                }
                                ?>

                                <span class="item-badge lost-badge">
                                    LOST
                                </span>

                                <h3>
                                    <?= htmlspecialchars($item["item_name"], ENT_QUOTES, 'UTF-8') ?>
                                </h3>

                                <p>
                                    <?= htmlspecialchars($item["description"], ENT_QUOTES, 'UTF-8') ?>
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

                            </article>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <div class="empty-state">

                            <div class="empty-icon">⌕</div>

                            <h3>
                                <?= $search !== "" ? "No Matching Lost Items" : "No Lost Items Yet" ?>
                            </h3>

                            <p>
                                <?= $search !== "" ? "Try searching with another keyword." : "Lost item reports will appear here." ?>
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </section>

        <!-- Found Items Section -->
        <section class="items-section found-section section-padding"
                 id="found-items">

            <div class="container">

                <div class="items-heading">

                    <div>

                        <span class="eyebrow">HELP SOMEONE TODAY</span>

                        <h2>Found Items</h2>

                        <p>
                            Explore items found by students around
                            your campus.
                        </p>

                    </div>

                    <?php if ($isLoggedIn): ?>
                     <a href="report.php" class="outline-btn">
                       + Report Found Item
                     </a>
                    <?php endif; ?>

                </div>

                <div class="item-grid">

                    <?php if (!$isLoggedIn): ?>

                        <div class="empty-state">

                            <h3>Login Required</h3>

                            <p>
                                Please log in to view found item reports.
                            </p>

                            <a href="login.php" class="primary-btn">
                                Login
                            </a>

                        </div>

                    <?php elseif ($queryError): ?>

                        <div class="empty-state">

                            <h3>Unable to Load Items</h3>

                            <p>
                                Something went wrong while loading the items.
                                Please try again.
                            </p>

                        </div>

                    <?php elseif ($foundItems && $foundItems->num_rows > 0): ?>

                        <?php while ($item = $foundItems->fetch_assoc()): ?>

                            <article
                                class="item-card"
                                <?php if ($search !== "" && $firstSearchResult): ?>
                                    id="search-results"
                                <?php endif; ?>
                            >

                                <?php
                                if ($search !== "" && $firstSearchResult) {
                                    $firstSearchResult = false;
                                }
                                ?>

                                <span class="item-badge found-badge">
                                    FOUND
                                </span>

                                <h3>
                                    <?= htmlspecialchars($item["item_name"], ENT_QUOTES, 'UTF-8') ?>
                                </h3>

                                <p>
                                    <?= htmlspecialchars($item["description"], ENT_QUOTES, 'UTF-8') ?>
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

                            </article>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <div class="empty-state">

                            <div class="empty-icon">✓</div>

                            <h3>
                                <?= $search !== "" ? "No Matching Found Items" : "No Found Items Yet" ?>
                            </h3>

                            <p>
                                <?= $search !== "" ? "Try searching with another keyword." : "Found item reports will appear here." ?>
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </section>

        <!-- CTA Section -->
        <section class="cta-section">

            <div class="container cta-content">

                <div>

                    <span class="eyebrow">BE PART OF THE SOLUTION</span>

                    <h2>Found something? Make someone's day.</h2>

                    <p>
                        Your small act of kindness can help another
                        student recover something important.
                    </p>

                </div>

               <?php if ($isLoggedIn): ?>
                 <a href="report.php" class="primary-btn">
                       Report an Item
                 </a>
              <?php endif; ?>

            </div>

        </section>

    </main>

    <!-- Footer -->
    <footer class="footer">

        <div class="container footer-content">

            <a href="index.php" class="logo footer-logo">
                Campus<span>Find</span>
            </a>

            <p>Connecting lost belongings with their owners.</p>

            <p class="copyright">
                &copy; 2026 CampusFind. All rights reserved.
            </p>

        </div>

    </footer>

</body>
</html>