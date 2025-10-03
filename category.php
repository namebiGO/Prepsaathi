<?php
include 'db.php';

// Get all ebooks
$stmt = $conn->prepare("SELECT * FROM ebooks ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
$ebooks = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PREPSAATHI.IN - Study Materials Collection</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Main heading styles */
        h1.site-title {
            font-size: 90px;
            margin-bottom: -20px;
        }

        /* Color classes for different parts */
        .prep-blue {
            color: #007cba;
        }

        .saathi-maroon {
            color: #800000;
        }

        .domain-gray {
            color: #555;
        }

        /* Responsive heading */
        @media (max-width: 1024px) {
            h1.site-title {
                font-size: 70px;
                margin-bottom: -15px;
            }
        }

        @media (max-width: 768px) {
            h1.site-title {
                font-size: 50px;
                margin-bottom: -10px;
            }
        }

        @media (max-width: 480px) {
            h1.site-title {
                font-size: 35px;
                margin-bottom: -5px;
            }
        }

        @media (max-width: 360px) {
            h1.site-title {
                font-size: 28px;
                margin-bottom: -3px;
            }
        }

        /* Page styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            color: #333;
        }

        .category-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            text-align: center;
            padding: 50px 0;
            margin-bottom: 30px;
        }

        .ebook-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 0 10px;
        }

        .ebook-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s, box-shadow 0.3s;
            position: relative;
        }

        .ebook-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        }

        .ebook-thumbnail {
            width: 100%;
            height: 200px;
            overflow: hidden;
            background-color: #eaeaea;
            position: relative;
        }

        .ebook-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s;
        }

        .preview-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .ebook-card:hover .preview-overlay {
            opacity: 1;
        }

        .preview-btn-overlay {
            background: #7d7deb;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .preview-btn-overlay:hover {
            background: #6b6bdb;
            transform: scale(1.05);
        }

        .ebook-content {
            padding: 15px;
            flex-grow: 1;
        }

        .ebook-content h3 {
            font-size: 1.1rem;
            margin-bottom: 10px;
            color: #111;
        }

        .ebook-content p {
            font-size: 0.95rem;
            margin: 6px 0;
            color: #555;
        }

        .ebook-actions {
            padding: 15px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .ebook-actions .preview-btn {
            background-color: #7d7deb !important;
            color: #fff !important;
            text-align: center;
        }

        .ebook-actions a,
        .ebook-actions button {
            padding: 10px 20px;
            background-color: #4f46e5;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }

        .ebook-actions a:hover,
        .ebook-actions button:hover {
            background-color: #3730a3;
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        .read-pdf-btn {
            background-color: #28a745 !important;
        }

        .read-pdf-btn:hover {
            background-color: #218838 !important;
        }

        .no-thumbnail {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 3rem;
            width: 100%;
            height: 100%;
        }

        .info-section {
            background: white;
            padding: 30px;
            margin-top: 40px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
            line-height: 1.6;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.9);
            animation: fadeIn 0.3s;
        }

        .modal-content {
            background-color: #fff;
            margin: 2% auto;
            padding: 20px;
            border-radius: 15px;
            width: 90%;
            max-width: 800px;
            position: relative;
            animation: slideIn 0.3s;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-close {
            color: #aaa;
            float: right;
            font-size: 32px;
            font-weight: bold;
            position: absolute;
            right: 20px;
            top: 15px;
            cursor: pointer;
            z-index: 1001;
        }

        .modal-close:hover {
            color: #000;
        }

        .modal-body {
            text-align: center;
            margin-top: 30px;
        }

        .modal-body img {
            max-width: 100%;
            max-height: 70vh;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
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
            .ebook-thumbnail {
                height: 180px;
            }
            
            .ebook-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .ebook-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .ebook-thumbnail {
                height: 150px;
            }

            .ebook-content h3 {
                font-size: 1rem;
            }

            .ebook-content p {
                font-size: 0.9rem;
            }

            .ebook-actions {
                flex-direction: column;
                gap: 8px;
            }

            .modal-content {
                width: 95%;
                margin: 5% auto;
            }
        }
    </style>
</head>
<body>
    <section class="category-section">
        <div class="page-header">
            <!-- Main Heading -->
            <h1 class="site-title">
                <span class="prep-blue">PREP</span><span class="saathi-maroon">SAATHI</span><span class="domain-gray">.IN</span>
            </h1>
            
            <!-- Tagline -->
            <h1 style="color: #800000; font-style: italic; font-size: 20px; margin-top: 15px;">
                Your One-Stop Shop for Study Materials and Question Banks
            </h1>
        </div>

        <div class="ebook-grid">
            <?php foreach ($ebooks as $ebook): ?>
                <div class="ebook-card">
                    <div class="ebook-thumbnail">
                        <?php if (!empty($ebook['thumbnail'])): ?>
                            <img src="<?= htmlspecialchars($ebook['thumbnail']); ?>" alt="<?= htmlspecialchars($ebook['title']); ?>">
                            <div class="preview-overlay">
                                <button class="preview-btn-overlay" onclick="showPreview('<?= htmlspecialchars($ebook['preview_image'] ?? $ebook['thumbnail']); ?>', '<?= htmlspecialchars($ebook['title']); ?>')">
                                    👁️ Preview
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="no-thumbnail">📖</div>
                        <?php endif; ?>
                    </div>

                    <div class="ebook-content">
                        <h3><?= htmlspecialchars($ebook['title']); ?></h3>
                        <?php if ($ebook['regular_price'] > 0): ?>
                            <p>Original: ₹ <?= htmlspecialchars($ebook['regular_price']); ?></p>
                        <?php endif; ?>
                        <?php if ($ebook['offer_price'] > 0): ?>
                            <p>Offer: ₹ <?= htmlspecialchars($ebook['offer_price']); ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="ebook-actions">
                        <?php if (!empty($ebook['preview_image'])): ?>
                            <button class="preview-btn" onclick="showPreview('<?= htmlspecialchars($ebook['preview_image']); ?>', '<?= htmlspecialchars($ebook['title']); ?>')">Preview</button>
                        <?php endif; ?>
                        
                        <?php if (!empty($ebook['pdf'])): ?>
                            <a href="<?= htmlspecialchars($ebook['pdf']); ?>" target="_blank" class="read-pdf-btn">Read PDF</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($ebooks)): ?>
            <div style="text-align: center; margin-top: 50px;">
                <h2>📚 No eBooks Available</h2>
                <p>Check back later for new releases!</p>
            </div>
        <?php endif; ?>

        <!-- Info Section -->
        <div class="info-section">
            <h3 style="color: #800000; margin-bottom: 20px;">General Studies Material by PrepSaathi.in</h3>
            <ul style="list-style-type: disc; padding-left: 20px;">
                <li><strong>Complete Coverage of GS Paper I</strong> – Includes History, Geography, Polity, Economy, Environment, Science & Tech, and Current Affairs.</li>
                <li><strong>Conceptual Clarity + Quick Revision</strong> – Crisp explanations and key points that balance depth with brevity.</li>
                <li><strong>One-Liners & Fact Sheets</strong> – High-retention formats ideal for last-minute prep and quick recall.</li>
                <li><strong>Updated with Latest Current Affairs</strong> – Monthly compilations and theme-based current affairs integrated within core subjects.</li>
                <li><strong>Topic-Wise Notes</strong> – Divided by UPSC syllabus subtopics to ensure easy navigation and focused study.</li>
                <li><strong>Mapped to PYQs</strong> – Every topic aligned with previous year questions to highlight areas of importance.</li>
                <li><strong>Smartly Structured PDFs</strong> – Minimalist layout, clean fonts, and exam-specific highlighting to reduce fatigue.</li>
                <li><strong>Perfect Companion to NCERTs & Standard Books</strong> – Complements traditional sources without overwhelming the learner.</li>
                <li><strong>Trusted by Aspirants Across India</strong> – Designed by experts with UPSC experience for serious contenders.</li>
                <li><strong>Continuously Updated & Improved</strong> – Feedback-based refinements ensure relevance and reliability.</li>
            </ul>
        </div>
    </section>

    <!-- Preview Modal -->
    <div id="previewModal" class="modal">
        <div class="modal-content">
            <span class="modal-close" onclick="closeModal()">&times;</span>
            <div class="modal-body">
                <h2 id="modalTitle"></h2>
                <img id="modalImage" src="" alt="Preview">
            </div>
        </div>
    </div>

    <script>
        function showPreview(imageSrc, title) {
            if (!imageSrc) {
                alert('No preview image available');
                return;
            }
            
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('modalImage').src = imageSrc;
            document.getElementById('previewModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('previewModal').style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('previewModal');
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>