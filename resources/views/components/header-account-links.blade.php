@props(['layout'])

<header-account-links
    layout="{{ $layout }}"
    authenticated="{{ auth()->check() ? '1' : '0' }}"
    login-url="{{ route('login') }}"
    register-url="{{ route('register') }}"
    account-url="{{ route('account.home') }}"
    login-label="{{ __('Autentificare') }}"
    register-label="{{ __('Creează cont') }}"
    account-label="{{ __('Contul meu') }}"
>
    @auth
        <a href="{{ route('account.home') }}" @class([
            'text-brand no-underline hover:underline' => $layout === 'desktop',
            'block px-3 py-2 text-brand no-underline' => $layout === 'menu',
        ])>{{ __('Contul meu') }}</a>
    @else
        <a href="{{ route('login') }}" @class([
            'text-brand no-underline hover:underline' => $layout === 'desktop',
            'block border-b border-hairline px-3 py-2 text-brand no-underline' => $layout === 'menu',
        ])>{{ __('Autentificare') }}</a>
        <a href="{{ route('register') }}" @class([
            'text-brand no-underline hover:underline' => $layout === 'desktop',
            'block px-3 py-2 text-brand no-underline' => $layout === 'menu',
        ])>{{ __('Creează cont') }}</a>
    @endauth
</header-account-links>
