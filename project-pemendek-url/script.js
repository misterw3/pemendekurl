document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('shortenForm');
    const urlInput = document.getElementById('urlInput');
    const resultCard = document.getElementById('resultCard');
    const shortUrlDisplay = document.getElementById('shortUrlDisplay');
    const errorMsg = document.getElementById('errorMsg');
    const btn = document.getElementById('shortenBtn');
    const btnText = document.getElementById('btnText');
    const btnLoader = document.getElementById('btnLoader');
    const visitBtn = document.getElementById('visitBtn');

    // Improved URL validation
    function isValidURL(string) {
        try {
            // Add protocol if missing
            let urlString = string.trim();
            if (!/^https?:\/\//i.test(urlString)) {
                urlString = 'https://' + urlString;
            }

            const url = new URL(urlString);

            // Check if it has a valid domain
            if (!url.hostname || url.hostname.indexOf('.') === -1) {
                return false;
            }

            // Check protocol
            if (!['http:', 'https:'].includes(url.protocol)) {
                return false;
            }

            return true;
        } catch (e) {
            return false;
        }
    }

    // Real-time validation feedback
    urlInput.addEventListener('input', () => {
        const url = urlInput.value.trim();
        if (url && !isValidURL(url)) {
            urlInput.style.borderColor = 'rgba(255, 77, 77, 0.5)';
        } else {
            urlInput.style.borderColor = '';
        }
    });

    // Clear result when user starts typing again
    urlInput.addEventListener('focus', () => {
        if (resultCard.classList.contains('show')) {
            // Don't clear immediately, just prepare for new input
        }
    });

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const url = urlInput.value.trim();

        // Validation
        if (!url) {
            showError('Please enter a URL.');
            urlInput.focus();
            return;
        }

        if (!isValidURL(url)) {
            showError('Please enter a valid URL (e.g., example.com or https://example.com)');
            urlInput.focus();
            return;
        }

        // Clear previous errors and results
        showError('');
        resultCard.classList.remove('show');
        setLoading(true);

        try {
            const response = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ url })
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.error || 'Something went wrong. Please try again.');
            }

            // Success - Display result
            shortUrlDisplay.textContent = data.shortUrl;
            visitBtn.href = data.shortUrl;

            // Smooth transition
            setTimeout(() => {
                setLoading(false);
                resultCard.style.display = 'block';

                // Trigger animation
                void resultCard.offsetWidth;
                resultCard.classList.add('show');

                // Optional: Clear input after success
                // urlInput.value = '';
            }, 500);

        } catch (err) {
            setLoading(false);
            showError(err.message || 'An unexpected error occurred. Please try again.');
            console.error('Error:', err);
        }
    });

    function setLoading(isLoading) {
        if (isLoading) {
            btn.disabled = true;
            btnText.classList.add('hidden');
            btnLoader.classList.remove('hidden');
            urlInput.disabled = true;
        } else {
            btn.disabled = false;
            btnText.classList.remove('hidden');
            btnLoader.classList.add('hidden');
            urlInput.disabled = false;
        }
    }

    function showError(msg) {
        errorMsg.textContent = msg;
        if (msg) {
            errorMsg.classList.remove('hidden');
            // Add shake animation
            errorMsg.style.animation = 'shake 0.3s';
            setTimeout(() => {
                errorMsg.style.animation = '';
            }, 300);
        } else {
            errorMsg.classList.add('hidden');
        }
    }

    // Add shake animation to CSS dynamically
    if (!document.querySelector('#shake-animation')) {
        const style = document.createElement('style');
        style.id = 'shake-animation';
        style.textContent = `
            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                75% { transform: translateX(5px); }
            }
        `;
        document.head.appendChild(style);
    }
});


// Copy to clipboard with enhanced feedback
async function copyToClipboard() {
    const text = document.getElementById('shortUrlDisplay').textContent;
    const btn = document.getElementById('copyBtn');
    const originalText = btn.innerHTML;

    try {
        await navigator.clipboard.writeText(text);

        // Success feedback
        btn.innerHTML = '<span style="color: #4ade80">✓ Copied!</span>';
        btn.style.background = 'rgba(74, 222, 128, 0.1)';

        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.style.background = '';
        }, 2000);
    } catch (err) {
        console.error('Failed to copy:', err);

        // Fallback for older browsers
        try {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            textArea.style.position = 'fixed';
            textArea.style.opacity = '0';
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);

            btn.innerHTML = '<span style="color: #4ade80">✓ Copied!</span>';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        } catch (fallbackErr) {
            btn.innerHTML = '<span style="color: #ff4d4d">✗ Error</span>';
            setTimeout(() => {
                btn.innerHTML = originalText;
            }, 2000);
        }
    }
}
