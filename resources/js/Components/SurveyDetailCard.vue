<template>
  <div class="card card-sm bg-base-100 shadow-lg">
    <div class="card-body p-4">
      <h2 class="card-title text-lg mb-3 flex items-center gap-2">
        <FileText class="h-5 w-5" />
        Survey Information
      </h2>
      
      <div class="grid grid-cols-2 md:grid-cols-2 gap-3">
        <!-- Title -->
        <div class="space-y-1">
          <div>
            <span class="text-xs font-medium text-base-content/60">Title</span>
          </div>
          <p class="text-sm font-medium truncate" :title="survey?.title || 'Sample Survey Title'">{{ survey?.title || 'Sample Survey Title' }}</p>
        </div>
        
        <!-- Code -->
        <div class="space-y-1">
          <div>
            <span class="text-xs font-medium text-base-content/60">Code</span>
          </div>
          <div class="flex items-center w-fit bg-base-200">
            <button class="btn btn-xs btn-ghost p-1" @click="copyToClipboard(survey?.code || 'SRV-2024-001')">
              <Copy class="h-3 w-3" />
            </button>
            <code class="px-2 py-1 rounded text-xs font-mono truncate">{{ survey?.code || 'SRV-2024-001' }}</code>
          </div>
        </div>
        
        <!-- Status -->
        <div class="space-y-1">
          <div>
            <span class="text-xs font-medium text-base-content/60">Status</span>
          </div>
          <div :class="getStatusBadgeClass(survey?.status)" class="text-xs">
            {{ survey?.status || 'Active' }}
          </div>
        </div>
        
        <!-- Visibility -->
        <div class="space-y-1">
          <div>
            <span class="text-xs font-medium text-base-content/60">Visibility</span>
          </div>
          <div :class="getVisibilityBadgeClass(survey?.visibility)" class="text-xs">{{ survey?.visibility || 'Public' }}</div>
        </div>
        
        <!-- Owner -->
        <div class="space-y-1">
          <div>
            <span class="text-xs font-medium text-base-content/60">Owner</span>
          </div>
          <div class="flex items-center gap-1">
            <span class="text-xs">{{ survey?.owner?.name || 'John Doe' }}</span>
          </div>
        </div>
        
        <!-- Anonymous -->
        <div class="space-y-1">
          <div>
            <span class="text-xs font-medium text-base-content/60">Anonymous</span>
          </div>
          <div class="badge badge-ghost badge-sm">{{ survey?.is_anonymous ? 'Yes' : 'No' }}</div>
        </div>
        
        <!-- Start Date -->
        <div class="space-y-1">
          <div>
            <span class="text-xs font-medium text-base-content/60">Start Date</span>
          </div>
          <p class="text-xs">{{ formatDate(survey?.starts_at) || '15 Jan 2024, 09:00' }}</p>
        </div>
        
        <!-- End Date -->
        <div class="space-y-1">
          <div>
            <span class="text-xs font-medium text-base-content/60">End Date</span>
          </div>
          <p class="text-xs">{{ formatDate(survey?.ends_at) || '31 Jan 2024, 23:59' }}</p>
        </div>
      </div>
      
      <!-- Description -->
      <div class="mt-3 space-y-1">
        <div>
          <span class="text-xs font-medium text-base-content/60">Description</span>
        </div>
        <p class="text-xs bg-base-200 p-2 rounded text-base-content/80">
          {{ survey?.description || 'This is a sample survey description that provides detailed information about the purpose and scope of the survey.' }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
import { 
  FileText, 
  Copy 
} from 'lucide-vue-next'

const props = defineProps({
  survey: {
    type: Object,
    default: null
  }
})

const getStatusBadgeClass = (status) => {
  const baseClass = 'badge'
  switch (status?.toLowerCase()) {
    case 'active':
      return `${baseClass} badge-success`
    case 'draft':
      return `${baseClass} badge-warning`
    case 'closed':
      return `${baseClass} badge-error`
    default:
      return `${baseClass} badge-success`
  }
}

const getVisibilityBadgeClass = (visibility) => {
  const baseClass = 'badge'
  switch (visibility?.toLowerCase()) {
    case 'public':
      return `${baseClass} badge-info`
    case 'private':
      return `${baseClass} badge-secondary`
    case 'restricted':
      return `${baseClass} badge-warning`
    default:
      return `${baseClass} badge-info`
  }
}

const getOwnerInitials = (name) => {
  if (!name) return 'JD'
  return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().slice(0, 2)
}

const formatDate = (dateString) => {
  if (!dateString) return null
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    // You can add a toast notification here
  } catch (err) {
    console.error('Failed to copy text: ', err)
  }
}
</script>