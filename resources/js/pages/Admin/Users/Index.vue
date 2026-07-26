<!-- resources/js/Pages/Admin/Users/Index.vue -->
<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import type { AuthUser, UserRole } from '@/types'

defineProps<{ users: AuthUser[] }>()

function changeRole(user: AuthUser, event: Event): void {
  const role = (event.target as HTMLSelectElement).value as UserRole
  router.put('/admin/users/'+ user.id, { role })
}

const roleBadge: Record<UserRole, string> = {
  ADMIN: 'bg-violet-100 text-violet-700',
  OFFICER: 'bg-teal-100 text-teal-700',
  PASSENGER: 'bg-orange-100 text-orange-700',
}
</script>

<template>
  <AppLayout>
    <h1 class="font-display font-bold text-3xl mb-6">Manajemen User & Role</h1>

    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 font-mono text-[11px] uppercase tracking-widest">
          <tr><th class="p-3 text-left">Nama</th><th class="p-3 text-left">Email</th><th class="p-3 text-left">Role</th></tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id" class="border-t border-slate-100">
            <td class="p-3 font-display">{{ u.name }}</td>
            <td class="p-3 text-slate-500">{{ u.email }}</td>
            <td class="p-3">
              <select
                :value="u.role"
                @change="changeRole(u, $event)"
                class="text-[10px] font-mono uppercase tracking-widest px-2 py-1.5 rounded-full border-0 focus:outline-none focus:ring-2 focus:ring-violet-400"
                :class="roleBadge[u.role]"
              >
                <option value="ADMIN">ADMIN</option>
                <option value="OFFICER">OFFICER</option>
                <option value="PASSENGER">PASSENGER</option>
              </select>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>