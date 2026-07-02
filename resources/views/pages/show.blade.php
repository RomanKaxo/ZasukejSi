@extends('layouts.app')

@section('title', $page->title)

@section('content')
    @if ($page->type === 'blog')
        <div class="w-full mt-20">
            <div class="mb-8 sm:rounded-lg overflow-hidden relative">
                @if ($page->hasMedia('header-image'))
                    <img src="{{ $page->getFirstMediaUrl('header-image') }}" alt="{{ $page->title }}"
                        class="w-full h-64 sm:h-96 object-cover">
                @else
                    <!-- Placeholder for blog posts without header image -->
                    <div class="w-full h-64 sm:h-96 bg-gradient-to-br from-primary-100 to-secondary-100 flex items-center justify-center">
                        <svg class="w-24 h-24 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
                
                <!-- Date and Reading Time Badges - Bottom Left Corner -->
                <div class="absolute bottom-4 left-4 flex gap-2">
                    <time datetime="{{ $page->created_at->format('Y-m-d') }}" 
                          class="inline-flex items-center px-4 py-3 bg-white backdrop-blur-sm text-gray-700 rounded-full text-sm font-medium shadow-lg">
                        <svg class="w-5 h-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $page->created_at->translatedFormat(__('blogs.date_format')) }}
                    </time>
                    
                    @if($page->aproximateReadingTime() > 0)
                        <div class="inline-flex items-center px-4 py-3 bg-white backdrop-blur-sm text-gray-700 rounded-full text-sm font-medium shadow-lg">
                            <svg class="w-5 h-5 mr-2 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $page->aproximateReadingTime() }} {{ __('blogs.min_read') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
    
    <div class="max-w-7xl mx-auto px-4 py-8 {{ $page->type === 'blog' ? 'pt-8' : 'pt-28' }}">
        <!-- Page Heading -->
        <h1 class="text-4xl font-bold text-gray-900 mb-6">
            {{ $page->title }}
        </h1>
        @if ($page->type === 'blog')
            <!-- Blog Description -->
            @if ($page->description)
                <div class="mb-8 text-xl text-gray-600 leading-relaxed">
                    {{ $page->description }}
                </div>
            @endif
        @endif

        <!-- Page Content -->
        <div class="max-w-none">
            @php
                $content = $page->content;
                // Ensure content is an array for the blocks renderer
                if (is_string($content)) {
                    $content = json_decode($content, true) ?? [];
                }
            @endphp
            @blocks($content)
        </div>
    </div>

    <style>
        /* Simple text-based styling for blocks */
        .prose h1 {
            font-size: 2.25rem;
            font-weight: 700;
            line-height: 2.5rem;
            margin-top: 1.5rem;
            margin-bottom: 1rem;
            color: #111827;
        }

        .prose h2 {
            font-size: 1.875rem;
            font-weight: 700;
            line-height: 2.25rem;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
            color: #111827;
        }

        .prose h3 {
            font-size: 1.5rem;
            font-weight: 600;
            line-height: 2rem;
            margin-top: 1.25rem;
            margin-bottom: 0.75rem;
            color: #111827;
        }

        .prose h4 {
            font-size: 1.25rem;
            font-weight: 600;
            line-height: 1.75rem;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            color: #111827;
        }

        .prose h5 {
            font-size: 1.125rem;
            font-weight: 600;
            line-height: 1.75rem;
            margin-top: 1rem;
            margin-bottom: 0.5rem;
            color: #111827;
        }

        .prose h6 {
            font-size: 1rem;
            font-weight: 600;
            line-height: 1.5rem;
            margin-top: 0.75rem;
            margin-bottom: 0.5rem;
            color: #111827;
        }

        .prose p {
            font-size: 1.125rem;
            line-height: 1.75rem;
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
            color: #374151;
        }

        .prose a {
            color: #ec4899;
            text-decoration: underline;
        }

        .prose a:hover {
            color: #db2777;
        }

        .prose strong {
            font-weight: 600;
            color: #111827;
        }

        .prose em {
            font-style: italic;
        }

        .prose ul {
            list-style-type: disc;
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
            padding-left: 1.625rem;
        }

        .prose ol {
            list-style-type: decimal;
            margin-top: 0.75rem;
            margin-bottom: 0.75rem;
            padding-left: 1.625rem;
        }

        .prose li {
            margin-top: 0.25rem;
            margin-bottom: 0.25rem;
            color: #374151;
        }

        .prose blockquote {
            border-left: 4px solid #ec4899;
            padding-left: 1rem;
            font-style: italic;
            margin-top: 1rem;
            margin-bottom: 1rem;
            color: #6b7280;
        }

        /* Card block styling */
        .prose [data-block-type="card"] {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-top: 1rem;
            margin-bottom: 1rem;
            background-color: #f9fafb;
        }
    </style>
@endsection
