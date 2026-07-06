<!DOCTYPE html>
<html lang="{{ $locale ?? 'en' }}">
<head>
    <meta charset="utf-8">
    <title>{{ $seoTitle ?? $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        h1 {
            font-size: 22pt;
            margin-bottom: 5px;
            color: #111;
        }
        h2 { font-size: 16pt; margin-top: 20px; color: #222; }
        h3 { font-size: 14pt; margin-top: 15px; color: #333; }
        p { margin: 8px 0; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 6px 10px;
            text-align: left;
        }
        th {
            background: #f5f5f5;
            font-weight: bold;
        }
        code {
            background: #f0f0f0;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 10pt;
        }
        pre {
            background: #f5f5f5;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 10pt;
            overflow-x: auto;
        }
        blockquote {
            border-left: 3px solid #ccc;
            margin: 10px 0;
            padding-left: 12px;
            color: #666;
        }
        .docs-callout { border-radius: 0.5rem; padding: 1rem 1.25rem; margin: 1.5rem 0; border-left: 4px solid; }
        .docs-callout--tip { border-color: #10b981; background-color: #ecfdf5; }
        .docs-callout--info { border-color: #3b82f6; background-color: #eff6ff; }
        .docs-callout--danger { border-color: #ef4444; background-color: #fef2f2; }
        .docs-callout--caution { border-color: #f59e0b; background-color: #fffbeb; }
        .docs-callout__title { display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 1.125rem; margin-bottom: 0.5rem; }
        .docs-callout__icon { flex-shrink: 0; width: 32px; height: 32px; }
        .docs-callout--tip .docs-callout__title { color: #065f46; }
        .docs-callout--info .docs-callout__title { color: #1e40af; }
        .docs-callout--danger .docs-callout__title { color: #991b1b; }
        .docs-callout--caution .docs-callout__title { color: #92400e; }
        .docs-callout__title--icon-only { margin-bottom: 0; }
        .docs-callout__content { font-size: 0.75rem; }
        .docs-callout__content > :first-child { margin-top: 0; }
        .docs-callout__content > :last-child { margin-bottom: 0; }
        .footer {
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
            font-size: 9pt;
            color: #999;
            text-align: center;
        }
        ul, ol {
            margin: 8px 0;
            padding-left: 20px;
        }
        .watermark {
            position: fixed;
            z-index: 9999;
            pointer-events: none;
            opacity: {{ config('blogr-docs.pdf.watermark.opacity', 0.2) }};
            font-size: {{ config('blogr-docs.pdf.watermark.size', 60) }}px;
            color: #999;
            transform: rotate({{ config('blogr-docs.pdf.watermark.rotation', -45) }}deg);
            text-align: center;
            @php $pos = config('blogr-docs.pdf.watermark.position', 'center'); @endphp
            @if($pos === 'center')
                top: 50%; left: 50%; margin-left: -200px; margin-top: -100px; width: 400px;
            @elseif($pos === 'top-left')
                top: 40px; left: 40px;
            @elseif($pos === 'top-center')
                top: 40px; left: 50%; margin-left: -200px; width: 400px;
            @elseif($pos === 'top-right')
                top: 40px; right: 40px;
            @elseif($pos === 'center-left')
                top: 50%; margin-top: -100px; left: 40px;
            @elseif($pos === 'center-right')
                top: 50%; margin-top: -100px; right: 40px;
            @elseif($pos === 'bottom-left')
                bottom: 40px; left: 40px;
            @elseif($pos === 'bottom-center')
                bottom: 40px; left: 50%; margin-left: -200px; width: 400px;
            @elseif($pos === 'bottom-right')
                bottom: 40px; right: 40px;
            @endif
        }
        .watermark img {
            max-width: 300px;
            max-height: 300px;
        }
    </style>
</head>
<body>
    @if(config('blogr-docs.pdf.watermark.enabled', false))
        <div class="watermark">
            @php
                $wmImage = config('blogr-docs.pdf.watermark.image');
                $wmText = config('blogr-docs.pdf.watermark.text', '');
                $wmSize = config('blogr-docs.pdf.watermark.size', 60);
            @endphp
            @if($wmImage)
                @php
                    $wmImagePath = str_contains($wmImage, '/') ? $wmImage : 'docs/pdf-watermarks/' . $wmImage;
                    $watermarkPath = \Illuminate\Support\Facades\Storage::disk('public')
                        ->path($wmImagePath);
                    $watermarkMime = \Illuminate\Support\Facades\Storage::disk('public')
                        ->mimeType($wmImagePath);
                @endphp
                @if(file_exists($watermarkPath))
                    <img src="data:{{ $watermarkMime }};base64,{{ base64_encode(file_get_contents($watermarkPath)) }}"
                         alt="Watermark"
                         style="max-width:{{ $wmSize }}px; max-height:{{ $wmSize }}px;">
                @endif
            @endif
            @if($wmText)
                <div style="font-size:{{ $wmSize }}px;">{{ $wmText }}</div>
            @endif
        </div>
    @endif

    <h1>{{ $title }}</h1>
    @if(!empty($seoDescription))
        <p style="font-size: 11pt; color: #666; margin-bottom: 20px;">{{ $seoDescription }}</p>
    @endif

    {!! $content !!}

    <div class="footer">
        Generated by Blogr Docs — {{ date('Y-m-d') }}
    </div>
</body>
</html>
