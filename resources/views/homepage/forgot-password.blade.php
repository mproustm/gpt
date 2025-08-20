<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>استعادة كلمة المرور</title>
  <!-- You can reuse the styles from your login page -->
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet"/>
  <style>
    body { font-family:'Cairo',sans-serif; background:#f9fafb; display:flex; justify-content:center; align-items:center; min-height:100vh; }
    .form-container { max-width:420px; width:100%; padding:40px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.1); text-align:center; }
    h2 { margin-bottom:15px; color:#333; }
    p { margin-bottom:25px; color:#666; font-size:15px; }
    .input-group input { width:100%; padding:15px; font-size:1rem; border:2px solid #ddd; border-radius:10px; margin-bottom:20px; text-align:right; }
    .submit { width:100%; padding:15px; background:#4e7659; color:#fff; border:none; border-radius:30px; font-size:1.1rem; cursor:pointer; }
    .alert-success { padding:15px; color:#155724; background-color:#d4edda; border-color:#c3e6cb; border-radius:8px; margin-bottom:20px; }
    .alert-danger { color:#721c24; background-color:#f8d7da; border-color:#f5c6cb; padding: .75rem 1.25rem; border-radius:.25rem; }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>نسيت كلمة المرور؟</h2>
    <p>لا مشكلة. فقط أدخل بريدك الإلكتروني أدناه وسنرسل لك رابطًا لإعادة تعيين كلمة المرور.</p>

    @if (session('status'))
      <div class="alert alert-success" role="alert">
        {{ session('status') }}
      </div>
    @endif

    <form method="POST" action="{{ route('owner.password.email') }}">
      @csrf
      <div class="input-group">
        <input type="email" id="email" name="email" required autofocus placeholder="بريدك الإلكتروني" value="{{ old('email') }}"/>
        @error('email')
          <div class="alert alert-danger">{{ $message }}</div>
        @enderror
      </div>
      <button class="submit" type="submit">إرسال رابط استعادة كلمة المرور</button>
    </form>
  </div>
</body>
</html>