const closeEditModalBtn = document.getElementById("closeEditModal")
if (closeEditModalBtn) {
  closeEditModalBtn.addEventListener("click", () => closeModal("editEquipmentModal"))
} else {
  console.error("closeEditModal button not found in the DOM")
}
function showSuccessMessage(message) {
  const notification = document.createElement("div")
  notification.className = "notification success"
  notification.textContent = message
  document.body.appendChild(notification)

  setTimeout(() => {
    notification.remove()
  }, 3000)
}

function showErrorMessage(message) {
  const notification = document.createElement("div")
  notification.className = "notification error"
  notification.textContent = message
  document.body.appendChild(notification)

  setTimeout(() => {
    notification.remove()
  }, 3000)
}
// Initial fetch when the page loads
document.addEventListener("DOMContentLoaded", () => {
  fetchEquipment(currentPage, sortColumn, sortOrder)

  // Modal event listeners
  document.getElementById("openModal").addEventListener("click", () => openModal("addEquipmentModal"))
  document.getElementById("closeModal").addEventListener("click", () => closeModal("addEquipmentModal"))
  document.getElementById("closeEditModal").addEventListener("click", () => closeModal("editEquipmentModal"))

  // New event listeners for category and location buttons
  document.getElementById("addCategoryBtn").addEventListener("click", () => openModal("addCategoryModal"))
  document.getElementById("addLocationBtn").addEventListener("click", () => openModal("addLocationModal"))
  document.getElementById("closeCategoryModal").addEventListener("click", () => closeModal("addCategoryModal"))
  document.getElementById("closeLocationModal").addEventListener("click", () => closeModal("addLocationModal"))

  // Logout modal listeners
  document.querySelector(".logout").addEventListener("click", (e) => {
    e.preventDefault()
    openModal("logoutConfirmModal")
  })

  // Add Equipment Success modal event listener
  document.getElementById("confirmAddSuccess").addEventListener("click", () => {
    closeModal("addEquipmentSuccessModal")
    fetchEquipment(currentPage)
  })

  document.getElementById("confirmLogout").addEventListener("click", handleLogout)
  document.getElementById("cancelLogout").addEventListener("click", () => closeModal("logoutConfirmModal"))

  // Form submissions
  document.getElementById("addCategoryForm").addEventListener("submit", handleAddCategory)
  document.getElementById("addLocationForm").addEventListener("submit", handleAddLocation)

  // Archive modal event listeners
  document.getElementById("confirmArchive").addEventListener("click", handleArchive)
  document.getElementById("cancelArchive").addEventListener("click", () => closeModal("archiveConfirmModal"))
  document.getElementById("confirmArchiveSuccess").addEventListener("click", () => {
    closeModal("archiveSuccessModal")
    fetchEquipment(currentPage, sortColumn, sortOrder)
  })

  // Add filter button event listener
  document.getElementById("filterBtn").addEventListener("click", () => {
    currentPage = 1
    fetchEquipment(currentPage, sortColumn, sortOrder)
  })

  // Initialize date inputs with current month range
  const today = new Date()
  const firstDay = new Date(today.getFullYear(), today.getMonth(), 1)
  const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0)

  const formatDate = (date) => {
    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, "0")
    const day = String(date.getDate()).padStart(2, "0")
    return `${year}-${month}-${day}`
  }

  // Uncomment these lines if you want to set default date range
  // document.getElementById("dateFrom").value = formatDate(firstDay);
  // document.getElementById("dateTo").value = formatDate(lastDay);
  document.getElementById("deleteCategoryBtn").addEventListener("click", function() {
    openModal("deleteCategoryModal");
  });
  
  // Delete Location Button Event
  document.getElementById("deleteLocationBtn").addEventListener("click", function() {
    openModal("deleteLocationModal");
  });
  
  // Close modal events
  document.getElementById("closeDeleteCategoryModal").addEventListener("click", function() {
    closeModal("deleteCategoryModal");
  });
  
  document.getElementById("closeDeleteLocationModal").addEventListener("click", function() {
    closeModal("deleteLocationModal");
  });
  
  // Confirm Delete Events
  document.getElementById("confirmDeleteCategory").addEventListener("click", function() {
    const select = document.getElementById("deleteCategorySelect");
    const categoryId = select.value;
    const categoryName = select.options[select.selectedIndex].text;
    
    if (!categoryId) {
      alert("Please select a category to delete.");
      return;
    }
    
    
  });
  
  document.getElementById("confirmDeleteLocation").addEventListener("click", function() {
    const select = document.getElementById("deleteLocationSelect");
    const locationId = select.value;
    const locationName = select.options[select.selectedIndex].text;
    
    if (!locationId) {
      alert("Please select a location to delete.");
      return;
    }
    
    
  });
}) // Notification functions


