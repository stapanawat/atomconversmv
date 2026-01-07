<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/layouts/PublicLayout.vue';
import { ref, onMounted, onUnmounted } from 'vue';

// Floating animation offset
const floatOffset = ref(0);
let animationFrame: number;

const animate = () => {
    floatOffset.value = Math.sin(Date.now() / 1000) * 10;
    animationFrame = requestAnimationFrame(animate);
};

onMounted(() => {
    animate();
});

onUnmounted(() => {
    cancelAnimationFrame(animationFrame);
});

// Features data
const features = [
    {
        icon: 'M13 10V3L4 14h7v7l9-11h-7z',
        title: 'แปลงไว',
        description: 'ใช้เวลาเพียงไม่กี่นาทีในการแปลงไฟล์เสียงเป็นวิดีโอคุณภาพสูง',
        gradient: 'from-yellow-500 to-orange-500'
    },
    {
        icon: 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
        title: 'ภาพสวยงาม',
        description: 'Audio Visualization ที่ตอบสนองกับจังหวะเพลงอย่างสมจริง',
        gradient: 'from-purple-500 to-pink-500'
    },
    {
        icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
        title: 'ใช้ง่าย',
        description: 'เพียงอัปโหลดไฟล์ MP3 แล้วรอรับวิดีโอได้เลย ไม่ต้องติดตั้งโปรแกรม',
        gradient: 'from-green-500 to-teal-500'
    }
];

// Steps data
const steps = [
    {
        number: '01',
        title: 'อัปโหลดไฟล์ MP3',
        description: 'เลือกไฟล์เสียงที่ต้องการแปลง',
        icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12'
    },
    {
        number: '02',
        title: 'รอการประมวลผล',
        description: 'ระบบจะแปลงเป็นวิดีโอให้อัตโนมัติ',
        icon: 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15'
    },
    {
        number: '03',
        title: 'ดาวน์โหลด MP4',
        description: 'รับไฟล์วิดีโอพร้อมใช้งาน',
        icon: 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4'
    }
];
</script>

