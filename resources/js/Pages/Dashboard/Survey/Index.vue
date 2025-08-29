<template>
  <DashboardLayout :pageTitle="'Survey Management'">
    <div class="space-y-6">
      <!-- Header Section -->
      <PageHeader title="Survey Management" description="Manage surveys and their configurations">
        <template #action>
          <button class="gap-2 btn btn-sm btn-primary" @click="openAddSurveyDrawer">
            <Plus :size="15" />
            Add New Survey
          </button>
        </template>
      </PageHeader>

      <!-- Filter Section -->
      <FilterSearch :search-query="searchQuery" search-placeholder="Search surveys..." :filters="filterOptions"
        @update:search-query="searchQuery = $event" @update:filter="updateFilter" />

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <span class="loading loading-spinner loading-sm"></span>
        <span class="ml-3 text-sm text-base-content/70">Loading surveys...</span>
      </div>

      <!-- Dynamic Data Table with Pagination -->
      <DataTable v-else :data="filteredSurveys" :columns="tableColumns" :actions="tableActions"
        :items-per-page="itemsPerPage" :selected-items="selectedSurveys" @manage-survey="manageSurvey"
        @view-responses="viewResponses" @view-survey="viewSurvey" @edit-survey="editSurvey"
        @delete-survey="deleteSurvey" @update:selected-items="selectedSurveys = $event"
        @update:current-page="currentPage = $event" @copy-success="handleCopySuccess" @copy-error="handleCopyError" />
    </div>

    <!-- Add Survey Drawer -->
    <SurveyDrawer :is-open="isAddSurveyDrawerOpen" title="Add New Survey" @close="closeAddSurveyDrawer"
      @success="handleSurveySuccess" />

    <!-- Edit Survey Drawer -->
    <SurveyDrawer :is-open="isEditSurveyDrawerOpen" :is-edit-mode="true" :survey-data="editingSurvey"
      title="Edit Survey" @close="closeEditSurveyDrawer" @success="handleSurveySuccess" />
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import FilterSearch from '@/Components/FilterSearch.vue'
import DataTable from '@/Components/DataTable.vue'
import SurveyDrawer from '@/Components/SurveyDrawer.vue'
import {
  Plus,
  Eye,
  Edit,
  Trash2,
  ListOrdered,
  FileText,
} from 'lucide-vue-next'

// Breadcrumb will auto-generate from URL

// Helper functions
const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'badge badge-neutral badge-outline',
    active: 'badge badge-success',
    closed: 'badge badge-error'
  }
  return classes[status] || 'badge badge-ghost'
}

const getVisibilityBadgeClass = (visibility) => {
  const classes = {
    private: 'badge badge-warning',
    link: 'badge badge-info',
    public: 'badge badge-success'
  }
  return classes[visibility] || 'badge badge-ghost'
}

// Reactive data
const searchQuery = ref('')
const selectedStatus = ref('')
const selectedVisibility = ref('')
const selectedSurveys = ref([])
const currentPage = ref(1)
const itemsPerPage = ref(10)
const isAddSurveyDrawerOpen = ref(false)
const isEditSurveyDrawerOpen = ref(false)
const editingSurvey = ref(null)
const isLoading = ref(false)
const surveys = ref([])

// Inject toast function from layout
const showToast = inject('showToast')

// Copy event handlers
const handleCopySuccess = (text) => {
  showToast(`Code "${text}" copied to clipboard!`, 'success')
}

const handleCopyError = (text) => {
  showToast('Failed to copy code', 'error')
}

// Table configuration
const tableColumns = ref([
  {
    key: 'title',
    label: 'Survey Title',
    type: 'text',
    formatter: (value) => value
  },
  {
    key: 'description',
    label: 'Description',
    type: 'text',
    formatter: (value) => value ? (value.length > 35 ? value.substring(0, 35) + '...' : value) : '-'
  },
  {
    key: 'code',
    label: 'Code',
    type: 'copy',
    formatter: (value) => value
  },
  {
    key: 'status',
    label: 'Status',
    type: 'badge',
    formatter: (value) => value,
    class: getStatusBadgeClass
  },
  {
    key: 'visibility',
    label: 'Visibility',
    type: 'badge',
    formatter: (value) => value,
    class: getVisibilityBadgeClass
  },
  {
    key: 'owner',
    label: 'Owner',
    type: 'text',
    formatter: (value, item) => item.owner?.name || 'Unknown'
  },
  {
    key: 'created_at',
    label: 'Created',
    type: 'date'
  }
])

