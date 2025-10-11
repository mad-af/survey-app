<template>
  <Head title="User Management" />
  <DashboardLayout :pageTitle="'User Management'">
    <div class="space-y-6">
      <!-- Header Section -->
      <PageHeader title="User Management" description="Manage system users and their permissions">
        <template #action>
          <button class="gap-2 btn btn-sm btn-primary" @click="openAddUserDrawer">
            <Plus :size="15" />
            Add New User
          </button>
        </template>
      </PageHeader>

      <!-- Filter Section -->
      <FilterSearch :search-query="searchQuery" search-placeholder="Search users..." :filters="filterOptions"
        @update:search-query="searchQuery = $event" @update:filter="updateFilter" />

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <span class="loading loading-spinner loading-sm"></span>
        <span class="ml-3 text-sm text-base-content/70">Loading users...</span>
      </div>

      <!-- Dynamic Data Table with Pagination -->
      <DataTable v-else :data="filteredUsers" :columns="tableColumns" :actions="tableActions"
        :items-per-page="itemsPerPage" :selected-items="selectedUsers" @edit-user="editUser" @delete-user="deleteUser"
        @update:selected-items="selectedUsers = $event" @update:current-page="currentPage = $event" />
    </div>

    <!-- Add User Drawer -->
    <UserDrawer :is-open="isAddUserDrawerOpen" title="Add New User" @close="closeAddUserDrawer"
      @submit="handleCreateUser" />

    <!-- Edit User Drawer -->
    <UserDrawer :is-open="isEditUserDrawerOpen" :is-edit-mode="true" :user-data="editingUser" title="Edit User"
      @close="closeEditUserDrawer" @submit="handleUpdateUser" />

    <!-- Delete Confirmation Modal -->
    <ConfirmationModal
      ref="deleteUserModal"
      modal-id="delete_user_modal"
      :title="`Delete User`"
      :message="`Are you sure you want to delete '${userToDelete?.name}'? This action cannot be undone.`"
      confirm-text="Delete"
      cancel-text="Cancel"
      confirm-button-type="error"
      confirm-button-class="btn-error"
      :loading="isLoading"
      @confirm="confirmDeleteUser"
      @cancel="cancelDeleteUser"
    />
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import FilterSearch from '@/Components/FilterSearch.vue'
import DataTable from '@/Components/DataTable.vue'
import UserDrawer from '@/Components/UserDrawer.vue'
import ConfirmationModal from '@/Components/ConfirmationModal.vue'
import {
  Users,
  Plus,
  Search,
  Eye,
  Edit,
  Trash2
} from 'lucide-vue-next'

// Breadcrumb will auto-generate from URL

// Helper functions
const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'badge badge-error',
    surveyor: 'badge badge-info'
  }
  return classes[role] || 'badge badge-ghost'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'badge badge-success',
    inactive: 'badge badge-ghost',
    suspended: 'badge badge-error'
  }
  return classes[status] || 'badge badge-ghost'
}

// Reactive data
const searchQuery = ref('')
const selectedRole = ref('')
const selectedStatus = ref('')
const selectedUsers = ref([])
const currentPage = ref(1)
const itemsPerPage = ref(10)
const isAddUserDrawerOpen = ref(false)
const isEditUserDrawerOpen = ref(false)
const editingUser = ref(null)
const isLoading = ref(false)
const users = ref([])
const deleteUserModal = ref(null)
const userToDelete = ref(null)

// Inject toast function from layout
const showToast = inject('showToast', () => { })

// Table configuration
const tableColumns = ref([
  {
    key: 'name',
    label: 'User',
    type: 'user',
    formatter: (value, item) => `${item.name} (${item.email})`
  },
  {
    key: 'role',
    label: 'Role',
    type: 'badge',
    formatter: (value) => value,
    class: getRoleBadgeClass
  },
  {
    key: 'created_at',
    label: 'Created',
    type: 'date'
  }
])

const tableActions = ref([
  {
    name: 'edit',
    event: 'edit-user',
    icon: Edit,
    label: 'Edit',
    tooltip: 'Edit User',
    class: '',
    visible: true
  },
  {
    name: 'delete',
    event: 'delete-user',
    icon: Trash2,
    label: 'Delete',
    tooltip: 'Delete User',
    class: 'text-error',
    visible: true
  }
])

const filterOptions = ref([
  {
    key: 'role',
    value: '',
    placeholder: 'All Roles',
    options: [
      { value: 'admin', label: 'Admin' },
      { value: 'surveyor', label: 'Surveyor' }
    ]
  }
])

