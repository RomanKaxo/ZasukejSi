{{-- The page template (pages/show) already prints the page title as the
     page's only <h1>. Editors often start the content with an "H1" heading
     block too, which left every CMS page with two <h1>s; such a block is
     rendered as an <h2> with the H1 size so the page looks the same. --}}
@php($tag = $level === 'h1' ? 'h2' : $level)
<{{ $tag }} @class([
    'font-bold leading-tight',
    'text-3xl' => $level == 'h1',
    'font-3xl' => $level == 'h1',
    'font-2xl' => $level == 'h2',
    'font-xl' => $level == 'h3',
    'font-lg' => $level == 'h4',
    'font-base' => $level == 'h5',
    'font-sm' => $level == 'h6',
])>{{ $content }}</{{ $tag }}>
