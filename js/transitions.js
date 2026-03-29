document.addEventListener('DOMContentLoaded', () => {
    // Fade in the page on load
    document.body.classList.add('fade-in');

    // Intercept navigation clicks to fade out
    document.querySelectorAll('.nav-links a, .logout-link').forEach(link => {
        link.addEventListener('click', e => {
            const href = e.currentTarget.getAttribute('href');

            // Don't intercept external links, or links to the same page
            if (href.startsWith('http') || href.startsWith('#')) {
                return;
            }

            // Prevent default navigation
            e.preventDefault();

            // Fade out the body
            document.body.classList.remove('fade-in');
            
            // Navigate to the new page after the transition
            setTimeout(() => {
                window.location.href = href;
            }, 400); // Must match the CSS transition duration
        });
    });
});
