<template>
    <div class="min-h-screen bg-base-200 flex items-center justify-center p-4">
        <div class="card w-full max-w-2xl bg-base-100 shadow-xl">
            <div class="card-body">
                <div class="flex items-center gap-2 mb-4">
                    <Sparkles class="w-6 h-6 text-primary" />
                    <div class="badge badge-primary badge-outline">
                        Laravel + Inertia.js + Vue.js
                    </div>
                </div>
                
                <h1 class="card-title text-3xl mb-2">
                    <Rocket class="w-8 h-8 text-secondary" />
                    Selamat Datang di Survey App!
                </h1>
                
                <p class="text-base-content/70 mb-6">
                    Aplikasi Laravel Anda dengan Inertia.js, Vue.js, DaisyUI, dan Lucide Icons telah berhasil dikonfigurasi.
                </p>
                
                <div class="flex flex-wrap gap-2 mb-6">
                    <div class="badge badge-success gap-2">
                        <CheckCircle class="w-4 h-4" />
                        Laravel 12
                    </div>
                    <div class="badge badge-info gap-2">
                        <Zap class="w-4 h-4" />
                        Inertia.js
                    </div>
                    <div class="badge badge-accent gap-2">
                        <Palette class="w-4 h-4" />
                        Vue.js 3
                    </div>
                    <div class="badge badge-secondary gap-2">
                        <Paintbrush class="w-4 h-4" />
                        DaisyUI
                    </div>
                    <div class="badge badge-warning gap-2">
                        <Star class="w-4 h-4" />
                        Lucide Icons
                    </div>
                </div>
                
                <div class="card-actions justify-center">
                    <button 
                        @click="showMessage = !showMessage"
                        class="btn btn-primary gap-2"
                    >
                        <component :is="showMessage ? EyeOff : Eye" class="w-4 h-4" />
                        {{ showMessage ? 'Sembunyikan' : 'Tampilkan' }} Pesan
                    </button>
                </div>
                
                <div v-if="showMessage" class="alert alert-success mt-6">
                    <CheckCircle class="w-6 h-6" />
                    <div>
                        <h3 class="font-bold">Berhasil!</h3>
                        <div class="text-xs">🎉 Semua tools telah dikonfigurasi dengan sempurna!</div>
                    </div>
                </div>
                
                <div class="divider">Theme Switcher</div>
                
                <div class="flex justify-center gap-4">
                    <a href="/test-daisy" class="btn btn-outline btn-primary">
                        Test DaisyUI
                    </a>
                    <div class="dropdown dropdown-top">
                        <div tabindex="0" role="button" class="btn btn-outline gap-2">
                            <Palette class="w-4 h-4" />
                            Ganti Theme
                        </div>
                        <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52 max-h-64 overflow-y-auto">
                            <li v-for="theme in themes" :key="theme">
                                <a @click="changeTheme(theme)" class="capitalize">{{ theme }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import { 
    Sparkles, 
    Rocket, 
    CheckCircle, 
    Zap, 
    Palette, 
    Paintbrush, 
    Star, 
    Eye, 
    EyeOff 
} from 'lucide-vue-next'

const showMessage = ref(false)

const themes = [
    'light', 'dark', 'cupcake', 'bumblebee', 'emerald', 'corporate',
    'synthwave', 'retro', 'cyberpunk', 'valentine', 'halloween', 'garden',
    'forest', 'aqua', 'lofi', 'pastel', 'fantasy', 'wireframe', 'black',
    'luxury', 'dracula', 'cmyk', 'autumn', 'business', 'acid', 'lemonade',
    'night', 'coffee', 'winter'
]

const changeTheme = (theme) => {
    document.documentElement.setAttribute('data-theme', theme)
    localStorage.setItem('theme', theme)
}

onMounted(() => {
    const savedTheme = localStorage.getItem('theme') || 'light'
    document.documentElement.setAttribute('data-theme', savedTheme)
})
</script>