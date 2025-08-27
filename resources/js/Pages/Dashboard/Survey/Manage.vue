<template>
  <DashboardLayout 
    :pageTitle="'Survey Management'"
  >
    <div class="space-y-6">
      <!-- Header Section -->
      <PageHeader 
        title="Manage Survey" 
        description="View detailed information about surveys including sections, responses, and configurations"
        :showBackButton="true"
      />

      <!-- Survey Information Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Survey Detail Information Card -->
        <div class="lg:col-span-3 space-y-6">
          dsada
        </div>
        
        <!-- Quick Actions and Statistics Cards -->
        <div class="space-y-6">
          <SurveyQuickActions 
            
          />
        </div>
      </div>

    </div>

  </DashboardLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import PageHeader from '@/Components/PageHeader.vue'
import SurveyQuickActions from '@/Components/SurveyQuickActions.vue'

</script>