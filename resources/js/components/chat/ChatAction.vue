<script setup lang="ts">
import { Icon } from '@iconify/vue'
import { router, useForm } from '@inertiajs/vue3'
import { inject } from 'vue'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import { Button } from '@/components/ui/button'

const chatId = inject<string | null>('chatId', null)
const deleteForm = useForm({})

function deleteChat(chatId?: string) {
  if (!chatId) {
    return
  }

  deleteForm.delete(route('chats.destroy', chatId), {
    preserveScroll: true,
    onSuccess: () => {
      router.flushAll()
    },
  })
}
</script>

<template>
  <AlertDialog v-if="chatId">
    <AlertDialogTrigger as-child>
      <Button variant="ghost" class="h-8 w-8 hover:text-destructive">
        <Icon icon="lucide:trash-2" class="h-4 w-4" />
      </Button>
    </AlertDialogTrigger>
    <AlertDialogContent>
      <AlertDialogHeader>
        <AlertDialogTitle>هل أنت متأكد تمامًا؟</AlertDialogTitle>
        <AlertDialogDescription>
          لا يمكن التراجع عن هذا الإجراء. سيؤدي ذلك إلى حذف المحادثة نهائيًا
          وإزالة جميع رسائلها من خوادمنا.
        </AlertDialogDescription>
      </AlertDialogHeader>
      <AlertDialogFooter>
        <AlertDialogCancel>إلغاء</AlertDialogCancel>
        <AlertDialogAction
          class="bg-destructive text-destructive-foreground hover:bg-destructive/90"
          :disabled="deleteForm.processing"
          @click="deleteChat(chatId)"
        >
          <Icon
            v-if="deleteForm.processing"
            icon="lucide:loader-2"
            class="h-4 w-4 mr-2 animate-spin"
          />
          {{ deleteForm.processing ? "جارٍ الحذف..." : "حذف المحادثة" }}
        </AlertDialogAction>
      </AlertDialogFooter>
    </AlertDialogContent>
  </AlertDialog>
</template>
