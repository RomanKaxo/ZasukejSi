import './bootstrap';

// Import Swiper bundle with all modules included
import Swiper from 'swiper/bundle';
import 'swiper/css/bundle';

// Bundled rather than pulled from a public CDN at request time: the statistics
// chart is behind a login, and a third-party host is both a dependency the
// site cannot control and one more party seeing who loads the page.
import Chart from 'chart.js/auto';

// Make Swiper globally available and fire a ready event
// so any inline scripts waiting for Swiper can hook in reliably
window.Swiper = Swiper;
// The statistics view looks for window.Chart, as it did with the CDN build.
window.Chart = Chart;
document.dispatchEvent(new CustomEvent('swiper:ready'));

// Note: Alpine is automatically included and started by Livewire 3
// Do NOT import or start Alpine separately to avoid "multiple instances" error
