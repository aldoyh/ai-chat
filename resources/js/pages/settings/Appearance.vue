<script setup lang="ts">
import type { BreadcrumbItem } from '@/types'

import { Head } from '@inertiajs/vue3'
import AppearanceTabs from '@/components/AppearanceTabs.vue'
import HeadingSmall from '@/components/HeadingSmall.vue'

import AppLayout from '@/layouts/AppLayout.vue'
import SettingsLayout from '@/layouts/settings/Layout.vue'

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'إعدادات المظهر',
    href: '/settings/appearance',
  },
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="إعدادات المظهر" />

    <SettingsLayout>
      <div class="space-y-6">
        <HeadingSmall
          title="إعدادات المظهر"
          description="تحديث إعدادات مظهر الحساب"
        />
        <AppearanceTabs />
      </div>
    </SettingsLayout>
  </AppLayout>
</template>
