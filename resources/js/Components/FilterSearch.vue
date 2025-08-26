<template>
  <div class="card card-sm bg-base-100 shadow-sm mb-2">
    <div class="card-body">
      <div class="flex flex-col lg:flex-row gap-4">
        <!-- Dynamic Filters -->
        <div 
          v-for="filter in filters" 
          :key="filter.key"
          class="form-control w-full lg:w-48"
        >
          <select 
            class="select select-sm select-bordered" 
            :value="filter.value"
            @change="$emit('update:filter', { key: filter.key, value: $event.target.value })"
          >
            <option value="">{{ filter.placeholder }}</option>
            <option 
              v-for="option in filter.options" 
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Search -->
        <div class="form-control flex-1 justify-items-start">
          <label class="input input-sm input-bordered flex items-center gap-2">
            <Search :size="16" class="opacity-50" />
            <input 
              type="search" 
              :placeholder="searchPlaceholder"
              class="grow"
              :value="searchQuery"
              @input="$emit('update:searchQuery', $event.target.value)"
            />
          </label>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Search } from 'lucide-vue-next'

// Props
defineProps({
  searchQuery: {
    type: String,
    default: ''
  },
  searchPlaceholder: {
    type: String,
    default: 'Search...'
  },
  filters: {
    type: Array,
    default: () => []
    // Expected format:
    // [
    //   {
    //     key: 'role',
    //     value: '',
    //     placeholder: 'All Roles',
    //     options: [
    //       { value: 'admin', label: 'Admin' },
    //       { value: 'user', label: 'User' }
    //     ]
    //   }
    // ]
  }
})

// Emits
defineEmits(['update:searchQuery', 'update:filter'])
</script>