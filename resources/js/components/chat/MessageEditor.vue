<script setup lang="ts">
import type { Message } from '@/types'
import { onMounted, ref } from 'vue'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'

const props = defineProps<{
  message: Message
}>()

const emit = defineEmits<{
  setMode: [mode: 'view' | 'edit']
}>()

const textareaRef = ref<InstanceType<typeof Textarea>>()

const message = ref(props.message.parts || '')

function handleKeyDown(event: KeyboardEvent) {
  if (event.key === 'Escape') {
    handleCancel()
  }
  else if (event.key === 'Enter' && (event.metaKey || event.ctrlKey)) {
    handleSave()
  }
}

function handleCancel() {
  emit('setMode', 'view')
}

function handleSave() {
  if (message.value.trim()) {
    emit('setMode', 'view')
  }
}

onMounted(() => {
  textareaRef.value?.$el?.focus()
  textareaRef.value?.$el?.select()
})
</script>

<template>
  <div class="flex flex-col gap-2 w-full">
    <Textarea
      ref="textareaRef"
      v-model="message"
      class="min-h-[100px] resize-none"
      placeholder="حرّر رسالتك..."
      <Button variant="outline" size="sm" @click="handleCancel">
        إلغاء
      </Button>
      <Button size="sm" :disabled="!message.trim()" @click="handleSave">
        حفظ
      </Button>
    </div>
  </div>
</template>
