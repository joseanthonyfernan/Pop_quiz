/**
 * DevTools Blocker
 * Prevents right-click and common shortcuts used to open DevTools.
 * Also attempts to detect when the console is opened.
 */

// Disable Right Click
document.addEventListener('contextmenu', (e) => e.preventDefault());

// Disable Key Combinations
document.onkeydown = function(e) {
    // Disable F12
    if (e.keyCode == 123) {
        return false;
    }
    // Disable Ctrl+Shift+I (Inspect)
    if (e.ctrlKey && e.shiftKey && (e.keyCode == 'I'.charCodeAt(0) || e.keyCode == 'i'.charCodeAt(0))) {
        return false;
    }
    // Disable Ctrl+Shift+J (Console)
    if (e.ctrlKey && e.shiftKey && (e.keyCode == 'J'.charCodeAt(0) || e.keyCode == 'j'.charCodeAt(0))) {
        return false;
    }
    // Disable Ctrl+Shift+C (Inspect Element)
    if (e.ctrlKey && e.shiftKey && (e.keyCode == 'C'.charCodeAt(0) || e.keyCode == 'c'.charCodeAt(0))) {
        return false;
    }
    // Disable Ctrl+U (View Source)
    if (e.ctrlKey && (e.keyCode == 'U'.charCodeAt(0) || e.keyCode == 'u'.charCodeAt(0))) {
        return false;
    }
    // Disable Ctrl+S (Save Page)
    if (e.ctrlKey && (e.keyCode == 'S'.charCodeAt(0) || e.keyCode == 's'.charCodeAt(0))) {
        return false;
    }
};

// Console detection (Debug deterrent)
setInterval(function() {
    const start = new Date();
    debugger;
    const end = new Date();
    if (end - start > 100) {
        // DevTools likely open
        document.body.innerHTML = '<div style="display:flex;justify-content:center;align-items:center;height:100vh;flex-direction:column;font-family:sans-serif;"><h1>Security Alert</h1><p>Developer Tools are disabled on this project. Please close them to continue.</p><button onclick="location.reload()">Reload</button></div>';
    }
}, 1000);
