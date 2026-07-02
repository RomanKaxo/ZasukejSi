@props(['name', 'class' => '', 'strokeWidth' => 1.5, 'fill' => false, 'block' => true, 'preserveColors' => false])

@php
    $iconPath = public_path("images/icons/{$name}.svg");
@endphp

@if (file_exists($iconPath))
    <span {{ $attributes->merge(['class' => trim(($block == "false" ? 'inline' : 'inline-block') . ' ' . $class)]) }}>
        @php
            $svgContent = file_get_contents($iconPath);

            if ($preserveColors) {
                $svgContent = preg_replace([
                    '/\bwidth="[^"]*"/',
                    '/\bheight="[^"]*"/',
                    '/<svg/'
                ], [
                    '',
                    '',
                    '<svg class="h-full w-full" '
                ], $svgContent);
            } else {
                $svgContent = preg_replace([
                    '/\bstroke="[^"]*"/',
                    '/\bwidth="[^"]*"/',
                    '/\bheight="[^"]*"/',
                    '/\bfill="[^"]*"/',
                    '/<svg/'
                ], [
                    'stroke="currentColor"',
                    '',
                    '',
                    $fill ? 'fill="currentColor"' : 'fill="none"',
                    '<svg stroke-width="' . ($strokeWidth) . '" '
                ], $svgContent);
            }
        @endphp
        {!! $svgContent !!}
    </span>
@else
    <!-- Icon not found: {{ $name }} -->
@endif