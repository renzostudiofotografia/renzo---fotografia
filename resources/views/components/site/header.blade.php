<header class="header" data-editor-section="branding">
    <nav class="navbar" aria-label="Navegación principal" data-editor-section="navigation">
        <a class="logo" href="{{ route('home') }}" aria-label="{{ $site->get('branding.brand') }}, inicio">
            @if($site->get('branding.logo'))<img class="site-logo-image" src="{{ asset($site->get('branding.logo')) }}" alt="{{ $site->get('branding.brand') }}">@else{{ $site->get('branding.brand') }}@endif <span>{{ $site->get('branding.studio') }}</span>
        </a>

        <button class="hamburger" type="button" aria-expanded="false" aria-controls="nav-menu" aria-label="Abrir menú">
            <span class="bar"></span>
            <span class="bar"></span>
            <span class="bar"></span>
        </button>

        <ul class="nav-menu" id="nav-menu">
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">{{ $site->get('navigation.home') }}</a></li>
            <li class="nav-item dropdown">
                <a class="nav-link {{ request()->routeIs('portfolio') ? 'active' : '' }}" href="{{ route('portfolio') }}">{{ $site->get('navigation.portfolio') }}</a>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link {{ request()->routeIs('services*') ? 'active' : '' }}" href="{{ route('services') }}">{{ $site->get('navigation.services') }} <span aria-hidden="true">▾</span></a>
                <ul class="dropdown-menu">
                    @foreach($site->services() as $service)
                        <li><a class="dropdown-link" href="{{ route('services.show', $service['slug']) }}">{{ $service['title'] }}</a></li>
                    @endforeach
                </ul>
            </li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('about') ? 'active' : '' }}" href="{{ route('about') }}">{{ $site->get('navigation.about') }}</a></li>
            <li class="nav-item"><a class="nav-link btn-contacto {{ request()->routeIs('contact*') ? 'active' : '' }}" href="{{ route('contact') }}">{{ $site->get('navigation.contact') }}</a></li>
            @if($site->get('booking.enabled'))
                <li class="nav-item"><a class="nav-link" href="{{ route('booking') }}">{{ $site->get('navigation.booking') }}</a></li>
            @endif
        </ul>
    </nav>
</header>
