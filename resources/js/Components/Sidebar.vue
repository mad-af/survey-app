<template>
  <aside class="flex flex-col w-60 min-h-full bg-base-100 text-base-content">
      <!-- Logo -->
      <div class="p-4">
        <!-- <div class="flex gap-3 justify-center items-center"> -->
          <!-- <div class="flex justify-center items-center rounded-xl shadow-sm size-8 bg-primary">
            <ClipboardList class="size-5 text-primary-content" />
          </div>
          <div class="text-start">
            <span class="block text-base font-bold tracking-wide text-base-content">Survey App</span>
            <span class="block text-xs text-base-content/60">Dashboard</span>
          </div> -->
        <!-- </div> -->
         <div class="flex justify-center items-center">
           <img src="/beraksi.webp" alt="SurveyApp Logo" class="h-6 lg:h-8 lg:w-32" />
         </div>
      </div>

      <!-- Navigation Menu -->
      <div class="flex flex-col flex-1 p-3">
        <div class="mb-4">
          <p class="px-2 mb-2 text-xs font-semibold tracking-wider uppercase text-base-content/60">Main menu</p>
          <ul class="gap-1 w-full menu menu-sm">
            <li v-for="item in menuItems" :key="item.name">
              <a :href="item.href" :class="[
                'flex items-center gap-2 p-2 rounded-lg text-sm',
                item.active 
                  ? 'bg-primary text-primary-content font-medium' 
                  : 'hover:bg-base-200 transition-colors'
              ]">
                <component :is="item.icon" class="w-4 h-4" />
                <span>{{ item.name }}</span>
                <div v-if="item.badge" class="ml-auto badge badge-xs badge-primary">{{ item.badge }}</div>
              </a>
            </li>
          </ul>
        </div>

        <div class="mb-4">
          <p class="px-2 mb-2 text-xs font-semibold tracking-wider uppercase text-base-content/60">Other</p>
          <ul class="gap-1 w-full menu menu-sm">
            <li v-for="item in otherMenuItems" :key="item.name">
              <a :href="item.href" :class="[
                'flex items-center gap-2 p-2 rounded-lg text-sm',
                item.active 
                  ? 'bg-primary text-primary-content font-medium' 
                  : 'hover:bg-base-200 transition-colors'
              ]">
                <component :is="item.icon" class="w-4 h-4" />
                <span>{{ item.name }}</span>
                <div v-if="item.badge" :class="[
                  'badge badge-xs ml-auto',
                  item.badgeType === 'success' ? 'badge-success' : 'badge-primary'
                ]">{{ item.badge }}</div>
              </a>
            </li>
          </ul>
        </div>

        <!-- Logout -->
        <div class="flex justify-center pt-3 mt-auto border-t border-base-300">
          <a @click="handleLogout" class="flex gap-2 justify-center items-center p-2 w-2/3 text-sm rounded-lg transition-colors cursor-pointer hover:bg-base-200">
            <LogOut class="w-4 h-4" />
            <span>Logout</span>
          </a>
        </div>
      </div>
    </aside>
</template>

<script setup>
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import { 
  ClipboardList,
  Home, 
  Users, 
  LogOut,
  FileText,
} from 'lucide-vue-next'

// Props
const props = defineProps({
  currentRoute: {
    type: String,
    default: ''
  }
})

// Get current page info
const page = usePage()
const user = computed(() => page.props.auth?.user || {})

// Computed menu items with active state based on current URL
const menuItems = computed(() => [
  {
    name: 'Home',
    href: '/dashboard',
    icon: Home,
    active: page.url === '/dashboard'
  },
  {
    name: 'Survey',
    href: '/dashboard/survey',
    icon: FileText,
    active: page.url === '/dashboard/survey'
  },
])

const otherMenuItems = computed(() => {
  const items = []
  
  // Only show User Management for admin users
  if (user.value.role === 'admin') {
    items.push({
      name: 'User Managements',
      href: '/dashboard/user-management',
      icon: Users,
      active: page.url === '/dashboard/user-management',
    })
  }
  
  return items
})

// Methods
const handleLogout = () => {
  router.post('/logout', {}, {
    onSuccess: () => {
      // Redirect will be handled by the backend
    }
  })
}
</script>