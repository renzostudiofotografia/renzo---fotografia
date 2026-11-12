<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>{{ $setup ? 'Configura tu estudio' : 'Accede a tu estudio' }}</title><link rel="stylesheet" href="{{ asset('css/admin.css') }}?v={{ filemtime(public_path('css/admin.css')) }}"></head>
<body class="login-page">
    <div class="login-art" style="background-image:url('{{ asset('assets/images/hero/hero-03.webp') }}')"><a class="login-wordmark" href="{{ route('home') }}">RENZO <span>Studio</span></a><div><span class="eyebrow">UN ESPACIO PARA CREAR</span><h1>Tu mirada.<br>Tu historia.<br>Tu sitio.</h1><p>Cada detalle cuenta. Haz que tu web hable de ti.</p></div><span class="art-caption">FOTOGRAFÍA · HISTORIAS · CONEXIONES</span></div>
    <main class="login-main"><div class="login-card"><span class="brand-mark">R.</span><span class="eyebrow">BIENVENIDO A TU ESTUDIO</span><h2>{{ $setup ? 'Hagámoslo tuyo.' : 'Qué bueno verte.' }}</h2><p>{{ $setup ? 'Elige tus datos para crear el acceso privado al panel de tu sitio.' : 'Inicia sesión para cuidar cada detalle de tu sitio.' }}</p>
    @if($errors->any())<div class="notice error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
    @if(!$hasAdmin && !$setup)<div class="setup-hint"><strong>Tu estudio está listo para configurarse.</strong><p>Utiliza el enlace privado de activación de la instalación para elegir tu correo y contraseña. Así el acceso queda exclusivamente en tus manos.</p></div>@else
    <form method="POST" action="{{ $setup ? request()->fullUrl() : route('admin.authenticate') }}">@csrf
        @if($setup)<label class="field">Tu nombre<input name="name" value="{{ old('name') }}" autocomplete="name" required maxlength="100"></label>@endif
        <label class="field">Correo electrónico<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required></label>
        <label class="field">Contraseña<input type="password" name="password" autocomplete="{{ $setup ? 'new-password' : 'current-password' }}" required @if($setup) minlength="12" @endif></label>
        @if($setup)<small class="field-help">Al menos 12 caracteres, con letras y números.</small><label class="field">Repite la contraseña<input type="password" name="password_confirmation" autocomplete="new-password" required></label>@else<label class="checkbox-label"><input type="checkbox" name="remember" value="1">Recordarme en este equipo</label>@endif
        <button class="button primary full-width" type="submit">{{ $setup ? 'Crear mi estudio' : 'Entrar al estudio' }} <span>→</span></button>
    </form>@endif
    <a class="subtle-link" href="{{ route('home') }}">← Volver al sitio</a><small class="login-footer">Un espacio privado. Todas tus ideas, en un solo lugar.</small></div></main>
</body></html>
