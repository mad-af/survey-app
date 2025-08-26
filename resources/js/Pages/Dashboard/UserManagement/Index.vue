<template>
  <DashboardLayout 
    :pageTitle="'User Management'"
    :breadcrumbItems="breadcrumbItems"
  >
    <div class="space-y-6">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold text-base-content">User Management</h1>
          <p class="text-base-content/60 mt-1">Manage system users and their permissions</p>
        </div>
        <button class="btn btn-primary gap-2">
          <UserPlus :size="20" />
          Add New User
        </button>
      </div>

      <!-- Filters and Search -->
      <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
          <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="form-control flex-1">
              <div class="input-group">
                <input 
                  type="text" 
                  placeholder="Search users..." 
                  class="input input-bordered flex-1"
                  v-model="searchQuery"
                />
                <button class="btn btn-square btn-outline">
                  <Search :size="20" />
                </button>
              </div>
            </div>
            
            <!-- Role Filter -->
            <div class="form-control w-full lg:w-48">
              <select class="select select-bordered" v-model="selectedRole">
                <option value="">All Roles</option>
                <option value="admin">Admin</option>
                <option value="user">User</option>
                <option value="moderator">Moderator</option>
              </select>
            </div>
            
            <!-- Status Filter -->
            <div class="form-control w-full lg:w-48">
              <select class="select select-bordered" v-model="selectedStatus">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
              </select>
            </div>
          </div>
        </div>
      </div>

      <!-- Users Table -->
      <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-0">
          <div class="overflow-x-auto">
            <table class="table table-zebra">
              <thead>
                <tr>
                  <th>
                    <label>
                      <input type="checkbox" class="checkbox" v-model="selectAll" @change="toggleSelectAll" />
                    </label>
                  </th>
                  <th>User</th>
                  <th>Role</th>
                  <th>Status</th>
                  <th>Last Login</th>
                  <th>Created</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="user in filteredUsers" :key="user.id">
                  <th>
                    <label>
                      <input type="checkbox" class="checkbox" v-model="selectedUsers" :value="user.id" />
                    </label>
                  </th>
                  <td>
                    <div class="flex items-center gap-3">
                      <div class="avatar">
                        <div class="mask mask-squircle w-12 h-12">
                          <img :src="user.avatar || '/api/placeholder/48/48'" :alt="user.name" />
                        </div>
                      </div>
                      <div>
                        <div class="font-bold">{{ user.name }}</div>
                        <div class="text-sm opacity-50">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="badge" :class="getRoleBadgeClass(user.role)">{{ user.role }}</div>
                  </td>
                  <td>
                    <div class="badge" :class="getStatusBadgeClass(user.status)">{{ user.status }}</div>
                  </td>
                  <td>
                    <span class="text-sm">{{ formatDate(user.last_login) }}</span>
                  </td>
                  <td>
                    <span class="text-sm">{{ formatDate(user.created_at) }}</span>
                  </td>
                  <td>
                    <div class="flex gap-2">
                      <button class="btn btn-ghost btn-xs" @click="viewUser(user)">
                        <Eye :size="16" />
                      </button>
                      <button class="btn btn-ghost btn-xs" @click="editUser(user)">
                        <Edit :size="16" />
                      </button>
                      <button class="btn btn-ghost btn-xs text-error" @click="deleteUser(user)">
                        <Trash2 :size="16" />
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex justify-between items-center">
        <div class="text-sm text-base-content/60">
          Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, totalUsers) }} of {{ totalUsers }} users
        </div>
        <div class="btn-group">
          <button class="btn btn-sm" :disabled="currentPage === 1" @click="currentPage--">
            <ChevronLeft :size="16" />
          </button>
          <button 
            v-for="page in visiblePages" 
            :key="page"
            class="btn btn-sm"
            :class="{ 'btn-active': page === currentPage }"
            @click="currentPage = page"
          >
            {{ page }}
          </button>
          <button class="btn btn-sm" :disabled="currentPage === totalPages" @click="currentPage++">
            <ChevronRight :size="16" />
          </button>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { 
  Users, 
  UserPlus, 
  Search, 
  Eye, 
  Edit, 
  Trash2, 
  ChevronLeft, 
  ChevronRight 
} from 'lucide-vue-next'

// Breadcrumb items
const breadcrumbItems = [
  { label: 'Dashboard', href: '/dashboard' },
  { label: 'User Management' }
]

// Reactive data
const searchQuery = ref('')
const selectedRole = ref('')
const selectedStatus = ref('')
const selectAll = ref(false)
const selectedUsers = ref([])
const currentPage = ref(1)
const itemsPerPage = ref(10)

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

const totalUsers = computed(() => filteredUsers.value.length)
const totalPages = computed(() => Math.ceil(totalUsers.value / itemsPerPage.value))

const visiblePages = computed(() => {
  const pages = []
  const start = Math.max(1, currentPage.value - 2)
  const end = Math.min(totalPages.value, currentPage.value + 2)
  
  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  
  return pages
})

// Methods
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedUsers.value = filteredUsers.value.map(user => user.id)
  } else {
    selectedUsers.value = []
  }
}

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'badge-error',
    moderator: 'badge-warning',
    user: 'badge-info'
  }
  return classes[role] || 'badge-ghost'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'badge-success',
    inactive: 'badge-ghost',
    suspended: 'badge-error'
  }
  return classes[status] || 'badge-ghost'
}

const formatDate = (dateString) => {
  if (!dateString) return 'Never'
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const viewUser = (user) => {
  console.log('View user:', user)
  // Implement view user logic
}

const editUser = (user) => {
  console.log('Edit user:', user)
  // Implement edit user logic
}

const deleteUser = (user) => {
  console.log('Delete user:', user)
  // Implement delete user logic with confirmation
}

// Lifecycle
onMounted(() => {
  // Load users data
  console.log('User Management page mounted')
})
</script>