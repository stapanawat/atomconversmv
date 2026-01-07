<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';

// Python Backend URL
const BACKEND_URL = 'http://192.168.1.123:5000';

// Types
interface Conversion {
    id: string;
    original_filename: string;
    status: 'pending' | 'processing' | 'completed' | 'failed';
    error_message?: string;
    created_at: string;
    mp4Url?: string;
    jobId?: string;
}

interface UploadSlot {
    id: number;
    conversion: Conversion | null;
    selectedFile: File | null;
    isUploading: boolean;
    uploadProgress: number;
    isDragging: boolean;
}

interface Toast {
    id: number;
    type: 'success' | 'error' | 'info' | 'warning';
    message: string;
}

// Constants
const MAX_SLOTS = 4;

// State
const slots = ref<UploadSlot[]>([createEmptySlot(1)]);
const completedConversions = ref<Conversion[]>([]);  // Separate storage for completed files
const toasts = ref<Toast[]>([]);
let pollInterval: ReturnType<typeof setInterval> | null = null;
let toastId = 0;
let slotIdCounter = 1;

// Create empty slot
function createEmptySlot(id?: number): UploadSlot {
    return {
        id: id || ++slotIdCounter,
        conversion: null,
        selectedFile: null,
        isUploading: false,
        uploadProgress: 0,
        isDragging: false,
    };
}

// Computed - limit to MAX_SLOTS total slots
const canAddSlot = computed(() => {
    return slots.value.length < MAX_SLOTS;
});

const hasActiveJobs = computed(() => {
    return slots.value.some(s => 
        s.conversion && (s.conversion.status === 'pending' || s.conversion.status === 'processing')
    );
});

// Toast functions
const showToast = (type: Toast['type'], message: string) => {
    const id = ++toastId;
    toasts.value.push({ id, type, message });
    setTimeout(() => {
        toasts.value = toasts.value.filter(t => t.id !== id);
    }, 5000);
};

// File selection (without auto-upload)
const selectFile = (slot: UploadSlot, file: File) => {
    if (!file.name.toLowerCase().endsWith('.mp3')) {
        showToast('error', 'กรุณาเลือกไฟล์ MP3 เท่านั้น');
        return;
    }
    slot.selectedFile = file;
};

// Cancel selected file
const cancelSelectedFile = (slot: UploadSlot) => {
    slot.selectedFile = null;
};

// Format file size
const formatFileSize = (bytes: number) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

// API functions - Mock upload (simulates success with mp4 same name)
const uploadFile = async (slot: UploadSlot) => {
    if (!slot.selectedFile) return;

    const file = slot.selectedFile;
    slot.isUploading = true;
    slot.uploadProgress = 0;
    slot.selectedFile = null;

    try {
        // Simulate upload progress
        for (let i = 0; i <= 100; i += 20) {
            slot.uploadProgress = i;
            await new Promise(resolve => setTimeout(resolve, 200));
        }

        // Generate MP4 filename (replace .mp3 with .mp4)
        const mp4Filename = file.name.replace(/\.mp3$/i, '.mp4');
        const mp4Url = `${BACKEND_URL}/files/mp4/${mp4Filename}`;

        // Create completed conversion record
        const conversion: Conversion = {
            id: `conv_${Date.now()}`,
            original_filename: file.name,
            status: 'completed',
            created_at: new Date().toISOString(),
            jobId: `job_${Date.now()}`,
            mp4Url: mp4Url,
        };

        // Move to completed list (at the beginning)
        completedConversions.value.unshift(conversion);
        saveCompletedToStorage();
        
        // Reset slot for reuse
        slot.conversion = null;
        slot.isUploading = false;
        slot.uploadProgress = 0;
        
        showToast('success', `"${file.name}" แปลงเสร็จแล้ว!`);
    } catch (error: any) {
        console.error('Upload error:', error);
        showToast('error', 'เกิดข้อผิดพลาด');
        slot.isUploading = false;
        slot.uploadProgress = 0;
    }
};

