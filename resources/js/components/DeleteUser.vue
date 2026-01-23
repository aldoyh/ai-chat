<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { ref } from 'vue'

// Components
import HeadingSmall from '@/components/HeadingSmall.vue'
import InputError from '@/components/InputError.vue'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const passwordInput = ref<HTMLInputElement | null>(null)

const form = useForm({
  password: '',
})

function deleteUser(e: Event) {
  e.preventDefault()

  form.delete(route('profile.destroy'), {
    preserveScroll: true,
    onSuccess: () => closeModal(),
    onError: () => passwordInput.value?.focus(),
    onFinish: () => form.reset(),
  })
}

function closeModal() {
  form.clearErrors()
  form.reset()
}
</script>

<template>
  <div class="space-y-6">
    <HeadingSmall
      title="حذف الحساب"
      description="حذف حسابك وكل موارده"
    />
    <div
      class="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10"
    >
      <div class="relative space-y-0.5 text-red-600 dark:text-red-100">
        <p class="font-medium">
          تحذير
        </p>
        <p class="text-sm">
          تابع بحذر، لا يمكن التراجع عن هذا الإجراء.
        </p>
      </div>
      <Dialog>
        <DialogTrigger as-child>
          <Button variant="destructive">
            حذف الحساب
          </Button>
        </DialogTrigger>
        <DialogContent>
          <form class="space-y-6" @submit="deleteUser">
            <DialogHeader class="space-y-3">
              <DialogTitle>
                هل أنت متأكد أنك تريد حذف حسابك؟
              </DialogTitle>
              <DialogDescription>
                بعد حذف حسابك، سيُحذف جميع موارده وبياناته بشكل دائم.
                الرجاء إدخال كلمة المرور لتأكيد رغبتك في حذف الحساب نهائيًا.
              </DialogDescription>
            </DialogHeader>

            <div class="grid gap-2">
              <Label for="password" class="sr-only">كلمة المرور</Label>
              <Input
                id="password"
                ref="passwordInput"
                v-model="form.password"
                type="password"
                name="password"
                placeholder="كلمة المرور"
              />
              <InputError :message="form.errors.password" />
            </div>

            <DialogFooter class="gap-2">
              <DialogClose as-child>
                <Button variant="secondary" @click="closeModal">
                  إلغاء
                </Button>
              </DialogClose>

              <Button
                variant="destructive"
                :disabled="form.processing"
              >
                <button type="submit">
                  حذف الحساب
                </button>
              </Button>
            </DialogFooter>
          </form>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>
