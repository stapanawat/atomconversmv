<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="เข้าสู่ระบบ"
        description="กรอกอีเมลและรหัสผ่านเพื่อเข้าใช้งาน"
    >
        <Head title="เข้าสู่ระบบ" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-400 bg-green-500/10 rounded-xl px-4 py-3"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="text-gray-300">อีเมล</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="bg-white/5 border-[#9E76B4]/30 text-white placeholder:text-gray-500 focus:border-[#FFD700] focus:ring-[#FFD700]/30"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-gray-300">รหัสผ่าน</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm text-[#9E76B4] hover:text-[#FFD700]"
                            :tabindex="5"
                        >
                            ลืมรหัสผ่าน?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="bg-white/5 border-[#9E76B4]/30 text-white placeholder:text-gray-500 focus:border-[#FFD700] focus:ring-[#FFD700]/30"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center space-x-3 text-gray-300">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span>จดจำฉันไว้</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full bg-gradient-to-r from-[#FFD700] to-[#FFA500] text-black font-bold hover:shadow-lg hover:shadow-[#FFD700]/30 hover:scale-[1.02] transition-all duration-300"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    เข้าสู่ระบบ
                </Button>
            </div>

            <div
                class="text-center text-sm text-gray-400"
                v-if="canRegister"
            >
                ยังไม่มีบัญชี?
                <TextLink :href="register()" :tabindex="5" class="text-[#FFD700] hover:text-[#FFA500]">
                    สมัครสมาชิก
                </TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
