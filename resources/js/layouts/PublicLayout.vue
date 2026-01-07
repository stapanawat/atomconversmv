<script setup lang="ts">
import PublicNavbar from '@/components/PublicNavbar.vue';
import PublicFooter from '@/components/PublicFooter.vue';
</script>

<template>
    <div class="min-h-screen flex flex-col" style="background: linear-gradient(135deg, #1a1625 0%, #2d2640 50%, #1a1625 100%);">
        <PublicNavbar />
        
        <main class="flex-grow pt-16 md:pt-20">
            <slot />
        </main>
        
        <PublicFooter />
    </div>
</template>
