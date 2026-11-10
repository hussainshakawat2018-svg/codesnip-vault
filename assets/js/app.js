// CodeSnip Vault - Custom JavaScript

document.addEventListener('DOMContentLoaded', () => {
    // 1. Copy to clipboard handler
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

    // 2. Fetch (Ajax) Delete with Loading Spinner (Course Requirement)
    document.querySelectorAll('.delete-btn').forEach(button => {
        button.addEventListener('click', async () => {
            const snippetId = button.getAttribute('data-id');
            if (!snippetId) return;

            if (!confirm('Are you sure you want to delete this snippet? This cannot be undone.')) {
                return;
            }

            // Put button into Loading State
            const originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Deleting...';

            try {
                const response = await fetch('delete_snippet.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: snippetId })
                });

                const data = await response.json();

                if (data.success) {
                    // Smoothly fade out and remove the card
                    const card = document.getElementById(`snippet-card-${snippetId}`);
                    if (card) {
                        card.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                        card.style.opacity = '0';
                        card.style.transform = 'translateY(-10px)';
                        setTimeout(() => {
                            card.remove();
                            // If no snippets left on page, reload to show empty state
                            const remaining = document.querySelectorAll('[id^="snippet-card-"]');
                            if (remaining.length === 0) {
                                window.location.reload();
                            }
                        }, 400);
                    }
                } else {
                    alert(data.message || 'Failed to delete snippet.');
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
            } catch (err) {
                console.error('Delete request failed: ', err);
                alert('A network error occurred while deleting.');
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        });
    });
});
