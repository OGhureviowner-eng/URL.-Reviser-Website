document.addEventListener('DOMContentLoaded', function() {
    const urlInput = document.getElementById('urlInput');
    const scanBtn = document.getElementById('scanBtn');
    const results = document.getElementById('results');
    const status = document.getElementById('status');
    status.className = 'status';
    
    scanBtn.addEventListener('click', scanUrl);
    urlInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') scanUrl();
    });

    async function scanUrl() {
        const url = urlInput.value.trim();
        if (!url) {
            alert('Please enter a URL');
            return;
        }

        // Show loading
        scanBtn.disabled = true;
        const btnText = scanBtn.querySelector('.btn-text');
        const btnLoading = scanBtn.querySelector('.btn-loading');
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline-flex';
        results.style.display = 'none';

        try {
            const response = await fetch('scanner.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `url=${encodeURIComponent(url)}`
            });

            const data = await response.json();
            
            displayResults(data);
        } catch (error) {
            displayError('Scan failed. Please try again.');
        } finally {
            // Reset button
            scanBtn.disabled = false;
            btnText.style.display = 'inline';
            btnLoading.style.display = 'none';
        }
    }

    function displayResults(data) {
        results.style.display = 'block';
        
        const statusIcon = document.getElementById('statusIcon');
        const statusTitle = document.getElementById('statusTitle');
        const statusMessage = document.getElementById('statusMessage');
        
        status.className = `status ${data.status}`;
        statusIcon.innerHTML = data.icon;
        statusTitle.textContent = data.title;
        statusMessage.textContent = data.message;
        
        // Update details
        document.getElementById('sslStatus').textContent = data.ssl;
        document.getElementById('sslStatus').className = `status-badge ${data.sslStatus}`;
        document.getElementById('reputation').textContent = data.reputation;
        document.getElementById('reputation').className = `status-badge ${data.repStatus}`;
        document.getElementById('phishing').textContent = data.phishing;
        document.getElementById('phishing').className = `status-badge ${data.phishingStatus}`;
        document.getElementById('lastScan').textContent = 'Just now';
    }

    function displayError(message) {
        status.className = 'status danger';
        document.getElementById('statusIcon').innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
        document.getElementById('statusTitle').textContent = 'Scan Error';
        document.getElementById('statusMessage').textContent = message;
        results.style.display = 'block';
    }
});
