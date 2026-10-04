<header class="header">
    <nav class="navbar" aria-label="Navegación principal">
        <a class="logo" href="{{ route('home') }}" aria-label="{{ config('site.brand') }}, inicio">
            {{ config('site.brand') }} <span>Studio</span>
        </a>

        <button class="hamburger" type="button" aria-expanded="false" aria-controls="nav-menu" aria-label="Abrir menú">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <ul class="nav-menu" id="nav-menu">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a></li>
            <li class="nav-item dropdown">
                <a class="nav-link {{ request()->routeIs('portfolio') ? 'active' : '' }}" href="{{ route('portfolio') }}">Portafolio</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link {{ request()->routeIs('services*') ? 'active' : '' }}" href="{{ route('services') }}">Servicios <span aria-hidden="true">▾</span></a>
                <ul class="dropdown-menu">
                    @foreach(config('site.services') as $service)
                        <li><a class="dropdown-link" href="{{ route('services.show', $service['slug']) }}">{{ $service['title'] }}</a></li>
                    @endforeach
                </ul>
            </li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">Sobre Mí</a></li>
            <li class="nav-item"><a class="nav-link btn-contacto {{ request()->routeIs('contact*') ? 'active' : '' }}" href="{{ route('contact') }}">Contacto</a></li>
        </ul>
    </nav>
</header>
