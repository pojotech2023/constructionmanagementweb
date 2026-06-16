document.addEventListener('DOMContentLoaded', function() {
    const monthPicker = document.getElementById('monthPicker');
    const weekButtons = document.querySelectorAll('.week-btn');
    const spinner = document.getElementById('loadingSpinner');

    let selectedWeek = 0;

    // Set default month
    const currentMonth = new Date().toISOString().slice(0, 7);
    monthPicker.value = currentMonth;

    // When week button clicked
    weekButtons.forEach((button, index) => {
        button.addEventListener('click', function() {
            selectedWeek = index + 1;

            // Highlight selected week
            weekButtons.forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');

            fetchData();
        });
    });

    // When month changed
    monthPicker.addEventListener('change', function() {
        // Reset selected week and remove the active class from week buttons
        selectedWeek = 0;
        weekButtons.forEach(btn => btn.classList.remove('active'));

        fetchData();
    });

    function fetchData() {
        spinner.classList.remove('d-none');

        // Fetch data
        fetch(`{{ route('bricks.getData', ['siteId' => $siteId]) }}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    monthYear: monthPicker.value,
                    week: selectedWeek
                })
            })
            .then(response => response.json())
            .then(data => {
                updateTable(data.bricks);

                // Update the summary based on the week selection or month selection
                if (selectedWeek > 0) {
                    updateSummary(data);
                } else {
                    // Show summary even when no week is selected
                    updateSummary(data);
                }

                spinner.classList.add('d-none');
            })
            .catch(error => {
                console.error(error);
                spinner.classList.add('d-none');
            });
    }

    function updateTable(bricks) {
        const tbody = document.querySelector('table#add-row tbody');
        tbody.innerHTML = '';

        if (bricks.length === 0) {
            tbody.innerHTML =
                '<tr><td colspan="6" class="text-center">No Bricks list found for this Site.</td></tr>';
        } else {
            bricks.forEach((brick, index) => {
                const row = `
            <tr>
                <td>${index + 1}</td>
                <td>${brick.date}</td>
                <td>${brick.unit}</td>
                <td>${brick.vendor?.name || ''}</td>
                <td>${brick.price}</td>
                <td>${brick.available_unit_count}</td>
            </tr>
        `;
                tbody.insertAdjacentHTML('beforeend', row);
            });
        }
    }

    function updateSummary(data) {
        const summaryTable = document.querySelector('.card + .card .card-body table tbody');
        summaryTable.innerHTML = `
    <tr>
        <td><h6 class="fw-bold text-info">TOTAL</h6></td>
        <td><h6 class="fw-bold text-info">${data.totalUnits} Units</h6></td>
        <td><h6 class="fw-bold text-info">${data.totalAmount}</h6></td>
    </tr>
    <tr>
        <td><p class="text-success fw-bold">Settled Amount</p></td>
        <td></td>
        <td><p class="text-success fw-bold">${data.settledAmount}</p></td>
    </tr>
    <tr>
        <td><p class="text-danger fw-bold">Pending Amount</p></td>
        <td></td>
        <td><p class="text-danger fw-bold">${data.pendingAmount}</p></td>
    </tr>
`;
    }
});