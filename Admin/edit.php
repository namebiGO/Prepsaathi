<?php
include 'auth_check.php';
include 'db.php';

$categories = [];
$res = $conn->query("SELECT id, name FROM categories ORDER BY id");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $categories[$row['id']] = $row['name'];
    }
}

if (!isset($_GET['id'])) {
    die("Invalid request");
}

$id = (int)$_GET['id'];

$stmt = $conn->prepare("SELECT * FROM ebooks WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$ebook = $result->fetch_assoc();

if (!$ebook) {
    die("PDF not found");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $regular_price = $_POST['regular_price'];
    $offer_price = $_POST['offer_price'];
    $category_id = (int)$_POST['category_id'];

    $thumbPath = $ebook['thumbnail'];
    $previewPath = $ebook['preview_image'];
    $pdfPath = $ebook['pdf'];

    if (!empty($_FILES['thumbnail']['name'])) {
        $thumbName = time() . "_" . basename($_FILES['thumbnail']['name']);
        $thumbPath = "uploads/" . $thumbName;
        move_uploaded_file($_FILES['thumbnail']['tmp_name'], $thumbPath);
    }

    if (!empty($_FILES['preview_image']['name'])) {
        $previewName = time() . "_preview_" . basename($_FILES['preview_image']['name']);
        $previewPath = "uploads/" . $previewName;
        move_uploaded_file($_FILES['preview_image']['tmp_name'], $previewPath);
    }

    if (!empty($_FILES['pdf']['name'])) {
        $pdfName = time() . "_" . basename($_FILES['pdf']['name']);
        $pdfPath = "uploads/" . $pdfName;
        move_uploaded_file($_FILES['pdf']['tmp_name'], $pdfPath);
    }

    $stmt = $conn->prepare("UPDATE ebooks SET title=?, regular_price=?, offer_price=?, thumbnail=?, preview_image=?, pdf=? WHERE id=?");
    $stmt->bind_param("sddsssi", $title, $regular_price, $offer_price, $thumbPath, $previewPath, $pdfPath, $id);

    if ($stmt->execute()) {
        header("Location: index.php?updated=1");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<?php include 'partials/header.php'; ?>
<div class="container">

    <div class="content">
        <div class="edit-container card">
            <h1>Edit PDF Details</h1>
            <form action="" method="POST" enctype="multipart/form-data" class="form-ui">

                <div class="form-group">
                    <label>Title</label>
                    <input class="readonly-input" type="text" name="title" value="<?= htmlspecialchars($ebook['title']); ?>" readonly>
                </div>

                <div class="form-group">
                    <label>Regular Price</label>
                    <input type="number" step="0.01" name="regular_price" value="<?= htmlspecialchars($ebook['regular_price']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Offer Price</label>
                    <input type="number" step="0.01" name="offer_price" value="<?= htmlspecialchars($ebook['offer_price']); ?>" required>
                </div>

                <!-- <div class="form-group">
                    <label>Category</label>
                    <select name="category_id" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($categories as $key => $value): ?>
                            <option value="<?= $key ?>" <?= ($ebook['category_id'] == $key) ? "selected" : "" ?>>
                                <?= htmlspecialchars($value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div> -->

                <div class="form-group">
                    <label>Thumbnail Image</label>
                    <input type="file" name="thumbnail" accept="image/*" id="thumbnailInput">
                    <?php if (!empty($ebook['thumbnail'])): ?>
                        <div class="current-file">
                            <small>📁 Current: <?= basename($ebook['thumbnail']); ?></small>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label>Preview Image</label>
                    <input type="file" name="preview_image" accept="image/*" id="previewImageInput">
                    <?php if (!empty($ebook['preview_image'])): ?>
                        <div class="current-file">
                            <small>📁 Current: <?= basename($ebook['preview_image']); ?></small>
                        </div>
                    <?php endif; ?>
                    
                    <div class="preview-actions">
                        <?php if (!empty($ebook['preview_image'])): ?>
                            <button type="button" class="btn-preview" onclick="showPreview('<?= $ebook['preview_image']; ?>')">
                                👁️ View Current Preview Image
                            </button>
                        <?php endif; ?>
                        <button type="button" class="btn-preview" onclick="previewNewPreviewImage()">
                            🖼️ Preview New Upload
                        </button>
                    </div>

                    <!-- Current preview image display (hidden by default) -->
                    <?php if (!empty($ebook['preview_image'])): ?>
                        <div class="preview" id="currentPreviewImagePreview" style="display: none;">
                            <h4>Current Preview Image:</h4>
                            <img src="<?= $ebook['preview_image']; ?>" alt="Current preview image">
                            <button type="button" class="btn-close" onclick="hidePreview('currentPreviewImagePreview')">✕ Close</button>
                        </div>
                    <?php endif; ?>

                    <!-- New preview image display (hidden by default) -->
                    <div class="preview" id="newPreviewImagePreview" style="display: none;">
                        <h4>New Preview Image:</h4>
                        <img id="newPreviewImageDisplay" src="" alt="New preview image">
                        <button type="button" class="btn-close" onclick="hidePreview('newPreviewImagePreview')">✕ Close</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>PDF File (optional)</label>
                    <input type="file" name="pdf" accept="application/pdf">
                    <?php if (!empty($ebook['pdf'])): ?>
                        <div class="preview">
                            <a href="<?= $ebook['pdf']; ?>" target="_blank">📄 View Current PDF</a>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn-primary">Update PDF</button>
            </form>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="previewModal" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="modal-close" onclick="closeModal()">&times;</span>
        <div class="modal-body">
            <img id="modalImage" src="" alt="Preview">
        </div>
    </div>
</div>

<?php include 'partials/footer.php'; ?>

<script>
function showPreview(imageSrc) {
    document.getElementById('modalImage').src = imageSrc;
    document.getElementById('previewModal').style.display = 'block';
}

function previewNewPreviewImage() {
    const fileInput = document.getElementById('previewImageInput');
    const file = fileInput.files[0];
    
    if (file) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('newPreviewImageDisplay').src = e.target.result;
                document.getElementById('newPreviewImagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            alert('Please select a valid image file.');
        }
    } else {
        alert('Please select an image file first.');
    }
}

function previewNewImage() {
    const fileInput = document.getElementById('thumbnailInput');
    const file = fileInput.files[0];
    
    if (file) {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('newImageDisplay').src = e.target.result;
                document.getElementById('newImagePreview').style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            alert('Please select a valid image file.');
        }
    } else {
        alert('Please select an image file first.');
    }
}

function hidePreview(previewId) {
    document.getElementById(previewId).style.display = 'none';
}

function closeModal() {
    document.getElementById('previewModal').style.display = 'none';
}

// Close modal when clicking outside of it
window.onclick = function(event) {
    const modal = document.getElementById('previewModal');
    if (event.target == modal) {
        closeModal();
    }
}
</script>

<style>
    .edit-container {
        max-width: 800px;
        margin: 40px auto;
        background: #fff;
        padding: 25px 30px;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .edit-container h1 {
        font-size: 24px;
        margin-bottom: 20px;
        text-align: center;
        color: #333;
    }

    .form-ui .form-group {
        margin-bottom: 18px;
    }

    .form-ui label {
        font-weight: 600;
        display: block;
        margin-bottom: 6px;
        color: #444;
    }

    .form-ui input,
    .form-ui select {
        width: 100%;
        padding: 10px 12px;
        font-size: 15px;
        border: 1px solid #ccc;
        border-radius: 6px;
        outline: none;
        transition: border 0.2s;
    }

    .form-ui input:focus,
    .form-ui select:focus {
        border-color: #007bff;
    }

    .current-file {
        margin-top: 5px;
        padding: 5px 8px;
        background: #e9ecef;
        border-radius: 4px;
        display: inline-block;
    }

    .current-file small {
        color: #6c757d;
        font-size: 12px;
    }

    .preview-section {
        border-top: 1px solid #e9ecef;
        padding-top: 15px;
        margin-top: 5px;
    }

    .preview-actions {
        margin-top: 10px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-preview {
        background: #28a745;
        color: #fff;
        padding: 8px 12px;
        border: none;
        border-radius: 4px;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-preview:hover {
        background: #218838;
        transform: translateY(-1px);
    }

    .preview {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }

    .preview h4 {
        margin: 0 0 10px 0;
        color: #495057;
        font-size: 14px;
    }

    .preview img {
        max-width: 200px;
        max-height: 200px;
        border-radius: 6px;
        border: 1px solid #ddd;
        display: block;
        margin-bottom: 10px;
    }

    .btn-close {
        background: #dc3545;
        color: #fff;
        padding: 4px 8px;
        border: none;
        border-radius: 4px;
        font-size: 12px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-close:hover {
        background: #c82333;
    }

    .preview a {
        display: inline-block;
        margin-top: 5px;
        color: #007bff;
        font-weight: 500;
        text-decoration: none;
    }

    .preview a:hover {
        text-decoration: underline;
    }

    .btn-primary {
        background: #007bff;
        color: #fff;
        padding: 12px;
        border: none;
        border-radius: 6px;
        font-size: 16px;
        font-weight: bold;
        width: 100%;
        cursor: pointer;
        transition: all 0.3s;
    }

    .btn-primary:hover {
        background: #0056b3;
    }

    /* Modal Styles */
    .modal {
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        animation: fadeIn 0.3s;
    }

    .modal-content {
        background-color: #fff;
        margin: 5% auto;
        padding: 20px;
        border-radius: 8px;
        width: 80%;
        max-width: 600px;
        position: relative;
        animation: slideIn 0.3s;
    }

    .modal-close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        position: absolute;
        right: 15px;
        top: 10px;
        cursor: pointer;
    }

    .modal-close:hover {
        color: #000;
    }

    .modal-body {
        text-align: center;
        margin-top: 20px;
    }

    .modal-body img {
        max-width: 100%;
        max-height: 70vh;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideIn {
        from { transform: translateY(-50px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    @media (max-width: 768px) {
        .preview-actions {
            flex-direction: column;
        }
        
        .btn-preview {
            width: 100%;
            justify-content: center;
        }
        
        .modal-content {
            width: 95%;
            margin: 10% auto;
        }
    }
</style>