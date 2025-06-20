
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>WeConnect - Welcome</title>
  <style>
    * { box-sizing: border-box; }
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      margin: 0;
      background: linear-gradient(135deg, #000 60%, #004aad 100%);
      min-height: 100vh;
      color: #fff;
      padding: 0;
    }
    .header {
      display: flex;
      align-items: center;
      gap: 24px;
      background: linear-gradient(90deg, #004aad 80%, #222 100%);
      color: #fff;
      padding: 32px 40px 24px 40px;
      border-top-left-radius: 22px;
      border-top-right-radius: 22px;
      box-shadow: 0 2px 12px rgba(0,74,173,0.08);
      margin-bottom: 0;
    }
    .logo-image {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background: #fff;
      object-fit: contain;
      box-shadow: 0 2px 12px rgba(0,74,173,0.15);
    }
    .logo-text {
      font-size: 2.7em;
      font-weight: 800;
      margin: 0;
      letter-spacing: 1px;
      text-shadow: 0 2px 8px #00337a44;
    }
    .tagline {
      font-style: italic;
      font-size: 1.15em;
      margin-top: 6px;
      color: #c7e0ff;
      font-weight: 500;
      letter-spacing: 0.5px;
    }
    .main-content-area {
      display: flex;
      flex-wrap: wrap;
      padding: 0 0 32px 0;
      gap: 0;
      width: 100%;
      max-width: 1200px;
      margin: 0 auto;
    }
    .sidebar {
      flex: 1 1 340px;
      min-width: 320px;
      background: rgba(0,0,0,0.7);
      padding: 38px 32px 38px 38px;
      display: flex;
      flex-direction: column;
      gap: 32px;
      border-right: 2px solid #004aad;
      border-bottom-left-radius: 22px;
    }
    .content-section h2 {
      color: #4eaaff;
      border-bottom: 2px solid #004aad;
      padding-bottom: 4px;
      margin-bottom: 12px;
      font-weight: 700;
      font-size: 1.3em;
      letter-spacing: 0.5px;
    }
    .content-section p {
      line-height: 1.7;
      font-size: 1.05em;
      color: #e0e0e0;
      font-weight: 400;
      margin: 0;
    }
    .contact-info {
      font-size: 1em;
      color: #c7e0ff;
    }
    .contact-info a {
      color: #4eaaff;
      text-decoration: underline;
      font-weight: 500;
    }
    .contact-info a:hover {
      color: #fff;
      text-decoration: underline;
    }
    .main-content {
      flex: 2 1 600px;
      min-width: 340px;
      padding: 38px 38px 38px 32px;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 38px;
      border-bottom-right-radius: 22px;
    }
    .slideshow-container {
      position: relative;
      width: 100%;
      max-width: 480px;
      height: 320px;
      border-radius: 18px;
      overflow: hidden;
      box-shadow: 0 0 18px rgba(0,0,0,0.25);
      margin-bottom: 18px;
      background: #222;
    }
    .mySlides {
      display: none;
      height: 100%;
    }
    .mySlides img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      border-radius: 18px;
      user-select: none;
      box-shadow: 0 2px 12px #0008;
    }
    .fade {
      animation: fadeEffect 1.5s;
    }
    @keyframes fadeEffect {
      from { opacity: 0.4; }
      to { opacity: 1; }
    }
    .login-link {
      background: linear-gradient(90deg, #004aad 70%, #007fff 100%);
      color: #fff;
      padding: 16px 48px;
      text-decoration: none;
      border-radius: 12px;
      font-weight: 700;
      font-size: 1.25em;
      letter-spacing: 1px;
      transition: background 0.3s, box-shadow 0.3s;
      box-shadow: 0 5px 16px rgba(0, 74, 173, 0.25);
      margin-top: 12px;
      border: none;
      outline: none;
      cursor: pointer;
      display: inline-block;
    }
    .login-link:hover {
      background: linear-gradient(90deg, #00337a 70%, #005bb5 100%);
      box-shadow: 0 8px 24px rgba(0, 74, 173, 0.35);
      color: #c7e0ff;
    }
    /* Responsive adjustments */
    @media (max-width: 900px) {
      .main-content-area {
        flex-direction: column;
      }
      .sidebar {
        border-right: none;
        border-bottom: 2px solid #004aad;
        border-bottom-left-radius: 0;
        border-bottom-right-radius: 0;
        padding: 28px 18px;
      }
      .main-content {
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 22px;
        padding: 28px 18px;
      }
      .header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
        padding: 24px 18px 18px 18px;
      }
    }
    @media (max-width: 600px) {
      .header, .sidebar, .main-content {
        border-radius: 0;
      }
      .slideshow-container {
        max-width: 100%;
        height: 180px;
      }
    }
  </style>
</head>
<body>
  <div class="header">
    <img src="images/logo.png" alt="WeConnect Logo" class="logo-image" />
    <div>
      <h1 class="logo-text">WeConnect</h1>
      <div class="tagline">The future is bright with us!</div>
    </div>
  </div>
  <div class="main-content-area">
    <div class="sidebar">
      <div class="content-section">
        <h2>Our Vision</h2>
        <p>
          <i>
            To become the leading digital education platform in Africa by promoting inclusive, quality education and leveraging technology to bridge communication gaps between learners, parents, and educators.
          </i>
        </p>
      </div>
      <div class="content-section">
        <h2>Our Mission</h2>
        <p>
          <i>
            To connect learners, teachers, and parents in a collaborative online environment that empowers every learner to reach their full academic potential.
          </i>
        </p>
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
      setTimeout(showSlides, 4000);
    }
  </script>
</body>
</html>