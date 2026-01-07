<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'MP3 to MV', href: '/mp3-to-mv' },
];

// Types
interface Conversion {
    id: number;
    original_filename: string;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    error_message?: string;
    created_at: string;
    mp4_path?: string;
}

interface Toast {
    id: number;
    type: 'success' | 'error' | 'info' | 'warning';
    message: string;
}

// State
const conversions = ref<Conversion[]>([]);
const isUploading = ref(false);
const uploadProgress = ref(0);
const isDragging = ref(false);
const toasts = ref<Toast[]>([]);
const isLoading = ref(true);
let pollInterval: ReturnType<typeof setInterval> | null = null;
let toastId = 0;

// Toast functions
const showToast = (type: Toast['type'], message: string) => {
    const id = ++toastId;
    toasts.value.push({ id, type, message });
    setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }, 5000);
};

// API functions
const fetchConversions = async () => {
    try {
        const response = await axios.get('/api/conversions');
        conversions.value = response.data.data;
    } catch (error) {
        console.error('Failed to fetch conversions:', error);
    } finally {
        isLoading.value = false;
    }
};

const uploadFile = async (file: File) => {
    if (!file.name.toLowerCase().endsWith('.mp3')) {
        showToast('error', 'กรุณาเลือกไฟล์ MP3 เท่านั้น');
        return;
    }

    isUploading.value = true;
    uploadProgress.value = 0;

    const formData = new FormData();
    formData.append('file', file);

    try {
        await axios.post('/api/conversions', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (progressEvent) => {
                if (progressEvent.total) {
                    uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                }
            },
        });
        showToast('success', 'อัปโหลดสำเร็จ! กำลังรอการแปลงไฟล์...');
        await fetchConversions();
    } catch (error: any) {
        const message = error.response?.data?.message || 'เกิดข้อผิดพลาดในการอัปโหลด';
        showToast('error', message);
    } finally {
        isUploading.value = false;
        uploadProgress.value = 0;
    }
};

const deleteConversion = async (id: number) => {
    if (!confirm('ต้องการลบไฟล์นี้หรือไม่?')) return;

    try {
        await axios.delete(`/api/conversions/${id}`);
        showToast('success', 'ลบเรียบร้อยแล้ว');
        await fetchConversions();
    } catch (error) {
        showToast('error', 'เกิดข้อผิดพลาดในการลบ');
    }
};

const downloadFile = (id: number) => {
    window.open(`/api/conversions/${id}/download`, '_blank');
};

// Drag & Drop handlers
const handleDragOver = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = true;
};

const handleDragLeave = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = false;
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    isDragging.value = false;
    const files = e.dataTransfer?.files;
    if (files && files.length > 0) {
        uploadFile(files[0]);
    }
};

const handleFileSelect = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        uploadFile(target.files[0]);
    }
};

const triggerFileInput = () => {
    document.getElementById('fileInput')?.click();
};

