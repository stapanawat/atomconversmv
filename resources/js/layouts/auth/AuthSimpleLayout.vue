<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen flex items-center justify-center p-4 md:p-8 relative overflow-hidden" style="background: linear-gradient(135deg, #1a1625 0%, #2d2640 50%, #1a1625 100%);">
        <!-- Background Effects -->
        <div class="absolute top-1/4 -left-20 w-80 h-80 bg-[#9E76B4]/30 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 -right-20 w-80 h-80 bg-[#FFD700]/20 rounded-full blur-3xl"></div>
        
        <!-- Grid Pattern -->
        <div class="absolute inset-0 opacity-5" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cpath d=&quot;M0 0h60v60H0z&quot; fill=&quot;none&quot; stroke=&quot;%239E76B4&quot; stroke-width=&quot;0.5&quot;/%3E%3C/svg%3E');"></div>

        <div class="w-full max-w-md relative z-10">
            <!-- Card -->
            <div class="rounded-3xl p-8 md:p-10" style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(158, 118, 180, 0.2);">
                <!-- Logo -->
                <div class="flex flex-col items-center mb-8">
                    <Link href="/" class="flex items-center gap-3 group mb-6">
                        <div class="relative">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#9E76B4] to-[#FFD700] flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </div>
                            <div class="absolute -inset-1 bg-gradient-to-r from-[#9E76B4] to-[#FFD700] rounded-xl blur opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                        </div>
                        <span class="text-2xl font-bold text-white">
                            MP3<span class="text-[#FFD700]">to</span>MV
                        </span>
                    </Link>
                    
                    <!-- Title -->
                    <h1 class="text-2xl font-bold text-white text-center">{{ title }}</h1>
                    <p class="text-gray-400 text-center mt-2">{{ description }}</p>
                </div>
                
                <!-- Form Slot -->
                <slot />
            </div>
            
            <!-- Back to Home -->
            <div class="text-center mt-6">
                <Link href="/" class="text-gray-400 hover:text-[#FFD700] transition-colors text-sm">
                    ← กลับหน้าหลัก
                </Link>
            </div>
        </div>
    </div>
</template>
