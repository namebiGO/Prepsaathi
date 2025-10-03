<?php include 'partials/header.php'; ?>

<section style="padding:50px 0; text-align:center;">
    <!-- Logo Section -->
    <div style="margin-bottom:30px;">
        <!--<img src="images/logo.png" alt="PrepSathi Logo" style="max-width:300px; height:auto; margin-bottom:20px;">-->


        <?php include 'db.php'; ?>

<div class="news-ticker">
    <div class="ticker-wrapper">
        <?php
        $stmt = $conn->query("SELECT news_text FROM banners ORDER BY id ASC LIMIT 8");
        while ($row = $stmt->fetch_assoc()):
        ?>
            <div class="ticker-item"><?= htmlspecialchars($row['news_text']); ?></div>
        <?php endwhile; ?>
    </div>
</div>
        
        <h1 style="color:#555; font-size:50px; margin-bottom: -20px;">PREPSAATHI.IN</h1>
         <p style="color:#555; font-size:18px; margin-bottom:40px;">
        <h1>Your One-Stop Shop For Study Materials and Questions Banks</h1>
    </p>
    </div>
    
    <!-- <h1 style="font-size:32px; font-weight:bold; margin-bottom:10px;">Mock Tests Categories</h1>
    <p style="color:#555; font-size:18px; margin-bottom:40px;">
        India’s Most Reliable Source for Study Material & Practice Question Banks
    </p> -->

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:20px; max-width:1100px; margin:0 auto;">
        <div style="background:#fff; border-radius:15px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <a href="category.php?id=1"><img src="images/1.-UPSC-P.png" alt="UPSC Prelims" style="width:100%; height:auto;"></a>
        </div>
        <div style="background:#fff; border-radius:15px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <a href="category.php?id=2"><img src="images/2.-UPSC-M.png" alt="UPSC Mains" style="width:100%; height:auto;"></a>
        </div>
        <div style="background:#fff; border-radius:15px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <a href="category.php?id=3"><img src="images/3.-NEET-2026.png" alt="NEET 2026" style="width:100%; height:auto;"></a>
        </div>
        <div style="background:#fff; border-radius:15px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <a href="category.php?id=4"><img src="images/4.-NDACDS.webp" alt="NDA CDS" style="width:100%; height:auto;"></a>
        </div>
        <div style="background:#fff; border-radius:15px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <a href="category.php?id=5"><img src="images/5.-CUET-2026.webp" alt="CUET UG" style="width:100%; height:auto;"></a>
        </div>
        <div style="background:#fff; border-radius:15px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <a href="category.php?id=6"><img src="images/6.-SSCCGL.webp" alt="SSC CGL" style="width:100%; height:auto;"></a>
        </div>
        <div style="background:#fff; border-radius:15px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <a href="category.php?id=7"><img src="images/7.-SSCCHSL.webp" alt="SSC CHSL" style="width:100%; height:auto;"></a>
        </div>
        <div style="background:#fff; border-radius:15px; overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
            <a href="category.php?id=8"><img src="images/8.-SSCMTS.webp" alt="SSC MTS" style="width:100%; height:auto;"></a>
        </div>
    </div>
</section>



<style>
.news-ticker {
    background: #007cba;
    color: white;
    overflow: hidden;
    white-space: nowrap;
    position: relative;
    height: 40px;
    display: flex;
    align-items: center;
    font-weight: bold;
    font-size: 16px;
    border-radius: 4px;
    padding-left: 5px;
}
.ticker-wrapper {
    display: inline-block;
    padding-left: 100%;
    animation: ticker 50s linear infinite;
}
.ticker-item {
    display: inline-block;
    margin: 0 50px;
}
@keyframes ticker {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}
</style>


<section class="testimonial-section">
    <p class="section-subtitle">Testimonial</p>
    <h2 class="section-title">Feedback from <span>Our Community</span></h2>

    <div class="testimonial-card">
        <div class="stars">★★★★★</div>
        <p class="testimonial-title">"PrepSathi is a Lifesaver for SSC Prep!"</p>
        <p class="testimonial-text">
            "As someone preparing for SSC CGL, I struggled to find quality study material for free.
            PrepSathi gave me access to organized PDFs, mock tests, and current affairs updates —
            all without any cost. It's the best platform for exam preparation!"
        </p>
        <p class="testimonial-author">Sikha Singh</p>
        <p class="testimonial-role">Student</p>
    </div>

    <div class="testimonial-dots">
        <span class="dot active"></span>
        <span class="dot"></span>
        <span class="dot"></span>
    </div>
</section>

<?php include 'partials/footer.php'; ?>