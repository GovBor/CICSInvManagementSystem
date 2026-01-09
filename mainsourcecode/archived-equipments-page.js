let currentPage = 1
let totalPages = 1
let sortColumn = "date_added" // Default sort column
let sortOrder = "desc" // Default sort order (newest first for archives)

document.addEventListener("DOMContentLoaded", () => {
  fetchArchivedEquipment(currentPage, sortColumn, sortOrder)
  setupEventListeners()
})

function setupEventListeners() {
  // Add sorting event listeners to table headers
  document.querySelectorAll("th[data-sort]").forEach((header) => {
    header.addEventListener("click", function (event) {
      event.preventDefault()
      const clickedColumn = this.getAttribute("data-sort")

      // Toggle sorting order if same column clicked
      if (clickedColumn === sortColumn) {
        sortOrder = sortOrder === "asc" ? "desc" : "asc"
      } else {
        // New column - default to ascending
        sortColumn = clickedColumn
        sortOrder = "asc"
      }

      // Remove all arrow classes
      document.querySelectorAll(".sort-arrow").forEach((arrow) => {
        arrow.classList.remove("asc", "desc")
      })

      // Add arrow class to clicked header
      const arrow = this.querySelector(".sort-arrow")
      arrow.classList.add(sortOrder)

      // Fetch sorted data
      fetchArchivedEquipment(currentPage, sortColumn, sortOrder)
    })
  })

  // Pagination buttons
  document.getElementById("prevPage").addEventListener("click", () => {
    if (currentPage > 1) {
      currentPage--
      fetchArchivedEquipment(currentPage, sortColumn, sortOrder)
    }
  })

  document.getElementById("nextPage").addEventListener("click", () => {
    if (currentPage < totalPages) {
      currentPage++
      fetchArchivedEquipment(currentPage, sortColumn, sortOrder)
    }
  })

  // Filter button
  document.getElementById("filterBtn").addEventListener("click", () => {
    currentPage = 1 // Reset to first page when filtering
    fetchArchivedEquipment(currentPage, sortColumn, sortOrder)
  })

  // Search box - optional: add debounce for better performance
  document.getElementById("searchBox").addEventListener("input", () => {
    currentPage = 1
    fetchArchivedEquipment(currentPage, sortColumn, sortOrder)
  })
}

function fetchArchivedEquipment(page = 1, sortColumn = "date_added", sortOrder = "desc") {
  const searchTerm = document.getElementById("searchBox").value
  const category = document.getElementById("categoryFilter").value
  const dateFrom = document.getElementById("dateFrom").value
  const dateTo = document.getElementById("dateTo").value

  // Build query string
  let query = `page=${page}&sort=${sortColumn}&order=${sortOrder}&archived=1`

  if (searchTerm) query += `&search=${encodeURIComponent(searchTerm)}`
  if (category) query += `&category=${encodeURIComponent(category)}`
  if (dateFrom) query += `&date_from=${encodeURIComponent(dateFrom)}`
  if (dateTo) query += `&date_to=${encodeURIComponent(dateTo)}`

  // Show loading state
  const tbody = document.getElementById("equipment-table-body")
  tbody.innerHTML = '<tr><td colspan="9">Loading...</td></tr>'

  fetch(`fetch-equipment.php?${query}`)
    .then((response) => response.json())
    .then((data) => {
      if (data.error) {
        throw new Error(data.message)
      }
      updateTable(data)
      updatePagination(data.pagination)
    })
    .catch((error) => {
      console.error("Error:", error)
      tbody.innerHTML = `<tr><td colspan="9">Error loading data: ${error.message}</td></tr>`
    })
}

function updateTable(data) {
  const tbody = document.getElementById("equipment-table-body")

  if (data.data.length === 0) {
    tbody.innerHTML = '<tr><td colspan="9">No archived equipment found</td></tr>'
    return
  }

  tbody.innerHTML = data.data
    .map(
      (equipment) => `
        <tr>
            <td>${equipment.category_name || "N/A"}</td>
            <td>${equipment.e_name}</td>
            <td>${equipment.asset_id || "N/A"}</td>
            <td>${equipment.e_ID}</td>
            <td>${equipment.e_desc}</td>
            <td>${equipment.location_name || "N/A"}</td>
            <td>${equipment.s_status || "N/A"}</td>
            <td>${equipment.date_added || "N/A"}</td>
            <td>
                <button onclick="openRestoreModal('${equipment.e_ID}')" class="restore-btn">
                    <i class="fas fa-undo"></i> Restore
                </button>
            </td>
        </tr>
    `,
    )
    .join("")
}

function updatePagination(pagination) {
  currentPage = pagination.currentPage
  totalPages = pagination.totalPages

  document.getElementById("pageInfo").textContent = `Page ${currentPage} of ${totalPages}`
  document.getElementById("prevPage").disabled = currentPage <= 1
  document.getElementById("nextPage").disabled = currentPage >= totalPages
}

// Restore Equipment Modal Functions (keep existing)
let equipmentToRestore = null

function openRestoreModal(e_ID) {
  equipmentToRestore = e_ID
  document.getElementById("restoreModal").classList.add("active")
}

function closeAllModals() {
  document.getElementById("restoreModal").classList.remove("active")
  document.getElementById("successModal").classList.remove("active")
}

// Initialize modal event listeners
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".close, #cancelRestore, #confirmSuccess").forEach((btn) => {
    btn.addEventListener("click", closeAllModals)
  })

  document.getElementById("confirmRestore").addEventListener("click", () => {
    if (equipmentToRestore) {
      fetch("restore-equipment.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "e_ID=" + encodeURIComponent(equipmentToRestore),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            closeAllModals()
            document.getElementById("successMessage").textContent = data.message || "Equipment restored successfully!"
            document.getElementById("successModal").classList.add("active")

            // Refresh the equipment list after success
            setTimeout(() => {
              fetchArchivedEquipment(currentPage, sortColumn, sortOrder)
            }, 1000)
          } else {
            document.getElementById("successMessage").textContent = data.message || "Failed to restore equipment"
            document.getElementById("successModal").classList.add("active")
          }
        })
        .catch((error) => {
          document.getElementById("successMessage").textContent = "Network error occurred"
          document.getElementById("successModal").classList.add("active")
        })
    }
  })

  window.addEventListener("click", (event) => {
    if (
      event.target === document.getElementById("restoreModal") ||
      event.target === document.getElementById("successModal")
    ) {
      closeAllModals()
    }
  })
})

// Logout functionality (keep existing)
document.querySelector("form.logout")?.addEventListener("submit", (e) => {
  e.preventDefault()
  document.getElementById("logoutConfirmModal").classList.add("active")
})

document.getElementById("confirmLogout")?.addEventListener("click", () => {
  document.getElementById("logoutConfirmModal").classList.remove("active")
  document.getElementById("logoutSuccessModal").classList.add("active")

  const form = document.createElement("form")
  form.method = "POST"
  form.action = window.location.href

  const input = document.createElement("input")
  input.type = "hidden"
  input.name = "logout"
  input.value = "1"

  form.appendChild(input)
  document.body.appendChild(form)

  setTimeout(() => {
    form.submit()
  }, 1500)
})

document.getElementById("cancelLogout")?.addEventListener("click", () => {
  document.getElementById("logoutConfirmModal").classList.remove("active")
})

window.addEventListener("click", (event) => {
  if (event.target === document.getElementById("logoutConfirmModal")) {
    document.getElementById("logoutConfirmModal").classList.remove("active")
  }
  if (event.target === document.getElementById("logoutSuccessModal")) {
    document.getElementById("logoutSuccessModal").classList.remove("active")
  }
})
