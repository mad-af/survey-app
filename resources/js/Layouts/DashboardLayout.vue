<template>
  <div class="min-h-screen bg-base-200">
    <!-- Sidebar -->
    <div class="drawer lg:drawer-open">
      <input id="drawer-toggle" type="checkbox" class="drawer-toggle" />
      <div class="drawer-content flex flex-col">
        <!-- Header -->
        <div class="navbar bg-base-100 p-3.5 border-b border-base-300">
          <div class="flex-none lg:hidden">
            <label for="drawer-toggle" class="btn btn-square btn-ghost btn-sm">
              <Menu class="w-4 h-4" />
            </label>
          </div>
          <div class="flex-1">
            <h1 class="text-lg font-semibold text-base-content">My Dashboard</h1>
          </div>
          <div class="flex-none gap-2">
            <!-- User Profile -->
              <div tabindex="0" role="button" class="btn btn-ghost btn-sm px-2 py-1 h-auto min-h-0">
                <div class="flex items-center gap-2">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center">
                    <img src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=32&h=32&fit=crop&crop=face" alt="Profile" class="w-8 h-8 rounded-full object-cover" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex'" />
                    <div class="w-8 h-8 rounded-full bg-primary flex items-center justify-center text-primary-content font-semibold text-xs" style="display:none">EH</div>
                  </div>
                  <div class="text-left hidden sm:block">
                    <div class="text-sm font-medium text-base-content">Esther H.</div>
                    <div class="text-xs text-base-content/60">Designer</div>
                  </div>
                </div>
              </div>

          </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 p-4">
          <slot />
        </main>
      </div>

      <!-- Sidebar -->
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
                <li>
                  <a class="flex items-center gap-2 p-2 rounded-lg bg-primary text-primary-content font-medium text-sm">
                    <Home class="w-4 h-4" />
                    <span>Home</span>
                  </a>
                </li>
                <li>
                  <a class="flex items-center gap-2 p-2 rounded-lg hover:bg-base-200 transition-colors text-sm">
                    <Users class="w-4 h-4" />
                    <span>Leads</span>
                    <div class="badge badge-xs badge-primary ml-auto">12</div>
                  </a>
                </li>
                <li>
                  <a class="flex items-center gap-2 p-2 rounded-lg hover:bg-base-200 transition-colors text-sm">
                    <Wallet class="w-4 h-4" />
                    <span>Wallet</span>
                  </a>
                </li>
                <li>
                  <a class="flex items-center gap-2 p-2 rounded-lg hover:bg-base-200 transition-colors text-sm">
                    <Contact class="w-4 h-4" />
                    <span>Contacts</span>
                  </a>
                </li>
                <li>
                  <a class="flex items-center gap-2 p-2 rounded-lg hover:bg-base-200 transition-colors text-sm">
                    <Package class="w-4 h-4" />
                    <span>Products</span>
                  </a>
                </li>
              </ul>
            </div>

            <div class="mb-4">
              <p class="text-xs font-semibold text-base-content/60 uppercase tracking-wider mb-2 px-2">Other</p>
              <ul class="menu menu-sm gap-1 w-full">
                <li>
                  <a class="flex items-center gap-2 p-2 rounded-lg hover:bg-base-200 transition-colors text-sm">
                    <MessageSquare class="w-4 h-4" />
                    <span>Sales Inbox</span>
                    <div class="badge badge-xs badge-success ml-auto">3</div>
                  </a>
                </li>
                <li>
                  <a class="flex items-center gap-2 p-2 rounded-lg hover:bg-base-200 transition-colors text-sm">
                    <BarChart3 class="w-4 h-4" />
                    <span>Analytics</span>
                  </a>
                </li>
                <li>
                  <a class="flex items-center gap-2 p-2 rounded-lg hover:bg-base-200 transition-colors text-sm">
                    <Settings class="w-4 h-4" />
                    <span>Settings</span>
                  </a>
                </li>
              </ul>
            </div>

            <!-- Logout -->
            <div class="mt-auto border-t border-base-300 pt-3 flex justify-center">
              <a class="flex items-center justify-center gap-2 p-2 rounded-lg hover:bg-base-200 transition-colors text-sm cursor-pointer w-2/3">
                <LogOut class="w-4 h-4" />
                <span>Logout</span>
              </a>
            </div>
          </div>
        </aside>
      </div>
    </div>
  </div>
</template>

<script setup>
import { 
  Menu, 
  Search, 
  Bell, 
  User, 
  Settings, 
  LogOut, 
  ClipboardList, 
  Home, 
  Users, 
  Wallet, 
  Contact, 
  Package, 
  MessageSquare, 
  BarChart3 
} from 'lucide-vue-next'

// Dashboard layout component
</script>

<style scoped>
/* Custom styles if needed */
</style>