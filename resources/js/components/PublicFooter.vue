<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();
</script>

<template>
    <footer class="relative overflow-hidden">
        <!-- Gradient Top Border -->
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[#9E76B4] to-transparent"></div>
        
        <div class="bg-[#0d0b12] py-12">
            <div class="container mx-auto px-4">
                <div class="grid md:grid-cols-4 gap-8">
                    <!-- Brand -->
                    <div class="md:col-span-2">
                        <Link href="/" class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#9E76B4] to-[#FFD700] flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                                </svg>
                            </div>
                            <span class="text-xl font-bold text-white">
                                MP3<span class="text-[#FFD700]">to</span>MV
                            </span>
                        </Link>
                        <p class="text-gray-400 max-w-md">
                            แปลงไฟล์เสียง MP3 ของคุณให้กลายเป็น Music Video สวยงาม 
                            พร้อมภาพ Visualization ที่ตอบสนองกับจังหวะเพลง
                        </p>
                        <div class="flex items-center gap-4 mt-6">
                            <span class="text-[#9E76B4] text-sm">Powered by</span>
                            <span class="text-white font-medium">คณะวิทยาศาสตร์ มหาวิทยาลัยเชียงใหม่</span>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div>
                        <h4 class="text-white font-semibold mb-4">ลิงก์ด่วน</h4>
                        <ul class="space-y-3">
                            <li>
                                <Link href="/" class="text-gray-400 hover:text-[#FFD700] transition-colors duration-200">
                                    หน้าหลัก
                                </Link>
                            </li>
                            <li>
                                <Link href="/convert" class="text-gray-400 hover:text-[#FFD700] transition-colors duration-200">
                                    แปลงไฟล์
                                </Link>
                            </li>
                            <li>
                                <Link href="/login" class="text-gray-400 hover:text-[#FFD700] transition-colors duration-200">
                                    เข้าสู่ระบบ
                                </Link>
                            </li>
                            <li>
                                <Link href="/register" class="text-gray-400 hover:text-[#FFD700] transition-colors duration-200">
                                    สมัครสมาชิก
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h4 class="text-white font-semibold mb-4">ติดต่อ</h4>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-gray-400">
                                <svg class="w-5 h-5 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>239 ถ.ห้วยแก้ว อ.เมือง จ.เชียงใหม่</span>
                            </li>
                            <li class="flex items-center gap-3 text-gray-400">
                                <svg class="w-5 h-5 text-[#9E76B4]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span>contact@sci.cmu.ac.th</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Bottom Bar -->
                <div class="mt-12 pt-8 border-t border-gray-800">
                    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                        <p class="text-gray-500 text-sm">
                            © {{ currentYear }} MP3toMV - คณะวิทยาศาสตร์ มหาวิทยาลัยเชียงใหม่. All rights reserved.
                        </p>
                        <div class="flex items-center gap-6">
                            <a href="#" class="text-gray-500 hover:text-[#9E76B4] transition-colors text-sm">
                                นโยบายความเป็นส่วนตัว
                            </a>
                            <a href="#" class="text-gray-500 hover:text-[#9E76B4] transition-colors text-sm">
                                ข้อกำหนดการใช้งาน
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>