// Add sorting event listeners to table headers
document.querySelectorAll("th[data-sort]").forEach((header) => {
  header.addEventListener("click", function (event) {
    event.preventDefault() // Prevent default behavior (e.g., page reload)

    const clickedColumn = this.getAttribute("data-sort")

    // Toggle sorting order if the same column is clicked again
    if (clickedColumn === sortColumn) {
      sortOrder = sortOrder === "asc" ? "desc" : "asc" // Toggle between asc and desc
    } else {
      // If a different column is clicked, default to ascending order
      sortColumn = clickedColumn
      sortOrder = "asc"
    }

    // Remove all arrow classes from all headers
    document.querySelectorAll(".sort-arrow").forEach((arrow) => {
      arrow.classList.remove("asc", "desc")
    })

    // Add the correct arrow class to the clicked header
    const arrow = this.querySelector(".sort-arrow")
    arrow.classList.add(sortOrder)

    // Fetch sorted data
    fetchEquipment(currentPage, sortColumn, sortOrder)
  })
})

// Add Equipment Form Submission
document.getElementById("addEquipmentForm").addEventListener("submit", function (event) {
  event.preventDefault()
  const formData = new FormData(this)

  // For duplicates, we want to keep the original category_id and location_id
  fetch("add_equipment.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        openModal("addEquipmentSuccessModal")
        closeModal("addEquipmentModal")
        fetchEquipment(currentPage)
      } else {
        showErrorMessage(data.message || "Failed to Add equipment")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showErrorMessage("An error occurred while Adding equipment")
    })
})

// Edit Equipment Form Submission
document.getElementById("editEquipmentForm").addEventListener("submit", function (event) {
  event.preventDefault() // Prevent default form submission

  const formData = new FormData(this) // Create FormData object from the form

  fetch("edit_equipment.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      const responseMessage = document.getElementById("editResponseMessage")
      if (data.success) {
        responseMessage.innerHTML = `<p class="success">${data.message}</p>`
        setTimeout(() => {
          closeModal("editEquipmentModal") // Close the modal
          fetchEquipment(currentPage) // Refresh the table
        }, 2000) // Close modal after 2 seconds
      } else {
        responseMessage.innerHTML = `<p class="error">${data.message}</p>`
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      document.getElementById("editResponseMessage").innerHTML =
        `<p class="error">An error occurred. Please try again.</p>`
    })
})

// Handle Add Category form submission
function handleAddCategory(event) {
  event.preventDefault()
  const categoryName = document.getElementById("categoryName").value

  fetch("add_category.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `category_name=${encodeURIComponent(categoryName)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      const responseMessage = document.getElementById("categoryResponseMessage")
      if (data.success) {
        responseMessage.innerHTML = `<p class="success">${data.message}</p>`
        // Clear the form and close modal after 1.5 seconds
        setTimeout(() => {
          document.getElementById("categoryName").value = ""
          closeModal("addCategoryModal")
          // Refresh the page to show the new category in dropdowns
          window.location.reload()
        }, 1500)
      } else {
        responseMessage.innerHTML = `<p class="error">${data.message}</p>`
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      document.getElementById("categoryResponseMessage").innerHTML =
        `<p class="error">An error occurred. Please try again.</p>`
    })
}

// Handle Add Location form submission
function handleAddLocation(event) {
  event.preventDefault()
  const locationName = document.getElementById("locationName").value

  fetch("add_location.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `location_name=${encodeURIComponent(locationName)}`,
  })
    .then((response) => response.json())
    .then((data) => {
      const responseMessage = document.getElementById("locationResponseMessage")
      if (data.success) {
        responseMessage.innerHTML = `<p class="success">${data.message}</p>`
        // Clear the form and close modal after 1.5 seconds
        setTimeout(() => {
          document.getElementById("locationName").value = ""
          closeModal("addLocationModal")
          // Refresh the page to show the new location in dropdowns
          window.location.reload()
        }, 1500)
      } else {
        responseMessage.innerHTML = `<p class="error">${data.message}</p>`
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      document.getElementById("locationResponseMessage").innerHTML =
        `<p class="error">An error occurred. Please try again.</p>`
    })
}

