@props(['type' => 'text'])

<input
    type="{{ $type }}"
    {{ $attributes->merge([
        'class' => 'border-input text-foreground placeholder:text-muted-foreground h-10 w-full min-w-0 rounded-md border bg-white px-3 text-sm shadow-xs transition-[color,box-shadow] outline-none focus-visible:border-ring focus-visible:ring-3 focus-visible:ring-ring/50 disabled:pointer-events-none disabled:opacity-50',
    ]) }}
>
