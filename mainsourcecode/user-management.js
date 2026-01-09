document.addEventListener("DOMContentLoaded", () => {
  // Initialize variables
  let currentUserPage = 1
  let totalUserPages = 1
  let currentEditingUser = null
  let userToDelete = null

  // DOM elements
  const elements = {
    modals: {
      addUser: document.getElementById("addUserModal"),
      editUser: document.getElementById("editUserModal"),
      editConfirm: document.getElementById("editConfirmModal"),
      editSuccess: document.getElementById("editSuccessModal"),
      deleteConfirm: document.getElementById("deleteConfirmModal"),
      deleteSuccess: document.getElementById("deleteSuccessModal"),
      logoutConfirm: document.getElementById("logoutConfirmModal"),
      logoutSuccess: document.getElementById("logoutSuccessModal"),
    },
    buttons: {
      openAddUser: document.getElementById("openAddUserModal"),
      closeAddUser: document.getElementById("closeAddUserModal"),
      closeEditUser: document.getElementById("closeEditUserModal"),
      confirmEdit: document.getElementById("confirmEdit"),
      cancelEdit: document.getElementById("cancelEdit"),
      confirmEditSuccess: document.getElementById("confirmEditSuccess"),
      confirmDelete: document.getElementById("confirmDelete"),
      cancelDelete: document.getElementById("cancelDelete"),
      confirmDeleteSuccess: document.getElementById("confirmDeleteSuccess"),
      confirmLogout: document.getElementById("confirmLogout"),
      cancelLogout: document.getElementById("cancelLogout"),
      prevPage: document.getElementById("prevUsersPage"),
      nextPage: document.getElementById("nextUsersPage"),
    },
    forms: {
      addUser: document.getElementById("addUserForm"),
      editUser: document.getElementById("editUserForm"),
      logout: document.querySelector("form.logout"),
    },
    displays: {
      usersTable: document.getElementById("users-table-body"),
      pageInfo: document.getElementById("usersPageInfo"),
      searchInput: document.getElementById("searchUsersInput"),
      addUserResponse: document.getElementById("addUserResponseMessage"),
      editSuccessMessage: document.getElementById("editSuccessMessage"),
      deleteSuccessMessage: document.getElementById("deleteSuccessMessage"),
      logoutSuccessMessage: document.getElementById("logoutSuccessMessage"),
    },
  }

  // OTP Handling
  const otpBox = document.getElementById("otpBox")

  // Initialize modals
  function initModals() {
    // Add User Modal
    if (elements.buttons.openAddUser) {
      elements.buttons.openAddUser.addEventListener("click", () => {
        elements.modals.addUser.classList.add("active")
      })
    }

    if (elements.buttons.closeAddUser) {
      elements.buttons.closeAddUser.addEventListener("click", () => {
        elements.modals.addUser.classList.remove("active")
      })
    }

    // Edit User Modal
    if (elements.buttons.closeEditUser) {
      elements.buttons.closeEditUser.addEventListener("click", () => {
        elements.modals.editUser.classList.remove("active")
      })
    }
    // Clickable OTP box to regenerate OTP
    if (otpBox) {
      otpBox.addEventListener("click", () => {
        fetch("generate-otp.php")
          .then((response) => response.json())
          .then((data) => {
            otpBox.textContent = "Generate OTP: " + data.otp
            startCountdown(180)
          })
          .catch((err) => {
            console.error("Error fetching OTP:", err)
          })
      })
    }

    // Edit Confirmation Modal
    if (elements.buttons.confirmEdit) {
      elements.buttons.confirmEdit.addEventListener("click", handleEditSubmit)
    }

    if (elements.buttons.cancelEdit) {
      elements.buttons.cancelEdit.addEventListener("click", () => {
        elements.modals.editConfirm.classList.remove("active")
      })
    }

    if (elements.buttons.confirmEditSuccess) {
      elements.buttons.confirmEditSuccess.addEventListener("click", () => {
        elements.modals.editSuccess.classList.remove("active")
        elements.modals.editUser.classList.remove("active")
        fetchUsers()
      })
    }

    // Delete Confirmation Modal
    if (elements.buttons.confirmDelete) {
      elements.buttons.confirmDelete.addEventListener("click", handleDeleteUser)
    }

    if (elements.buttons.cancelDelete) {
      elements.buttons.cancelDelete.addEventListener("click", () => {
        elements.modals.deleteConfirm.classList.remove("active")
      })
    }

    if (elements.buttons.confirmDeleteSuccess) {
      elements.buttons.confirmDeleteSuccess.addEventListener("click", () => {
        elements.modals.deleteSuccess.classList.remove("active")
        fetchUsers()
      })
    }

    // Logout Confirmation Modal
    if (elements.buttons.confirmLogout) {
      elements.buttons.confirmLogout.addEventListener("click", handleLogout)
    }

    if (elements.buttons.cancelLogout) {
      elements.buttons.cancelLogout.addEventListener("click", () => {
        elements.modals.logoutConfirm.classList.remove("active")
      })
    }

    // Close modals when clicking outside
    window.addEventListener("click", (event) => {
      Object.values(elements.modals).forEach((modal) => {
        if (event.target === modal) {
          modal.classList.remove("active")
          if (modal === elements.modals.editSuccess || modal === elements.modals.deleteSuccess) {
            fetchUsers()
          }
        }
      })
    })

    // Close buttons for all modals
    document.querySelectorAll(".modal .close").forEach((btn) => {
      btn.addEventListener("click", function () {
        this.closest(".modal").classList.remove("active")
      })
    })
  }

  // Form submissions
  function initForms() {
    // Add User Form
    if (elements.forms.addUser) {
      elements.forms.addUser.addEventListener("submit", (e) => {
        e.preventDefault()
        submitAddUserForm()
      })
    }

    // Edit User Form
    if (elements.forms.editUser) {
      elements.forms.editUser.addEventListener("submit", (e) => {
        e.preventDefault()
        elements.modals.editConfirm.classList.add("active")
      })
    }

    // Logout Form
    if (elements.forms.logout) {
      elements.forms.logout.addEventListener("submit", (e) => {
        e.preventDefault()
        elements.modals.logoutConfirm.classList.add("active")
      })
    }
  }

  function handleLogout() {
    // Remove confirmation modal
    elements.modals.logoutConfirm.classList.remove("active")

    // Show success message briefly
    elements.modals.logoutSuccess.classList.add("active")

    // Create and submit a hidden form to ensure POST request
    const form = document.createElement("form")
    form.method = "POST"
    form.action = window.location.href

    const input = document.createElement("input")
    input.type = "hidden"
    input.name = "logout"
    input.value = "1"

    form.appendChild(input)
    document.body.appendChild(form)
    form.submit()

    setTimeout(() => {
      form.submit()
    }, 1500)
  }

  function submitAddUserForm() {
    const formData = new FormData(elements.forms.addUser)

    fetch(elements.forms.addUser.action, {
      method: "POST",
      body: formData,
    })
      .then(handleResponse)
      .then((data) => {
        elements.displays.addUserResponse.textContent = data.success || data.error
        elements.displays.addUserResponse.className = data.success ? "success" : "error"
        elements.displays.addUserResponse.style.display = "block"

        if (data.success) {
          elements.forms.addUser.reset()
          setTimeout(() => {
            elements.modals.addUser.classList.remove("active")
            fetchUsers()
          }, 1500)
        }
      })
      .catch(handleError.bind(null, elements.displays.addUserResponse))
  }

  function handleEditSubmit() {
    const formData = new FormData(elements.forms.editUser)

    fetch("edit_user.php", {
      method: "POST",
      body: formData,
    })
      .then(handleResponse)
      .then((data) => {
        elements.modals.editConfirm.classList.remove("active")

        if (data.success) {
          elements.displays.editSuccessMessage.textContent = data.success
          elements.modals.editSuccess.classList.add("active")
        } else {
          showErrorMessage("editUserResponseMessage", data.error || "Failed to update user")
        }
      })
      .catch(handleError.bind(null, "editUserResponseMessage"))
  }

  function handleDeleteUser() {
    if (!userToDelete) return

    fetch("delete_user.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `user_id=${userToDelete}`,
    })
      .then(handleResponse)
      .then((data) => {
        elements.modals.deleteConfirm.classList.remove("active")

        if (data.success) {
          elements.displays.deleteSuccessMessage.textContent = data.success
          elements.modals.deleteSuccess.classList.add("active")
        } else {
          showErrorMessage(null, data.error || "Failed to delete user")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
        elements.modals.deleteConfirm.classList.remove("active")
        showErrorMessage(null, "An error occurred while deleting the user.")
      })
  }

  function deleteUser(userId) {
    userToDelete = userId
    elements.modals.deleteConfirm.classList.add("active")
  }

  function handleResponse(response) {
    if (!response.ok) {
      throw new Error("Network response was not ok")
    }
    return response.json()
  }

  function handleError(elementId, error) {
    console.error("Error:", error)
    showErrorMessage(
      elementId,
      typeof elementId === "string" ? "Failed to complete operation. Please try again." : error.message,
    )
  }

  function showErrorMessage(elementId, message) {
    const element = typeof elementId === "string" ? document.getElementById(elementId) : elementId
    if (element) {
      element.textContent = message
      element.className = "error"
      element.style.display = "block"
    } else {
      alert(message)
    }
  }

  // Fetch users from server
  function fetchUsers() {
    const searchValue = elements.displays.searchInput ? elements.displays.searchInput.value : ""
    const url = `fetch_users.php?page=${currentUserPage}&search=${encodeURIComponent(searchValue)}`

    fetch(url)
      .then(handleResponse)
      .then((data) => {
        if (data.error) {
          throw new Error(data.error)
        }
        populateUsersTable(data.users)
        updateUserPagination(data.pagination)
      })
      .catch((error) => {
        console.error("Error fetching users:", error)
        showErrorMessage("users-table-body", "Error loading user data")
      })
  }

  // Populate users table
  function populateUsersTable(users) {
    elements.displays.usersTable.innerHTML = ""

    if (users.length === 0) {
      elements.displays.usersTable.innerHTML = '<tr><td colspan="5" class="no-users">No users found</td></tr>'
      return
    }

    users.forEach((user) => {
      const row = document.createElement("tr")
      const lastLogin = user.last_logged_in ? new Date(user.last_logged_in).toLocaleString() : "Never logged in"

      const roleBadge = document.createElement("span")
      roleBadge.className = `role-badge ${user.role}`
      roleBadge.textContent = user.role

      row.innerHTML = `
                <td>${user.username}</td>
                <td>${user.email}</td>
                <td></td>
                <td>${lastLogin}</td>
                <td class="user-actions">
                    <button class="edit-user-btn" data-user-id="${user.id}">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button class="delete-user-btn" data-user-id="${user.id}">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            `

      row.querySelector("td:nth-child(3)").appendChild(roleBadge)
      elements.displays.usersTable.appendChild(row)
    })

    // Add event listeners to action buttons
    document.querySelectorAll(".edit-user-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        const userId = this.getAttribute("data-user-id")
        openEditUserModal(userId)
      })
    })

    document.querySelectorAll(".delete-user-btn").forEach((btn) => {
      btn.addEventListener("click", function () {
        const userId = this.getAttribute("data-user-id")
        deleteUser(userId)
      })
    })
  }

  // Open edit user modal with user data
  function openEditUserModal(userId) {
    fetch(`get_user.php?id=${userId}`)
      .then(handleResponse)
      .then((user) => {
        if (user.error) {
          throw new Error(user.message)
        }

        currentEditingUser = user
        document.getElementById("editUserId").value = user.id
        document.getElementById("editUsername").value = user.username
        document.getElementById("editEmail").value = user.email
        document.getElementById("editRole").value = user.role

        // Reset any previous messages
        const responseElement = document.getElementById("editUserResponseMessage")
        if (responseElement) {
          responseElement.textContent = ""
          responseElement.className = ""
          responseElement.style.display = "none"
        }

        elements.modals.editUser.classList.add("active")
      })
      .catch((error) => {
        console.error("Error fetching user:", error)
        showErrorMessage("editUserResponseMessage", "Failed to load user details")
      })
  }

  // Update pagination controls
  function updateUserPagination(pagination) {
    currentUserPage = pagination.page
    totalUserPages = Math.ceil(pagination.total / pagination.limit)

    if (elements.displays.pageInfo) {
      elements.displays.pageInfo.textContent = `Page ${currentUserPage} of ${totalUserPages}`
    }

    if (elements.buttons.prevPage) {
      elements.buttons.prevPage.disabled = currentUserPage <= 1
    }

    if (elements.buttons.nextPage) {
      elements.buttons.nextPage.disabled = currentUserPage >= totalUserPages
    }
  }

  // Initialize event listeners
  function initEventListeners() {
    // Pagination
    if (elements.buttons.prevPage) {
      elements.buttons.prevPage.addEventListener("click", () => {
        if (currentUserPage > 1) {
          currentUserPage--
          fetchUsers()
        }
      })
    }

    if (elements.buttons.nextPage) {
      elements.buttons.nextPage.addEventListener("click", () => {
        if (currentUserPage < totalUserPages) {
          currentUserPage++
          fetchUsers()
        }
      })
    }

    // Search
    if (elements.displays.searchInput) {
      elements.displays.searchInput.addEventListener("input", () => {
        currentUserPage = 1
        fetchUsers()
      })
    }
  }

  // Initialize everything
  function init() {
    initModals()
    initForms()
    initEventListeners()
    fetchUsers()
  }

  // Start the application
  init()
})
