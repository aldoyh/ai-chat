<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'

import { Head, Link, useForm } from '@inertiajs/vue3'
import DeleteUser from '@/components/DeleteUser.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useAuth } from '@/composables/useAuth'
import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'

interface Props {
  mustVerifyEmail: boolean
  status?: string
}

defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'إعدادات الملف الشخصي',
    href: '/settings/profile',
  },
]

const { user } = useAuth()

const form = useForm({
  name: user.value?.name,
  email: user.value?.email,
})

function submit() {
  form.patch(route('profile.update'), {
    preserveScroll: true,
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Head title="إعدادات الملف الشخصي" />

    <SettingsLayout>
      <div class="flex flex-col space-y-6">
        <HeadingSmall
          title="معلومات الملف الشخصي"
          description="حدّث اسمك وعنوان بريدك الإلكتروني"
        />

        <form class="space-y-6" @submit.prevent="submit">
          <div class="grid gap-2">
            <Label for="name">الاسم</Label>
            <Input
              id="name"
              v-model="form.name"
              class="mt-1 block w-full"
              required
              autocomplete="name"
              placeholder="الاسم الكامل"
            />
            <InputError class="mt-2" :message="form.errors.name" />
          </div>

          <div class="grid gap-2">
            <Label for="email">البريد الإلكتروني</Label>
            <Input
              id="email"
              v-model="form.email"
              type="email"
              class="mt-1 block w-full"
              required
              autocomplete="username"
              placeholder="البريد الإلكتروني"
            />
            <InputError class="mt-2" :message="form.errors.email" />
          </div>

          <div v-if="mustVerifyEmail && !user.email_verified_at">
            <p class="-mt-4 text-sm text-muted-foreground">
              عنوان بريدك الإلكتروني غير مُحقق.
              <Link
                :href="route('verification.send')"
                method="post"
                as="button"
                class="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
              >
                انقر هنا لإعادة إرسال بريد التحقق.
              </Link>
            </p>

            <div
              v-if="status === 'verification-link-sent'"
              class="mt-2 text-sm font-medium text-green-600"
            >
              تم إرسال رابط تحقق جديد إلى بريدك الإلكتروني.
            </div>
          </div>

          <div class="flex items-center gap-4">
            <Button :disabled="form.processing">
              حفظ
            </Button>

            <Transition
              enter-active-class="transition ease-in-out"
              enter-from-class="opacity-0"
              leave-active-class="transition ease-in-out"
              leave-to-class="opacity-0"
            >
              <p
                v-show="form.recentlySuccessful"
                class="text-sm text-neutral-600"
              >
                تم الحفظ.
              </p>
            </Transition>
          </div>
        </form>
      </div>

      <DeleteUser />
    </SettingsLayout>
  </AppLayout>
</template>
