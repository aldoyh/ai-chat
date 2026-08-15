import type { SharedData, User } from '@/types'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

export function useAuth() {
  const page = usePage<SharedData>()

  const user = computed<User | null>(
    () => page.props.auth.user ?? page.props.auth.guestUser ?? null,
  )

  const isGuest = computed(() => !page.props.auth.user)

  const isAuthenticated = computed(() => !!page.props.auth.user)

  return {
    user,
    isGuest,
    isAuthenticated,
    guestUser: computed<User | null>(() => page.props.auth.guestUser ?? null),
  }
}
