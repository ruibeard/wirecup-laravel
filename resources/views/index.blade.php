<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f5f1e8;
            color: #2f2a23;
        }

        .layout {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            min-height: 100vh;
        }

        .sidebar {
            padding: 24px;
            border-right: 2px solid #d7cfbf;
            background: #efe8da;
        }

        .sidebar h1 {
            margin: 0 0 8px;
            font-size: 1.35rem;
        }

        .sidebar p {
            margin: 0 0 24px;
            color: #5c5448;
            line-height: 1.5;
        }

        .files {
            display: grid;
            gap: 10px;
        }

        .file-link {
            display: block;
            padding: 12px 14px;
            text-decoration: none;
            color: inherit;
            background: #fffdf8;
            border: 2px solid #d7cfbf;
            border-radius: 12px;
        }

        .file-link.active {
            border-color: #7a6850;
            background: #fff7dd;
        }

        .file-link strong {
            display: block;
            font-size: 0.95rem;
        }

        .file-link span {
            display: block;
            margin-top: 4px;
            font-size: 0.8rem;
            color: #6a6257;
        }

        .preview-shell {
            display: grid;
            grid-template-rows: auto minmax(0, 1fr);
            min-height: 100vh;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 18px 24px;
            border-bottom: 2px solid #d7cfbf;
            background: rgba(255, 252, 245, 0.92);
        }

        .toolbar h2 {
            margin: 0;
            font-size: 1rem;
        }

        .toolbar code {
            font-size: 0.9rem;
            background: #f1ece0;
            padding: 2px 6px;
            border-radius: 6px;
        }

        .open-link {
            color: #5c4a34;
            text-decoration: none;
            font-weight: 600;
        }

        iframe {
            width: 100%;
            height: 100%;
            border: 0;
            background: white;
        }

        .empty {
            padding: 32px;
        }

        @media (max-width: 900px) {
            .layout {
                grid-template-columns: 1fr;
            }

            .sidebar {
                border-right: 0;
                border-bottom: 2px solid #d7cfbf;
            }

            .preview-shell {
                min-height: 70vh;
            }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <h1>{{ $title }}</h1>
        <p>Browsing <code>{{ $root }}</code></p>

        <div class="files">
            @forelse ($files as $file)
                @php
                    $href = route('wirecup.index').'?file='.rawurlencode($file);
                @endphp
                <a class="file-link {{ $selected === $file ? 'active' : '' }}" href="{{ $href }}">
                    <strong>{{ basename($file, '.cup') }}</strong>
                    <span>{{ $file }}</span>
                </a>
            @empty
                <div class="file-link">
                    <strong>No .cup files found</strong>
                    <span>Add files under {{ $root }}</span>
                </div>
            @endforelse
        </div>
    </aside>

    <main class="preview-shell">
        @if ($previewUrl)
            <div class="toolbar">
                <h2>Previewing <code>{{ $selected }}</code></h2>
                <a class="open-link" href="{{ $previewUrl }}" target="_blank" rel="noreferrer">Open preview</a>
            </div>
            <iframe src="{{ $previewUrl }}" title="Wirecup preview"></iframe>
        @else
            <div class="empty">
                <h2>No previews available</h2>
                <p>Create a <code>.cup</code> file in <code>{{ $root }}</code> and refresh this page.</p>
            </div>
        @endif
    </main>
</div>
</body>
</html>
