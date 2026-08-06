document.addEventListener('DOMContentLoaded', () => {
    const galleryWrapper = document.querySelector('.profile-gallery-wrapper');
    if (!galleryWrapper) return;

    const slides = JSON.parse(galleryWrapper.dataset.slides || '[]');
    
    // Lightbox init
    const lightbox = document.getElementById('vip-lightbox');
    const lightboxSwiperEl = document.querySelector('.vip-lightbox-swiper');
    let lightboxSwiper = null;

    if (lightboxSwiperEl && lightbox) {
        // Build Swiper structure dynamically
        const wrapper = lightboxSwiperEl.querySelector('.swiper-wrapper');
        wrapper.innerHTML = slides.map(url => `
            <div class="swiper-slide flex items-center justify-center">
                <img src="${url}" class="max-w-full max-h-[60vh] object-contain rounded-[24px]">
            </div>
        `).join('');

        lightboxSwiper = new Swiper(lightboxSwiperEl, {
            loop: true,
            slidesPerView: 1,
            navigation: {
                nextEl: '#vip-lightbox-next',
                prevEl: '#vip-lightbox-prev',
            },
            keyboard: {
                enabled: true,
            },
        });

        // Trigger open
        document.querySelectorAll('.lightbox-trigger').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const index = Number(trigger.dataset.index || 0);
                lightbox.classList.remove('hidden');
                lightbox.classList.add('flex', 'is-open');
                document.body.style.overflow = 'hidden';
                lightboxSwiper.slideToLoop(index, 0);
            });
        });

        // Close
        const closeBtn = document.getElementById('vip-lightbox-close');
        const hideLightbox = () => {
            lightbox.classList.add('hidden');
            lightbox.classList.remove('flex', 'is-open');
            document.body.style.overflow = '';
        };

        closeBtn?.addEventListener('click', hideLightbox);
        lightbox.addEventListener('click', (e) => {
            if (e.target === lightbox) hideLightbox();
        });
    }

    // Desktop fade transition (300ms)
    let currentIndex = 1; // Default middle card index
    const updateDesktopGallery = (offset) => {
        const desktopImgs = document.querySelectorAll('.vip-gallery-desktop-card img');
        const desktopBtns = document.querySelectorAll('.vip-gallery-desktop-card');
        
        // Fade out
        desktopImgs.forEach(img => img.classList.add('opacity-0'));

        setTimeout(() => {
            // Update index
            currentIndex = (currentIndex + offset + slides.length) % slides.length;
            
            // Update src and data-index for left, main, right (index-1, index, index+1)
            const leftIdx = (currentIndex - 1 + slides.length) % slides.length;
            const rightIdx = (currentIndex + 1 + slides.length) % slides.length;
            
            desktopImgs[0].src = slides[leftIdx];
            desktopBtns[0].dataset.index = leftIdx;
            
            desktopImgs[1].src = slides[currentIndex];
            desktopBtns[1].dataset.index = currentIndex;
            
            desktopImgs[2].src = slides[rightIdx];
            desktopBtns[2].dataset.index = rightIdx;

            // Fade in
            desktopImgs.forEach(img => img.classList.remove('opacity-0'));
        }, 300);
    };


    document.getElementById('vip-gallery-desktop-prev')?.addEventListener('click', () => updateDesktopGallery(-1));
    document.getElementById('vip-gallery-desktop-next')?.addEventListener('click', () => updateDesktopGallery(1));

});
