@props(['headers' => []])

{{--
    LEVEL 30 requirement: tables must not simply overflow unusably on
    mobile. This wrapper scrolls horizontally with a visible affordance
    rather than shrinking text or breaking layout — a per-table
    "transform to cards on mobile" treatment is a per-screen decision
    made when each dashboard table is actually built (LEVEL 5+), not
    something this generic wrapper can force.
--}}
<div class="overflow-x-auto rounded-md border border-ink-200">
    <table class="min-w-full divide-y divide-ink-200 text-sm">
        @if (count($headers))
            <thead class="bg-ink-50">
                <tr>
                    @foreach ($headers as $header)
                        <th scope="col" class="px-4 py-3 text-left font-medium text-ink-600">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-ink-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