// Status helpers
const statusText = (status: string) => {
    const texts: Record<string, string> = {
        pending: 'รอคิว',
        processing: 'กำลังแปลง',
        completed: 'เสร็จสิ้น',
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

const toastClass = (type: string) => {
    const classes: Record<string, string> = {
        success: 'bg-green-600',
        error: 'bg-red-600',
        info: 'bg-blue-600',
        warning: 'bg-yellow-600',
    };
    return classes[type] || 'bg-gray-600';
};

const hasActiveJobs = computed(() => {
    return conversions.value.some(c => c.status === 'pending' || c.status === 'processing');
});

// Lifecycle
onMounted(() => {
    fetchConversions();
    // Poll for updates
    pollInterval = setInterval(() => {
        if (hasActiveJobs.value) {
            fetchConversions();
        }
    }, 5000);
});

onUnmounted(() => {
    if (pollInterval) {
        clearInterval(pollInterval);
    }
});
</script>

<template>
    <Head title="MP3 to MV" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen p-6" style="background: linear-gradient(135deg, #1a1625 0%, #2d2640 100%);">
            <!-- Toast Notifications -->
            <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
                <TransitionGroup name="toast">
                    <div
                        v-for="toast in toasts"
                        :key="toast.id"
                        :class="[toastClass(toast.type), 'px-4 py-3 rounded-lg shadow-lg text-white flex items-center gap-2']"
                    >
                        <!-- Success Icon -->
                        <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <!-- Error Icon -->
                        <svg v-else-if="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <!-- Info Icon -->
                        <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>{{ toast.message }}</span>
                    </div>
                </TransitionGroup>
            </div>

            <!-- Header -->
            <div class="text-center mb-8">
                <div class="flex items-center justify-center gap-3 mb-2">
                    <!-- Music Icon -->
                    <svg class="w-10 h-10 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                    <h1 class="text-3xl font-bold text-white">MP3 to MV Converter</h1>
                    <!-- Video Icon -->
                    <svg class="w-10 h-10 text-[#FFD700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="text-gray-400">คณะวิทยาศาสตร์ มหาวิทยาลัยเชียงใหม่</p>
            </div>

            <!-- Upload Zone -->
            <div
                @dragover="handleDragOver"
                @dragleave="handleDragLeave"
                @drop="handleDrop"
                @click="triggerFileInput"
                :class="[
                    'relative mx-auto max-w-2xl cursor-pointer rounded-2xl border-2 border-dashed p-12 text-center transition-all duration-300',
                    isDragging
                        ? 'border-[#FFD700] bg-[#FFD700]/10 scale-105'
                        : 'border-[#9E76B4]/50 bg-[#9E76B4]/10 hover:border-[#9E76B4] hover:bg-[#9E76B4]/20',
                    isUploading ? 'pointer-events-none opacity-60' : ''
                ]"
                style="backdrop-filter: blur(10px);"
            >
                <input
                    type="file"
                    id="fileInput"
                    accept=".mp3"
                    @change="handleFileSelect"
                    class="hidden"
                />

                <!-- Upload Icon -->
                <div class="mb-4 flex justify-center">
                    <svg class="w-16 h-16 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                </div>

                <h3 class="text-xl font-semibold text-white mb-2">
                    {{ isDragging ? 'วางไฟล์ที่นี่!' : 'ลากไฟล์ MP3 มาวางที่นี่' }}
                </h3>
                <p class="text-gray-400 mb-4">หรือคลิกเพื่อเลือกไฟล์</p>

                <button
                    class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-black transition-all duration-300 hover:scale-105"
                    style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);"
                >
                    <!-- Upload Icon -->
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    เลือกไฟล์
                </button>

                <!-- Progress Bar -->
                <div v-if="isUploading" class="mt-6">
                    <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                        <div
                            class="h-full transition-all duration-300"
                            :style="{ width: uploadProgress + '%', background: 'linear-gradient(90deg, #9E76B4 0%, #FFD700 100%)' }"
                        />
                    </div>
                    <p class="text-[#FFD700] mt-2">กำลังอัปโหลด... {{ uploadProgress }}%</p>
                </div>
            </div>

            <!-- Clip Gallery -->
            <div class="mt-12 max-w-4xl mx-auto">
                <div class="flex items-center gap-2 mb-4">
                    <!-- Folder Icon -->
                    <svg class="w-6 h-6 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                    <h2 class="text-2xl font-bold text-white">คลังคลิปของคุณ</h2>
                </div>

                <!-- Loading -->
                <div v-if="isLoading" class="text-center py-12 text-gray-400">
                    <svg class="animate-spin w-8 h-8 mx-auto mb-2 text-[#9E76B4]" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                    </svg>
                    กำลังโหลด...
                </div>

                <!-- Empty State -->
                <div v-else-if="conversions.length === 0" class="text-center py-12">
                    <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                    <p class="text-gray-400">ยังไม่มีคลิป เริ่มอัปโหลด MP3 กันเลย!</p>
                </div>

                <!-- Conversion List -->
                <div v-else class="space-y-3">
                    <div
                        v-for="conversion in conversions"
                        :key="conversion.id"
                        class="flex items-center gap-4 p-4 rounded-xl transition-all duration-300 hover:scale-[1.01]"
                        style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(158, 118, 180, 0.2);"
                    >
                        <!-- File Icon -->
                        <div class="flex-shrink-0">
                            <svg v-if="conversion.status === 'completed'" class="w-10 h-10 text-[#FFD700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else-if="conversion.status === 'processing'" class="w-10 h-10 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            <svg v-else-if="conversion.status === 'failed'" class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <svg v-else class="w-10 h-10 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <!-- Info -->
                        <div class="flex-grow min-w-0">
                            <h4 class="text-white font-medium truncate">{{ conversion.original_filename }}</h4>
                            <p class="text-sm text-gray-400">{{ new Date(conversion.created_at).toLocaleString('th-TH') }}</p>
                        </div>

                        <!-- Status Badge -->
                        <div :class="[statusClass(conversion.status), 'px-3 py-1 rounded-full text-sm font-medium']">
                            {{ statusText(conversion.status) }}
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <!-- Download Button -->
                            <button
                                v-if="conversion.status === 'completed'"
                                @click="downloadFile(conversion.id)"
                                class="p-2 rounded-lg bg-[#286428] hover:bg-[#2d722d] text-white transition-colors"
                                title="ดาวน์โหลด"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                            </button>

                            <!-- Delete Button -->
                            <button
                                @click="deleteConversion(conversion.id)"
                                class="p-2 rounded-lg bg-red-600/20 hover:bg-red-600/40 text-red-400 transition-colors"
                                title="ลบ"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>
