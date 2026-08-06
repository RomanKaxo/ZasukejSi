@props(['profile', 'gallerySlides'])

<div class="profile-gallery-wrapper" data-slides="{{ $gallerySlides->toJson() }}">
    <x-profile-gallery.desktop :profile="$profile" :gallery-slides="$gallerySlides" />
    <x-profile-gallery.mobile :profile="$profile" :gallery-slides="$gallerySlides" />
    <x-profile-gallery.lightbox :profile="$profile" :gallery-slides="$gallerySlides" />
</div>

@push('scripts')
<script src="{{ asset('js/profile-gallery.js') }}"></script>
@endpush
