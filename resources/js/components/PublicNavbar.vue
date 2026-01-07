<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';

const isScrolled = ref(false);
const isMobileMenuOpen = ref(false);

const handleScroll = () => {
    isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <nav
        :class="[
            'fixed top-0 left-0 right-0 z-50 transition-all duration-300',
            isScrolled
                ? 'bg-[#1a1625]/95 backdrop-blur-lg shadow-lg shadow-purple-900/20'
                : 'bg-transparent'
        ]"
    >
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-16 md:h-20">
                <!-- Logo -->
                <Link href="/" class="flex items-center gap-3 group">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#9E76B4] to-[#FFD700] flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                            </svg>
                        </div>
                        <div class="absolute -inset-1 bg-gradient-to-r from-[#9E76B4] to-[#FFD700] rounded-xl blur opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
                    </div>
                    <span class="text-xl font-bold text-white">
                        MP3<span class="text-[#FFD700]">to</span>MV
                    </span>
                </Link>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-8">
                    <Link
                        href="/"
                        class="text-gray-300 hover:text-white transition-colors duration-200 relative group"
                    >
                        หน้าหลัก
                        <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#FFD700] group-hover:w-full transition-all duration-300"></span>
                    </Link>
                    <Link
                        href="/convert"
                        class="text-gray-300 hover:text-white transition-colors duration-200 relative group"
                    >
                        แปลงไฟล์
                        <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#FFD700] group-hover:w-full transition-all duration-300"></span>
                    </Link>
                </div>

                <!-- Auth Buttons -->
                <div class="hidden md:flex items-center gap-4">
                    <Link
                        href="/login"
                        class="px-5 py-2.5 text-white hover:text-[#FFD700] transition-colors duration-200"
                    >
                        เข้าสู่ระบบ
                    </Link>
                    <Link
                        href="/register"
                        class="px-5 py-2.5 rounded-xl font-semibold text-black bg-gradient-to-r from-[#FFD700] to-[#FFA500] hover:shadow-lg hover:shadow-[#FFD700]/30 hover:scale-105 transition-all duration-300"
                    >
                        สมัครสมาชิก
                    </Link>
                </div>

                <!-- Mobile Menu Button -->
                <button
                    @click="isMobileMenuOpen = !isMobileMenuOpen"
                    class="md:hidden p-2 text-white hover:text-[#FFD700] transition-colors"
                >
                    <svg v-if="!isMobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <Transition
                enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2"
                enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in"
                leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2"
            >
                <div
                    v-if="isMobileMenuOpen"
                    class="md:hidden pb-4 space-y-2"
                >
                    <Link
                        href="/"
                        class="block px-4 py-3 rounded-xl text-white hover:bg-[#9E76B4]/20 transition-colors"
                    >
                        หน้าหลัก
                    </Link>
                    <Link
                        href="/convert"
                        class="block px-4 py-3 rounded-xl text-white hover:bg-[#9E76B4]/20 transition-colors"
                    >
                        แปลงไฟล์
                    </Link>
                    <div class="pt-2 space-y-2">
                        <Link
                            href="/login"
                            class="block px-4 py-3 rounded-xl text-center text-white border border-[#9E76B4]/50 hover:bg-[#9E76B4]/20 transition-colors"
                        >
                            เข้าสู่ระบบ
                        </Link>
                        <Link
                            href="/register"
                            class="block px-4 py-3 rounded-xl text-center font-semibold text-black bg-gradient-to-r from-[#FFD700] to-[#FFA500]"
                        >
                            สมัครสมาชิก
                        </Link>
                    </div>
                </div>
            </Transition>
        </div>
    </nav>
</template>