// API Functions
const fetchUsers = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/api/users')

    if (response.data.success) {
      users.value = response.data.data
    } else {
      throw new Error(response.data.message || 'Failed to fetch users')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to fetch users'
    showToast(errorMessage, 'error')
    console.error('Error fetching users:', err)
  } finally {
    isLoading.value = false
  }
}

const createUser = async (userData) => {
  try {
    isLoading.value = true

    const response = await axios.post('/api/users', {
      name: userData.name,
      email: userData.email,
      role: userData.role
    })

    if (response.data.success) {
      await fetchUsers() // Refresh the list
      showToast('User created successfully!', 'success')
      return response.data.data
    } else {
      throw new Error(response.data.message || 'Failed to create user')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to create user'
    showToast(errorMessage, 'error')
    console.error('Error creating user:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

const updateUser = async (userData) => {
  try {
    isLoading.value = true

    const updateData = {
      name: userData.name,
      email: userData.email,
      role: userData.role
    }

    const response = await axios.put(`/api/users/${userData.id}`, updateData)

    if (response.data.success) {
      await fetchUsers() // Refresh the list
      showToast('User updated successfully!', 'success')
      return response.data.data
    } else {
      throw new Error(response.data.message || 'Failed to update user')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to update user'
    showToast(errorMessage, 'error')
    console.error('Error updating user:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

const removeUser = async (userId) => {
  try {
    isLoading.value = true

    const response = await axios.delete(`/api/users/${userId}`)

    if (response.data.success) {
      await fetchUsers() // Refresh the list
      showToast('User deleted successfully!', 'success')
      return true
    } else {
      throw new Error(response.data.message || 'Failed to delete user')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to delete user'
    showToast(errorMessage, 'error')
    console.error('Error deleting user:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

// Computed properties
const filteredUsers = computed(() => {
  let filtered = users.value

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(user =>
      user.name.toLowerCase().includes(query) ||
      user.email.toLowerCase().includes(query)
    )
  }

  // Role filter
  if (selectedRole.value) {
    filtered = filtered.filter(user => user.role === selectedRole.value)
  }

  // Status filter
  if (selectedStatus.value) {
    filtered = filtered.filter(user => user.status === selectedStatus.value)
  }

  return filtered
})

const editUser = (user) => {
  // console.log('Edit user:', user)
  editingUser.value = user
  isEditUserDrawerOpen.value = true
}

const deleteUser = (user) => {
  userToDelete.value = user
  // Open modal via ref for consistency with other pages
  deleteUserModal.value?.openModal()
}

const confirmDeleteUser = async () => {
  if (!userToDelete.value) return

  try {
    await removeUser(userToDelete.value.id)
    showToast('User deleted successfully!', 'success')
    // Success toast already handled inside removeUser
  } catch (err) {
    // Error toast already handled inside removeUser
    console.error('Failed to delete user:', err)
    showToast('Failed to delete user!', 'error')
  } finally {
    userToDelete.value = null
  }
}

const cancelDeleteUser = () => {
  userToDelete.value = null
}

const openAddUserDrawer = () => {
  isAddUserDrawerOpen.value = true
}

const closeAddUserDrawer = () => {
  isAddUserDrawerOpen.value = false
}

const openEditUserDrawer = (user) => {
  editingUser.value = user
  isEditUserDrawerOpen.value = true
}

const closeEditUserDrawer = () => {
  isEditUserDrawerOpen.value = false
  editingUser.value = null
}

const handleCreateUser = async (userData) => {
  try {
    await createUser(userData)
    closeAddUserDrawer()
    // console.log('User created successfully')
  } catch (err) {
    console.error('Failed to create user:', err)
  }
}

const handleUpdateUser = async (userData) => {
  try {
    await updateUser(userData)
    closeEditUserDrawer()
    // console.log('User updated successfully')
  } catch (err) {
    console.error('Failed to update user:', err)
  }
}

const updateFilter = ({ key, value }) => {
  if (key === 'role') {
    selectedRole.value = value
  } else if (key === 'status') {
    selectedStatus.value = value
  }

  // Update the filter options to reflect current values
  const filterOption = filterOptions.value.find(f => f.key === key)
  if (filterOption) {
    filterOption.value = value
  }
}

// Lifecycle
onMounted(() => {
  fetchUsers()
})
</script>