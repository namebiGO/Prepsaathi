<?php
include 'Admin/db.php';

// Get category ID from URL
$category_id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Fetch category name
$cat_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
$cat_stmt->bind_param("i", $category_id);
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();
$category = $cat_result->fetch_assoc();

if (!$category) {
    die("Category not found!");
}

// Fetch PDFs for this category
$stmt = $conn->prepare("SELECT * FROM ebooks WHERE category_id = ? ORDER BY id DESC");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($category['name']); ?> - eBooks</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .pdf-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px;
        }

        .pdf-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            background: #fff;
            text-align: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .pdf-card img {
            max-width: 100%;
            height: auto;
        }

        .pdf-card h3 {
            font-size: 18px;
            margin: 10px 0;
        }

        .price {
            font-size: 16px;
            color: #333;
        }

        .offer-price {
            color: green;
            font-weight: bold;
        }

        .view-btn {
            display: inline-block;
            margin-top: 8px;
            padding: 8px 12px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }

        .view-btn:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>

    <h1 style="text-align:center; margin:20px 0;"><?= htmlspecialchars($category['name']); ?> eBooks</h1>

    <div class="pdf-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="pdf-card">
                <?php if (!empty($row['thumbnail'])): ?>
                    <img src="Admin/<?= htmlspecialchars($row['thumbnail']); ?>" alt="Thumbnail">
                <?php else: ?>
                    <img src="placeholder.png" alt="No Thumbnail">
                <?php endif; ?>

                <h3><?= htmlspecialchars($row['title']); ?></h3>
                <p class="price">
                    <del>₹<?= htmlspecialchars($row['regular_price']); ?></del>
                    <span class="offer-price">₹<?= htmlspecialchars($row['offer_price']); ?></span>
                </p>
                <?php if (!empty($row['pdf'])): ?>
                    <a href="Admin/<?= htmlspecialchars($row['pdf']); ?>" target="_blank" class="view-btn">View PDF</a>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>

</body>

</html>