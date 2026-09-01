@props(['items' => []]) {{-- [['label' => 'Dashboard', 'href' => '/dashboard'], ['label' => 'Orders']] --}}

<nav aria-label="Breadcrumb" class="text-sm">
    <ol class="flex items-center flex-wrap gap-1.5 text-ink-500">
        @foreach ($items as $index => $item)
            <li class="flex items-center gap-1.5">
                @if (!empty($item['href']) && $index < count($items) - 1)
                    <a href="{{ $item['href'] }}" class="hover:text-ink-800">{{ $item['label'] }}</a>
                    <span aria-hidden="true">/</span>
                @else
                    <span class="text-ink-800 font-medium" aria-current="page">{{ $item['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
