@props(['question', 'open' => false])

{{-- Native <details> rather than an Alpine accordion: keyboard-accessible
     and screen-reader-friendly by default, with no JS required. --}}
<details class="group border-b border-ink-200 py-5" @if($open) open @endif>
    <summary class="flex items-start justify-between gap-4 cursor-pointer list-none">
        <h3 class="font-medium text-ink-900 text-base">{{ $question }}</h3>
        <span class="shrink-0 mt-1 text-ink-400 transition-transform group-open:rotate-45" aria-hidden="true">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
            </svg>
        </span>
    </summary>
    <div class="mt-3 text-ink-600 max-w-prose">{{ $slot }}</div>
</details>
