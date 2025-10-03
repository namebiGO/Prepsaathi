<?php include 'partials/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>About Us - Prepsaathi</title>
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin:0;
      padding:0;
      background:#f9fafc;
      color:#333;
    }
    .hero {
      background: linear-gradient(135deg, #4f46e5, #4338ca);
      color:white;
      text-align:center;
      padding:80px 20px;
    }
    .hero h1 {
      font-size:42px;
      margin:0;
    }
    .hero p {
      font-size:18px;
      margin-top:10px;
      opacity:0.9;
    }

    /* Renamed container to avoid conflict */
    .about-container {
      margin:50px auto;
      padding:0 20px;
      max-width:1200px;
    }

    h2 {
      color:#4f46e5;
      margin-bottom:15px;
    }
    .section {
      margin-bottom:50px;
    }
    .card-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));
      gap:20px;
    }
    .card {
      background:white;
      padding:25px;
      border-radius:12px;
      box-shadow:0 8px 25px rgba(0,0,0,0.08);
      transition:0.3s;
    }
    .card:hover {
      transform: translateY(-5px);
    }
    .team-member {
      text-align:center;
    }
    .team-member img {
      width:120px;
      height:120px;
      border-radius:50%;
      object-fit:cover;
      margin-bottom:10px;
    }
    footer {
      background:#f1f1f1;
      /*text-align:center;*/
      padding:20px;
      margin-top:50px;
      font-size:14px;
    }
  </style>
</head>
<body>

  <!-- Hero Section -->
  <div class="hero">
    <h1>About Prepsaathi</h1>
    <p>Your trusted learning companion for exam preparation 📘✨</p>
  </div>

  <!-- About Us Main Content -->
  <div class="about-container">

    <!-- About Section -->
    <div class="section">
      <h2>Who We Are</h2>
      <p>
        Prepsaathi is an educational platform built to support students in their journey of learning and exam preparation. 
        We provide high-quality study materials, PDFs, guides, and resources to help you succeed. 
        Our mission is to make learning accessible, affordable, and effective for every student.
      </p>
    </div>

    <!-- Mission & Vision -->
    <div class="section card-grid">
      <div class="card">
        <h3>🎯 Our Mission</h3>
        <p>
          To empower students by providing reliable, affordable, and easy-to-understand learning resources 
          that strengthen their knowledge and boost their confidence.
        </p>
      </div>
      <div class="card">
        <h3>🌍 Our Vision</h3>
        <p>
          To become India’s most trusted learning companion by building a supportive community of learners 
          and providing world-class resources tailored for success.
        </p>
      </div>
    </div>

    <!-- Optional: Team Section -->
    <!--
    <div class="section">
      <h2>Meet Our Team</h2>
      <div class="card-grid">
        <div class="card team-member">
          <img src="https://via.placeholder.com/120" alt="Team Member">
          <h4>Rahul Sharma</h4>
          <p>Founder & CEO</p>
        </div>
        <div class="card team-member">
          <img src="https://via.placeholder.com/120" alt="Team Member">
          <h4>Priya Singh</h4>
          <p>Content Head</p>
        </div>
        <div class="card team-member">
          <img src="https://via.placeholder.com/120" alt="Team Member">
          <h4>Amit Kumar</h4>
          <p>Tech Lead</p>
        </div>
      </div>
    </div>
    -->

  </div>

</body>
</html>
<?php include 'partials/footer.php'; ?>
