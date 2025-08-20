<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="UTF-8" />
  <title>إعادة تعيين كلمة المرور</title>
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet"/>
  <style>
    body { font-family:'Cairo',sans-serif; background:#f9fafb; display:flex; justify-content:center; align-items:center; min-height:100vh; }
    .form-container { max-width:420px; width:100%; padding:40px; background:#fff; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.1); }
    h2 { text-align:center; margin-bottom:25px; color:#333; }
    .input-group { margin-bottom: 20px; }
    .input-group input { width:100%; padding:15px; font-size:1rem; border:2px solid #ddd; border-radius:10px; text-align:right; }
    .submit { width:100%; padding:15px; background:#4e7659; color:#fff; border:none; border-radius:30px; font-size:1.1rem; cursor:pointer; }
    .alert-danger { color:#721c24; background-color:#f8d7da; border-color:#f5c6cb; padding: .75rem 1.25rem; margin-top:5px; border-radius:.25rem; font-size: 14px; }
  </style>
</head>
<body>
  <div class="form-container">
    <h2>إعادة تعيين كلمة المرور</h2>

    <form method="POST" action="{{ route('owner.password.update') }}">
      @csrf
      <!-- Password Reset Token -->
      <input type="hidden" name="token" value="{{ $token }}">

      <div class="input-group">
        <input type="email" id="email" name="email" required placeholder="البريد الإلكتروني" value="{{ $email ?? old('email') }}"/>
        @error('email') <div class="alert alert-danger">{{ $message }}</div> @enderror
      </div>

      <div class="input-group">
        <input type="password" id="password" name="password" required placeholder="كلمة المرور الجديدة"/>
        @error('password') <div class="alert alert-danger">{{ $message }}</div> @enderror
      </div>

      <div class="input-group">
        <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="تأكيد كلمة المرور الجديدة"/>
      </div>

      <button class="submit" type="submit">إعادة تعيين كلمة المرور</button>
    </form>
  </div>
</body>
</html>