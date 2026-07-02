@props(['posts'])

@if($posts->count() > 0)
<div class="container mx-auto px-4 py-20 blog-section">
    <div class="mb-12">
        <h2 class="text-4xl font-bold text-secondary mb-4">{{ __('Blog') }}</h2>
        <p class="text-gray-600 text-lg">{{ __('Nejnovější články a tipy') }}</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        @foreach($posts as $post)
        <article class="group blog-card">
            <!-- Header Image with Overlay Pills -->
            <a href="{{ route('pages.show', $post->slug) }}" class="block relative mb-4 rounded-none md:rounded-xl overflow-hidden -mx-4 md:mx-0 blog-card-media">
                @if($post->hasMedia('header-image'))
                    <img src="{{ $post->getFirstMediaUrl('header-image') }}" 
                         alt="{{ $post->title }}"
                         class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300 blog-card-image">
                @else
                    <!-- Placeholder -->
                    <div class="w-full h-64 bg-gradient-to-br from-primary-100 to-secondary-100 flex items-center justify-center group-hover:scale-105 transition-transform duration-300 blog-card-image">
                        <svg class="w-24 h-24 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                @endif
                
                <!-- Info Pills - Bottom Left Corner -->
                <div class="absolute bottom-2 md:bottom-4 left-2 md:left-4 flex flex-wrap gap-1.5 md:gap-2">
                    <time datetime="{{ $post->created_at->format('Y-m-d') }}" 
                          class="inline-flex items-center px-2 py-1 md:px-3 md:py-2 bg-white/90 backdrop-blur-sm text-gray-700 rounded-full text-[10px] md:text-xs font-medium shadow-lg">
                        <svg class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-1.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $post->created_at->format('j. n. Y') }}
                    </time>
                    
                    @if($post->aproximateReadingTime() > 0)
                        <div class="inline-flex items-center px-2 py-1 md:px-3 md:py-2 bg-white/90 backdrop-blur-sm text-gray-700 rounded-full text-[10px] md:text-xs font-medium shadow-lg">
                            <svg class="w-3 h-3 md:w-4 md:h-4 mr-1 md:mr-1.5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $post->aproximateReadingTime() }} {{ __('min') }}
                        </div>
                    @endif
                </div>
            </a>

            <!-- Post Content -->
            <div class="space-y-4">
                <h3 class="text-2xl mt-4 font-bold text-secondary group-hover:text-primary transition-colors blog-card-title">
                    <a href="{{ route('pages.show', $post->slug) }}">
                        {{ $post->title }}
                    </a>
                </h3>

                @if($post->description)
                    <p class="text-gray-600 leading-relaxed line-clamp-3">
                        {{ $post->description }}
                    </p>
                @endif

                <a href="{{ route('pages.show', $post->slug) }}" 
                         class="block mt-8 w-full py-3 px-6 bg-secondary text-white text-center font-medium rounded-lg hover:bg-secondary-600 transition-colors blog-card-button">
                    {{ __('Číst článek') }}
                </a>
            </div>
        </article>
        @endforeach
    </div>
</div>
@endif
