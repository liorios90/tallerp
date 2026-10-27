<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ingresar · TallerLab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#f5f7fa;color:#15233b;font:15px 'DM Sans',sans-serif;display:grid;place-items:center;padding:20px}.card{width:min(420px,100%);background:#fff;border:1px solid #e8edf3;border-radius:16px;padding:30px;box-shadow:0 18px 50px #15233b12}.brand{display:flex;align-items:center;gap:11px;color:#15233b;font:800 19px Manrope,sans-serif;margin-bottom:30px}.mark{width:38px;height:38px;border-radius:12px;background:#315cf5;color:#fff;display:grid;place-items:center}.eyebrow{color:#8290a5;font-size:11px;text-transform:uppercase;letter-spacing:1.2px;font-weight:700;margin:0 0 8px}h1{font:700 26px Manrope;margin:0}p.sub{color:#8290a5;font-size:13px;margin:8px 0 24px}.field{display:flex;flex-direction:column;gap:7px;margin:15px 0}.field label{font-size:12px;font-weight:600;color:#56647a}.field input{width:100%;border:1px solid #e3e8ef;border-radius:8px;padding:12px;color:#15233b;font:inherit}.remember{display:flex;align-items:center;gap:8px;font-size:12px;color:#68768c;margin:16px 0}.button{border:0;border-radius:8px;background:#315cf5;color:#fff;width:100%;padding:12px;font:600 14px 'DM Sans',sans-serif;cursor:pointer}.error{background:#ffeded;border:1px solid #f5cccc;color:#b33;border-radius:8px;padding:11px;font-size:13px;margin-bottom:14px}
    </style>
</head>
<body>
    <main class="card">
        <div class="brand"><span class="mark">T</span><span>TallerLab</span></div>
        <p class="eyebrow">Acceso al taller</p>
        <h1>Iniciar sesión</h1>
        <p class="sub">Ingresa con la cuenta del personal autorizado.</p>
        @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('login.store') }}">
            @csrf
            <div class="field"><label for="email">Correo electrónico</label><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="username" required autofocus></div>
            <div class="field"><label for="password">Contraseña</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
            <label class="remember"><input type="checkbox" name="remember" value="1"> Mantener sesión iniciada</label>
            <button class="button" type="submit">Ingresar</button>
        </form>
    </main>
</body>
</html>