<template>
    <Head title="MP3 to MV Converter - แปลงเสียงเป็นวิดีโอ" />
    
    <PublicLayout>
        <!-- Hero Section -->
        <section class="relative min-h-[90vh] flex items-center justify-center overflow-hidden">
            <!-- Background Effects -->
            <div class="absolute inset-0">
                <!-- Gradient Orbs -->
                <div 
                    class="absolute top-1/4 left-1/4 w-96 h-96 bg-[#9E76B4]/30 rounded-full blur-3xl"
                    :style="{ transform: `translateY(${floatOffset}px)` }"
                ></div>
                <div 
                    class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-[#FFD700]/20 rounded-full blur-3xl"
                    :style="{ transform: `translateY(${-floatOffset}px)` }"
                ></div>
                
                <!-- Grid Pattern -->
                <div class="absolute inset-0 opacity-10" style="background-image: url('data:image/svg+xml,%3Csvg width=&quot;60&quot; height=&quot;60&quot; viewBox=&quot;0 0 60 60&quot; xmlns=&quot;http://www.w3.org/2000/svg&quot;%3E%3Cpath d=&quot;M0 0h60v60H0z&quot; fill=&quot;none&quot; stroke=&quot;%239E76B4&quot; stroke-width=&quot;0.5&quot;/%3E%3C/svg%3E');"></div>
            </div>

            <div class="container mx-auto px-4 text-center relative z-10">
                <!-- Badge -->
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[#9E76B4]/20 border border-[#9E76B4]/30 mb-8">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    <span class="text-[#9E76B4] text-sm font-medium">ฟรี! ไม่ต้องสมัครก็ใช้ได้</span>
                </div>

                <!-- Main Title -->
                <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6">
                    แปลงเสียง<span class="text-[#9E76B4]">สุดโปรด</span>
                    <br>
                    เป็น<span class="text-[#FFD700]">มิวสิกวิดีโอ</span>
                </h1>

                <p class="text-lg md:text-xl text-gray-400 max-w-2xl mx-auto mb-10">
                    เปลี่ยนไฟล์ MP3 ของคุณให้กลายเป็น Music Video สวยงาม
                    พร้อม Audio Visualization ที่เคลื่อนไหวตามจังหวะเพลง
                </p>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-16">
                    <Link
                        href="/convert"
                        class="group px-8 py-4 rounded-2xl font-bold text-lg text-black bg-gradient-to-r from-[#FFD700] to-[#FFA500] hover:shadow-lg hover:shadow-[#FFD700]/40 hover:scale-105 transition-all duration-300 flex items-center gap-3"
                    >
                        <span>เริ่มแปลงไฟล์เลย</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    <Link
                        href="/register"
                        class="px-8 py-4 rounded-2xl font-semibold text-white border-2 border-[#9E76B4]/50 hover:bg-[#9E76B4]/20 hover:border-[#9E76B4] transition-all duration-300"
                    >
                        สมัครสมาชิกฟรี
                    </Link>
                </div>

                <!-- Preview Image/Animation -->
                <div class="relative max-w-4xl mx-auto">
                    <div class="relative rounded-2xl overflow-hidden border border-[#9E76B4]/30 shadow-2xl shadow-purple-900/30" style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(20px);">
                        <div class="p-8">
                            <!-- Mock Audio Waveform -->
                            <div class="flex items-end justify-center gap-1 h-32">
                                <div v-for="i in 40" :key="i" 
                                    class="w-2 bg-gradient-to-t from-[#9E76B4] to-[#FFD700] rounded-full"
                                    :style="{ 
                                        height: `${20 + Math.sin((i + floatOffset/5) * 0.5) * 50 + Math.random() * 30}%`,
                                        opacity: 0.5 + Math.sin(i * 0.2) * 0.5
                                    }"
                                ></div>
                            </div>
                            <p class="text-gray-400 mt-4 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                                Audio Visualization Preview
                            </p>
                        </div>
                    </div>
                    
                    <!-- Floating Elements -->
                    <div class="absolute -top-4 -right-4 w-16 h-16 rounded-xl bg-[#FFD700]/20 border border-[#FFD700]/30 flex items-center justify-center rotate-12" :style="{ transform: `translateY(${floatOffset/2}px) rotate(12deg)` }">
                        <!-- Music Note -->
                        <svg class="w-8 h-8 text-[#FFD700]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                        </svg>
                    </div>
                    <div class="absolute -bottom-4 -left-4 w-16 h-16 rounded-xl bg-[#9E76B4]/20 border border-[#9E76B4]/30 flex items-center justify-center -rotate-12" :style="{ transform: `translateY(${-floatOffset/2}px) rotate(-12deg)` }">
                        <!-- Movie Icon -->
                        <svg class="w-8 h-8 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section class="py-24 relative">
            <div class="container mx-auto px-4">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                        ทำไมต้องเลือก <span class="text-[#FFD700]">MP3toMV</span>
                    </h2>
                    <p class="text-gray-400 max-w-xl mx-auto">
                        เราทำให้การแปลงไฟล์เสียงเป็นวิดีโอง่ายและรวดเร็วที่สุด
                    </p>
                </div>

                <div class="grid md:grid-cols-3 gap-8">
                    <div 
                        v-for="(feature, index) in features" 
                        :key="index"
                        class="group p-8 rounded-3xl transition-all duration-300 hover:scale-105 hover:-translate-y-2"
                        style="background: rgba(158, 118, 180, 0.1); backdrop-filter: blur(20px); border: 1px solid rgba(158, 118, 180, 0.2);"
                    >
                        <div :class="['w-16 h-16 rounded-2xl flex items-center justify-center mb-6 bg-gradient-to-br', feature.gradient, 'group-hover:scale-110 transition-transform duration-300']">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3">{{ feature.title }}</h3>
                        <p class="text-gray-400">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- How It Works Section -->
        <section class="py-24 relative">
            <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#9E76B4]/5 to-transparent"></div>
            
            <div class="container mx-auto px-4 relative z-10">
                <div class="text-center mb-16">
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                        วิธีใช้งาน <span class="text-[#9E76B4]">ง่ายมาก!</span>
                    </h2>
                    <p class="text-gray-400">เพียง 3 ขั้นตอนก็ได้วิดีโอสวยๆ</p>
                </div>

                <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div 
                        v-for="(step, index) in steps" 
                        :key="index"
                        class="relative text-center"
                    >
                        <!-- Connector Line -->
                        <div v-if="index < 2" class="hidden md:block absolute top-12 left-[60%] w-full h-px bg-gradient-to-r from-[#9E76B4] to-transparent"></div>
                        
                        <!-- Step Number -->
                        <div class="w-24 h-24 mx-auto mb-6 rounded-full flex items-center justify-center relative" style="background: rgba(158, 118, 180, 0.2); border: 2px solid rgba(158, 118, 180, 0.5);">
                            <span class="text-2xl font-bold text-[#FFD700]">{{ step.number }}</span>
                            <div class="absolute inset-0 rounded-full bg-[#9E76B4]/20 blur-xl"></div>
                        </div>
                        
                        <!-- Icon -->
                        <div class="w-12 h-12 mx-auto mb-4 rounded-xl bg-[#9E76B4]/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="step.icon" />
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-white mb-2">{{ step.title }}</h3>
                        <p class="text-gray-400">{{ step.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="py-24 relative">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto text-center p-12 rounded-3xl relative overflow-hidden" style="background: linear-gradient(135deg, rgba(158, 118, 180, 0.2) 0%, rgba(255, 215, 0, 0.1) 100%); backdrop-filter: blur(20px); border: 1px solid rgba(158, 118, 180, 0.3);">
                    <!-- Decorative Elements -->
                    <div class="absolute top-0 right-0 w-40 h-40 bg-[#FFD700]/20 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-40 h-40 bg-[#9E76B4]/30 rounded-full blur-3xl"></div>
                    
                    <h2 class="text-3xl md:text-4xl font-bold text-white mb-4 relative z-10">
                        พร้อมแปลงเพลงโปรดของคุณยัง?
                    </h2>
                    <p class="text-gray-300 mb-8 relative z-10">
                        เริ่มต้นใช้งานได้ฟรีเลยวันนี้ ไม่ต้องสมัครสมาชิกก็ใช้ได้
                    </p>
                    
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative z-10">
                        <Link
                            href="/convert"
                            class="px-8 py-4 rounded-2xl font-bold text-lg text-black bg-gradient-to-r from-[#FFD700] to-[#FFA500] hover:shadow-lg hover:shadow-[#FFD700]/40 hover:scale-105 transition-all duration-300 flex items-center gap-2"
                        >
                            <!-- Rocket Icon -->
                            
                             เริ่มแปลงไฟล์เลย
                        </Link>
                        <Link
                            href="/login"
                            class="px-8 py-4 rounded-2xl font-semibold text-white hover:text-[#FFD700] transition-colors"
                        >
                            มีบัญชีแล้ว? เข้าสู่ระบบ →
                        </Link>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>

<style scoped>
@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}
</style>
