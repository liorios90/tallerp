<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro · TallerLab</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#f5f7fa;color:#15233b;font:15px 'DM Sans',sans-serif;padding:28px 16px}.card{width:min(760px,100%);margin:0 auto;background:#fff;border:1px solid #e8edf3;border-radius:16px;padding:30px;box-shadow:0 18px 50px #15233b12}.brand{display:flex;align-items:center;gap:11px;color:#15233b;font:800 19px Manrope,sans-serif;margin-bottom:24px;text-decoration:none}.mark{width:38px;height:38px;border-radius:12px;background:#315cf5;color:#fff;display:grid;place-items:center}.eyebrow{color:#8290a5;font-size:11px;text-transform:uppercase;letter-spacing:1.2px;font-weight:700;margin:0 0 8px}h1{font:700 26px Manrope;margin:0}p.sub{color:#8290a5;font-size:13px;margin:8px 0 8px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}.field{display:flex;flex-direction:column;gap:7px;margin:12px 0}.field.full{grid-column:1/-1}.field label{font-size:12px;font-weight:600;color:#56647a}.field input{width:100%;border:1px solid #e3e8ef;border-radius:8px;padding:12px;color:#15233b;font:inherit}.button{border:0;border-radius:8px;background:#315cf5;color:#fff;width:100%;padding:12px;font:600 14px 'DM Sans',sans-serif;cursor:pointer;margin-top:8px}.error{background:#ffeded;border:1px solid #f5cccc;color:#b33;border-radius:8px;padding:11px;font-size:13px;margin:14px 0}.login{display:block;text-align:center;margin-top:16px;color:#315cf5;font-size:13px;font-weight:600}@media(max-width:680px){.grid{grid-template-columns:1fr}.field.full{grid-column:auto}}
    </style>
</head>
<body>
    <main class="card">
        <a class="brand" href="{{ route('landing') }}"><span class="mark">T</span><span>TallerLab</span></a>
        <p class="eyebrow">Nueva empresa</p>
        <h1>Registrarse</h1>
        <p class="sub">La cuenta queda en demostración por 10 días, contados desde el día del registro.</p>
        @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
        <form method="post" action="{{ route('register.store') }}">
            @csrf
            <div class="grid">
                <div class="field"><label for="firstName">Nombres *</label><input id="firstName" name="first_name" value="{{ old('first_name') }}" required maxlength="80" autofocus></div>
                <div class="field"><label for="lastName">Apellidos *</label><input id="lastName" name="last_name" value="{{ old('last_name') }}" required maxlength="80"></div>
                <div class="field"><label for="phone">Teléfono *</label><input id="phone" name="phone" value="{{ old('phone') }}" required maxlength="40"></div>
                <div class="field"><label for="phoneSecondary">Teléfono adicional</label><input id="phoneSecondary" name="phone_secondary" value="{{ old('phone_secondary') }}" maxlength="40"></div>
                <div class="field"><label for="document">Cédula o RUC *</label><input id="document" name="document" value="{{ old('document') }}" required maxlength="40"></div>
                <div class="field"><label for="email">Correo de acceso *</label><input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="160" autocomplete="username"></div>
                <div class="field full"><label for="address">Dirección</label><input id="address" name="address" value="{{ old('address') }}" maxlength="255"></div>
                <div class="field"><label for="password">Contraseña *</label><input id="password" name="password" type="password" required minlength="8" autocomplete="new-password"></div>
                <div class="field"><label for="passwordConfirmation">Confirmar contraseña *</label><input id="passwordConfirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password"></div>
            </div>
            <button class="button" type="submit">Crear cuenta</button>
        </form>
        <a class="login" href="{{ route('login') }}">Ya tengo cuenta. Iniciar sesión</a>
    </main>
</body>
</html>