// Dropzone functionality
const dropZone = document.getElementById("dropZone")
if (dropZone) {
  dropZone.addEventListener("click", () => document.getElementById("fileInput").click())
  dropZone.addEventListener("dragover", (event) => {
    event.preventDefault()
    dropZone.style.backgroundColor = "#f6e5e5"
  })
  dropZone.addEventListener("dragleave", () => (dropZone.style.backgroundColor = "white"))
  dropZone.addEventListener("drop", (event) => {
    event.preventDefault()
    dropZone.style.backgroundColor = "white"
    document.getElementById("fileInput").files = event.dataTransfer.files
  })
}

// Toggle Dropdowns
document.querySelectorAll(".dropdown-btn").forEach((button) => {
  button.addEventListener("click", function () {
    // Toggle active class on the clicked button
    this.classList.toggle("active")

    // Close other dropdowns
    document.querySelectorAll(".dropdown-btn").forEach((otherButton) => {
      if (otherButton !== this) {
        otherButton.classList.remove("active")
      }
    })

    // Toggle dropdown content visibility
    const dropdownContent = this.nextElementSibling
    dropdownContent.style.display = dropdownContent.style.display === "block" ? "none" : "none"
  })
})

// Close dropdowns when clicking outside
window.addEventListener("click", (event) => {
  if (!event.target.matches(".dropdown-btn")) {
    document.querySelectorAll(".dropdown-btn").forEach((button) => {
      button.classList.remove("active")
    })
    document.querySelectorAll(".dropdown-content").forEach((content) => {
      content.style.display = "none"
    })
  }
})
// Add event listeners to duplicate buttons
function addDuplicateButtonEventListeners() {
  document.querySelectorAll(".duplicate-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.stopPropagation()
      const row = this.closest("tr")
      const equipmentDataCell = row.querySelector("td[data-equipment]")

      if (!equipmentDataCell) {
        console.error("Equipment data cell not found")
        return
      }

      try {
        const equipmentData = JSON.parse(equipmentDataCell.dataset.equipment)
        openDuplicateModal(equipmentData)
      } catch (error) {
        console.error("Error parsing equipment data:", error)
      }
    })
  })
}
function openDuplicateModal(equipmentData) {
  // Reset the form first
  const form = document.getElementById("addEquipmentForm")
  form.reset()

  // Open modal first to ensure dropdowns are rendered
  openModal("addEquipmentModal")

  // Short delay to ensure DOM is ready
  setTimeout(() => {
    // Set basic fields with "Copy" appended
    form.querySelector('input[name="e_name"]').value = `${equipmentData.e_name}`
    form.querySelector('input[name="asset_id"]').value = `${equipmentData.asset_id}`
    form.querySelector('input[name="e_desc"]').value = equipmentData.e_desc || ""

    // Set dropdown values
    const categorySelect = form.querySelector('select[name="category_id"]')
    const locationSelect = form.querySelector('select[name="location_id"]')

    if (categorySelect && equipmentData.category_id) {
      // Verify option exists before setting
      const categoryOptionExists = Array.from(categorySelect.options).some(
        (opt) => opt.value == equipmentData.category_id,
      )

      if (categoryOptionExists) {
        categorySelect.value = equipmentData.category_id
        console.log("Duplicate category set to:", equipmentData.category_id)
      } else {
        console.warn("Category ID not found in dropdown:", equipmentData.category_id)
      }
    }

    if (locationSelect && equipmentData.location_id) {
      // Verify option exists before setting
      const locationOptionExists = Array.from(locationSelect.options).some(
        (opt) => opt.value == equipmentData.location_id,
      )

      if (locationOptionExists) {
        locationSelect.value = equipmentData.location_id
        console.log("Duplicate location set to:", equipmentData.location_id)
      } else {
        console.warn("Location ID not found in dropdown:", equipmentData.location_id)
      }
    }

    // If using Select2, trigger change
    if (typeof $ !== "undefined" && typeof jQuery !== "undefined") {
      jQuery('select[name="location_id"]').trigger("change")
      jQuery('select[name="category_id"]').trigger("change")
    }
  }, 150) // Slightly longer delay to ensure dropdowns are ready
}

