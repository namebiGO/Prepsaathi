<?php include 'db.php'; ?>
<?php include 'partials/header.php'; ?>

<div class="main-body">
    <h1>Manage News Ticker (8 Slots)</h1>

    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div id="successMsg" style="background: #d1fae5; color: #065f46; padding: 10px 15px; border: 1px solid #10b981; border-radius: 6px; margin-bottom: 15px; font-weight: bold; text-align: center;">
            ✅ News updated successfully!
        </div>
        <script>
            setTimeout(() => {
                const msg = document.getElementById("successMsg");
                if (msg) msg.style.display = "none";
            }, 3000);
        </script>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Slot</th>
                <th>Current News Text</th>
                <th>Edit</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->query("SELECT id, news_text FROM banners ORDER BY id ASC");
            while ($row = $stmt->fetch_assoc()):
            ?>
                <tr>
                    <td>Slot <?= (int)$row['id']; ?></td>
                    <td><?= htmlspecialchars($row['news_text']); ?></td>
                    <td>
                        <form action="upload_banner.php" method="POST" style="display: flex; gap: 5px;">
                            <input type="hidden" name="slot_id" value="<?= (int)$row['id']; ?>">
                            <input type="text" name="news_text" placeholder="Enter News Text"
                                   value="<?= htmlspecialchars($row['news_text']); ?>" 
                                   style="flex:1; padding: 5px;">
                            <button type="submit" style="padding: 8px 12px; background: #007cba; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                Save
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<style>
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
table th, table td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}
table th {
    background-color: #f4f4f4;
    font-weight: bold;
}
table tr:nth-child(even) {
    background-color: #f9f9f9;
}
</style>

<?php include 'partials/footer.php'; ?>
