@props(['name', 'class' => 'size-6'])

<svg {{ $attributes->class([$class]) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('search')
            <circle cx="10.8" cy="10.8" r="6.8" /><path d="m16 16 4.5 4.5" /><path d="M8 10.8h5.5M10.8 8v5.5" />
            @break
        @case('calendar')
            <rect x="3.5" y="5" width="17" height="15.5" rx="2" /><path d="M7.5 3v4M16.5 3v4M3.5 9.5h17M8 13h2m4 0h2M8 16.5h2" />
            @break
        @case('document')
            <path d="M7 3.5h7l5 5v12H7a2 2 0 0 1-2-2v-13a2 2 0 0 1 2-2Z" /><path d="M14 3.5v5h5M9 13h6m-6 3.5h6" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3.5 2" />
            @break
        @case('tool')
            <path d="M14.5 6.5a5 5 0 0 0-6.3 6.3L3.5 17.5a2.1 2.1 0 0 0 3 3l4.7-4.7a5 5 0 0 0 6.3-6.3L14 13l-3-3 3.5-3.5Z" />
            @break
        @case('leaf')
            <path d="M20.5 3.5C11 3.5 5 6.7 5 13a5.5 5.5 0 0 0 5.5 5.5c6.3 0 9.5-6 10-15Z" /><path d="M3.5 21c2.8-5.3 6.5-8.7 12-11" />
            @break
        @case('shield')
            <path d="M12 3 20 6v5.5c0 5-3.3 8-8 9.5-4.7-1.5-8-4.5-8-9.5V6l8-3Z" /><path d="m8.5 12 2.3 2.3 4.8-5" />
            @break
        @case('handshake')
            <path d="m3 9 4-4 4 2 2-1 8 4-3 8-5-2-3 2-4-3-3-6Z" /><path d="m7 5 2 5 3 2 3-2 3 2M9 15l2 1m1-3 2 1m1-3 2 1" />
            @break
        @case('eye')
            <path d="M2.5 12s3.3-6 9.5-6 9.5 6 9.5 6-3.3 6-9.5 6-9.5-6-9.5-6Z" /><circle cx="12" cy="12" r="2.5" />
            @break
        @case('wallet')
            <rect x="3" y="5" width="18" height="15" rx="2.5" /><path d="M3 8h18m-5 5h5v4h-5a2 2 0 0 1 0-4Z" />
            @break
        @case('lightning')
            <path d="m13.5 2.8-9 11.1h6l-.7 7.3 9.7-12.1h-6.2l.2-6.3Z" />
            @break
        @case('message')
            <path d="M20.5 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-4-.9L3 20l1-4.2a8.5 8.5 0 1 1 16.5-4.3Z" /><path d="M8 11.5h8m-8 3h5" />
            @break
        @default
            <circle cx="12" cy="12" r="8.5" /><path d="M12 7v5l3 2" />
    @endswitch
</svg>
