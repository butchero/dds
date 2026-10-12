@props([
    'open' => false,
    'label',
    'title',
    'triggerClass' => null,
])

<div
    data-account-modal
    data-open="{{ $open ? '1' : '0' }}"
    data-label="{{ $label }}"
    data-title="{{ $title }}"
    data-cancel-label="{{ __('Renunță') }}"
    data-close-label="{{ __('Închide') }}"
    @if ($triggerClass !== null) data-trigger-class="{{ $triggerClass }}" @endif
>
    <template data-dialog-body>
        {{ $slot }}
    </template>
</div>