const checkAllStatuses = async () => {
    for (const slot of slots.value) {
        if (slot.conversion && (slot.conversion.status === 'pending' || slot.conversion.status === 'processing')) {
            try {
                const jobId = slot.conversion.jobId;
                if (!jobId) continue;

                // Poll Python backend for job status
                const response = await axios.get(`${BACKEND_URL}/status/${jobId}`);
                
                const data = response.data;
                const prevStatus: Conversion['status'] = slot.conversion.status;
                
                // Update status
                if (data.status) {
                    slot.conversion.status = data.status;
                }
                
                // If completed, store the mp4 URL
                if (data.mp4_url) {
                    slot.conversion.mp4Url = data.mp4_url;
                }
                
                // If failed, store error message
                if (data.error) {
                    slot.conversion.error_message = data.error;
                }
                
                // Show toast when status changes to completed/failed
                if (slot.conversion.status === 'completed') {
                    showToast('success', `"${slot.conversion.original_filename}" แปลงเสร็จแล้ว!`);
                } else if (slot.conversion.status === 'failed') {
                    showToast('error', `"${slot.conversion.original_filename}" แปลงล้มเหลว`);
                }
            } catch (error) {
                console.error('Error checking status:', error);
            }
        }
    }

    // Stop polling if no active jobs
    if (!hasActiveJobs.value) {
        stopPolling();
    }
};

const downloadFile = (slot: UploadSlot) => {
    if (!slot.conversion?.mp4Url) {
        showToast('error', 'ไม่พบไฟล์สำหรับดาวน์โหลด');
        return;
    }
    // Open Firebase download URL in new tab
    window.open(slot.conversion.mp4Url, '_blank');
};

const resetSlot = (slot: UploadSlot) => {
    // Clear from storage
    localStorage.removeItem(`slot_${slot.id}_id`);
    localStorage.removeItem(`slot_${slot.id}_jobId`);
    localStorage.removeItem(`slot_${slot.id}_filename`);
    localStorage.removeItem(`slot_${slot.id}_status`);
    localStorage.removeItem(`slot_${slot.id}_mp4Url`);
    
    // Reset slot to empty state
    slot.conversion = null;
    slot.selectedFile = null;
    slot.isUploading = false;
    slot.uploadProgress = 0;
};

const removeSlot = (slotId: number) => {
    // Clear storage
    localStorage.removeItem(`slot_${slotId}_id`);
    localStorage.removeItem(`slot_${slotId}_jobId`);
    localStorage.removeItem(`slot_${slotId}_filename`);
    localStorage.removeItem(`slot_${slotId}_status`);
    localStorage.removeItem(`slot_${slotId}_mp4Url`);
    
    // Remove slot
    slots.value = slots.value.filter(s => s.id !== slotId);
    
    // Ensure at least one slot exists
    if (slots.value.length === 0) {
        slots.value.push(createEmptySlot());
    }
};

const addNewSlot = () => {
    if (canAddSlot.value) {
        slots.value.push(createEmptySlot());
    }
};

// Save completed conversions to localStorage
const saveCompletedToStorage = () => {
    localStorage.setItem('completedConversions', JSON.stringify(completedConversions.value));
};

// Remove a completed conversion
const removeCompleted = (id: string) => {
    completedConversions.value = completedConversions.value.filter(c => c.id !== id);
    saveCompletedToStorage();
};

// Download a completed file
const downloadCompleted = (conversion: Conversion) => {
    if (conversion.mp4Url) {
        window.open(conversion.mp4Url, '_blank');
    } else {
        showToast('error', 'ไม่พบไฟล์สำหรับดาวน์โหลด');
    }
};

const saveSlotToStorage = (slot: UploadSlot) => {
    if (slot.conversion) {
        localStorage.setItem(`slot_${slot.id}_id`, slot.conversion.id);
        localStorage.setItem(`slot_${slot.id}_jobId`, slot.conversion.jobId || '');
        localStorage.setItem(`slot_${slot.id}_filename`, slot.conversion.original_filename);
        localStorage.setItem(`slot_${slot.id}_status`, slot.conversion.status);
        if (slot.conversion.mp4Url) {
            localStorage.setItem(`slot_${slot.id}_mp4Url`, slot.conversion.mp4Url);
        }
    }
};

const startPolling = () => {
    if (pollInterval) return;
    pollInterval = setInterval(checkAllStatuses, 3000);
};

const stopPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

// Drag & Drop handlers
const handleDragOver = (e: DragEvent, slot: UploadSlot) => {
    e.preventDefault();
    slot.isDragging = true;
};

