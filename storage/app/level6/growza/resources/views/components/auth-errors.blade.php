@props(['bag' => 'default'])

@if ($errors->getBag($bag)->any())
    <x-alert variant="danger" class="mb-6" role="alert">
        <ul class="space-y-1">
            @foreach ($errors->getBag($bag)->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </x-alert>
@endif
