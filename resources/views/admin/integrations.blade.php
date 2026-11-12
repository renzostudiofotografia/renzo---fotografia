@extends('admin.layout')
@section('title', 'Conexiones del estudio')
@section('content')
<main class="page-padding"><div class="page-intro"><div><span class="eyebrow">UN ESTUDIO CONECTADO</span><h1>Menos tareas. Más historias.</h1><p>Conecta tus fotografías y tu agenda sin complicar la edición de tu sitio.</p></div></div>
<div class="integration-grid">
    <article class="integration-card">
        <div class="integration-logo cloudflare-logo">☁</div>
        <span class="connection-pill {{ $r2Ready ? 'connected' : '' }}">{{ $r2Ready ? 'Configurado' : 'Pendiente de configurar' }}</span>
        <h2>Cloudflare R2</h2>
        <p>Guarda las fotos públicas de tu biblioteca en un bucket y entrégalas desde un dominio con caché de Cloudflare.</p>
        <ol>
            <li>Crea un bucket en R2 y conecta un subdominio público para las fotos.</li>
            <li>Crea credenciales S3 con permiso <strong>Object Read &amp; Write</strong> para ese bucket.</li>
            <li>Añade <code>CLOUDFLARE_R2_ACCOUNT_ID</code>, <code>CLOUDFLARE_R2_BUCKET</code>, <code>CLOUDFLARE_R2_ACCESS_KEY_ID</code>, <code>CLOUDFLARE_R2_SECRET_ACCESS_KEY</code> y <code>CLOUDFLARE_R2_PUBLIC_URL</code> al entorno del servidor.</li>
            <li>Sube una foto de prueba desde la biblioteca y comprueba que su tarjeta indique <strong>CLOUDFLARE R2</strong>.</li>
        </ol>
        <p class="muted">Si activaste Transformations para el dominio de las fotos, puedes habilitar la optimización con <code>CLOUDFLARE_R2_OPTIMIZE=true</code>.</p>
        <a class="button secondary" href="{{ route('admin.media') }}">Ir a mi biblioteca →</a>
        <a class="text-link" href="https://developers.cloudflare.com/r2/get-started/s3/" target="_blank" rel="noopener">Guía de Cloudflare R2 ↗</a>
    </article>
<article class="integration-card"><div class="integration-logo cloudflare-logo">☁</div><span class="connection-pill {{ $cloudflareReady ? 'connected' : '' }}">{{ $cloudflareReady ? 'Conectado' : 'Pendiente de configurar' }}</span><h2>Cloudflare Images</h2><p>Almacena y entrega tus fotografías desde Cloudflare. La biblioteca conserva enlaces estables para utilizarlas en todas las páginas.</p><ol><li>Crea tu cuenta y activa Cloudflare Images.</li><li>Genera un token con permiso <strong>Images: Edit</strong> para tu cuenta.</li><li>Configura <code>CLOUDFLARE_ACCOUNT_ID</code> y <code>CLOUDFLARE_IMAGES_TOKEN</code> en el entorno del servidor.</li></ol><p class="muted">Las credenciales permanecen en el servidor. Este panel nunca muestra tu token.</p><a class="button secondary" href="{{ route('admin.media') }}">Ir a mi biblioteca →</a><a class="text-link" href="https://developers.cloudflare.com/images/" target="_blank" rel="noopener">Guía de Cloudflare ↗</a></article>
<article class="integration-card"><div class="integration-logo calendly-logo">C</div><span class="connection-pill {{ $calendlyReady ? 'connected' : '' }}">{{ $calendlyReady ? 'Conectado' : 'Añade tu enlace' }}</span><h2>Calendly</h2><p>Permite que las parejas consulten tu disponibilidad real y elijan una cita directamente desde tu sitio.</p><ol><li>Crea tu cuenta y un evento para conocer a las parejas.</li><li>Conecta tu calendario y define tus horarios en Calendly.</li><li>Pega el enlace del evento en el apartado Citas, guarda y publica.</li></ol><p class="muted">Las reservas y los cambios de horario se gestionan en Calendly. No necesitas compartir una clave de API para mostrar la agenda.</p><a class="button primary" href="{{ route('admin.edit', 'booking') }}">Configurar mi agenda →</a><a class="text-link" href="https://calendly.com/" target="_blank" rel="noopener">Abrir Calendly ↗</a></article></div>
<div class="inline-note">Mientras configuras estas conexiones, la biblioteca local y el contacto directo siguen disponibles.</div></main>
@endsection
