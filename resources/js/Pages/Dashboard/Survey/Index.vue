<template>
  <DashboardLayout 
    :pageTitle="'Survey Management'"
  >
    <div class="space-y-6">
      <!-- Header Section -->
      <PageHeader 
        title="Survey Management" 
        description="Manage surveys and their configurations"
      >
        <template #action>
          <button class="btn btn-sm btn-primary gap-2" @click="openAddSurveyDrawer">
            <Plus :size="15" />
            Add New Survey
          </button>
        </template>
      </PageHeader>

      <!-- Filter Section -->
      <FilterSearch
        :search-query="searchQuery"
        search-placeholder="Search surveys..."
        :filters="filterOptions"
        @update:search-query="searchQuery = $event"
        @update:filter="updateFilter"
      />

      <!-- Loading State -->
      <div v-if="isLoading" class="flex justify-center items-center py-12">
        <span class="loading loading-spinner loading-sm"></span>
        <span class="ml-3 text-sm text-base-content/70">Loading surveys...</span>
      </div>

      <!-- Dynamic Data Table with Pagination -->
      <DataTable 
        v-else
        :data="filteredSurveys"
        :columns="tableColumns"
        :actions="tableActions"
        :items-per-page="itemsPerPage"
        :selected-items="selectedSurveys"
        @edit-survey="editSurvey"
        @delete-survey="deleteSurvey"
        @update:selected-items="selectedSurveys = $event"
        @update:current-page="currentPage = $event"
      />
    </div>

    <!-- Add Survey Drawer -->
    <SurveyDrawer 
      :is-open="isAddSurveyDrawerOpen"
      title="Add New Survey"
      @close="closeAddSurveyDrawer"
      @submit="handleCreateSurvey"
    />

    <!-- Edit Survey Drawer -->
    <SurveyDrawer 
      :is-open="isEditSurveyDrawerOpen"
      :is-edit-mode="true"
      :survey-data="editingSurvey"
      title="Edit Survey"
      @close="closeEditSurveyDrawer"
      @submit="handleUpdateSurvey"
    />
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, inject } from 'vue'
import axios from 'axios'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import FilterSearch from '@/Components/FilterSearch.vue'
import DataTable from '@/Components/DataTable.vue'
import SurveyDrawer from '@/Components/SurveyDrawer.vue'
import { 
  Plus, 
  Search,
  Eye,
  Edit,
  Trash2
} from 'lucide-vue-next'

// Breadcrumb will auto-generate from URL

// Helper functions
const getStatusBadgeClass = (status) => {
  const classes = {
    draft: 'badge badge-ghost',
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

// Table configuration
const tableColumns = ref([
  {
    key: 'title',
    label: 'Survey',
    type: 'text',
    formatter: (value, item) => `${item.title} (${item.code})`
  },
  {
    key: 'description',
    label: 'Description',
    type: 'text',
    formatter: (value) => value?.length > 50 ? value.substring(0, 50) + '...' : value
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
    name: 'view',
    event: 'view-survey',
    icon: Eye,
    label: 'View',
    tooltip: 'View Survey',
    class: 'text-info',
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

const createSurvey = async (surveyData) => {
  try {
    isLoading.value = true
    
    const response = await axios.post('/api/surveys', {
      title: surveyData.title,
      description: surveyData.description,
      status: surveyData.status || 'draft',
      visibility: surveyData.visibility || 'private',
      start_date: surveyData.start_date,
      end_date: surveyData.end_date
    })
    
    if (response.data.success) {
      await fetchSurveys() // Refresh the list
      showToast('Survey created successfully!', 'success')
      return response.data.data
    } else {
      throw new Error(response.data.message || 'Failed to create survey')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to create survey'
    showToast(errorMessage, 'error')
    console.error('Error creating survey:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

const updateSurvey = async (surveyData) => {
  try {
    isLoading.value = true
    
    const updateData = {
      title: surveyData.title,
      description: surveyData.description,
      status: surveyData.status,
      visibility: surveyData.visibility,
      start_date: surveyData.start_date,
      end_date: surveyData.end_date
    }
    
    const response = await axios.put(`/api/surveys/${surveyData.id}`, updateData)
    
    if (response.data.success) {
      await fetchSurveys() // Refresh the list
      showToast('Survey updated successfully!', 'success')
      return response.data.data
    } else {
      throw new Error(response.data.message || 'Failed to update survey')
    }
  } catch (err) {
    const errorMessage = err.response?.data?.message || err.message || 'Failed to update survey'
    showToast(errorMessage, 'error')
    console.error('Error updating survey:', err)
    throw err
  } finally {
    isLoading.value = false
  }
}

const removeSurvey = async (surveyId) => {
  try {
    isLoading.value = true
    
    const response = await axios.delete(`/api/surveys/${surveyId}`)
    
    if (response.data.success) {
      await fetchSurveys() // Refresh the list
      showToast('Survey deleted successfully!', 'success')
      return true
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

const viewSurvey = (survey) => {
  console.log('View survey:', survey)
  // Navigate to survey detail page or open modal
  // router.push(`/dashboard/surveys/${survey.id}`)
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

const handleCreateSurvey = async (surveyData) => {
  try {
    await createSurvey(surveyData)
    closeAddSurveyDrawer()
    console.log('Survey created successfully')
  } catch (err) {
    console.error('Failed to create survey:', err)
  }
}

const handleUpdateSurvey = async (surveyData) => {
  try {
    await updateSurvey(surveyData)
    closeEditSurveyDrawer()
    console.log('Survey updated successfully')
  } catch (err) {
    console.error('Failed to update survey:', err)
  }
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