const tableActions = ref([
  {
    name: 'manage',
    event: 'manage-survey',
    icon: ListOrdered,
    label: 'Manage',
    tooltip: 'Manage Survey',
    class: '',
    visible: true
  },
  {
    name: 'response',
    event: 'view-responses',
    icon: FileText,
    label: 'Response',
    tooltip: 'View Responses',
    class: '',
    visible: true
  },
  {
    name: 'view',
    event: 'view-survey',
    icon: Eye,
    label: 'View',
    tooltip: 'View Survey',
    class: '',
    visible: true
  },
  {
    name: 'edit',
    event: 'edit-survey',
    icon: Edit,
    label: 'Edit',
    tooltip: 'Edit Survey',
    class: '',
    visible: true
  },
  {
    name: 'delete',
    event: 'delete-survey',
    icon: Trash2,
    label: 'Delete',
    tooltip: 'Delete Survey',
    class: 'text-error',
    visible: true
  }
])

const filterOptions = ref([
  {
    key: 'status',
    value: '',
    placeholder: 'All Status',
    options: [
      { value: 'draft', label: 'Draft' },
      { value: 'active', label: 'Active' },
      { value: 'closed', label: 'Closed' }
    ]
  },
  {
    key: 'visibility',
    value: '',
    placeholder: 'All Visibility',
    options: [
      { value: 'private', label: 'Private' },
      { value: 'link', label: 'Link Only' },
      { value: 'public', label: 'Public' }
    ]
  }
])

// API Functions
const fetchSurveys = async () => {
  try {
    isLoading.value = true
    const response = await axios.get('/api/surveys')

    if (response.data.success) {
      surveys.value = response.data.data
    } else {
      throw new Error(response.data.message || 'Failed to fetch surveys')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to fetch surveys'
    showToast(errorMessage, 'error')
    console.error('Error fetching surveys:', err)
  } finally {
    isLoading.value = false
  }
}



const removeSurvey = async (surveyId) => {
  try {
    isLoading.value = true

    const response = await axios.delete(`/api/surveys/${surveyId}`)

    if (response.data.success) {
      surveys.value = surveys.value.filter(s => s.id !== surveyId)
      showToast('Survey deleted successfully', 'success')
    } else {
      throw new Error(response.data.message || 'Failed to delete survey')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to delete survey'
    showToast(errorMessage, 'error')
    console.error('Error deleting survey:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

// Computed properties
const filteredSurveys = computed(() => {
  let filtered = surveys.value

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(survey =>
      survey.title.toLowerCase().includes(query) ||
      survey.description?.toLowerCase().includes(query) ||
      survey.code?.toLowerCase().includes(query)
    )
  }

  // Status filter
  if (selectedStatus.value) {
    filtered = filtered.filter(survey => survey.status === selectedStatus.value)
  }

  // Visibility filter
  if (selectedVisibility.value) {
    filtered = filtered.filter(survey => survey.visibility === selectedVisibility.value)
  }

  return filtered
})

const manageSurvey = (survey) => {
  console.log('Manage survey:', survey)
  // Navigate to survey manage page
  router.visit(`/dashboard/survey/${survey.id}/manage`)
}

const viewResponses = (survey) => {
  console.log('View responses:', survey)
  // Navigate to survey responses page
  router.visit(`/dashboard/survey/${survey.id}/response`)
}

const viewSurvey = (survey) => {
  console.log('View survey:', survey)
  // Navigate to survey detail page
  router.visit(`/dashboard/survey/${survey.id}`)
}

const editSurvey = (survey) => {
  console.log('Edit survey:', survey)
  editingSurvey.value = survey
  isEditSurveyDrawerOpen.value = true
}

const deleteSurvey = async (survey) => {
  if (confirm(`Are you sure you want to delete survey "${survey.title}"?`)) {
    try {
      await removeSurvey(survey.id)
      console.log('Survey deleted successfully')
    } catch (err) {
      console.error('Failed to delete survey:', err)
    }
  }
}

const openAddSurveyDrawer = () => {
  isAddSurveyDrawerOpen.value = true
}

const closeAddSurveyDrawer = () => {
  isAddSurveyDrawerOpen.value = false
}

const openEditSurveyDrawer = (survey) => {
  editingSurvey.value = survey
  isEditSurveyDrawerOpen.value = true
}

const closeEditSurveyDrawer = () => {
  isEditSurveyDrawerOpen.value = false
  editingSurvey.value = null
}

const handleSurveySuccess = async (surveyData) => {
  // Refresh the surveys list
  await fetchSurveys()

  // Close any open drawers
  closeAddSurveyDrawer()
  closeEditSurveyDrawer()
}

const updateFilter = ({ key, value }) => {
  if (key === 'status') {
    selectedStatus.value = value
  } else if (key === 'visibility') {
    selectedVisibility.value = value
  }

  // Update the filter options to reflect current values
  const filterOption = filterOptions.value.find(f => f.key === key)
  if (filterOption) {
    filterOption.value = value
  }
}

// Lifecycle
onMounted(() => {
  fetchSurveys()
})
</script>