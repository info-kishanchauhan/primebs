<?php
  // Redirect to login if not in maintenance mode
  if (!file_exists('maintenance.flag')) {
    header("Location: /login");
    exit;
  }

  // Get maintenance start time from file's last modified time
  $maintenanceStart = filemtime('maintenance.flag');
  $maintenanceEnd = $maintenanceStart + (6 * 60 * 60); // 6 hours after start
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Maintenance Mode | Prime Digital Arena</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      height: 100vh;
      background: #ffffff;
      font-family: 'Segoe UI', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      color: #1e293b;
      transition: background 0.3s, color 0.3s;
    }

    .card {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      padding: 3rem 2rem;
      border-radius: 20px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
      text-align: center;
    }

    .card img {
      width: 120px;
      margin-bottom: 1.5rem;
    }

    h1 {
      font-size: 1.75rem;
      margin-bottom: 0.75rem;
    }

    p {
      font-size: 1rem;
      line-height: 1.5;
      margin-bottom: 1rem;
    }

    .countdown {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    .progress {
      height: 8px;
      background: #e2e8f0;
      border-radius: 10px;
      overflow: hidden;
      margin-bottom: 1.5rem;
    }

    .progress-bar {
      height: 100%;
      background: #3b82f6;
      width: 0%;
      transition: width 0.3s ease;
    }

    .btn {
      padding: 10px 16px;
      margin: 0.4rem;
      font-size: 0.9rem;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    .btn-dark {
      background: #1f2937;
      color: #ffffff;
    }

    .btn-contact {
      background: #3b82f6;
      color: #ffffff;
    }

    .lang-select {
      margin-top: 1rem;
      padding: 8px;
      font-size: 0.9rem;
      border-radius: 6px;
    }

    .footer {
      margin-top: 2rem;
      font-size: 0.875rem;
      color: #6b7280;
    }
  </style>
</head>
<body>
  <div class="card">
    <img src="/public/uploads/20240602220857.png" alt="Maintenance Logo" />
    <h1>We'll Be Right Back</h1>
    <p id="main-msg">
      Our systems are undergoing maintenance to serve you better.<br />
      We’ll be back shortly.
    </p>
    <div class="countdown" id="countdown"></div>
    <div class="progress">
      <div class="progress-bar" id="progressBar"></div>
    </div>

    <button class="btn btn-dark" onclick="toggleTheme()">Toggle Dark Mode</button>
    <button class="btn btn-contact" onclick="window.location='mailto:support@primedigitalarena.com'">Contact Support</button>

    <div>
      <select class="lang-select" onchange="changeLanguage(this.value)">
        <option value="en">English</option>
        <option value="hi">Hindi</option>
        <option value="ur">Urdu</option>
      </select>
    </div>

    <div class="footer">
      &copy; <?= date('Y') ?> Prime Digital Arena. All rights reserved.
    </div>
  </div>

  <script>
    // Get maintenance end time from PHP
    const maintenanceEnd = <?= $maintenanceEnd ?> * 1000;
    const totalDuration = 7 * 60 * 60 * 1000; // 6 hours in ms

    const countdownEl = document.getElementById('countdown');
    const progressBar = document.getElementById('progressBar');

    const interval = setInterval(() => {
      const now = new Date().getTime();
      const distance = maintenanceEnd - now;

      if (distance <= 0) {
        clearInterval(interval);
        countdownEl.innerText = "Back online soon!";
        progressBar.style.width = "100%";
        return;
      }

      const hrs = Math.floor((distance / (1000 * 60 * 60)) % 24);
      const mins = Math.floor((distance / (1000 * 60)) % 60);
      const secs = Math.floor((distance / 1000) % 60);

      countdownEl.innerText = `Returning in: ${hrs}h ${mins}m ${secs}s`;

      const progress = 100 - (distance / totalDuration) * 100;
      progressBar.style.width = progress.toFixed(1) + '%';
    }, 1000);

    // Theme Toggle (Dark Mode)
    let dark = false;
    function toggleTheme() {
      dark = !dark;
      document.body.style.background = dark ? "#0f172a" : "#ffffff";
      document.body.style.color = dark ? "#f1f5f9" : "#1e293b";
      document.querySelector('.card').style.background = dark ? "#1e293b" : "#ffffff";
    }

    // Language Switch
    function changeLanguage(lang) {
      const msg = {
        en: "Our systems are undergoing maintenance to serve you better.<br />We’ll be back shortly.",
        hi: "हमारी प्रणाली बेहतर सेवा के लिए रखरखाव में है।<br />हम जल्द ही वापस आएंगे।",
        ur: "ہماری سروس کو بہتر بنانے کے لیے دیکھ بھال جاری ہے۔<br />ہم جلد واپس آئیں گے۔"
      };
      document.getElementById("main-msg").innerHTML = msg[lang];
    }
  </script>
</body>
</html>
