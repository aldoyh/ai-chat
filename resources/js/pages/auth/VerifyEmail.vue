<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import { LoaderCircle } from '@lucide/vue'
import TextLink from '@/components/TextLink.vue'
import { Button } from '@/components/ui/button'
import AuthLayout from '@/layouts/AuthLayout.vue'

defineProps<{
  status?: string
}>()

const form = useForm({})

function submit() {
  form.post(route('verification.send'))
}
</script>

<template>
  <AuthLayout
    title="تحقق من البريد الإلكتروني"
    description="يرجى التحقق من عنوان بريدك الإلكتروني بالنقر على الرابط الذي أرسلناه إليك."
  >
    <Head title="التحقق من البريد الإلكتروني" />

    <div
      v-if="status === 'verification-link-sent'"
      class="mb-4 text-center text-sm font-medium text-green-600"
    >
      تم إرسال رابط تحقق جديد إلى عنوان البريد الإلكتروني الذي قدمته أثناء التسجيل.
    </div>

    <form class="space-y-6 text-center" @submit.prevent="submit">
      <Button :disabled="form.processing" variant="secondary">
        <LoaderCircle
          v-if="form.processing"
          class="h-4 w-4 animate-spin"
        />
        إعادة إرسال رابط التحقق
      </Button>

      <TextLink
        :href="route('logout')"
        method="post"
        as="button"
        class="mx-auto block text-sm"
      >
        تسجيل الخروج
      </TextLink>
    </form>
  </AuthLayout>
</template>
