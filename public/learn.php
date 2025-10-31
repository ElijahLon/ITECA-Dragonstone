<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Learn More | DragonStone</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      color: #000;
    }
    .hero-section {
      background: linear-gradient(135deg, #2e7d32 0%, #4caf50 100%);
      color: #fff;
      text-align: center;
      padding: 120px 20px;
      position: relative;
    }
    .hero-section .container {
      position: relative;
      z-index: 1;
    }
    h1, h2, h3 {
      font-weight: 700;
    }
    .mission-section {
      background-color: #fff;
      padding: 80px 20px;
      color: #000;
    }
    .vision-section {
      background-color: #f8f9fa;
      padding: 80px 20px;
      color: #000;
    }
    .story-section {
      background-color: #fff;
      padding: 80px 20px;
      color: #000;
    }
    .btn-primary-custom {
      background: #000;
      border: none;
      color: #fff;
      border-radius: 8px;
      font-weight: 600;
    }
    .btn-primary-custom:hover {
      background: #333;
      color: #fff;
    }
    .founder-card {
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      transition: transform 0.3s;
      border: none;
    }
    .founder-card:hover {
      transform: translateY(-5px);
    }
    .founder-icon {
      font-size: 3rem;
      color: #2e7d32;
      margin-bottom: 15px;
    }
    .ecopoints-section {
      background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
      padding: 80px 20px;
      color: #000;
    }
    .ecopoints-card {
      background: #fff;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      text-align: center;
      border: none;
    }
  </style>
</head>
<body>

  <!-- Hero Section -->
  <section class="hero-section d-flex align-items-center justify-content-center">
    <div class="container">
      <h1 class="display-4 fw-bold">Discover DragonStone</h1>
      <p class="lead">Making sustainable living stylish, accessible, and empowering for everyone.</p>
      <a href="#founders" class="btn btn-primary btn-lg btn-primary-custom mt-3">Learn Our Story</a>
    </div>
  </section>

  <!-- Founders Story Section -->
  <section id="founders" class="story-section text-center container">
    <h2 class="fw-bold mb-5">Meet the Founders</h2>
    <div class="row justify-content-center g-4">
      <div class="col-md-4">
        <div class="founder-card">
          <i class="bi bi-truck founder-icon"></i>
          <h4 class="fw-semibold">Aegon</h4>
          <p>Logistics expert applying supply chain skills to ethical sourcing and sustainable practices.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="founder-card">
          <i class="bi bi-house-heart founder-icon"></i>
          <h4 class="fw-semibold">Visenya</h4>
          <p>Former interior designer passionate about eco-conscious living and genuine sustainable products.</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="founder-card">
          <i class="bi bi-megaphone founder-icon"></i>
          <h4 class="fw-semibold">Rhaenys</h4>
          <p>Digital marketer and environmental activist creating a brand that educates and empowers consumers.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Mission Section -->
  <section class="mission-section text-center">
    <div class="container">
      <h2 class="fw-bold mb-4">Our Mission</h2>
      <p class="lead mb-4">DragonStone was born from a shared frustration: the difficulty of finding truly sustainable home products that are both stylish and affordable. Our mission is to make eco-conscious living simple, empowering, and beautiful for everyone.</p>
      <a href="#vision" class="btn btn-primary btn-lg btn-primary-custom">See Our Vision</a>
    </div>
  </section>

  <!-- Vision Section -->
  <section id="vision" class="vision-section text-center">
    <div class="container">
      <h2 class="fw-bold mb-4">Our Vision</h2>
      <p class="lead mb-5">We aim to build a community where sustainability isn’t just a choice, but a lifestyle. By curating eco-friendly products, promoting ethical practices, and engaging consumers with education and rewards, DragonStone is more than a store—it’s a movement for a greener, smarter future.</p>
      <a href="category.php" class="btn btn-primary btn-lg btn-primary-custom">Start Shopping</a>
    </div>
  </section>

  <!-- EcoPoints Section -->
  <section class="ecopoints-section text-center">
    <div class="container">
      <h2 class="fw-bold mb-4">Earn EcoPoints & Make a Difference</h2>
      <p class="lead mb-5">At DragonStone, every purchase and action contributes to a sustainable future. Earn EcoPoints for eco-friendly choices and redeem them for rewards!</p>
      <div class="row justify-content-center g-4">
        <div class="col-md-4">
          <div class="ecopoints-card">
            <i class="bi bi-cart-check founder-icon"></i>
            <h4 class="fw-semibold">Shop Sustainably</h4>
            <p>Earn 10 EcoPoints for every R100 spent on eco-friendly products. Use points to get discounts on future purchases.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="ecopoints-card">
            <i class="bi bi-chat-dots founder-icon"></i>
            <h4 class="fw-semibold">Join the Community</h4>
            <p>Earn 5 EcoPoints daily for active participation in our Community Hub. Share tips, learn from others, and stay motivated!</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="ecopoints-card">
            <i class="bi bi-calendar-check founder-icon"></i>
            <h4 class="fw-semibold">Stay Engaged</h4>
            <p>Earn bonus points for logging in daily and completing eco-challenges. Your consistent efforts help build a greener world.</p>
          </div>
        </div>
      </div>
      <a href="register.php" class="btn btn-primary btn-lg btn-primary-custom mt-4">Join Now & Start Earning</a>
    </div>
  </section>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
