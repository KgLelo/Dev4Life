<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>WeConnect - Welcome</title>
  <style>
    /* Reset some default styles */
    * {
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      background-image: url('images/img10.jpg');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      color: #333;
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px 20px;
    }
    .container {
      background-color: none;
      border-radius: 15px;
      max-width: 1100px;
      width: 100%;
      box-shadow: 0 0 20px rgba(0,0,0,0.3);
      display: flex;
      flex-wrap: wrap;
      overflow: hidden;
    }
    /* Sidebar for text content */
    .sidebar {
      flex: 1 1 350px;
      padding: 40px 30px;
      border-right: 2px solid #004aad;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 30px;
    }
    /* Header area with logo and title */
    .header {
      flex-basis: 100%;
      background-color: #004aad;
      color: white;
      padding: 20px 40px;
      display: flex;
      align-items: center;
      gap: 20px;
      border-top-left-radius: 15px;
      border-top-right-radius: 15px;
    }
    .logo-image {
      width: 80px;
      height: auto;
    }
    .logo-text {
      font-size: 2.8em;
      font-weight: 700;
      margin: 0;
      user-select: none;
    }
    .tagline {
      font-style: italic;
      font-size: 1.2em;
      margin-top: 5px;
      color: #a9caff;
      user-select: none;
    }
    /* Main content area for slideshow and login */
    .main-content {
      flex: 2 1 600px;
      padding: 40px 30px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      gap: 30px;
    }
    /* Slideshow styles */
    .slideshow-container {
      position: relative;
      width: 100%;
      height: 320px;
      border-radius: 15px;
      overflow: hidden;
      box-shadow: 0 0 15px rgba(0,0,0,0.25);
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
      user-select: none;
    }
    .fade {
      animation: fadeEffect 2s ease-in-out;
    }
    @keyframes fadeEffect {
      from { opacity: 0.4; }
      to { opacity: 1; }
    }
    /* Login button */
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
      user-select: none;
    }
    .login-link:hover {
      background-color: #00337a;
      box-shadow: 0 6px 14px rgba(0, 51, 122, 0.6);
    }
    /* Sidebar sections */
    .content-section h2 {
      color: #004aad;
      border-bottom: 3px solid #004aad;
      padding-bottom: 6px;
      margin-bottom: 10px;
      font-weight: 700;
      font-size: 1.5em;
      user-select: none;
    }
    .content-section p {
      line-height: 1.6;
      font-size: 1em;
      color: #222;
      user-select: text;
      color: white;
      font-weight: bold;
    }

    .contact-info a {
      color: #004aad;
      text-decoration: none;
    }
    .contact-info a:hover {
      text-decoration: underline;
    }

    /* Responsive adjustments */
    @media (max-width: 900px) {
      .container {
        flex-direction: column;
      }
      .sidebar {
        border-right: none;
        border-bottom: 2px solid #004aad;
        padding: 30px 20px;
      }
      .main-content {
        padding: 30px 20px;
      }
      .header {
        justify-content: center;
      }
    }
  </style>
</head>
<body>

  <div class="container">
    <div class="header">
      <img src="images/logo.png" alt="WeConnect Logo" class="logo-image" />
      <div>
        <h1 class="logo-text">WeConnect</h1>
        <div class="tagline">The future is bright with us!</div>
      </div>
    </div>

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
          🌍 Website: <a href="https://www.weconnect.com" target="_blank" rel="noopener noreferrer">www.weconnect.com</a>
        </p>
      </div>
    </div>

    <div class="main-content">
      <!-- Slideshow -->
      <div class="slideshow-container">
        <div class="mySlides fade">
          <img src="images/img12.jpg" alt="E-Learning Slide 1" />
        </div>
        <div class="mySlides fade">
          <img src="images/img14.jpg" alt="E-Learning Slide 2" />
        </div>
        <div class="mySlides fade">
          <img src="images/img16.jpg" alt="E-Learning Slide 3" />
        </div>
      </div>

      <a href="login.html" class="login-link">Go to Login</a>
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
      setTimeout(showSlides, 4000); // Change slide every 4 seconds
    }
  </script>

</body>
</html>