function openAddEquipmentModal(equipmentData) {
  // Reset the form
  document.getElementById("addEquipmentForm").reset()

  // Open modal first (in case dropdowns load dynamically)
  openModal("addEquipmentModal")

  // Short delay to ensure dropdowns are ready
  setTimeout(() => {
    const form = document.getElementById("addEquipmentForm")

    // Set basic fields
    form.querySelector('input[name="e_name"]').value = equipmentData.e_name || ""
    form.querySelector('input[name="asset_id"]').value = equipmentData.asset_id || ""
    form.querySelector('input[name="e_ID"]').value = equipmentData.e_ID || ""
    form.querySelector('textarea[name="e_desc"]').value = equipmentData.e_desc || ""

    // Handle Category Dropdown
    const categorySelect = form.querySelector('select[name="category_id"]')
    if (categorySelect) {
      if (equipmentData.category_id) {
        categorySelect.value = equipmentData.category_id // Set the category value
        console.log("Set category to:", equipmentData.category_id, "Current value:", categorySelect.value)
      } else {
        console.warn("Category ID is not available in equipment data.")
      }
    } else {
      console.error("Category select element not found.")
    }

    // Handle Location Dropdown
    const locationSelect = form.querySelector('select[name="location_id"]')
    if (locationSelect) {
      if (equipmentData.location_id) {
        // Verify the option exists before setting
        const optionExists = Array.from(locationSelect.options).some(
          (option) => option.value == equipmentData.location_id,
        )

        if (optionExists) {
          locationSelect.value = equipmentData.location_id // Set the location value
          console.log("Set location to:", equipmentData.location_id, "Current value:", locationSelect.value)
        } else {
          console.warn("Location ID not found in dropdown:", equipmentData.location_id)
        }
      } else {
        console.warn("Location ID is not available in equipment data.")
      }
    } else {
      console.error("Location select element not found.")
    }
  }, 100) // Adjust the timeout as necessary
  // Show the modal
  openModal("addEquipmentModal")
}

let currentPage = 1
let totalPages = 1
let sortColumn = "s_status" // Default sort column
let sortOrder = "asc" // Default sort order

// Fetch equipment with sorting and pagination
function fetchEquipment(page = 1, sortColumn = "s_status", sortOrder = "asc") {
    try {
        const searchQuery = document.getElementById("searchBox")?.value.trim() || "";
        const categoryFilter = document.getElementById("categoryFilter")?.value || "";
        const statusFilter = document.getElementById("statusFilter")?.value || "";
        const locationFilter = document.getElementById("locationFilter")?.value || ""; // Safely get value
        const dateFrom = document.getElementById("dateFrom")?.value || "";
        const dateTo = document.getElementById("dateTo")?.value || "";

        // Base URL without optional params
        let url = `fetch-equipment.php?page=${page}&sort=${sortColumn}&order=${sortOrder}`;

        // Add optional parameters only if they have values
        if (searchQuery) url += `&search=${encodeURIComponent(searchQuery)}`;
        if (categoryFilter) url += `&category=${encodeURIComponent(categoryFilter)}`;
        if (statusFilter) url += `&status=${encodeURIComponent(statusFilter)}`;
        if (locationFilter) url += `&location=${encodeURIComponent(locationFilter)}`; // Will only appear if not empty
        if (dateFrom) url += `&date_from=${dateFrom}`;
        if (dateTo) url += `&date_to=${dateTo}`;

        console.log("Request URL:", url); // Debugging

        fetch(url)
            .then((response) => {
                if (!response.ok) throw new Error("Network response was not ok");
                return response.json();
            })
            .then((data) => {
                if (data.error) {
                    console.error("Error:", data.message);
                    alert("Error: " + data.message);
                } else {
                    populateTable(data.data);
                    updatePagination(data.pagination);
                }
            })
            .catch((error) => {
                console.error("Error fetching data:", error);
                alert("Error fetching data. Please check the console for details.");
            });
    } catch (error) {
        console.error("Error in fetchEquipment:", error);
    }
}

// Pagination event listeners
document.getElementById("prevPageBtn").addEventListener("click", () => {
  if (currentPage > 1) {
    currentPage--
    fetchEquipment(currentPage, sortColumn, sortOrder)
  }
})

document.getElementById("nextPageBtn").addEventListener("click", () => {
  if (currentPage < totalPages) {
    currentPage++
    fetchEquipment(currentPage, sortColumn, sortOrder)
  }
})

// Initial fetch
fetchEquipment(currentPage, sortColumn, sortOrder)

function showNotification(message, type) {
  // Implement your notification system here
  alert(`${type.toUpperCase()}: ${message}`)
}

// Add event listeners to archive buttons
function addArchiveButtonEventListeners() {
  document.querySelectorAll(".archive-btn").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.stopPropagation()
      const e_ID = this.getAttribute("data-id")
      const equipmentName = this.closest("tr").querySelector("td:nth-child(2)").textContent

      // Store the equipment ID in a data attribute on the confirm button
      document.getElementById("confirmArchive").setAttribute("data-id", e_ID)
      document.getElementById("confirmArchive").setAttribute("data-name", equipmentName)

      // Show confirmation modal
      openModal("archiveConfirmModal")
    })
  })
}

