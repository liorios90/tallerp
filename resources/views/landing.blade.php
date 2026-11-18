<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TallerLab · Reparación de computadoras, celulares y electrónicos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{margin:0;background:#f4f7fb;color:#15233b;font:16px/1.5 'DM Sans',sans-serif}
        a{color:inherit;text-decoration:none}
        .wrap{width:min(1080px,calc(100% - 32px));margin:0 auto}
        .top{position:sticky;top:0;z-index:5;background:rgba(255,255,255,.94);border-bottom:1px solid #e6edf5;backdrop-filter:blur(10px)}
        .nav{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:14px 0}
        .brand{display:flex;align-items:center;gap:10px;font:800 18px Manrope,sans-serif}
        .mark{width:36px;height:36px;border-radius:11px;background:#315cf5;color:#fff;display:grid;place-items:center}
        .menu{display:flex;align-items:center;gap:18px;color:#56647a;font-size:14px;font-weight:600}
        .actions{display:flex;gap:8px}
        .btn{display:inline-flex;align-items:center;justify-content:center;border-radius:10px;padding:12px 16px;font:600 14px 'DM Sans',sans-serif;border:1px solid transparent}
        .btn-primary{background:#315cf5;color:#fff}
        .btn-light{background:#fff;color:#15233b;border-color:#dbe3ee}
        .btn-whatsapp{background:#128c7e;color:#fff}
        .hero{padding:64px 0 36px;display:grid;grid-template-columns:1.15fr .85fr;gap:36px;align-items:center}
        .eyebrow{margin:0 0 12px;color:#315cf5;font-size:12px;font-weight:700;letter-spacing:1.3px;text-transform:uppercase}
        h1{margin:0;font:800 52px/1.05 Manrope,sans-serif;letter-spacing:-1.4px}
        .lead{margin:18px 0 0;color:#56647a;font-size:18px}
        .value{margin:18px 0 0;padding-left:14px;border-left:3px solid #315cf5;color:#15233b;font-weight:600}
        .hero-actions{display:flex;flex-wrap:wrap;gap:10px;margin-top:26px}
        .panel{background:#15233b;color:#fff;border-radius:20px;padding:26px}
        .panel h2{margin:0 0 8px;font:700 22px Manrope,sans-serif}
        .panel p{margin:0;color:#c5d0e0}
        .panel ul{list-style:none;margin:18px 0 0;padding:0;display:grid;gap:10px}
        .panel li{background:#ffffff12;border-radius:12px;padding:12px 14px}
        .panel b{display:block}
        .panel span{color:#c5d0e0;font-size:14px}
        section{padding:28px 0}
        .section-head{max-width:640px;margin-bottom:18px}
        h2{margin:0;font:800 32px/1.15 Manrope,sans-serif;letter-spacing:-.6px}
        .section-head p{margin:8px 0 0;color:#68768c}
        .grid{display:grid;gap:12px}
        .services{grid-template-columns:repeat(3,1fr)}
        .benefits{grid-template-columns:repeat(4,1fr)}
        .card{background:#fff;border:1px solid #e6edf5;border-radius:16px;padding:18px}
        .card h3{margin:0 0 6px;font:700 18px Manrope,sans-serif}
        .card p{margin:0;color:#68768c}
        footer{padding:28px 0 40px;color:#8290a5;font-size:13px}
        @media(max-width:860px){
            h1{font-size:36px}
            .hero,.services,.benefits{grid-template-columns:1fr}
            .nav,footer .wrap{flex-direction:column;align-items:flex-start}
            .menu{display:none}
        }
    </style>
</head>
<body>
    <header class="top">
        <div class="wrap nav">
            <a class="brand" href="{{ route('landing') }}"><span class="mark">T</span> TallerLab</a>
            <nav class="menu">
                <a href="#servicios">Servicios</a>
                <a href="#beneficios">Beneficios</a>
            </nav>
            <div class="actions">
                <a class="btn btn-light" href="{{ route('login') }}">Iniciar sesión</a>
                <a class="btn btn-primary" href="{{ route('register') }}">Registrarse</a>
            </div>
        </div>
    </header>

    <main>
        <div class="wrap hero">
            <div>
                <p class="eyebrow">Computadoras, celulares y electrónicos</p>
                <h1>Tu equipo vuelve a funcionar. Hoy.</h1>
                <!-- <p class="lead">Reparamos y vendemos repuestos para computadoras, celulares y aparatos electrónicos. Diagnóstico claro, precio por adelantado y la falla lista el mismo día cuando se puede.</p>
                <p class="value">Sabes cuánto cuesta antes de autorizar. Si no queda bien, la garantía responde.</p> -->
                @if($whatsappUrl)
                    <div class="hero-actions">
                        <a class="btn btn-whatsapp" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Escribir por WhatsApp</a>
                    </div>
                @endif
            </div>
            <!-- <aside class="panel">
                <h2>Así trabajamos</h2>
                <p>Sin letra chica y sin llevarte el equipo a otro lado.</p>
                <ul>
                    <li><b>1. Nos cuentas la falla</b><span>Pantalla, batería, lentitud o algo que ya no enciende.</span></li>
                    <li><b>2. Te damos el precio</b><span>Presupuesto sin cargo, antes de abrir el equipo.</span></li>
                    <li><b>3. Lo reparamos</b><span>Repuestos originales y entrega con garantía.</span></li>
                </ul>
            </aside> -->
        </div>

        <section id="servicios">
            <div class="wrap">
                <div class="section-head">
                    <p class="eyebrow">Servicios</p>
                    <h2>Lo que más nos traen, y lo dejamos resuelto.</h2>
                    <p>Computadoras, celulares, tablets y aparatos del hogar. Si se puede reparar, te lo decimos. Si no, también.</p>
                </div>
                <div class="grid services">
                    <article class="card"><h3>Pantallas rotas</h3><p>Vidrio estrellado o imagen con rayas en celulares, tablets y laptops.</p></article>
                    <article class="card"><h3>Baterías que no duran</h3><p>Se apaga solo, se hincha o hay que cargarlo todo el día.</p></article>
                    <article class="card"><h3>Equipos lentos</h3><p>Tarda en abrir, se traba o se calienta con el uso normal.</p></article>
                    <article class="card"><h3>Virus y fallas</h3><p>Ventanas extrañas, archivos perdidos o un sistema que no arranca.</p></article>
                    <article class="card"><h3>No enciende</h3><p>Pantalla negra, no carga, se mojó o dejó de responder.</p></article>
                    <article class="card"><h3>Repuestos</h3><p>Pantallas, baterías, cargadores y piezas para instalar o llevar.</p></article>
                </div>
            </div>
        </section>

        <section id="beneficios">
            <div class="wrap">
                <div class="section-head">
                    <p class="eyebrow">Por qué dejarnos el equipo</p>
                    <h2>Rápido, con precio claro y con respaldo.</h2>
                </div>
                <div class="grid benefits">
                    <article class="card"><h3>Garantía</h3><p>La reparación sale con respaldo. Si la falla vuelve, la revisamos.</p></article>
                    <article class="card"><h3>Presupuesto sin cargo</h3><p>Te decimos el costo antes de empezar. Tú decides.</p></article>
                    <article class="card"><h3>Reparación en el día</h3><p>Pantallas, baterías y fallas comunes quedan listas en la misma jornada.</p></article>
                    <article class="card"><h3>Repuestos originales</h3><p>Piezas correctas para tu modelo, sin cambios que no te avisamos.</p></article>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <div class="wrap" style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <span>TallerLab · Reparación y venta de repuestos</span>
            <span><a href="{{ route('login') }}">Iniciar sesión</a> · <a href="{{ route('register') }}">Registrarse</a></span>
        </div>
    </footer>
</body>
</html>
