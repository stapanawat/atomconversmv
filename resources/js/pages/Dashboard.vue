<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
];

// Stats
const stats = ref({
    total: 0,
    pending: 0,
    processing: 0,
    completed: 0,
    failed: 0,
});

const recentConversions = ref<any[]>([]);
const isLoading = ref(true);

// Fetch user conversions
const fetchConversions = async () => {
    try {
        const response = await axios.get('/api/conversions');
        const conversions = response.data.data || [];
        
        recentConversions.value = conversions.slice(0, 5);
        
        stats.value = {
            total: conversions.length,
            pending: conversions.filter((c: any) => c.status === 'pending').length,
            processing: conversions.filter((c: any) => c.status === 'processing').length,
            completed: conversions.filter((c: any) => c.status === 'completed').length,
            failed: conversions.filter((c: any) => c.status === 'failed').length,
        };
    } catch (error) {
        console.error('Error fetching conversions:', error);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchConversions();
});

const statsCards = [
    { key: 'total', label: 'ทั้งหมด', icon: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3', color: 'from-purple-500 to-purple-600', textColor: 'text-purple-400' },
    { key: 'pending', label: 'รอคิว', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', color: 'from-yellow-500 to-orange-500', textColor: 'text-yellow-400' },
    { key: 'completed', label: 'สำเร็จ', icon: 'M5 13l4 4L19 7', color: 'from-green-500 to-emerald-500', textColor: 'text-green-400' },
    { key: 'failed', label: 'ล้มเหลว', icon: 'M6 18L18 6M6 6l12 12', color: 'from-red-500 to-rose-500', textColor: 'text-red-400' },
];

const statusText = (status: string) => {
    const texts: Record<string, string> = {
        pending: 'รอคิว',
        processing: 'กำลังแปลง',
        completed: 'สำเร็จ',
        failed: 'ล้มเหลว',
    };
    return texts[status] || status;
};

const statusClass = (status: string) => {
    const classes: Record<string, string> = {
        pending: 'bg-yellow-500/20 text-yellow-400',
        processing: 'bg-blue-500/20 text-blue-400',
        completed: 'bg-green-500/20 text-green-400',
        failed: 'bg-red-500/20 text-red-400',
    };
    return classes[status] || '';
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6" style="background: linear-gradient(135deg, #1a1625 0%, #2d2640 100%); min-height: 100%;">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-white mb-2">ยินดีต้อนรับ! 👋</h1>
                <p class="text-gray-400">จัดการไฟล์ MP3 to MV ของคุณ</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div
                    v-for="stat in statsCards"
                    :key="stat.key"
                    class="p-6 rounded-2xl transition-all duration-300 hover:scale-105"
                    style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(158, 118, 180, 0.2);"
                >
                    <div class="flex items-center gap-4">
                        <div :class="['w-12 h-12 rounded-xl flex items-center justify-center bg-gradient-to-br', stat.color]">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon" />
                            </svg>
                        </div>
                        <div>
                            <p :class="['text-3xl font-bold', stat.textColor]">
                                {{ isLoading ? '-' : stats[stat.key as keyof typeof stats] }}
                            </p>
                            <p class="text-gray-400 text-sm">{{ stat.label }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-3 gap-6">
                <!-- Quick Actions -->
                <div class="lg:col-span-1">
                    <div class="p-6 rounded-2xl h-full" style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(158, 118, 180, 0.2);">
                        <h2 class="text-xl font-bold text-white mb-6">ทำอะไรต่อ?</h2>
                        
                        <div class="space-y-4">
                            <Link
                                href="/mp3-to-mv"
                                class="flex items-center gap-4 p-4 rounded-xl bg-gradient-to-r from-[#9E76B4]/20 to-[#FFD700]/10 border border-[#9E76B4]/30 hover:border-[#FFD700]/50 transition-all duration-300 group"
                            >
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#FFD700] to-[#FFA500] flex items-center justify-center group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-white">อัปโหลดไฟล์ใหม่</p>
                                    <p class="text-gray-400 text-sm">แปลงเพลง MP3 เป็นวิดีโอ</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 ml-auto group-hover:text-[#FFD700] group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                            
                            <Link
                                href="/settings/profile"
                                class="flex items-center gap-4 p-4 rounded-xl bg-white/5 border border-white/10 hover:border-[#9E76B4]/50 transition-all duration-300 group"
                            >
                                <div class="w-12 h-12 rounded-xl bg-[#9E76B4]/30 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-white">ตั้งค่าโปรไฟล์</p>
                                    <p class="text-gray-400 text-sm">แก้ไขข้อมูลส่วนตัว</p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 ml-auto group-hover:text-[#9E76B4] group-hover:translate-x-1 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Recent Conversions -->
                <div class="lg:col-span-2">
                    <div class="p-6 rounded-2xl" style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(158, 118, 180, 0.2);">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-white">ไฟล์ล่าสุด</h2>
                            <Link href="/mp3-to-mv" class="text-[#9E76B4] hover:text-[#FFD700] text-sm transition-colors">
                                ดูทั้งหมด →
                            </Link>
                        </div>

                        <div v-if="isLoading" class="flex items-center justify-center py-12">
                            <div class="w-8 h-8 border-2 border-[#9E76B4] border-t-transparent rounded-full animate-spin"></div>
                        </div>

                        <div v-else-if="recentConversions.length === 0" class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-[#9E76B4]/20 flex items-center justify-center">
                                <svg class="w-8 h-8 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </div>
                            <p class="text-gray-400 mb-4">ยังไม่มีไฟล์ที่แปลง</p>
                            <Link
                                href="/mp3-to-mv"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-black bg-gradient-to-r from-[#FFD700] to-[#FFA500] hover:shadow-lg hover:shadow-[#FFD700]/30 transition-all"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                อัปโหลดไฟล์แรก
                            </Link>
                        </div>

                        <div v-else class="space-y-3">
                            <div
                                v-for="conversion in recentConversions"
                                :key="conversion.id"
                                class="flex items-center gap-4 p-4 rounded-xl bg-white/5 hover:bg-white/10 transition-colors"
                            >
                                <div class="w-10 h-10 rounded-lg bg-[#9E76B4]/20 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                    </svg>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <p class="text-white font-medium truncate">{{ conversion.original_filename }}</p>
                                    <p class="text-gray-500 text-sm">
                                        {{ new Date(conversion.created_at).toLocaleDateString('th-TH') }}
                                    </p>
                                </div>
                                <span :class="[statusClass(conversion.status), 'px-3 py-1 rounded-full text-xs font-medium']">
                                    {{ statusText(conversion.status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