// Archive equipment handler
function handleArchive() {
  const confirmBtn = document.getElementById("confirmArchive")
  const e_ID = confirmBtn.getAttribute("data-id")
  const equipmentName = confirmBtn.getAttribute("data-name")

  closeModal("archiveConfirmModal")

  fetch("archive_equipment.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ e_ID: e_ID }),
  })
    .then((response) => {
      const contentType = response.headers.get("content-type")
      if (!contentType || !contentType.includes("application/json")) {
        return response.text().then((text) => {
          throw new Error(`Invalid response: ${text}`)
        })
      }
      return response.json()
    })
    .then((data) => {
      if (data.success) {
        // Show success modal
        document.querySelector("#archiveSuccessModal p").textContent =
          `${equipmentName} has been successfully archived.`
        openModal("archiveSuccessModal")

        // Refresh the equipment list after a delay
        setTimeout(() => {
          closeModal("archiveSuccessModal")
          fetchEquipment(currentPage, sortColumn, sortOrder)
        }, 2000)
      } else {
        showNotification(data.message || "Failed to archive equipment", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("An error occurred while archiving: " + error.message, "error")
    })
}

// Function to archive equipment via AJAX
function archiveEquipment(e_ID) {
  fetch("archive_equipment.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ e_ID: e_ID }),
  })
    .then((response) => {
      // First check if the response is JSON
      const contentType = response.headers.get("content-type")
      if (!contentType || !contentType.includes("application/json")) {
        return response.text().then((text) => {
          throw new Error(`Invalid response: ${text}`)
        })
      }
      return response.json()
    })
    .then((data) => {
      if (data.success) {
        showNotification(data.message, "success")
        // Refresh the equipment list
        fetchEquipment(currentPage, sortColumn, sortOrder)
      } else {
        showNotification(data.message || "Failed to archive equipment", "error")
      }
    })
    .catch((error) => {
      console.error("Error:", error)
      showNotification("An error occurred while archiving: " + error.message, "error")
    })
}

function populateTable(data) {
  const tableBody = document.getElementById("equipment-table-body")
  if (!tableBody) {
    console.error("Table body not found!")
    return
  }

  tableBody.innerHTML = "" // Clear existing table rows

  data.forEach((item) => {
    const row = document.createElement("tr")
    row.innerHTML = `
              <td data-equipment='${JSON.stringify(item)}'>${item.category_name || "N/A"}</td>
              <td>${item.e_name}</td>
              <td>${item.asset_id || "N/A"}</td>
              <td>${item.e_ID}</td>
              <td>${item.e_desc}</td>
              <td>${item.location_name || "N/A"}</td>
              <td class="status-cell" data-id="${item.e_ID}" data-status="${item.s_ID}">
                  <span>${item.s_status}</span>
              </td>
              <td>${item.date_added || "N/A"}</td>
              <td class="actions">
                  <button class="editEquipmentBtn" data-equipment='${JSON.stringify(item)}'>
                      <i class="fas fa-pen"></i>
                  </button>
                  <button class="duplicate-btn" data-id="${item.e_ID}"><i class="fas fa-copy"></i></button>
                  <button class="archive-btn" data-id="${item.e_ID}">
                      <i class="fas fa-archive"></i>
                  </button>
              </td>
          `
    tableBody.appendChild(row)
  })

  // Always call these after populating the table
  addArchiveButtonEventListeners()
  addEditButtonEventListeners()
  addStatusChangeEvent()
  addDuplicateButtonEventListeners() // Ensure this is called here
}

// Update pagination controls
function updatePagination(pagination) {
  currentPage = pagination.currentPage
  totalPages = pagination.totalPages

  const paginationContainer = document.querySelector(".pagination")
  if (paginationContainer) {
    paginationContainer.innerHTML = `
              <button id="prevPageBtn" ${currentPage === 1 ? "disabled" : ""}>Previous</button>
              <span>Page ${currentPage} of ${totalPages}</span>
              <button id="nextPageBtn" ${currentPage === totalPages ? "disabled" : ""}>Next</button>
          `

    document.getElementById("prevPageBtn").addEventListener("click", previousPage)
    document.getElementById("nextPageBtn").addEventListener("click", nextPage)
  }
}

// Go to previous page
function previousPage() {
  if (currentPage > 1) {
    currentPage--
    fetchEquipment(currentPage, sortColumn, sortOrder)
  }
}

