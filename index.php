<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>WeConnect - Welcome</title>
  <style>
    * {
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-image: url('images/img13.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      color: white;
      min-height: 100vh;
    }

    .overlay {
      background: rgba(0, 0, 0, 0.65);
      min-height: 100vh;
      padding: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: flex-start;
      background-color: rgba(0, 74, 173, 0.9);
      padding: 20px 40px;
      border-radius: 10px;
      margin-bottom: 30px;
      width: 100%;
      max-width: 1100px;
      gap: 20px;
    }

    .logo-image {
      width: 90px;
      height: auto;
    }

    .logo-text {
      font-size: 2.8em;
      font-weight: bold;
      margin: 0;
    }

    .tagline {
      font-style: italic;
      font-size: 1.2em;
      color: #a9caff;
      margin-top: 5px;
    }

    .content-wrapper {
      display: flex;
      flex-wrap: wrap;
      width: 100%;
      max-width: 1100px;
      gap: 30px;
    }

    .sidebar {
      flex: 1;
      background-color: rgba(0, 0, 0, 0.5);
      padding: 30px;
      border-radius: 10px;
    }

    .main-content {
      flex: 2;
      display: flex;
      flex-direction: column;
      gap: 30px;
    }

    .slideshow-container {
      position: relative;
      width: 100%;
      height: 320px;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 0 15px rgba(0,0,0,0.4);
    }

    .mySlides {
      display: none;
      height: 100%;
    }

    .mySlides img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 15px;
    }

    .fade {
      animation: fadeEffect 2s ease-in-out;
    }

    @keyframes fadeEffect {
      from { opacity: 0.4; }
      to { opacity: 1; }
    }

    .login-link {
      align-self: center;
      background-color: #004aad;
      color: white;
      padding: 15px 40px;
      text-decoration: none;
      border-radius: 10px;
      font-weight: 700;
      font-size: 1.2em;
      transition: background-color 0.3s ease;
      box-shadow: 0 5px 10px rgba(0, 74, 173, 0.4);
    }

    .login-link:hover {
      background-color: #00337a;
    }

    .content-section h2 {
      color: #ffffff;
      border-bottom: 2px solid #fff;
      padding-bottom: 6px;
      margin-bottom: 10px;
      font-size: 1.4em;
    }

    .content-section p {
      line-height: 1.6;
      font-size: 1em;
      color: #f0f0f0;
    }

    .contact-info a {
      color: #87cefa;
      text-decoration: none;
    }

    .contact-info a:hover {
      text-decoration: underline;
    }

    .social-icons {
      margin-top: 15px;
      display: flex;
      gap: 15px;
      flex-wrap: wrap;
    }

    .social-icons a img {
      width: 32px;
      height: 32px;
      transition: transform 0.2s ease;
    }

    .social-icons a img:hover {
      transform: scale(1.1);
    }

    @media (max-width: 900px) {
      .content-wrapper {
        flex-direction: column;
      }
      .header {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
      }
    }
  </style>
</head>
<body>
  <div class="overlay">
    <!-- Header with left-aligned logo -->
    <div class="header">
      <img src="images/logo.png" alt="WeConnect Logo" class="logo-image" />
      <div>
        <h1 class="logo-text">WeConnect</h1>
        <div class="tagline">The future is bright with us!</div>
      </div>
    </div>

    <!-- Main content wrapper -->
    <div class="content-wrapper">
      <!-- Left side -->
      <div class="sidebar">
        <div class="content-section">
          <h2>Our Vision</h2>
          <p><i>To become the leading digital education platform in Africa by promoting inclusive, quality education and leveraging technology to bridge communication gaps between learners, parents, and educators.</i></p>
        </div>

        <div class="content-section">
          <h2>Our Mission</h2>
          <p><i>To connect learners, teachers, and parents in a collaborative online environment that empowers every learner to reach their full academic potential.</i></p>
        </div>

        <div class="content-section">
          <h2>Contact Us</h2>
          <p class="contact-info">
            📞 Phone: +27 123 456 7890<br />
            📧 Email: <a href="mailto:support@weconnect.com">support@weconnect.com</a><br />
            🌍 Website: <a href="https://www.weconnect.com" target="_blank">www.weconnect.com</a>
          </p>

          <div class="social-icons">
            <a href="https://www.facebook.com" target="_blank"><img src="images/facebook.png" alt="Facebook" /></a>
            <a href="https://www.linkedin.com" target="_blank"><img src="images/linkedin.png" alt="LinkedIn" /></a>
            <a href="https://wa.me/271234567890" target="_blank"><img src="images/whatsapp.png" alt="WhatsApp" /></a>
            <a href="https://www.instagram.com" target="_blank"><img src="images/instagram.png" alt="Instagram" /></a>
          </div>
        </div>
      </div>

      <!-- Right side -->
      <div class="main-content">
        <div class="slideshow-container">
          <div class="mySlides fade">
            <img src="images/img12.jpg" alt="Slide 1" />
          </div>
          <div class="mySlides fade">
            <img src="images/img14.jpg" alt="Slide 2" />
          </div>
          <div class="mySlides fade">
            <img src="images/img16.jpg" alt="Slide 3" />
          </div>
        </div>

        <a href="login.html" class="login-link">Go to Login</a>
      </div>
    </div>
  </div>

  <script>
    let slideIndex = 0;
    showSlides();

    function showSlides() {
      const slides = document.getElementsByClassName("mySlides");
      for (let i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
      }
      slideIndex++;
      if (slideIndex > slides.length) { slideIndex = 1; }
      slides[slideIndex - 1].style.display = "block";
      setTimeout(showSlides, 4000);
    }
  </script>
</body>
</html>
