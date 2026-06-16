<!-- Spinner Overlay -->
<div id="loadingSpinner" class="d-none position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center" style="z-index: 1050;">
    <div class="spinner-border text-light" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<script>
    function showSpinner() {
        document.getElementById('loadingSpinner').classList.remove('d-none');
    }

    function hideSpinner() {
        document.getElementById('loadingSpinner').classList.add('d-none');
    }

    async function apiCall(url, options = {}) {
        showSpinner(); // Show spinner before API call
        try {
            let response = await fetch(url, options);
            let data = await response.json();
            alert("API Call Success: " + (data.message || "Success"));
            return data; // Return response data
        } catch (error) {
            console.error('API Error:', error);
            alert("API Call Failed!");
            return null;
        } finally {
            hideSpinner(); // Hide spinner after API call
        }
    }
</script>