// Go to next page
function nextPage() {
  if (currentPage < totalPages) {
    currentPage++
    fetchEquipment(currentPage, sortColumn, sortOrder)
  }
}

// Function to open modals with transitions
function openModal(modalId) {
  const modal = document.getElementById(modalId)
  modal.classList.remove("hidden")
  modal.classList.add("active")

  // Special handling for logout success modal
  if (modalId === "logoutSuccessModal") {
    modal.querySelector(".modal-content").classList.add("logout-success-redirect")
  }
}

// Function to close modals with transitions
function closeModal(modalId) {
  const modal = document.getElementById(modalId)
  modal.classList.remove("active")
  setTimeout(() => modal.classList.add("hidden"), 300)
}

// Function to open the edit modal and populate fields
function openEditModal(equipmentData) {
  setTimeout(() => {
    // Populate basic fields
    document.getElementById("oldEditId").value = equipmentData.e_ID
    document.getElementById("editId").value = equipmentData.e_ID
    document.getElementById("editName").value = equipmentData.e_name
    document.getElementById("editAssetId").value = equipmentData.asset_id
    document.getElementById("editDescription").value = equipmentData.e_desc

    // Set category dropdown
    const categorySelect = document.querySelector('#editEquipmentForm select[name="category_id"]')
    if (categorySelect && equipmentData.category_id) {
      categorySelect.value = equipmentData.category_id
      console.log("Set edit category to:", equipmentData.category_id)
    }

    // Set location dropdown
    const locationSelect = document.querySelector('#editEquipmentForm select[name="location_id"]')
    if (locationSelect && equipmentData.location_id) {
      locationSelect.value = equipmentData.location_id
      console.log("Set edit location to:", equipmentData.location_id)
    }
  }, 100)

  // Open the edit modal
  openModal("editEquipmentModal")
}

// Add event listeners to Edit buttons
function addEditButtonEventListeners() {
  // Use event delegation on the table body
  document.getElementById("equipment-table-body").addEventListener("click", (event) => {
    // Check if the clicked element is an edit button or its child
    const editButton = event.target.closest(".editEquipmentBtn")

    if (editButton) {
      const equipmentData = JSON.parse(editButton.getAttribute("data-equipment"))
      openEditModal(equipmentData)
    }
  })
}

// Function to update status
// Function to add event listeners for status changes
function addStatusChangeEvent() {
  document.querySelectorAll(".status-cell").forEach((cell) => {
    cell.addEventListener("click", function (event) {
      event.stopPropagation()

      const e_ID = this.getAttribute("data-id")
      const currentStatus = this.getAttribute("data-status")

      // Don't allow changing status if already archived (status 3)
      if (currentStatus === "3") {
        showNotification("Archived items cannot be modified", "warning")
        return
      }

      // Toggle between Working (1) and Defective (2)
      const newStatus = currentStatus === "1" ? "2" : "1"

      // Add changing animation
      cell.classList.add("changing")

      // Update status via AJAX
      updateEquipmentStatus(e_ID, newStatus)
        .then(() => {
          // Update cell appearance
          cell.setAttribute("data-status", newStatus)
          const statusText = newStatus === "1" ? "Working" : "Defective"
          cell.querySelector("span").textContent = statusText

          // Remove changing class after animation
          setTimeout(() => {
            cell.classList.remove("changing")
          }, 400)
        })
        .catch((error) => {
          console.error("Status update failed:", error)
          cell.classList.remove("changing")
          showNotification("Failed to update status: " + error.message, "error")
        })
    })
  })
}

// Improved updateStatus function that returns a Promise
function updateEquipmentStatus(e_ID, s_ID) {
  console.log("Sending data to server:", { e_ID, s_ID })
  return new Promise((resolve, reject) => {
    fetch("update-status.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
        Accept: "application/json",
      },
      body: `e_ID=${encodeURIComponent(e_ID)}&s_ID=${encodeURIComponent(s_ID)}`,
    })
      .then((response) => {
        console.log("Raw response:", response)
        if (!response.ok) {
          throw new Error("Network response was not ok")
        }
        return response.json()
      })
      .then((data) => {
        console.log("Server response:", data)
        if (data.success) {
          resolve(data)
        } else {
          reject(new Error(data.message || "Failed to update status"))
        }
      })
      .catch((error) => {
        console.error("Full error details:", error)
        reject(error)
      })
  })
}

