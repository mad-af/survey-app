<template>
  <DashboardLayout 
    :pageTitle="'User Management'"
  >
    <div class="space-y-6">
      <!-- Header Section -->
      <PageHeader 
        title="User Management" 
        description="Manage system users and their permissions"
      >
        <template #action>
          <button class="btn btn-sm btn-primary gap-2" @click="openAddUserDrawer">
            <UserPlus :size="15" />
            Add New User
          </button>
        </template>
      </PageHeader>

      <!-- Filter Section -->
      <FilterSearch
        :search-query="searchQuery"
        search-placeholder="Search users..."
        :filters="filterOptions"
        @update:search-query="searchQuery = $event"
        @update:filter="updateFilter"
      />

      <!-- Dynamic Data Table with Pagination -->
      <DataTable 
        :data="filteredUsers"
        :columns="tableColumns"
        :actions="tableActions"
        :items-per-page="itemsPerPage"
        :selected-items="selectedUsers"
        @edit-user="editUser"
        @delete-user="deleteUser"
        @update:selected-items="selectedUsers = $event"
        @update:current-page="currentPage = $event"
      />
    </div>

    <!-- Add User Drawer -->
    <UserDrawer 
      :is-open="isAddUserDrawerOpen"
      title="Add New User"
      @close="closeAddUserDrawer"
      @submit="handleCreateUser"
    />

    <!-- Edit User Drawer -->
    <UserDrawer 
      :is-open="isEditUserDrawerOpen"
      :is-edit-mode="true"
      :user-data="editingUser"
      title="Edit User"
      @close="closeEditUserDrawer"
      @submit="handleUpdateUser"
    />
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import FilterSearch from '@/Components/FilterSearch.vue'
import DataTable from '@/Components/DataTable.vue'
import UserDrawer from '@/Components/UserDrawer.vue'
import { 
  Users, 
  UserPlus, 
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
    moderator: 'badge badge-warning',
    user: 'badge badge-info'
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
    key: 'status',
    label: 'Status',
    type: 'badge',
    formatter: (value) => value,
    class: getStatusBadgeClass
  },
  {
    key: 'last_login',
    label: 'Last Login',
    type: 'date'
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

// Mock data - replace with actual API calls
const users = ref([
  {
    id: 1,
    name: 'John Doe',
    email: 'john@example.com',
    role: 'admin',
    status: 'active',
    avatar: null,
    last_login: '2024-01-15T10:30:00Z',
    created_at: '2024-01-01T00:00:00Z'
  },
  {
    id: 2,
    name: 'Jane Smith',
    email: 'jane@example.com',
    role: 'user',
    status: 'active',
    avatar: null,
    last_login: '2024-01-14T15:45:00Z',
    created_at: '2024-01-02T00:00:00Z'
  },
  {
    id: 3,
    name: 'Bob Johnson',
    email: 'bob@example.com',
    role: 'moderator',
    status: 'inactive',
    avatar: null,
    last_login: '2024-01-10T09:15:00Z',
    created_at: '2024-01-03T00:00:00Z'
  },
  {
    id: 4,
    name: 'Alice Brown',
    email: 'alice@example.com',
    role: 'user',
    status: 'suspended',
    avatar: null,
    last_login: '2024-01-05T14:20:00Z',
    created_at: '2024-01-04T00:00:00Z'
  },
  {
    id: 5,
    name: 'Charlie Wilson',
    email: 'charlie@example.com',
    role: 'user',
    status: 'active',
    avatar: null,
    last_login: '2024-01-13T11:30:00Z',
    created_at: '2024-01-05T00:00:00Z'
  }
])

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
  console.log('Edit user:', user)
  editingUser.value = user
  isEditUserDrawerOpen.value = true
}

const deleteUser = (user) => {
  console.log('Delete user:', user)
  // Implement delete user logic with confirmation
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

const handleCreateUser = (userData) => {
  console.log('Creating user:', userData)
  
  // Generate new user ID
  const newId = Math.max(...users.value.map(u => u.id)) + 1
  
  // Create new user object
  const newUser = {
    id: newId,
    name: userData.name,
    email: userData.email,
    role: userData.role,
    status: 'active',
    avatar: null,
    last_login: null,
    created_at: new Date().toISOString()
  }
  
  // Add to users array
  users.value.push(newUser)
  
  // Close drawer
  closeAddUserDrawer()
  
  // Show success message (you can implement toast notification here)
  console.log('User created successfully:', newUser)
}

const handleUpdateUser = (userData) => {
  console.log('Updating user:', userData)
  
  // Find and update the user
  const userIndex = users.value.findIndex(u => u.id === userData.id)
  if (userIndex !== -1) {
    // Update user data
    users.value[userIndex] = {
      ...users.value[userIndex],
      name: userData.name,
      email: userData.email,
      role: userData.role,
      // Only update password if provided
      ...(userData.password && { password: userData.password })
    }
    
    // Close drawer
    closeEditUserDrawer()
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
  // Load users data
  console.log('User Management page mounted')
})
</script>