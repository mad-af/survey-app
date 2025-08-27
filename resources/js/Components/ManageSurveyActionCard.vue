<template>
  <div class="sticky top-6 border shadow-sm card card-sm bg-base-100 border-base-200">
    <div class="p-4 card-body">
      <h2 class="flex gap-2 items-center mb-3 text-lg card-title">
        <Settings class="w-5 h-5" />
        Actions
      </h2>

      <div class="grid grid-cols-1 gap-2">
        <button class="gap-2 justify-start w-full h-8 text-xs transition-all duration-200 btn btn-sm hover:shadow-sm"
          @click="$emit('add-section')">
          <SquarePlus class="size-3" />
          <span class="font-medium">Add Section</span>
        </button>

        <button class="gap-2 justify-start w-full h-8 text-xs transition-all duration-200 btn btn-sm hover:shadow-sm"
          @click="$emit('add-question')">
          <CopyPlus class="size-3" />
          <span class="font-medium">Add Question</span>
        </button>

        <!-- <div class="m-0 opacity-50 divider"></div>

        <button
          class="gap-2 justify-start w-full h-8 text-xs transition-all duration-200 btn text-error btn-sm hover:shadow-sm"
          @click="$emit('delete-survey')">
          <Trash2 class="w-3 h-3" />
          <span class="font-medium">Delete Survey</span>
        </button> -->
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineEmits } from 'vue'
import { Settings, SquarePlus, CopyPlus } from 'lucide-vue-next'

const emit = defineEmits([
  'add-section',
  'add-question',
  'edit-survey',
  'view-responses',
  'share-survey',
  'export-data',
  'delete-survey'
])
</script>