// Logout handler function
function handleLogout() {
  closeModal("logoutConfirmModal")

  // Show success modal
  openModal("logoutSuccessModal")

  // Create a form and submit it to ensure POST request
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
}

// Search functionality
document.getElementById("searchBox").addEventListener("input", (e) => {
  // If we're typing in the search box, we might want to apply filters immediately
  // or wait for the Apply button - depending on your preference
  // For immediate filtering, uncomment the next line:
  // fetchEquipment(1, sortColumn, sortOrder);
})

// Make sure the filter button applies all filters
document.getElementById("filterBtn").addEventListener("click", () => {
  currentPage = 1 // Reset to first page when applying filters
  fetchEquipment(currentPage, sortColumn, sortOrder)
})

function searchEquipment(searchTerm) {
  fetch("search_equipment.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `searchTerm=${encodeURIComponent(searchTerm)}`,
  })
    .then((response) => {
      if (!response.ok) {
        throw new Error("Network response was not ok")
      }
      return response.json()
    })
    .then((data) => {
      console.log("Search response:", data) // Log the data

      // Check if data is structured correctly
      if (!data || !data.data) {
        console.error('Data is undefined or does not contain "data" property:', data)
        return
      }

      // Clear previous results
      const tableBody = document.getElementById("equipment-table-body")
      tableBody.innerHTML = "" // Clear existing rows

      // Check if data is an empty array
      if (!data.data.length) {
        displayNoResults() // Call the function to display no results
        return // Exit if there's no data
      }

      // Process and display the data
      data.data.forEach((equipment) => {
        console.log("Equipment item:", equipment) // Log each equipment item
        const row = document.createElement("tr")
        row.innerHTML = `
                  <td data-equipment='${JSON.stringify(equipment)}'>${equipment.category_name || "N/A"}</td>
                  <td>${equipment.e_name || "N/A"}</td>
                  <td>${equipment.asset_id || "N/A"}</td>
                  <td>${equipment.e_ID || "N/A"}</td>
                  <td>${equipment.e_desc || "N/A"}</td>
                  <td>${equipment.location_name || "N/A"}</td>
                  <td class="status-cell" data-id="${equipment.e_ID || ""}" data-status="${equipment.s_ID || ""}">
                      <span>${equipment.s_status || "Unknown"}</span>
                  </td>
                  <td class="actions">
                      <button class="editEquipmentBtn" data-equipment='${JSON.stringify(equipment)}'>
                          <i class="fas fa-pen"></i>
                      </button>
                      <button class="duplicate-btn" data-id="${equipment.e_ID || ""}"><i class="fas fa-copy"></i></button>
                      <button class="archive-btn" data-id="${equipment.e_ID || ""}">
                          <i class="fas fa-archive"></i>
                      </button>
                  </td>
              `
        tableBody.appendChild(row)
      })

      // Call necessary functions for the new rows
      addArchiveButtonEventListeners()
      addEditButtonEventListeners()
      addStatusChangeEvent()
      addDuplicateButtonEventListeners()
    })
    .catch((error) => {
      console.error("Fetch error:", error)
      alert("An error occurred while fetching equipment data. Please try again later.")
    })
}

// Function to display no results message
function displayNoResults() {
  const tableBody = document.getElementById("equipment-table-body")
  tableBody.innerHTML = '<tr><td colspan="8" class="no-results">No equipment found matching your search.</td></tr>'
}

// Function to show error messages
function showError(message) {
  const tableBody = document.getElementById("equipment-table-body")
  tableBody.innerHTML = `<tr><td colspan="8" class="error">${message}</td></tr>`
}

// Update the filter button event listener to prevent popup
document.addEventListener("DOMContentLoaded", () => {
  // Get the filter button
  const filterBtn = document.getElementById("filterBtn")

  if (filterBtn) {
    // Remove any existing event listeners
    const newFilterBtn = filterBtn.cloneNode(true)
    filterBtn.parentNode.replaceChild(newFilterBtn, filterBtn)

    // Add new event listener that doesn't show a popup
    newFilterBtn.addEventListener("click", (e) => {
      e.preventDefault()
      // Just fetch the equipment with the current filters
      fetchEquipment(1, sortColumn, sortOrder)
    })
  }
})

// Declare $ and jQuery if they are not already declared
if (typeof $ === "undefined") {
  $ = jQuery = {} // Or any other suitable fallback
}

document.getElementById("deleteCategoryBtn").addEventListener("click", function() {
    const select = document.getElementById("deleteCategorySelect");
    if (select.options.length === 0) {
        showErrorMessage("No categories available to delete.");
        return;
    }
    openModal("deleteCategoryModal");
});

