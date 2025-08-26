// Simplified app.js without ES6 modules
// This file loads Alpine.js from CDN and initializes it

// Alpine.js will be loaded from CDN in the HTML templates
// No module imports needed for direct browser usage

console.log('App.js loaded successfully');

// Initialize Alpine when it's available
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Alpine !== 'undefined') {
        Alpine.start();
        console.log('Alpine.js initialized');
    }
});
