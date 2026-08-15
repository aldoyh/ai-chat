<script setup lang="ts">
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { useAuth } from '@/composables/useAuth'

const { isGuest } = useAuth()

const subtitle = computed(() =>
  isGuest.value
    ? 'ابدأ كمستخدم مجهول الآن، أو أنشئ حسابًا لحفظ سجل محادثاتك.'
    : 'كيف يمكنني مساعدتك اليوم؟',
)
</script>

<template>
  <div class="max-w-3xl mx-auto px-8 size-full flex flex-col justify-center">
    <Transition
      appear
      enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-1 translate-y-0"
    >
      <div class="text-2xl font-semibold">
        مرحبًا!
      </div>
    </Transition>

    <Transition
      appear
      enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0 translate-y-2"
      enter-to-class="opacity-1 translate-y-0"
    >
      <div class="space-y-4 text-2xl text-zinc-500">
        <div>
          {{ subtitle }}
        </div>
        <div
          v-if="isGuest"
          class="flex flex-wrap gap-3 text-sm"
        >
          <Link
            :href="route('register')"
            class="rounded-full bg-primary px-4 py-2 font-medium text-primary-foreground"
          >
            إنشاء حساب
          </Link>
          <Link
            :href="route('login')"
            class="rounded-full border border-border px-4 py-2 font-medium text-foreground"
          >
            تسجيل الدخول
          </Link>
        </div>
      </div>
    </Transition>
  </div>
</template>