const handleDragLeave = (e: DragEvent, slot: UploadSlot) => {
    e.preventDefault();
    slot.isDragging = false;
};

const handleDrop = (e: DragEvent, slot: UploadSlot) => {
    e.preventDefault();
    slot.isDragging = false;
    const files = e.dataTransfer?.files;
    if (files && files.length > 0) {
        selectFile(slot, files[0]);  // Select file, don't upload yet
    }
};

const handleFileSelect = (e: Event, slot: UploadSlot) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        selectFile(slot, target.files[0]);  // Select file, don't upload yet
        target.value = ''; // Reset input
    }
};

const triggerFileInput = (slotId: number) => {
    document.getElementById(`fileInput_${slotId}`)?.click();
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
        pending: 'bg-yellow-500/20 text-yellow-400 border-yellow-500/50',
        processing: 'bg-blue-500/20 text-blue-400 border-blue-500/50',
        completed: 'bg-green-500/20 text-green-400 border-green-500/50',
        failed: 'bg-red-500/20 text-red-400 border-red-500/50',
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

const isSlotEmpty = (slot: UploadSlot) => {
    return !slot.conversion && !slot.isUploading && !slot.selectedFile;
};

const isSlotFinished = (slot: UploadSlot) => {
    return slot.conversion && ['completed', 'failed'].includes(slot.conversion.status);
};

// Lifecycle - restore from localStorage
onMounted(() => {
    // Restore completed conversions
    const savedCompleted = localStorage.getItem('completedConversions');
    if (savedCompleted) {
        try {
            completedConversions.value = JSON.parse(savedCompleted);
        } catch (e) {
            console.error('Error parsing completedConversions:', e);
        }
    }
    
    // Check for any saved active slots
    const restoredSlots: UploadSlot[] = [];
    
    for (let i = 1; i <= MAX_SLOTS + 10; i++) {
        const savedId = localStorage.getItem(`slot_${i}_id`);
        const savedJobId = localStorage.getItem(`slot_${i}_jobId`);
        const savedFilename = localStorage.getItem(`slot_${i}_filename`);
        const savedStatus = localStorage.getItem(`slot_${i}_status`) as Conversion['status'] | null;
        const savedMp4Url = localStorage.getItem(`slot_${i}_mp4Url`);
        
        // Only restore non-completed slots (completed ones are in completedConversions)
        if (savedId && savedFilename && savedStatus && savedStatus !== 'completed') {
            const slot = createEmptySlot(i);
            slotIdCounter = Math.max(slotIdCounter, i);
            
            slot.conversion = {
                id: savedId,
                original_filename: savedFilename,
                status: savedStatus,
                created_at: new Date().toISOString(),
                jobId: savedJobId || undefined,
                mp4Url: savedMp4Url || undefined,
            };
            
            restoredSlots.push(slot);
        }
    }
    
    if (restoredSlots.length > 0) {
        slots.value = restoredSlots;
        if (hasActiveJobs.value) {
            startPolling();
            checkAllStatuses();
        }
    }
});

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <Head title="MP3 to MV Converter - คณะวิทยาศาสตร์ มช." />

    <div class="min-h-screen" style="background: linear-gradient(135deg, #1a1625 0%, #2d2640 50%, #1a1625 100%);">
        <!-- Toast Notifications -->
        <div class="fixed top-4 right-4 z-50 flex flex-col gap-2">
            <TransitionGroup name="toast">
                <div
                    v-for="toast in toasts"
                    :key="toast.id"
                    :class="[toastClass(toast.type), 'px-4 py-3 rounded-lg shadow-lg text-white flex items-center gap-2']"
                >
                    <svg v-if="toast.type === 'success'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else-if="toast.type === 'error'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ toast.message }}</span>
                </div>
            </TransitionGroup>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-12">
            <!-- Header -->
            <div class="text-center mb-12">
                <div class="flex items-center justify-center gap-4 mb-4">
                    <svg class="w-12 h-12 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                    <h1 class="text-4xl md:text-5xl font-bold text-white">MP3 to MV</h1>
                    <svg class="w-12 h-12 text-[#FFD700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </div>
                <p class="text-xl text-gray-300">แปลงไฟล์เสียงเป็น Music Video</p>
                <p class="text-[#9E76B4] mt-2">คณะวิทยาศาสตร์ มหาวิทยาลัยเชียงใหม่</p>
                <p class="text-gray-400 mt-2 text-sm">อัปโหลดได้สูงสุด {{ MAX_SLOTS }} ไฟล์พร้อมกัน</p>
            </div>

            <!-- Upload Slots Grid -->
            <div class="max-w-4xl mx-auto">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Slot Cards -->
                    <TransitionGroup name="slot">
                        <div
                            v-for="slot in slots"
                            :key="slot.id"
                            class="relative"
                        >
                            <!-- Empty Slot - Upload Zone -->
                            <div
                                v-if="isSlotEmpty(slot)"
                                @dragover="handleDragOver($event, slot)"
                                @dragleave="handleDragLeave($event, slot)"
                                @drop="handleDrop($event, slot)"
                                @click="triggerFileInput(slot.id)"
                                :class="[
                                    'relative cursor-pointer rounded-2xl border-2 border-dashed p-8 text-center transition-all duration-300 min-h-[200px] flex flex-col justify-center',
                                    slot.isDragging
                                        ? 'border-[#FFD700] bg-[#FFD700]/10 scale-105'
                                        : 'border-[#9E76B4]/50 bg-[#9E76B4]/5 hover:border-[#9E76B4] hover:bg-[#9E76B4]/10',
                                ]"
                                style="backdrop-filter: blur(20px);"
                            >
                                <input
                                    type="file"
                                    :id="`fileInput_${slot.id}`"
                                    accept=".mp3"
                                    @change="handleFileSelect($event, slot)"
                                    class="hidden"
                                />

                                <!-- Remove Slot Button (if more than 1 slot) -->
                                <button
                                    v-if="slots.length > 1"
                                    @click.stop="removeSlot(slot.id)"
                                    class="absolute top-2 right-2 p-1 rounded-full bg-red-500/20 hover:bg-red-500/40 text-red-400 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <div class="mb-4 flex justify-center">
                                    <div class="p-4 rounded-full bg-[#9E76B4]/20">
                                        <svg class="w-10 h-10 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                        </svg>
                                    </div>
                                </div>

                                <h3 class="text-lg font-semibold text-white mb-2">
                                    {{ slot.isDragging ? 'วางไฟล์ที่นี่!' : 'ลากไฟล์ MP3 มาวาง' }}
                                </h3>
                                <p class="text-gray-400 text-sm">หรือคลิกเพื่อเลือกไฟล์</p>
                            </div>

                            <!-- File Selected - Waiting for Upload Confirmation -->
                            <div
                                v-else-if="slot.selectedFile && !slot.isUploading"
                                class="rounded-2xl p-6 min-h-[200px]"
                                style="background: rgba(255, 215, 0, 0.1); backdrop-filter: blur(20px); border: 2px solid rgba(255, 215, 0, 0.4);"
                            >
                                <!-- Remove Slot Button -->
                                <button
                                    v-if="slots.length > 1"
                                    @click.stop="removeSlot(slot.id)"
                                    class="absolute top-2 right-2 p-1 rounded-full bg-red-500/20 hover:bg-red-500/40 text-red-400 transition-colors"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>

                                <!-- File Info -->
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-3 rounded-xl bg-[#FFD700]/20 flex-shrink-0">
                                        <svg class="w-8 h-8 text-[#FFD700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-grow">
                                        <h3 class="text-white font-medium truncate">{{ slot.selectedFile.name }}</h3>
                                        <p class="text-gray-400 text-sm">{{ formatFileSize(slot.selectedFile.size) }}</p>
                                    </div>
                                </div>

                                <!-- Ready to Upload Badge -->
                                <div class="mb-4">
                                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full border bg-[#FFD700]/20 text-[#FFD700] border-[#FFD700]/50 text-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-medium">พร้อมอัปโหลด</span>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-3">
                                    <button
                                        @click="uploadFile(slot)"
                                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl font-bold text-black transition-all duration-300 hover:scale-105"
                                        style="background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                        </svg>
                                        อัปโหลด
                                    </button>
                                    <button
                                        @click="cancelSelectedFile(slot)"
                                        class="px-4 py-3 rounded-xl font-bold text-red-400 transition-all duration-300 hover:bg-red-500/20"
                                        style="border: 2px solid rgba(239, 68, 68, 0.5);"
                                        title="ยกเลิก"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Uploading State -->
                            <div
                                v-else-if="slot.isUploading"
                                class="rounded-2xl p-8 min-h-[200px] flex flex-col justify-center"
                                style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(158, 118, 180, 0.3);"
                            >
                                <div class="text-center">
                                    <div class="mb-4 flex justify-center">
                                        <div class="p-4 rounded-full bg-[#FFD700]/20">
                                            <svg class="w-10 h-10 text-[#FFD700] animate-bounce" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="h-2 bg-gray-700 rounded-full overflow-hidden mb-2">
                                        <div
                                            class="h-full transition-all duration-300"
                                            :style="{ width: slot.uploadProgress + '%', background: 'linear-gradient(90deg, #9E76B4 0%, #FFD700 100%)' }"
                                        />
                                    </div>
                                    <p class="text-[#FFD700]">กำลังอัปโหลด... {{ slot.uploadProgress }}%</p>
                                </div>
                            </div>

                            <!-- Has Conversion State -->
                            <div
                                v-else-if="slot.conversion"
                                class="rounded-2xl p-6 min-h-[200px]"
                                style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(158, 118, 180, 0.2);"
                            >
                                <!-- File Info -->
                                <div class="flex items-center gap-3 mb-4">
                                    <div class="p-3 rounded-xl flex-shrink-0" :class="slot.conversion.status === 'completed' ? 'bg-green-500/20' : 'bg-[#9E76B4]/20'">
                                        <!-- Video icon when completed -->
                                        <svg v-if="slot.conversion.status === 'completed'" class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                        <!-- Music icon for other states -->
                                        <svg v-else class="w-6 h-6 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0 flex-grow">
                                        <!-- Show MP4 filename when completed, otherwise original filename -->
                                        <h3 class="text-white font-medium truncate text-sm">
                                            {{ slot.conversion.status === 'completed' 
                                                ? slot.conversion.original_filename.replace(/\.mp3$/i, '.mp4')
                                                : slot.conversion.original_filename }}
                                        </h3>
                                        <p class="text-gray-400 text-xs">{{ new Date(slot.conversion.created_at).toLocaleString('th-TH') }}</p>
                                    </div>
                                </div>

                                <!-- Status Badge -->
                                <div class="mb-4">
                                    <div :class="[statusClass(slot.conversion.status), 'inline-flex items-center gap-2 px-3 py-1.5 rounded-full border text-sm']">
                                        <svg v-if="slot.conversion.status === 'pending'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <svg v-else-if="slot.conversion.status === 'processing'" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                                        </svg>
                                        <svg v-else-if="slot.conversion.status === 'completed'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span class="font-medium">{{ statusText(slot.conversion.status) }}</span>
                                    </div>
                                </div>

                                <!-- Progress Animation for pending/processing -->
                                <div v-if="slot.conversion.status === 'pending' || slot.conversion.status === 'processing'" class="mb-4">
                                    <div class="h-1.5 bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full w-full" style="background: linear-gradient(90deg, #9E76B4 0%, #FFD700 50%, #9E76B4 100%); background-size: 200% 100%; animation: shimmer 2s infinite;"></div>
                                    </div>
                                </div>

                                <!-- Error Message -->
                                <div v-if="slot.conversion.status === 'failed' && slot.conversion.error_message" class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/30">
                                    <p class="text-red-400 text-sm">{{ slot.conversion.error_message }}</p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex gap-2">
                                    <button
                                        v-if="slot.conversion.status === 'completed'"
                                        @click="downloadFile(slot)"
                                        class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl font-bold text-sm text-white transition-all duration-300 hover:scale-105"
                                        style="background: linear-gradient(135deg, #286428 0%, #3d9d3d 100%);"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                        ดาวน์โหลด
                                    </button>
                                    
                                    <!-- Reset slot (for completed/failed) -->
                                    <button
                                        v-if="isSlotFinished(slot)"
                                        @click="resetSlot(slot)"
                                        class="px-4 py-2.5 rounded-xl font-bold text-white transition-all duration-300 hover:bg-[#9E76B4]/20"
                                        style="border: 2px solid rgba(158, 118, 180, 0.5);"
                                        title="อัปโหลดไฟล์ใหม่"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </TransitionGroup>

                    <!-- Add New Slot Button -->
                    <div
                        v-if="canAddSlot"
                        @click="addNewSlot"
                        class="cursor-pointer rounded-2xl border-2 border-dashed border-[#FFD700]/30 p-8 text-center transition-all duration-300 min-h-[200px] flex flex-col justify-center items-center hover:border-[#FFD700] hover:bg-[#FFD700]/5"
                    >
                        <div class="p-4 rounded-full bg-[#FFD700]/20 mb-4">
                            <svg class="w-10 h-10 text-[#FFD700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-[#FFD700]">เพิ่มไฟล์ใหม่</h3>
                        <p class="text-gray-400 text-sm mt-1">{{ slots.length }}/{{ MAX_SLOTS }} slots</p>
                    </div>
                </div>
            </div>

            <!-- Completed Files Section -->
            <div v-if="completedConversions.length > 0" class="max-w-4xl mx-auto mt-8">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <h2 class="text-xl font-semibold text-white">ไฟล์ที่แปลงเสร็จแล้ว ({{ completedConversions.length }})</h2>
                </div>
                
                <div class="space-y-3">
                    <TransitionGroup name="slot">
                        <div
                            v-for="conversion in completedConversions"
                            :key="conversion.id"
                            class="relative flex items-center gap-4 p-4 rounded-xl"
                            style="background: rgba(34, 197, 94, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(34, 197, 94, 0.3);"
                        >
                            <!-- Video Icon -->
                            <div class="p-3 rounded-xl bg-green-500/20 flex-shrink-0">
                                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </div>
                            
                            <!-- File Info -->
                            <div class="flex-grow min-w-0">
                                <h3 class="text-white font-medium truncate">
                                    {{ conversion.original_filename.replace(/\.mp3$/i, '.mp4') }}
                                </h3>
                                <p class="text-gray-400 text-xs">{{ new Date(conversion.created_at).toLocaleString('th-TH') }}</p>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <button
                                    @click="downloadCompleted(conversion)"
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors flex items-center gap-2"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    ดาวน์โหลด
                                </button>
                                <button
                                    @click="removeCompleted(conversion.id)"
                                    class="p-2 text-gray-400 hover:text-red-400 transition-colors"
                                    title="ลบออก"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </TransitionGroup>
                </div>
            </div>

            <!-- Info Cards -->
            <div class="max-w-4xl mx-auto mt-12">
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="p-6 rounded-2xl text-center" style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(158, 118, 180, 0.2);">
                        <div class="w-12 h-12 mx-auto mb-4 flex items-center justify-center rounded-full bg-[#9E76B4]/20">
                            <svg class="w-6 h-6 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                            </svg>
                        </div>
                        <h4 class="text-white font-semibold mb-2">อัปโหลด MP3</h4>
                        <p class="text-gray-400 text-sm">เลือกไฟล์เสียงที่ต้องการแปลง</p>
                    </div>
                    <div class="p-6 rounded-2xl text-center" style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(158, 118, 180, 0.2);">
                        <div class="w-12 h-12 mx-auto mb-4 flex items-center justify-center rounded-full bg-[#FFD700]/20">
                            <svg class="w-6 h-6 text-[#FFD700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </div>
                        <h4 class="text-white font-semibold mb-2">รอประมวลผล</h4>
                        <p class="text-gray-400 text-sm">ระบบจะแปลงเป็นวิดีโอให้อัตโนมัติ</p>
                    </div>
                    <div class="p-6 rounded-2xl text-center" style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(158, 118, 180, 0.2);">
                        <div class="w-12 h-12 mx-auto mb-4 flex items-center justify-center rounded-full bg-[#286428]/30">
                            <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                        </div>
                        <h4 class="text-white font-semibold mb-2">ดาวน์โหลด MP4</h4>
                        <p class="text-gray-400 text-sm">รับไฟล์วิดีโอที่แปลงเสร็จ</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center mt-16 text-gray-500 text-sm">
                <p>© 2026 คณะวิทยาศาสตร์ มหาวิทยาลัยเชียงใหม่</p>
            </div>
        </div>
    </div>
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

.slot-enter-active,
.slot-leave-active {
    transition: all 0.3s ease;
}

.slot-enter-from {
    opacity: 0;
    transform: scale(0.9);
}

.slot-leave-to {
    opacity: 0;
    transform: scale(0.9);
}

@keyframes shimmer {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>
