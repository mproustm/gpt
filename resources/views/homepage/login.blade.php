<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>منصة راصد | تسجيل الدخول</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

  <style>
    /* === Cairo & Reset === */
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box }
    html { scroll-behavior:smooth }
    body {
      font-family:'Cairo',sans-serif;
      direction:rtl;
      background:#f9fafb;
      color:#374151;
      min-height:100vh;
      display:flex; flex-direction:column;
      padding-top:75px /* navbar height */;
    }
    a { text-decoration:none; color:inherit }

    /* === Navbar from The Match === */
    nav {
      position:fixed; top:0; left:0; right:0;
      height:80px; padding:0 60px;
      background:#fff; display:flex; align-items:center; justify-content:space-between;
      z-index:1000; transition:box-shadow .3s;
      border-bottom:1px solid #e5e7eb;
    }
    nav.scrolled { box-shadow:0 3px 12px rgba(0,0,0,.08) }
    .logo img { width:80px }
    ul { list-style:none; display:flex; gap:35px }
    ul a { color:#4e7659; font-weight:600; opacity:.85; transition:opacity .25s }
    ul a:hover, ul a.active { opacity:1 }
    .account-btn {
      background:#4e7659; color:#fff;
      padding:8px 26px; border-radius:30px;
      font-weight:700; font-size:15px;
      transition:transform .25s, box-shadow .25s;
    }
    .account-btn:hover {
      transform:translateY(-2px);
      box-shadow:0 6px 15px rgba(0,0,0,.18);
    }

    /* === Login Form Styles === */
    @keyframes fadeIn {
      0% { opacity:0; transform:scale(0.9) }
      100% { opacity:1; transform:scale(1) }
    }
    main {
      flex:1;
      display:flex; justify-content:center; align-items:flex-start;
      padding:10px 20px 200px; /* رفع الفورم قليلاً من الأسفل */
    }
    .login-form {
      max-width:420px; width:100%;
      margin-top:40px; /* نزلتها شوية */
      padding:40px;
      background:rgba(255,255,255,0.95);
      border-radius:12px;
      box-shadow:0 10px 30px rgba(0,0,0,0.1);
      animation:fadeIn 1.2s ease-out;
    }
    .form-heading {
      text-align:center; color:#333;
      font-size:2rem; font-weight:600;
      margin-bottom:25px; letter-spacing:1px;
      text-transform:uppercase;
    }
    .input-group {
      position:relative; margin-bottom:20px;
    }
    .input-group .label {
      position:absolute; top:-16px; right:12px;
      font-size:12px; color:#4e7659; font-weight:600;
      opacity:0.7; transition:all .3s ease;
    }
    .input-group input {
      width:100%; padding:15px 50px 15px 20px;
      font-size:1rem; color:#333;
      background:#f5f5f5; border:2px solid #ddd;
      border-radius:10px; outline:none;
      transition:all .3s ease;
    }
    .input-group input:focus {
      border-color:#4e7659;
      box-shadow:0 0 10px rgba(88,188,130,0.4);
    }
    .input-group input:focus + .label {
      top:-20px; font-size:11px; color:#4e7659; opacity:1;
    }
    .input-group .toggle-password {
      position:absolute;
      top:50%; left:15px;
      transform:translateY(-50%);
      font-size:18px; color:#6b7280;
      cursor:pointer; transition:color .2s;
    }
    .input-group .toggle-password:hover {
      color:#4e7659;
    }
    .forgot-password {
      text-align:left; margin-bottom:20px;
    }
    .forgot-password a {
      font-size:14px; color:#4e7659;
      transition:color .3s ease;
    }
    .forgot-password a:hover { color:#45a56b }
    .submit {
      width:100%; padding:15px;
      background:#4e7659; color:#fff;
      border:none; border-radius:30px;
      font-size:1.1rem; font-weight:600;
      cursor:pointer; transition:all .3s ease;
    }
    .submit:hover {
      background:#4e7659;
      transform:translateY(-2px);
      box-shadow:0 5px 20px rgba(88,188,130,0.3);
    }
    .signup-link {
      text-align:center; font-size:14px; color:#333;
      margin-top:15px;
    }
    .signup-link a {
      color:#4e7659; font-weight:600; transition:color .3s ease;
    }
    .signup-link a:hover { color:#45a56b }
    @media (max-width:480px) {
      main { padding:80px 10px 20px }
      .login-form { padding:30px; width:90%; margin-top:20px }
    }

    /* === Footer مرتب === */
    footer {
      background:#fff;
      border-top:1px solid #e5e7eb;
      padding:20px 0;
      font-size:14px;
      color:#555;
    }
    .footer-container {
      max-width:1024px; margin:auto;
      display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center;
      gap:15px;
      padding:0 20px;
    }
    .footer-left, .footer-right {
      flex:1; min-width:200px; text-align:right;
    }
    .footer-right { text-align:left }
    .footer-links a {
      margin:0 8px; color:#4e7659; transition:opacity .2s;
    }
    .footer-links a:hover { opacity:.7 }
  </style>
</head>

<body>

  <!-- Navbar -->
  <nav id="navbar">
    <div class="logo">
      <img src="/img/HomePage/homepage_logo_nobackground_green.png" alt="The Match">
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

  <!-- Login Form -->
  <main>
    <form class="login-form" action="{{ route('login.post') }}" method="POST" novalidate>
      @csrf
      <div class="form-heading">تسجيل الدخول</div>

      <div class="input-group">
        <input type="email" id="email" name="email" required placeholder="أدخل بريدك الإلكتروني" value="{{ old('email') }}"/>
        <label class="label" for="email">البريد الإلكتروني</label>
        @error('email')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="input-group">
        <input type="password" id="password" name="password" required placeholder="••••••••"/>
        <label class="label" for="password">كلمة المرور</label>
        <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
        @error('password')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <div class="forgot-password">
  <a href="{{ route('owner.password.request') }}">نسيت كلمة المرور؟</a>
      </div>

      <button class="submit" type="submit">دخول</button>

      <div class="signup-link">
        ليس لديك حساب؟ <a href="">إنشاء حساب</a>
      </div>
    </form>
  </main>

  <!-- Footer -->
  <footer>
    <div class="footer-container">
      <div class="footer-left">© {{ date('Y') }} منصة راصد – جميع الحقوق محفوظة.</div>
      <div class="footer-right footer-links">
        <a href="#">سياسة الخصوصية</a>|
        <a href="#">الشروط والأحكام</a>|
        <a href="#">تواصل معنا</a>
      </div>
    </div>
  </footer>

  <script>
    // إضافة ظل للنافبار عند التمرير
    const nav = document.getElementById('navbar');
    window.addEventListener('scroll', () => {
      nav.classList.toggle('scrolled', window.scrollY > 50);
    });

    // إخفاء/إظهار كلمة المرور
    function togglePassword() {
      const pw = document.getElementById('password');
      const icon = document.querySelector('.toggle-password');
      if (pw.type === 'password') {
        pw.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
      } else {
        pw.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
      }
    }
  </script>

</body>
</html>
