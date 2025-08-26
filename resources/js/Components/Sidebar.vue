<template>
  <div class="drawer-side">
        <label for="drawer-toggle" aria-label="close sidebar" class="drawer-overlay"></label>
        <aside class="min-h-full w-60 bg-base-100 text-base-content flex flex-col">
          <!-- Logo -->
          <div class="p-4">
            <div class="flex items-center gap-3 justify-center ">
              <div class="size-8 bg-primary rounded-xl flex items-center justify-center shadow-sm">
                <ClipboardList class="size-5 text-primary-content" />
              </div>
              <div class="text-start">
                <span class="text-base font-bold text-base-content block tracking-wide">Survey App</span>
                <span class="text-xs text-base-content/60 block">Dashboard</span>
              </div>
            </div>
          </div>

          <!-- Navigation Menu -->
          <div class="flex-1 flex flex-col p-3">
            <div class="mb-4">
              <p class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-2 px-2">Main menu</p>
              <ul class="menu menu-sm gap-1 w-full">
                <li v-for="item in menuItems" :key="item.name">
                  <a :href="item.href" :class="[
                    'flex items-center gap-2 p-2 rounded-lg text-sm',
                    item.active 
                      ? 'bg-primary text-primary-content font-medium' 
                      : 'hover:bg-base-200 transition-colors'
                  ]">
                    <component :is="item.icon" class="w-4 h-4" />
                    <span>{{ item.name }}</span>
                    <div v-if="item.badge" class="badge badge-xs badge-primary ml-auto">{{ item.badge }}</div>
                  </a>
                </li>
              </ul>
            </div>

            <div class="mb-4">
              <p class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-2 px-2">Other</p>
              <ul class="menu menu-sm gap-1 w-full">
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
            <div class="mt-auto border-t border-base-300 pt-3 flex justify-center">
              <a @click="handleLogout" class="flex items-center justify-center gap-2 p-2 rounded-lg hover:bg-base-200 transition-colors text-sm cursor-pointer w-2/3">
                <LogOut class="w-4 h-4" />
                <span>Logout</span>
              </a>
            </div>
          </div>
        </aside>
      </div>
</template>

<script setup>
import { 
  ClipboardList,
  Home, 
  Users, 
  Wallet, 
  Contact, 
  Package, 
  MessageSquare,
  BarChart3, 
  Settings, 
  LogOut
} from 'lucide-vue-next'
// Props
const props = defineProps({
  currentRoute: {
    type: String,
    default: 'dashboard'
  }
})

// Menu items
const menuItems = [
  {
    name: 'Home',
    href: '/dashboard',
    icon: Home,
    active: props.currentRoute === 'dashboard'
  },
  {
    name: 'Leads',
    href: '#',
    icon: Users,
    active: false,
    badge: '12'
  },
  {
    name: 'Wallet',
    href: '#',
    icon: Wallet,
    active: false
  },
  {
    name: 'Contacts',
    href: '#',
    icon: Contact,
    active: false
  },
  {
    name: 'Products',
    href: '#',
    icon: Package,
    active: false
  }
]

const otherMenuItems = [
  {
    name: 'Sales Inbox',
    href: '#',
    icon: MessageSquare,
    active: false,
    badge: '3',
    badgeType: 'success'
  },
  {
    name: 'Analytics',
    href: '#',
    icon: BarChart3,
    active: false
  },
  {
    name: 'Settings',
    href: '#',
    icon: Settings,
    active: false
  }
]

// Methods
const handleLogout = () => {
  // Handle logout logic here
  console.log('Logout clicked')
}
</script>