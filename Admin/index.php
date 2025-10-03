<?php
include 'partials/header.php';
require_once __DIR__ . '/models/CategoryModel.php';
$categories = CategoryModel::all($conn);
?>

<h1>Manage PDFs</h1>

<?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div id="successMsg" style="background:#d1fae5;color:#065f46;padding:10px 15px;border:1px solid #10b981;border-radius:6px;margin-bottom:15px;font-weight:bold;text-align:center;">
        ✅ E-book uploaded successfully!
    </div>
    <script>
        setTimeout(() => document.getElementById("successMsg").style.display = "none", 3000);
    </script>
<?php endif; ?>


<div class="filter-bar">
    <label for="categoryFilter">Filter by Category:</label>
    <select id="categoryFilter">
        <option value="all">All</option>
        <?php foreach ($categories as $id => $name): ?>
            <option value="<?= (int)$id ?>"><?= htmlspecialchars($name) ?></option>
        <?php endforeach; ?>
    </select>
</div>
<style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        padding: 10px;
        border: 1px solid #ccc;
    }


    td:first-child {
        text-align: left;
    }


    th {
        background-color: #4f46e5;
        color: white;
        text-align: center;
    }

    tbody tr:nth-child(odd) {
        background-color: #ffffff;
    }

    tbody tr:nth-child(even) {
        background-color: #e9e8ffff;
    }
</style>

<table>
    <thead>
        <tr>
            <th>Description</th>
            <th>Original Price</th>
            <th>Offered Price</th>
            <!-- <th>Category</th> -->
            <th>Preview</th>
            <th>PDF</th>
            <th>Edit</th>
        </tr>
    </thead>
    <tbody>
        <!-- <tr>
            <form action="upload.php" method="POST" enctype="multipart/form-data">
                <td><input type="text" name="title" required></td>
                <td><input type="number" name="regular_price" required></td>
                <td><input type="number" name="offer_price" required></td>
                <!-- <td>
                    <select name="category_id" required>
                        <option value="">--Select--</option>
                        <?php foreach ($categories as $id => $name): ?>
                            <option value="<?= (int)$id ?>"><?= htmlspecialchars($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td> -->
        <!-- <td>
                    <label class="upload-box">
                        <input type="file" name="thumbnail" accept="image/*" required onchange="previewImage(event)">
                        <span>📷 Upload Thumbnail</span>
                    </label>
                    <div id="thumbPreview" class="preview-box"></div>
                </td>
                <td>
                    <label class="upload-box">
                        <input type="file" name="pdf" accept="application/pdf" required onchange="previewPDF(event)">
                        <span>📄 Upload PDF</span>
                    </label>
                    <div id="pdfPreview" class="preview-box"></div>
                </td>
                <td><button type="submit">Upload</button></td>
            </form>
        </tr> -->

        <?php
        $stmt = $conn->query("
      SELECT e.id, e.title, e.regular_price, e.offer_price, e.thumbnail, e.pdf, c.id as cat_id,
            COALESCE(c.name, '-') AS category
      FROM ebooks e
      LEFT JOIN categories c ON e.category_id = c.id
      ORDER BY c.id ASC
    ");
        while ($row = $stmt->fetch_assoc()):
        ?>
            <tr data-category="<?= htmlspecialchars($row['cat_id']); ?>">
                <td><?= htmlspecialchars($row['title']); ?></td>
                <td>₹<?= htmlspecialchars($row['regular_price']); ?></td>
                <td>₹<?= htmlspecialchars($row['offer_price']); ?></td>
                <!-- <td><?= htmlspecialchars($row['category']); ?></td> -->
                <td><?php if ($row['thumbnail']): ?><img src="<?= htmlspecialchars($row['thumbnail']); ?>"><?php endif; ?></td>
                <td><?php if ($row['pdf']): ?><a href="<?= htmlspecialchars($row['pdf']); ?>" target="_blank">View</a><?php endif; ?></td>
                <td><a href="edit.php?id=<?= (int)$row['id']; ?>">✏️ Edit</a></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
    document.getElementById('categoryFilter').addEventListener('change', function() {
        const selected = this.value;
        document.querySelectorAll('table tbody tr').forEach(row => {
            const rowCat = row.getAttribute('data-category');
            if (rowCat !== null) {
                row.style.display = (selected === "all" || rowCat === selected) ? "" : "none";
            }
        });
    });

    function previewImage(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith("image/")) {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById("thumbPreview").innerHTML = `<img src="${e.target.result}" style="max-width:80px; max-height:100px;">`;
            };
            reader.readAsDataURL(file);
        }
    }

    function previewPDF(event) {
        const file = event.target.files[0];
        if (file && file.type === "application/pdf") {
            const reader = new FileReader();
            reader.onload = e => {
                document.getElementById("pdfPreview").innerHTML = `<embed src="${e.target.result}" width="120" height="150">`;
            };
            reader.readAsDataURL(file);
        }
    }
</script>

<?php include '../partials/footer.php'; ?>