<template>
  <div class="card card-sm bg-base-100 shadow-lg border border-base-200">
    <div class="card-body p-4">
      <h2 class="card-title text-lg mb-3 flex items-center gap-2">
        <Zap class="h-5 w-5" />
        Quick Actions
      </h2>
      
      <div class="grid grid-cols-1 gap-2">
         <button 
           class="btn btn-sm h-8 w-full justify-start gap-2 hover:shadow-sm transition-all duration-200 text-xs" 
           @click="$emit('edit-survey')"
         >
           <Edit3 class="h-3 w-3" />
           <span class="font-medium">Edit Survey</span>
         </button>
         
         <button 
           class="btn btn-sm h-8 w-full justify-start gap-2 hover:shadow-sm transition-all duration-200 text-xs" 
           @click="$emit('view-responses')"
         >
           <ListOrdered class="h-3 w-3" />
           <span class="font-medium">Manage Survey</span>
         </button>
         
         <!-- <button 
           class="btn btn-sm h-8 w-full justify-start gap-2 hover:shadow-sm transition-all duration-200 text-xs" 
           @click="$emit('share-survey')"
         >
           <Share2 class="h-3 w-3" />
           <span class="font-medium">Share Survey</span>
         </button>
         
         <button 
           class="btn btn-sm h-8 w-full justify-start gap-2 hover:shadow-sm transition-all duration-200 text-xs" 
           @click="$emit('export-data')"
         >
           <Download class="h-3 w-3" />
           <span class="font-medium">Export Data</span>
         </button>
          -->
         <div class="divider m-0 opacity-50"></div>
         
         <button 
           class="btn text-error btn-sm h-8 w-full justify-start gap-2 hover:shadow-sm transition-all duration-200 text-xs" 
           @click="$emit('delete-survey')"
         >
           <Trash2 class="h-3 w-3" />
           <span class="font-medium">Delete Survey</span>
         </button>
       </div>
    </div>
  </div>
</template>

<script setup>
import { defineEmits } from 'vue'
import { Zap, Edit3, FileText, Share2, Download, Trash2, ListOrdered } from 'lucide-vue-next'

const emit = defineEmits([
  'edit-survey',
  'view-responses', 
  'share-survey',
  'export-data',
  'delete-survey'
])
</script>