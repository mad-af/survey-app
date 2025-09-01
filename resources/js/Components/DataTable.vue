<template>
  <div class="space-y-4">
    <!-- Dynamic Data Table -->
    <div class="mb-2 shadow-sm card bg-base-100">
      <div class="p-0 card-body">
        <div class="overflow-x-auto">
          <table class="table table-zebra table-compact">
            <thead>
              <tr>
                <th v-for="column in columns" :key="column.key" class="text-xs font-medium">{{ column.label }}</th>
                <th v-if="actions.length > 0" class="text-xs font-medium">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="item in paginatedData" :key="getItemId(item)">
                <td v-for="column in columns" :key="column.key">
                  <!-- User type with avatar -->
                  <div v-if="column.type === 'user'" class="flex gap-2 items-center">
                    <Avatar :name="item.name" />
                    <div>
                      <div class="text-sm font-medium">{{ item.name }}</div>
                      <div class="text-xs opacity-50">{{ item.email }}</div>
                    </div>
                  </div>
                  <!-- Badge type -->
                  <div v-else-if="column.type === 'badge'" :class="`badge badge-sm ${getCellClass(item, column)}`">
                    {{ formatCellValue(item, column) }}
                  </div>
                  <!-- Date type -->
                  <span v-else-if="column.type === 'date'" class="text-xs">
                    {{ formatCellValue(item, column) }}
                  </span>
                  <!-- Copy type with copy button -->
                  <div v-else-if="column.type === 'copy'" class="flex items-center bg-base-200">
                    <button 
                      class="p-1 h-6 btn btn-ghost btn-xs min-h-6" 
                      @click="copyToClipboard(formatCellValue(item, column))"
                      :title="'Copy ' + formatCellValue(item, column)"
                    >
                      <Copy :size="12" />
                    </button>
                    <span class="px-2 py-1 font-mono text-xs rounded">
                      {{ formatCellValue(item, column) }}
                    </span>
                  </div>
                  <!-- Default type -->
                  <component 
                    v-else
                    :is="column.component || 'span'"
                    v-bind="column.props || {}"
                    :class="getCellClass(item, column)"
                  >
                    {{ formatCellValue(item, column) }}
                  </component>
                </td>
                <td v-if="actions.length > 0">
                  <!-- Single action - display directly -->
                  <button 
                    v-if="actions.length === 1"
                    class="p-1 h-6 btn btn-ghost btn-xs min-h-6" 
                    @click="$emit(actions[0].event, item)"
                    :class="actions[0].class || ''"
                    :title="actions[0].label"
                  >
                    <component :is="actions[0].icon" :size="14" v-if="actions[0].icon" />
                  </button>
                  
                  <!-- Multiple actions - use dropdown -->
                  <template v-else>
                    <button 
                      class="p-1 h-6 btn btn-ghost btn-xs min-h-6" 
                      :popovertarget="`popover-${getItemId(item)}`" 
                      :style="`anchor-name: --anchor-${getItemId(item)}`"
                    >
                      <EllipsisVertical :size="14" />
                    </button>
                    <ul 
                      class="dropdown dropdown-left dropdown-center menu w-32 rounded-box bg-base-100 shadow-sm p-2 z-[1]" 
                      popover 
                      :id="`popover-${getItemId(item)}`" 
                      :style="`position-anchor: --anchor-${getItemId(item)}`"
                    >
                      <li v-for="action in actions" :key="action.name" v-show="action.visible !== false">
                        <a @click="$emit(action.event, item); $event.target.closest('[popover]').hidePopover()" :class="`text-xs flex items-center gap-2 ${action.class || ''}`">
                          <component :is="action.icon" :size="12" v-if="action.icon" />
                          {{ action.label }}
                        </a>
                      </li>
                    </ul>
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="flex justify-between items-center">
      <div class="text-xs text-base-content/60">
        Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, totalItems) }} of {{ totalItems }} items
      </div>
      <div class="btn-group">
        <button class="btn btn-xs" :disabled="currentPage === 1" @click="goToPage(currentPage - 1)">
          <ChevronLeft :size="14" />
        </button>
        <button 
          v-for="page in visiblePages" 
          :key="page"
          class="btn btn-xs"
          :class="{ 'btn-active': page === currentPage }"
          @click="goToPage(page)"
        >
          {{ page }}
        </button>
        <button class="btn btn-xs" :disabled="currentPage === totalPages" @click="goToPage(currentPage + 1)">
          <ChevronRight :size="14" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { ChevronLeft, ChevronRight, EllipsisVertical, Copy } from 'lucide-vue-next'
import Avatar from './Avatar.vue'

// Props
const props = defineProps({
  data: {
    type: Array,
    required: true,
    default: () => []
  },
  columns: {
    type: Array,
    required: true,
    default: () => []
  },
  actions: {
    type: Array,
    default: () => []
  },
  itemsPerPage: {
    type: Number,
    default: 10
  },
  selectedItems: {
    type: Array,
    default: () => []
  },
  showCheckbox: {
    type: Boolean,
    default: true
  },
  idField: {
    type: String,
    default: 'id'
  }
})

// Emits - Dynamic events based on actions
const emit = defineEmits([
  'update:selectedItems',
  'update:currentPage',
  'copy-success',
  'copy-error',
  // Action events
  'manage-survey',
  'view-responses',
  'view-survey',
  'edit-survey',
  'delete-survey',
  'manage-user',
  'edit-user',
  'delete-user'
])

// Reactive data
const currentPage = ref(1)
const selectAll = ref(false)
const selectedItems = ref([...props.selectedItems])

// Computed properties
const totalItems = computed(() => props.data.length)
const totalPages = computed(() => Math.ceil(totalItems.value / props.itemsPerPage))

const paginatedData = computed(() => {
  const start = (currentPage.value - 1) * props.itemsPerPage
  const end = start + props.itemsPerPage
  return props.data.slice(start, end)
})

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
    selectedItems.value = paginatedData.value.map(item => getItemId(item))
  } else {
    selectedItems.value = []
  }
  emit('update:selectedItems', selectedItems.value)
}

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    emit('update:currentPage', page)
  }
}

const getItemId = (item) => {
  return item[props.idField]
}

const formatCellValue = (item, column) => {
  const value = getNestedValue(item, column.key)
  
  if (column.formatter && typeof column.formatter === 'function') {
    return column.formatter(value, item)
  }
  
  if (column.type === 'date') {
    return formatDate(value)
  }
  
  if (column.type === 'badge') {
    return value
  }
  
  return value || '-'
}

const getNestedValue = (obj, path) => {
  return path.split('.').reduce((current, key) => current?.[key], obj)
}

const getCellClass = (item, column) => {
  if (typeof column.class === 'function') {
    return column.class(getNestedValue(item, column.key), item)
  }
  return column.class || ''
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

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    emit('copy-success', text)
  } catch (err) {
    console.error('Failed to copy: ', err)
    emit('copy-error', text)
  }
}

// Watchers
watch(() => props.selectedItems, (newVal) => {
  selectedItems.value = [...newVal]
}, { deep: true })

watch(selectedItems, (newVal) => {
  emit('update:selectedItems', newVal)
}, { deep: true })

// Update selectAll state based on selected items
watch([selectedItems, paginatedData], () => {
  const currentPageItemIds = paginatedData.value.map(item => getItemId(item))
  selectAll.value = currentPageItemIds.length > 0 && 
    currentPageItemIds.every(id => selectedItems.value.includes(id))
})

</script>