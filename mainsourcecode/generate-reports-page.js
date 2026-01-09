document.addEventListener("DOMContentLoaded", function () {
    let currentPage = 1;
    const limit = 15;
    let sortColumn = "date_added"; // Default sort column
    let sortOrder = "desc"; // Default sort order

    // DOM elements for modals
    const modals = {
        logoutConfirm: document.getElementById('logoutConfirmModal'),
        logoutSuccess: document.getElementById('logoutSuccessModal'),
        exportModal: document.getElementById('exportModal')
    };

    const buttons = {
        confirmLogout: document.getElementById('confirmLogout'),
        cancelLogout: document.getElementById('cancelLogout'),
        confirmLogoutSuccess: document.getElementById('confirmLogoutSuccess'),
        exportBtn: document.getElementById('exportBtn'),
        closeExportModal: document.getElementById('closeExportModal'),
        exportPDF: document.getElementById('exportPDF'),
        exportExcel: document.getElementById('exportExcel')
    };

    const forms = {
        logout: document.querySelector('form.logout')
    };

    // Initialize all modals
    function initModals() {
        // Logout Confirmation Modal
        if (buttons.confirmLogout) {
            buttons.confirmLogout.addEventListener('click', handleLogout);
        }

        if (buttons.cancelLogout) {
            buttons.cancelLogout.addEventListener('click', () => {
                modals.logoutConfirm.style.display = 'none';
            });
        }

        if (buttons.confirmLogoutSuccess) {
            buttons.confirmLogoutSuccess.addEventListener('click', () => {
                modals.logoutSuccess.style.display = 'none';
            });
        }

        // Logout Form
        if (forms.logout) {
            forms.logout.addEventListener("submit", function(e) {
                e.preventDefault();
                modals.logoutConfirm.style.display = 'flex';
            });
        }

        // Export Modal
        if (buttons.exportBtn) {
            buttons.exportBtn.addEventListener('click', showExportOptions);
        }

        if (buttons.exportPDF) {
            buttons.exportPDF.addEventListener('click', () => exportReport('pdf'));
        }

        if (buttons.exportExcel) {
            buttons.exportExcel.addEventListener('click', () => exportReport('excel'));
        }

        // Close modals when clicking outside
        window.addEventListener('click', (event) => {
            if (event.target === modals.logoutConfirm) {
                modals.logoutConfirm.style.display = 'none';
            }
            if (event.target === modals.logoutSuccess) {
                modals.logoutSuccess.style.display = 'none';
            }
            if (event.target === modals.exportModal) {
                modals.exportModal.style.display = 'none';
            }
        });

        // Close buttons for all modals
        document.querySelectorAll('.modal .close').forEach(btn => {
            btn.addEventListener('click', function() {
                this.closest('.modal').style.display = 'none';
            });
        });
    }

    // Add sorting event listeners to table headers
    function setupSorting() {
        document.querySelectorAll("th[data-sort]").forEach((header) => {
            header.addEventListener("click", function (event) {
                event.preventDefault();
                const clickedColumn = this.getAttribute("data-sort");

                // Toggle sorting order if same column clicked
                if (clickedColumn === sortColumn) {
                    sortOrder = sortOrder === "asc" ? "desc" : "asc";
                } else {
                    // New column - default to ascending
                    sortColumn = clickedColumn;
                    sortOrder = "asc";
                }

                // Remove all arrow classes
                document.querySelectorAll(".sort-arrow").forEach((arrow) => {
                    arrow.classList.remove("asc", "desc");
                });

                // Add arrow class to clicked header
                const arrow = this.querySelector(".sort-arrow");
                arrow.classList.add(sortOrder);

                // Fetch sorted data
                fetchEquipment(currentPage);
            });
        });
    }

    function handleLogout() {
        // Remove confirmation modal
        modals.logoutConfirm.style.display = 'none';
        
        // Show success message briefly
        modals.logoutSuccess.style.display = 'flex';
        
        // Create and submit a hidden form to ensure POST request
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = window.location.href;
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'logout';
        input.value = '1';
        
        form.appendChild(input);
        document.body.appendChild(form);
        
        setTimeout(() => {
            form.submit();
        }, 1500);
    }

    function fetchEquipment(page = 1) {
        let searchQuery = document.getElementById("searchBox").value.trim();
        let categoryFilter = document.getElementById("categoryFilter").value;
        let statusFilter = document.getElementById("statusFilter").value;
        let dateFrom = document.getElementById("dateFrom").value;
        let dateTo = document.getElementById("dateTo").value;
        let locationFilter = document.getElementById("locationFilter").value;

        let queryString = `search=${encodeURIComponent(searchQuery)}&category=${encodeURIComponent(categoryFilter)}&location=${encodeURIComponent(locationFilter)}&status=${encodeURIComponent(statusFilter)}&dateFrom=${dateFrom}&dateTo=${dateTo}&page=${page}&limit=${limit}&sort=${sortColumn}&order=${sortOrder}`;

        fetch("fetch-filtered-reports.php?" + queryString)
        .then(response => response.json())
        .then(responseData => {
            let data = responseData.data;
            let totalRows = responseData.totalRows;
            let totalPages = Math.ceil(totalRows / limit);
            let tableBody = document.getElementById("reportTableBody");

            tableBody.innerHTML = "";

            if (data.length > 0) {
                data.forEach(row => {
                    let tr = document.createElement("tr");
                    tr.innerHTML = `
                        <td>${row.category_name || 'N/A'}</td>
                        <td>${row.e_name}</td>
                        <td>${row.asset_id || 'N/A'}</td>
                        <td>${row.e_ID}</td>
                        <td>${row.e_desc}</td>
                        <td>${row.location_name || 'N/A'}</td>
                        <td>${row.s_status}</td>
                        <td>${row.date_added}</td>
                    `;
                    tableBody.appendChild(tr);
                });
            } else {
                tableBody.innerHTML = "<tr><td colspan='8'>No records found</td></tr>";
            }

            document.getElementById("pageInfo").textContent = `Page ${page} of ${totalPages}`;
            document.getElementById("prevPage").disabled = (page <= 1);
            document.getElementById("nextPage").disabled = (page >= totalPages);
        })
        .catch(error => console.error("Error fetching data:", error));
    }

    // Initialize event listeners
    function initEventListeners() {
        document.getElementById("prevPage").addEventListener("click", function() {
            if (currentPage > 1) {
                currentPage--;
                fetchEquipment(currentPage);
            }
        });

        document.getElementById("nextPage").addEventListener("click", function() {
            currentPage++;
            fetchEquipment(currentPage);
        });

        document.getElementById("filterBtn").addEventListener("click", function() {
            currentPage = 1;
            fetchEquipment(currentPage);
        });

        // Search box with debounce
        let searchTimeout;
        document.getElementById("searchBox").addEventListener("input", function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentPage = 1;
                fetchEquipment(currentPage);
            }, 300);
        });
    }

    function showExportOptions() {
        modals.exportModal.style.display = "flex";
    }

    function closeExportModal() {
        modals.exportModal.style.display = "none";
    }

    function exportReport(type) {
        let searchQuery = document.getElementById("searchBox").value.trim();
        let categoryFilter = document.getElementById("categoryFilter").value;
        let locationFilter = document.getElementById("locationFilter").value;
        let statusFilter = document.getElementById("statusFilter").value;
        let dateFrom = document.getElementById("dateFrom").value;
        let dateTo = document.getElementById("dateTo").value;

        let queryString = `type=${type}&sort=${sortColumn}&order=${sortOrder}`;
        
        if (searchQuery || categoryFilter || statusFilter || locationFilter || (dateFrom && dateTo)) {
            queryString += `&search=${encodeURIComponent(searchQuery)}&category=${encodeURIComponent(categoryFilter)}&location=${encodeURIComponent(locationFilter)}&status=${encodeURIComponent(statusFilter)}&dateFrom=${dateFrom}&dateTo=${dateTo}`;
        }

        window.location.href = "download-report.php?" + queryString;
        closeExportModal();
    }

    // Initialize everything
    function init() {
        initModals();
        setupSorting();
        initEventListeners();
        fetchEquipment();
    }

    // Start the application
    init();
});