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
        .hero{padding:36px 0 28px;display:grid;grid-template-columns:minmax(0,.9fr) minmax(0,1.1fr);gap:32px;align-items:center}
        .build{margin:0;background:linear-gradient(180deg,#fff,#eef3fa);border:1px solid #e6edf5;border-radius:24px;padding:8px 8px 0;box-shadow:0 18px 50px #15233b14}
        .build svg{display:block;width:100%;height:auto}
        .part{opacity:0;transform-box:fill-box;transform-origin:center;animation:part-in .7s cubic-bezier(.2,.7,.2,1) forwards;animation-delay:var(--d,0s)}
        .screen-ui{opacity:0;animation:fade .45s ease 2.55s forwards}
        .bar{transform-box:fill-box;transform-origin:left center;transform:scaleX(0);animation:grow .55s ease forwards}
        .blades{transform-box:fill-box;transform-origin:center;animation:spin 1.15s linear 1.7s infinite}
        .cable{stroke-dasharray:240;stroke-dashoffset:240;animation:draw .7s ease 2.15s forwards}
        .led{opacity:0;animation:glow 1.1s ease 3.1s infinite alternate}
        @keyframes part-in{from{opacity:0;transform:translate(var(--dx,0),var(--dy,18px)) scale(var(--s,.97))}to{opacity:1;transform:none}}
        @keyframes fade{to{opacity:1}}
        @keyframes grow{to{transform:scaleX(1)}}
        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes draw{to{stroke-dashoffset:0}}
        @keyframes glow{from{opacity:.25}to{opacity:1}}
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
        @media(prefers-reduced-motion:reduce){
            .part,.screen-ui,.bar,.cable,.blades,.led{animation:none;opacity:1;transform:none;stroke-dashoffset:0}
        }
        @media(max-width:860px){
            h1{font-size:36px}
            .hero,.services,.benefits{grid-template-columns:1fr}
            .build{order:-1}
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
            <figure class="build">
                <svg viewBox="0 0 640 520" role="img" aria-label="Una computadora que se va armando">
                    <defs>
                        <linearGradient id="screenGrad" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0%" stop-color="#8eb6ff"/>
                            <stop offset="100%" stop-color="#315cf5"/>
                        </linearGradient>
                        <clipPath id="bay"><rect x="64" y="92" width="176" height="312" rx="16"/></clipPath>
                    </defs>
                    <ellipse cx="300" cy="486" rx="230" ry="16" fill="#d5deea"/>
                    <g class="part" style="--d:.05s;--dy:12px;--s:.96">
                        <polygon points="248,72 276,94 276,424 248,446" fill="#0e1728"/>
                        <rect x="46" y="72" width="202" height="374" rx="22" fill="#15233b"/>
                        <rect x="64" y="92" width="176" height="312" rx="16" fill="#1a2744"/>
                        <rect x="232" y="118" width="8" height="34" rx="3" fill="#315cf5"/>
                        <circle cx="236" cy="176" r="5" fill="#243656"/>
                        <circle cx="236" cy="196" r="5" fill="#243656"/>
                    </g>
                    <g clip-path="url(#bay)">
                        <g class="part" style="--d:.35s;--dx:-36px">
                            <rect x="80" y="110" width="144" height="248" rx="8" fill="#243656"/>
                            <path d="M96 132h112M96 156h80M96 250h120M120 132v150" fill="none" stroke="#6aa2ff" stroke-width="2" opacity=".7"/>
                        </g>
                        <g class="part" style="--d:.7s;--dy:-46px">
                            <rect x="98" y="126" width="16" height="78" rx="3" fill="#b9d4ff"/>
                            <rect x="100" y="190" width="12" height="8" fill="#e3c27a"/>
                        </g>
                        <g class="part" style="--d:.88s;--dy:-46px">
                            <rect x="122" y="126" width="16" height="78" rx="3" fill="#8eb6ff"/>
                            <rect x="124" y="190" width="12" height="8" fill="#e3c27a"/>
                        </g>
                        <g class="part" style="--d:1.15s;--s:.8">
                            <rect x="156" y="128" width="52" height="52" rx="8" fill="#315cf5"/>
                        </g>
                        <g class="part" style="--d:1.4s;--s:.7">
                            <circle cx="182" cy="154" r="20" fill="#d7e4f5"/>
                            <g class="blades">
                                <g transform="translate(182 154)">
                                    <path d="M0 0 C10 -2 16 -12 8 -16 C2 -12 -2 -6 0 0Z" fill="#315cf5"/>
                                    <path d="M0 0 C2 10 12 16 16 8 C12 2 6 -2 0 0Z" fill="#6aa2ff"/>
                                    <path d="M0 0 C-10 2 -16 12 -8 16 C-2 12 2 6 0 0Z" fill="#315cf5"/>
                                </g>
                            </g>
                            <circle cx="182" cy="154" r="4" fill="#15233b"/>
                        </g>
                        <g class="part" style="--d:1.75s;--dx:48px">
                            <rect x="90" y="248" width="124" height="40" rx="7" fill="#121c33"/>
                            <circle cx="118" cy="268" r="10" fill="#243656" stroke="#6aa2ff" stroke-width="2"/>
                            <circle cx="154" cy="268" r="10" fill="#243656" stroke="#6aa2ff" stroke-width="2"/>
                        </g>
                        <g class="part" style="--d:2s;--dx:-28px">
                            <rect x="96" y="306" width="78" height="22" rx="4" fill="#314e7a"/>
                            <rect x="104" y="314" width="28" height="6" rx="2" fill="#8eb6ff"/>
                        </g>
                    </g>
                    <g class="part" style="--d:2.2s;--dy:0px;--s:1">
                        <rect x="64" y="92" width="176" height="312" rx="16" fill="#ffffff" opacity=".08" stroke="#d7e4f5" stroke-opacity=".55"/>
                        <circle cx="78" cy="108" r="3" fill="#d7e4f5"/>
                        <circle cx="226" cy="108" r="3" fill="#d7e4f5"/>
                        <circle cx="78" cy="388" r="3" fill="#d7e4f5"/>
                        <circle cx="226" cy="388" r="3" fill="#d7e4f5"/>
                    </g>
                    <circle class="led" cx="236" cy="390" r="5" fill="#49e6a2"/>
                    <g class="part" style="--d:2.35s;--dy:28px">
                        <rect x="300" y="338" width="168" height="14" rx="7" fill="#15233b"/>
                        <rect x="368" y="268" width="28" height="74" rx="6" fill="#15233b"/>
                        <rect x="286" y="64" width="300" height="204" rx="16" fill="#15233b"/>
                        <rect x="302" y="80" width="268" height="156" rx="8" fill="url(#screenGrad)"/>
                    </g>
                    <g class="screen-ui">
                        <rect class="bar" x="320" y="100" width="150" height="14" rx="4" fill="#fff" style="animation-delay:2.7s"/>
                        <rect class="bar" x="320" y="126" width="210" height="48" rx="8" fill="#ffffff" opacity=".9" style="animation-delay:2.9s"/>
                        <rect class="bar" x="320" y="186" width="120" height="10" rx="4" fill="#d7e4f5" style="animation-delay:3.1s"/>
                    </g>
                    <path class="cable" d="M248 360 C 290 430, 360 430, 382 352" fill="none" stroke="#15233b" stroke-width="5" stroke-linecap="round"/>
                    <g class="part" style="--d:2.85s;--dy:24px">
                        <rect x="300" y="392" width="286" height="40" rx="10" fill="#15233b"/>
                        <g fill="#243656">
                            <rect x="314" y="402" width="22" height="10" rx="2"/>
                            <rect x="342" y="402" width="22" height="10" rx="2"/>
                            <rect x="370" y="402" width="22" height="10" rx="2"/>
                            <rect x="398" y="402" width="22" height="10" rx="2"/>
                            <rect x="426" y="402" width="22" height="10" rx="2"/>
                            <rect x="454" y="402" width="22" height="10" rx="2"/>
                            <rect x="482" y="402" width="22" height="10" rx="2"/>
                            <rect x="510" y="402" width="22" height="10" rx="2"/>
                            <rect x="538" y="402" width="32" height="10" rx="2"/>
                        </g>
                    </g>
                    <g class="part" style="--d:3.1s;--dx:20px">
                        <rect x="596" y="404" width="28" height="40" rx="12" fill="#15233b"/>
                        <rect x="602" y="410" width="16" height="16" rx="6" fill="#243656"/>
                    </g>
                </svg>
            </figure>
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
