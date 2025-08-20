<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>The Match – دا ماتش</title>

  <!-- Cairo Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap"
    rel="stylesheet"
  />

  <!-- Font-Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    integrity="sha512-oqgC01qb9lE2Bf/7gZFFxGAgYfh9l5tMSAeuJvJEf4cJUJRMmlobgYy3J8eUE5+rV0f6A2Usp3Ah+5eyDyIxsw=="
    crossorigin="anonymous"
    referrerpolicy="no-referrer"
  />

  <style>
    /* === Reset & Base === */
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box }
    html { scroll-behavior:smooth; font-size:16px }
    body {
      font-family:"Cairo",sans-serif;
      direction:rtl;
      background:#fff;
      color:#374151;
      line-height:1.6;
    }
    a { text-decoration:none; color:inherit; cursor:pointer }
    img { display:block; max-width:100%; height:auto }

    /* === Color Variables === */
    :root {
      --green: #4e7659;
      --gold:  #f3c766;
      --white: #ffffff;
      --grey-light: #f7f7f7;
      --border-light: #e5e7eb;
    }

    /* === Navbar === */
    nav {
      position:fixed; top:0; left:0; right:0;
      height:80px; padding:0 60px;
      background:var(--white);
      display:flex; align-items:center; justify-content:space-between;
      border-bottom:1px solid var(--border-light);
      z-index:1000; transition:box-shadow .3s;
    }
    nav.scrolled { box-shadow:0 3px 12px rgba(0,0,0,0.08) }
    nav .logo img { width:75px }
    nav ul { list-style:none; display:flex; gap:35px }
    nav ul a {
      color:var(--green);
      font-weight:600;
      font-size:1rem;
      opacity:.85;
      transition:opacity .25s;
    }
    nav ul a:hover, nav ul a.active { opacity:1 }
    .account-btn {
      background:#4e7659;
      color:var(--white);
      padding:8px 26px;
      border-radius:30px;
      font-weight:700;
      font-size:.9375rem;
      white-space:nowrap;
      transition:transform .25s,box-shadow .25s;
    }
    .account-btn:hover {
      transform:translateY(-2px);
      box-shadow:0 6px 15px rgba(0,0,0,0.18);
    }

    /* === Hero Section === */
    .hero {
      padding-top:130px;
      min-height:100vh;
      background:var(--white);
      display:flex; align-items:center; justify-content:center;
      position:relative; overflow:hidden;
    }
    .hero::after {
      content:""; position:absolute; bottom:0; left:0;
      width:100%; height:180px;
      background-image:radial-gradient(circle 1px,var(--green) 100%,transparent 0);
      background-size:40px 40px; opacity:.12;
    }
    .hero .container {
      max-width:1200px; width:100%;
      display:flex; gap:90px;
      align-items:center;
      padding:0 24px;
      flex-direction:row-reverse;
    }
    .illustration { flex:0 0 420px; position:relative }
    .phone-img {
      width:130%; max-width:500px;
      transform:translateY(-15%);
      margin:0 auto;
    }
    .ball {
      position:absolute; top:30px; right:-35px;
      width:200px;
      animation:spin 5s linear infinite;
    }
    @keyframes spin { to{transform:rotate(360deg)} }
    .hero-content { max-width:550px; text-align:right }
    .hero-content h1 {
      font-size:2.125rem;
      color:var(--green);
      margin-bottom:28px;
      line-height:1.4;
    }
    .hero-content h1 .highlight { color:var(--gold) }

    /* === Store Buttons === */
    .store-buttons {
      display:flex;
      gap:1rem;
      flex-wrap:wrap;
      align-items:center;
      justify-content:flex-end;
    }
    .playstore-button {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border: 2px solid #000;
      border-radius: 9999px;
      background-color: rgba(0, 0, 0, 1);
      padding: 0.625rem 1.5rem;
      text-align: center;
      color: rgba(255, 255, 255, 1);
      outline: 0;
      transition: all 0.2s ease;
    }
    .playstore-button:hover {
      background-color: transparent;
      color: rgba(0, 0, 0, 1);
    }
    .icon {
      height: 1.5rem;
      width: 1.5rem;
    }
    .texts {
      margin-left: 1rem;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      line-height: 1;
    }
    .text-1 {
      margin-bottom: 0.25rem;
      font-size: 0.75rem;
      line-height: 1rem;
    }
    .text-2 {
      font-weight: 600;
    }

    /* === Sections === */
    .section {
      padding: 80px 0;
      background: var(--grey-light);
    }
    .section:nth-of-type(even) { background: #fff; }
    .container { max-width:1200px; margin:0 auto; padding:0 24px; }
    .section-title {
      text-align: right;
      font-size: 1.75rem;
      margin-bottom: 40px;
      color: var(--green);
    }

    /* ملاعبنا */
    .stadium-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 24px;
    }
    .stadium-card {
      background: #fff;
      border: 1px solid var(--border-light);
      border-radius: 8px;
      overflow: hidden;
      text-align: right;
    }
    .stadium-card img { width:100%; height:160px; object-fit:cover }
    .stadium-card h3 {
      margin: 16px;
      color: var(--green);
    }
    .stadium-card p {
      margin: 0 16px 16px;
      font-size: 0.9375rem;
    }

    /* خدماتنا */
    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 24px;
      text-align: center;
    }
    .service-card {
      background: #fff;
      padding: 24px;
      border-radius: 8px;
      border: 1px solid var(--border-light);
    }
    .service-icon {
      font-size: 2rem;
      margin-bottom: 16px;
      color: var(--gold);
    }
    .service-card h3 {
      margin-bottom: 12px;
      color: var(--green);
    }

    /* تواصل معنا */
    .contact-form {
      background: #fff;
      padding: 24px;
      border-radius: 8px;
      border: 1px solid var(--border-light);
      max-width: 600px;
      margin: 0 auto;
    }
    .form-group { margin-bottom: 16px; text-align: right; }
    .form-group label { display: block; margin-bottom: 8px; }
    .form-group input,
    .form-group textarea {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid var(--border-light);
      border-radius: 4px;
      font-size: 1rem;
    }
    .btn-submit {
      background: var(--green);
      color: #fff;
      padding: 12px 24px;
      border: none;
      border-radius: 30px;
      font-size: 1rem;
      cursor: pointer;
      transition: background .25s;
    }
    .btn-submit:hover {
      background: #0b6b4b;
    }

    /* Footer */
    .footer {
      background: var(--green);
      color: #fff;
      padding: 40px 0;
    }
    .footer-container {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
    }
    .footer-info img { width: 80px; margin-bottom: 16px; }
    .footer-info p { font-size: 0.875rem; }
    .footer-links {
      list-style: none;
      display: flex;
      gap: 16px;
    }
    .footer-links a {
      color: #fff;
      font-size: 0.9375rem;
      opacity: .85;
      transition: opacity .25s;
    }
    .footer-links a:hover { opacity: 1; }
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav id="navbar">
    <div class="logo">
      <img src="/img/HomePage/homepage_logo_nobackground_green.png" alt="The Match" />
    </div>
    <ul>
      <li><a href="#hero" class="active">الرئيسية</a></li>
      <li><a href="#stadiums">ملاعب</a></li>
      <li><a href="#about">من نحن</a></li>
      <li><a href="#services">خدماتنا</a></li>
      <li><a href="#contact">تواصل معنا</a></li>
    </ul>
    <a href="/login" class="account-btn">دخول الملاعب</a>
  </nav>

  <!-- Hero -->
  <header id="hero" class="hero">
    <div class="container">
      <div class="illustration">
        <img src="/img/HomePage/homepage_player_nobackground2.png" class="phone-img" alt="هاتف داخل التطبيق" />
        <img src="/img/HomePage/homepage_Ball_nobackground.png" class="ball" alt="كرة قدم" />
      </div>
      <div class="hero-content">
        <h1>
          الـمنصة الأولى من نــوعها فـي ليبيا<br />
          لــتنظيم مباريات كرة القدم للــهواة<br />
          <span class="highlight">الآن يمكنك إنشاء حجوزاتك بسهولة</span>
        </h1>
        <div class="store-buttons">
          <!-- Google Play Button -->
          <a href="#" class="playstore-button">
            <span class="texts">
              <span class="text-1">GET IT ON</span>
              <span class="text-2">Google Play</span>
            </span>
            <span class="icon">
              <svg viewBox="0 0 512 512" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                <path d="M99.617 8.057a50.191 50.191 0 00-38.815-6.713l230.932 230.933 74.846-74.846L99.617 8.057zM32.139 20.116c-6.441 8.563-10.148 19.077-10.148 30.199v411.358c0 11.123 3.708 21.636 10.148 30.199l235.877-235.877L32.139 20.116zM464.261 212.087l-67.266-37.637-81.544 81.544 81.548 81.548 67.273-37.64c16.117-9.03 25.738-25.442 25.738-43.908s-9.621-34.877-25.749-43.907zM291.733 279.711L60.815 510.629c3.786.891 7.639 1.371 11.492 1.371a50.275 50.275 0 0027.31-8.07l266.965-149.372-74.849-74.847z"/>
              </svg>
            </span>
          </a>

          <!-- App Store Button -->
          <a href="#" class="playstore-button">
            <span class="texts">
              <span class="text-1">Download from</span>
              <span class="text-2">App Store</span>
            </span>
            <span class="icon">
              <svg fill="currentcolor" viewBox="-52.01 0 560.035 560.035" xmlns="http://www.w3.org/2000/svg" stroke="#ffffff">
                <path d="M380.844 297.529c.787 84.752 74.349 112.955 75.164 113.314-.622 1.988-11.754 40.191-38.756 79.652-23.343 34.117-47.568 68.107-85.731 68.811-37.499.691-49.557-22.236-92.429-22.236-42.859 0-56.256 21.533-91.753 22.928-36.837 1.395-64.889-36.891-88.424-70.883-48.093-69.53-84.846-196.475-35.496-282.165 24.516-42.554 68.328-69.501 115.882-70.192 36.173-.69 70.315 24.336 92.429 24.336 22.1 0 63.59-30.096 107.208-25.676 18.26.76 69.517 7.376 102.429 55.552-2.652 1.644-61.159 35.704-60.523 106.559M310.369 89.418C329.926 65.745 343.089 32.79 339.498 0 311.308 1.133 277.22 18.785 257 42.445c-18.121 20.952-33.991 54.487-29.709 86.628 31.421 2.431 63.52-15.967 83.078-39.655"/>
              </svg>
            </span>
          </a>
        </div>
      </div>
    </div>
  </header>

  <!-- ملاعبنا Section -->
  <section id="stadiums" class="section stadiums">
    <div class="container">
      <h2 class="section-title">ملاعبنا</h2>
      <div class="stadium-grid">
        <div class="stadium-card">
          <img src="/img/Stadiums/stadium1.jpg" alt="ملعب 1">
          <h3>الملعب البلدي</h3>
          <p>صالح لمباريات الهواة بسعر مناسب وملاعب عشب صناعي.</p>
        </div>
        <div class="stadium-card">
          <img src="/img/Stadiums/stadium2.jpg" alt="ملعب 2">
          <h3>ملعب النافورة</h3>
          <p>مضمار صغير ومقاعد للجماهير، قريب من وسط المدينة.</p>
        </div>
        <div class="stadium-card">
          <img src="/img/Stadiums/stadium3.jpg" alt="ملعب 3">
          <h3>الملعب الرياضي الجامعي</h3>
          <p>مرافق متكاملة ومساحة واسعة للعائلات.</p>
        </div>
        <div class="stadium-card">
          <img src="/img/Stadiums/stadium4.jpg" alt="ملعب 4">
          <h3>ملعب الأصدقاء</h3>
          <p>موقع هادئ وخدمة حجز سهلة مع مظلات خشبية للمظلة.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- من نحن Section -->
  <section id="about" class="section about">
    <div class="container">
      <h2 class="section-title">من نحن</h2>
      <p>
        منصة «The Match» هي أول منصة ليبية متكاملة لتنظيم مباريات كرة القدم للهواة. نسعى لجعل عملية حجز الملاعب والتواصل مع الفرق أكثر سهولة وسرعة،
        مع تقديم خدمات احترافية ودعم فني متواصل.
      </p>
      <p>
        يتميز فريقنا بشغف الرياضة وحب العمل الميداني، وهدفنا الأساسي هو نشر ثقافة الرياضة وتشجيع الأنشطة الجماعية بين الشباب.
      </p>
    </div>
  </section>

  <!-- خدماتنا Section -->
  <section id="services" class="section services">
    <div class="container">
      <h2 class="section-title">خدماتنا</h2>
      <div class="services-grid">
        <div class="service-card">
          <i class="fas fa-calendar-check service-icon"></i>
          <h3>حجز الملاعب</h3>
          <p>اختيار الملعب المناسب وحجزه في الوقت الذي يناسبك عبر التطبيق.</p>
        </div>
        <div class="service-card">
          <i class="fas fa-users service-icon"></i>
          <h3>تشكيل الفرق</h3>
          <p>إنشاء فريقك ودعوة الأصدقاء للانضمام وتنظيم المباريات بسهولة.</p>
        </div>
        <div class="service-card">
          <i class="fas fa-trophy service-icon"></i>
          <h3>بطولات محلية</h3>
          <p>المشاركة في بطولات تنافسية مع جوائز للفائزين وتنظيم كامل للفعاليات.</p>
        </div>
        <div class="service-card">
          <i class="fas fa-headset service-icon"></i>
          <h3>دعم فني</h3>
          <p>فريقنا متواجد على مدار الساعة لحل أي مشكلة تقنية أو استفسار.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- تواصل معنا Section -->
  <section id="contact" class="section contact">
    <div class="container">
      <h2 class="section-title">تواصل معنا</h2>
      <form action="/contact" method="POST" class="contact-form">
        <div class="form-group">
          <label for="name">الاسم الكامل</label>
          <input type="text" id="name" name="name" placeholder="أدخل اسمك" required>
        </div>
        <div class="form-group">
          <label for="email">البريد الإلكتروني</label>
          <input type="email" id="email" name="email" placeholder="name@example.com" required>
        </div>
        <div class="form-group">
          <label for="message">رسالتك</label>
          <textarea id="message" name="message" rows="5" placeholder="كيف نستطيع مساعدتك؟" required></textarea>
        </div>
        <button type="submit" class="btn-submit">إرسال</button>
      </form>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container footer-container">
      <div class="footer-info">
        <img src="/img/HomePage/homepage_logo_nobackground_green.png" alt="The Match">
        <p>© <span id="year"></span> جميع الحقوق محفوظة لمنصة The Match.</p>
      </div>
      <ul class="footer-links">
        <li><a href="#hero">الرئيسية</a></li>
        <li><a href="#stadiums">ملاعب</a></li>
        <li><a href="#about">من نحن</a></li>
        <li><a href="#services">خدماتنا</a></li>
        <li><a href="#contact">تواصل معنا</a></li>
      </ul>
    </div>
  </footer>

  <script>
    document.getElementById("year").textContent = new Date().getFullYear();
    const navbar = document.getElementById("navbar"),
          links  = document.querySelectorAll("nav ul a"),
          secs   = document.querySelectorAll("section, header");
    window.addEventListener("scroll", () => {
      const y = window.pageYOffset;
      navbar.classList.toggle("scrolled", y > 50);
      secs.forEach(sec => {
        const top = sec.offsetTop - 90,
              bot = top + sec.offsetHeight;
        if (y >= top && y < bot) {
          links.forEach(l=>l.classList.remove("active"));
          const id = sec.getAttribute("id") || "hero";
          document.querySelector(`nav ul a[href="#${id}"]`)?.classList.add("active");
        }
      });
    });
  </script>

</body>
</html>
