// CodeSnip Vault - Custom JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // Copy to clipboard handler
    document.querySelectorAll('.copy-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const codeToCopy = button.getAttribute('data-code');
            if (!codeToCopy) return;

            try {
                await navigator.clipboard.writeText(codeToCopy);
                const originalText = button.textContent;
                button.textContent = 'Copied! ✓';
                button.classList.replace('btn-outline-light', 'btn-success');

                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.replace('btn-success', 'btn-outline-light');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy: ', err);
                alert('Could not copy code automatically. Please copy manually.');
            }
        });
    });
});
