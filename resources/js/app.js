import './bootstrap';

// Import Bootstrap JS
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

// Import Alpine.js
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();

// Import ClipboardJS
import ClipboardJS from 'clipboard';
window.ClipboardJS = ClipboardJS;

// Initialize ClipboardJS
document.addEventListener('DOMContentLoaded', function() {
    if (document.querySelectorAll('.btn-clipboard').length > 0) {
        new ClipboardJS('.btn-clipboard');
    }
});
