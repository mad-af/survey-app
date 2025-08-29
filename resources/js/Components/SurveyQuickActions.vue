<template>
  <div class="border shadow-lg card card-sm bg-base-100 border-base-200">
    <div class="p-4 card-body">
      <h2 class="flex gap-2 items-center mb-3 text-lg card-title">
        <Zap class="w-5 h-5" />
        Quick Actions
      </h2>

      <div class="grid grid-cols-1 gap-2">
        <button class="gap-2 justify-start w-full h-8 text-xs transition-all duration-200 btn btn-sm hover:shadow-sm"
          @click="$emit('edit-survey')">
          <Edit3 class="w-3 h-3" />
          <span class="font-medium">Edit Survey</span>
        </button>

        <button class="gap-2 justify-start w-full h-8 text-xs transition-all duration-200 btn btn-sm hover:shadow-sm"
          @click="$emit('manage-survey')">
          <ListOrdered class="w-3 h-3" />
          <span class="font-medium">Manage Survey</span>
        </button>

        <button class="gap-2 justify-start w-full h-8 text-xs transition-all duration-200 btn btn-sm hover:shadow-sm"
          @click="$emit('view-responses')">
          <FileText class="w-3 h-3" />
          <span class="font-medium">View Response</span>
        </button>

        <!-- <button 
           class="gap-2 justify-start w-full h-8 text-xs transition-all duration-200 btn btn-sm hover:shadow-sm" 
           @click="$emit('share-survey')"
         >
           <Share2 class="w-3 h-3" />
           <span class="font-medium">Share Survey</span>
         </button>
         
         <button 
           class="gap-2 justify-start w-full h-8 text-xs transition-all duration-200 btn btn-sm hover:shadow-sm" 
           @click="$emit('export-data')"
         >
           <Download class="w-3 h-3" />
           <span class="font-medium">Export Data</span>
         </button>
          -->
        <div class="m-0 opacity-50 divider"></div>

        <button
          class="gap-2 justify-start w-full h-8 text-xs transition-all duration-200 btn text-error btn-sm hover:shadow-sm"
          @click="$emit('delete-survey')">
          <Trash2 class="w-3 h-3" />
          <span class="font-medium">Delete Survey</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineEmits } from 'vue'
import { Zap, Edit3, FileText, Trash2, ListOrdered } from 'lucide-vue-next'

const emit = defineEmits([
  'edit-survey',
  'manage-survey',
  'view-responses',
  'share-survey',
  'export-data',
  'delete-survey'
])
</script>