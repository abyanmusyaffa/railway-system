<!-- resources/js/Layouts/AppLayout.vue -->
<script setup lang="ts">
import { usePage, Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { PageProps, UserRole } from '@/types'

const page = usePage<PageProps>()
const user = computed(() => page.props.auth.user)
const role = computed<UserRole | undefined>(() => user.value?.role)

interface RoleTheme { label: string; text: string; badge: string; hover: string }
const roleTheme: Record<UserRole, RoleTheme> = {
  ADMIN:     { label: 'Admin',            text: 'text-violet-300',  badge: 'bg-violet-500/20 text-violet-300',   hover: 'hover:text-violet-400' },
  OFFICER:   { label: 'Petugas Stasiun',  text: 'text-teal-300',    badge: 'bg-teal-500/20 text-teal-300',       hover: 'hover:text-teal-400' },
  PASSENGER: { label: 'Penumpang',        text: 'text-orange-300',  badge: 'bg-orange-500/20 text-orange-300',   hover: 'hover:text-orange-400' },
}
const theme = computed<RoleTheme>(() => roleTheme[role.value ?? 'PASSENGER'])

interface NavLink { label: string; href: string }
const navByRole: Record<UserRole, NavLink[]> = {
  ADMIN: [
    { label: 'Kereta', href: '/admin/trains' },
    { label: 'Jadwal', href: '/admin/schedules' },
    { label: 'Penumpang', href: '/admin/passengers' },
    { label: 'Laporan', href: '/admin/report' },
    { label: 'Diskon', href: '/admin/discount' },
    { label: 'User', href: '/admin/users' },
  ],
  OFFICER: [
    { label: 'Gate Scan', href: '/officer/gate-scan' },
    { label: 'Monitor', href: '/officer/monitor' },
    { label: 'Eksekutif', href: '/officer/executive' },
  ],
  PASSENGER: [
    { label: 'Booking', href: '/booking' },
    { label: 'Riwayat', href: '/my/bookings' },
    { label: 'Profil', href: '/my/profile' },
  ],
}
const links = computed<NavLink[]>(() => navByRole[role.value ?? 'PASSENGER'])

function logout(): void {
  router.post('/logout')
}
</script>

<template>
  <div class="min-h-screen bg-paper font-body text-ink">
    <header class="bg-board sticky top-0 z-10">
      <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
          <span class="font-display font-bold text-lg text-white tracking-tight">RAILWAY//SYS</span>
          <span class="font-mono text-[10px] uppercase tracking-widest px-2 py-0.5 rounded-full" :class="theme.badge">
            {{ theme.label }}
          </span>
        </div>

        <nav class="hidden md:flex gap-6 font-mono text-xs uppercase tracking-widest">
          <Link v-for="link in links" :key="link.href" :href="link.href"
            class="text-slate-400 transition-colors" :class="theme.hover">
            {{ link.label }}
          </Link>
        </nav>

        <div class="flex items-center gap-4">
          <span class="hidden sm:inline text-sm text-slate-400">{{ user?.name }}</span>
          <button @click="logout" class="font-mono text-xs uppercase tracking-widest text-slate-400 hover:text-rose-400 transition-colors">
            Keluar
          </button>
        </div>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-10">
      <slot />
    </main>
  </div>
</template>