<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
</script>

<template>
    <AuthBase
        title="สมัครสมาชิก"
        description="กรอกข้อมูลด้านล่างเพื่อสร้างบัญชีใหม่"
    >
        <Head title="สมัครสมาชิก" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-5">
                <div class="grid gap-2">
                    <Label for="name" class="text-gray-300">ชื่อ-นามสกุล</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="ชื่อของคุณ"
                        class="bg-white/5 border-[#9E76B4]/30 text-white placeholder:text-gray-500 focus:border-[#FFD700] focus:ring-[#FFD700]/30"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email" class="text-gray-300">อีเมล</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                        class="bg-white/5 border-[#9E76B4]/30 text-white placeholder:text-gray-500 focus:border-[#FFD700] focus:ring-[#FFD700]/30"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label for="password" class="text-gray-300">รหัสผ่าน</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="••••••••"
                        class="bg-white/5 border-[#9E76B4]/30 text-white placeholder:text-gray-500 focus:border-[#FFD700] focus:ring-[#FFD700]/30"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label for="password_confirmation" class="text-gray-300">ยืนยันรหัสผ่าน</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="••••••••"
                        class="bg-white/5 border-[#9E76B4]/30 text-white placeholder:text-gray-500 focus:border-[#FFD700] focus:ring-[#FFD700]/30"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full bg-gradient-to-r from-[#FFD700] to-[#FFA500] text-black font-bold hover:shadow-lg hover:shadow-[#FFD700]/30 hover:scale-[1.02] transition-all duration-300"
                    tabindex="5"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <Spinner v-if="processing" />
                    สร้างบัญชี
                </Button>
            </div>

            <div class="text-center text-sm text-gray-400">
                มีบัญชีอยู่แล้ว?
                <TextLink
                    :href="login()"
                    :tabindex="6"
                    class="text-[#FFD700] hover:text-[#FFA500]"
                >
                    เข้าสู่ระบบ
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
