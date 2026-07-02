import './bootstrap';

// Import Swiper bundle with all modules included
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';

// Make Swiper globally available and fire a ready event
// so any inline scripts waiting for Swiper can hook in reliably
window.Swiper = Swiper;
document.dispatchEvent(new CustomEvent('swiper:ready'));

// Note: Alpine is automatically included and started by Livewire 3
// Do NOT import or start Alpine separately to avoid "multiple instances" error
