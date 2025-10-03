<?php include 'partials/header.php'; ?>

<?php
// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name    = htmlspecialchars($_POST['name']);
    $email   = htmlspecialchars($_POST['email']);
    $message = htmlspecialchars($_POST['message']);

    // You can replace this with email sending (PHPMailer / mail())
    $success = "Thank you $name, we have received your message!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us</title>
  <!-- Bootstrap 5 CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

  <!-- Hero Section -->
  <section class="bg-primary text-white text-center py-5">
    <h1 class="display-4 fw-bold">Contact Us</h1>
    <p class="lead">We'd love to hear from you! Get in touch with us today.</p>
  </section>

  <!-- Contact Section -->
  <section class="container py-5">
    <div class="row g-5">
      
      <!-- Contact Form -->
      <div class="col-md-6">
        <div class="card shadow-sm p-4">
          <h2 class="h4 mb-4">Send us a Message</h2>
          <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
          <?php endif; ?>
          <form method="POST" action="">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Message</label>
              <textarea name="message" rows="5" class="form-control" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary w-100">Send Message</button>
          </form>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="col-md-6">
        <div class="card shadow-sm p-4">
          <h2 class="h4 mb-4">Our Information</h2>
          <p><strong>Address:</strong> Mayur Vihar Phase-I, Delhi-110091</p>
          <p><strong>Email:</strong> support@prepsaathi.in</p>
          <p><strong>Phone:</strong> +91 9911968787</p>
          <div class="mt-4">
            <!-- Google Map Embed -->
            <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3651.902674273438!2d85.13756621498128!3d25.594094683692908!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39ed58d382b5f5f5%3A0x85f83c5e0b6b3e5c!2sPatna%2C%20Bihar!5e0!3m2!1sen!2sin!4v1691313925165!5m2!1sen!2sin" 
              width="100%" 
              height="250" 
              style="border:0;" 
              allowfullscreen="" 
              loading="lazy">
            </iframe>
          </div>
        </div>
      </div>

    </div>
  </section>

</body>
</html>

<?php include 'partials/footer.php'; ?>
