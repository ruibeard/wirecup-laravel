<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Balsamiq+Sans:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Balsamiq Sans', sans-serif; }
    </style>
</head>
<body class="m-0 bg-stone-100 text-stone-800">
<div class="flex h-screen">
    <aside class="w-52 h-full overflow-y-auto border-r-2 border-stone-300 bg-stone-200 p-3 flex flex-col">
        <h1 class="text-lg font-semibold mb-3 text-stone-700">{{ $title }}</h1>

        <div class="space-y-1 flex-1">
            @forelse ($files as $file)
                @php
                    $href = route('wirecup.index').'?file='.rawurlencode($file);
                    $previewHref = route('wirecup.render', ['file' => $file]);
                    $isActive = $selected === $file;
                @endphp
                <a class="flex items-center justify-between p-2 rounded-lg border-2 no-underline text-inherit {{ $isActive ? 'border-stone-600 bg-yellow-50' : 'border-transparent bg-stone-50 hover:border-stone-300' }}"
                   href="{{ $href }}">
                    <div class="flex flex-col min-w-0">
                        <span class="text-sm font-medium truncate">{{ basename($file, '.cup') }}</span>
                        <span class="text-[10px] text-stone-400">{{ $tokenCounts[$file] ?? 0 }} tokens</span>
                    </div>
                    <span class="ml-2 text-stone-500 hover:text-stone-700 shrink-0"
                          onclick="event.preventDefault(); event.stopPropagation(); window.open('{{ $previewHref }}', '_blank');" role="button" aria-label="Open {{ $file }}">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                <polyline points="15 3 21 3 21 9"/>
                                <line x1="10" y1="14" x2="21" y2="3"/>
                            </svg>
                        </span>
                </a>
            @empty
                <p class="text-sm text-stone-500">No .cup files found</p>
            @endforelse
        </div>

        <div class="pt-4 border-t border-stone-300">
            <a href="https://github.com/ruibeard/wirecup-laravel" target="_blank" rel="noopener noreferrer" class="text-xs text-stone-500 hover:text-stone-700">
                Rui Almeida
            </a>
        </div>
    </aside>

    <main class="flex-1 flex flex-col relative min-w-0">
        @if ($previewUrl)
            <span class="absolute top-4 left-4 z-10 text-xs text-stone-500 bg-stone-100 px-2 py-1 rounded">{{ $tokenCounts[$selected] ?? 0 }} tokens</span>
            <iframe src="{{ $previewUrl }}" class="w-full h-full border-0"></iframe>
        @else
            <div class="p-8">
                <h2 class="text-xl font-semibold">No previews available</h2>
                <p class="mt-2 text-stone-500">Create a <code class="text-sm bg-stone-200 px-1 rounded">.cup</code> file in <code
                        class="text-sm bg-stone-200 px-1 rounded">{{ $root }}</code> and refresh this page.</p>
            </div>
        @endif
    </main>
</div>
</body>
</html>