// Delete Location Button Event
document.getElementById("deleteLocationBtn").addEventListener("click", function() {
    const select = document.getElementById("deleteLocationSelect");
    if (select.options.length === 0) {
        showErrorMessage("No locations available to delete.");
        return;
    }
    openModal("deleteLocationModal");
});

// Category Delete Confirmation Flow
document.getElementById("confirmDeleteCategory").addEventListener("click", function() {
    const select = document.getElementById("deleteCategorySelect");
    const categoryId = select.value;
    const categoryName = select.options[select.selectedIndex].text;
    
    if (!categoryId) {
        showErrorMessage("Please select a category to delete.");
        return;
    }
    
    // Show confirmation modal
    document.getElementById("categoryToDeleteName").textContent = categoryName;
    closeModal("deleteCategoryModal");
    openModal("deleteCategoryConfirmModal");
    
    // Store the category ID in the final confirm button
    document.getElementById("finalConfirmDeleteCategory").setAttribute("data-id", categoryId);
});

// Location Delete Confirmation Flow
document.getElementById("confirmDeleteLocation").addEventListener("click", function() {
    const select = document.getElementById("deleteLocationSelect");
    const locationId = select.value;
    const locationName = select.options[select.selectedIndex].text;
    
    if (!locationId) {
        showErrorMessage("Please select a location to delete.");
        return;
    }
    
    // Show confirmation modal
    document.getElementById("locationToDeleteName").textContent = locationName;
    closeModal("deleteLocationModal");
    openModal("deleteLocationConfirmModal");
    
    // Store the location ID in the final confirm button
    document.getElementById("finalConfirmDeleteLocation").setAttribute("data-id", locationId);
});

// Final Category Delete
document.getElementById("finalConfirmDeleteCategory").addEventListener("click", function() {
    const categoryId = this.getAttribute("data-id");
    const select = document.getElementById("deleteCategorySelect");
    const categoryName = select.options[select.selectedIndex].text;
    
    // Show loading state
    const confirmBtn = this;
    
    fetch('delete_category.php?id=' + categoryId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove from delete modal dropdown
                select.remove(select.selectedIndex);
                
                // Remove from filter dropdown
                const categoryFilter = document.getElementById('categoryFilter');
                for (let i = 0; i < categoryFilter.options.length; i++) {
                    if (categoryFilter.options[i].value === categoryId) {
                        categoryFilter.remove(i);
                        break;
                    }
                }
                
                // Show success modal
                document.getElementById("deletedCategoryName").textContent = categoryName;
                closeModal("deleteCategoryConfirmModal");
                openModal("deleteCategorySuccessModal");
                
                // Auto-close success modal after 2 seconds
                setTimeout(() => {
                    closeModal("deleteCategorySuccessModal");
                }, 2000);
            } else {
                showErrorMessage(data.message || "Error deleting category");
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Delete";
            }
        })
        .catch(err => {
            console.error(err);
            showErrorMessage("An error occurred while deleting the category");
            confirmBtn.disabled = false;
            confirmBtn.textContent = "Delete";
        });
});

// Final Location Delete
document.getElementById("finalConfirmDeleteLocation").addEventListener("click", function() {
    const locationId = this.getAttribute("data-id");
    const select = document.getElementById("deleteLocationSelect");
    const locationName = select.options[select.selectedIndex].text;
    
    // Show loading state
    const confirmBtn = this;
    
    fetch('delete_location.php?id=' + locationId)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Remove from delete modal dropdown
                select.remove(select.selectedIndex);
                
                // Remove from filter dropdown
                const locationFilter = document.getElementById('locationFilter');
                for (let i = 0; i < locationFilter.options.length; i++) {
                    if (locationFilter.options[i].value === locationId) {
                        locationFilter.remove(i);
                        break;
                    }
                }
                
                // Show success modal
                document.getElementById("deletedLocationName").textContent = locationName;
                closeModal("deleteLocationConfirmModal");
                openModal("deleteLocationSuccessModal");
                
                // Auto-close success modal after 2 seconds
                setTimeout(() => {
                    closeModal("deleteLocationSuccessModal");
                }, 2000);
            } else {
                showErrorMessage(data.message || "Error deleting location");
                confirmBtn.disabled = false;
                confirmBtn.textContent = "Delete";
            }
        })
        .catch(err => {
            console.error(err);
            showErrorMessage("An error occurred while deleting the location");
            confirmBtn.disabled = false;
            confirmBtn.textContent = "Delete";
        });
});