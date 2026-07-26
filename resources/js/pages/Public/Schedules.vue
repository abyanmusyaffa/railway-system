<!-- resources/js/Pages/Public/Schedules.vue -->
<script setup lang="ts">
import { router, Link, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import type { PageProps, TrainClass } from '@/types'

interface PublicSchedule extends Pick<Schedule, 'schedule_id' | 'origin_station' | 'destination_station' | 'departure_time' | 'arrival_time'> {
  train_name: Train['train_name']
  train_class: TrainClass
}

const props = defineProps<{ schedules: PublicSchedule[]; filters: { origin?: string; destination?: string } }>()
const origin = ref<string>(props.filters?.origin ?? '')
const destination = ref<string>(props.filters?.destination ?? '')

function search(): void {
  router.get('/', { origin: origin.value, destination: destination.value }, { preserveState: true })
}

const classBadge: Record<TrainClass, string> = {
  EXECUTIVE: 'bg-violet-100 text-violet-700',
  BUSINESS: 'bg-blue-100 text-blue-700',
  ECONOMY: 'bg-teal-100 text-teal-700',
}
const page = usePage<PageProps>()
const user = computed(() => page.props.auth.user)

const ctaRoute = computed<{ label: string; href: string }>(() => {
  if (!user.value) {
    return { label: 'Masuk', href: '/login' }
  }

  if (user.value.role === 'PASSENGER') {
    return { label: 'Pesan Tiket', href: '/booking' }
  }

  if (user.value.role === 'OFFICER') {
    return { label: 'Dashboard Petugas', href: '/officer/monitor' }
  }

  return { label: 'Dashboard Admin', href: '/admin/trains' }
})
</script>

<template>
  <div class="min-h-screen bg-paper font-body text-ink">
    <header class="bg-board px-6 py-5 flex items-center justify-between">
      <span class="font-display font-bold text-lg text-white">RAILWAY//SYS</span>

      <div class="flex items-center gap-3">
        <Link v-if="!user" :href="'/register'" class="text-slate-300 hover:text-white text-sm font-mono uppercase tracking-widest">
          Daftar
        </Link>
        <Link :href="ctaRoute.href" class="bg-gradient-to-r from-orange-600 to-rose-600 text-white text-sm font-medium px-4 py-2 rounded-xl hover:opacity-90 transition">
          {{ ctaRoute.label }}
        </Link>
      </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-10">
      <h1 class="font-display font-bold text-3xl mb-6">Cari Jadwal Kereta</h1>

      <div class="flex gap-3 mb-8">
        <input v-model="origin" placeholder="Stasiun asal" class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400" />
        <input v-model="destination" placeholder="Stasiun tujuan" class="flex-1 border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400" />
        <button @click="search" class="bg-gradient-to-r from-orange-600 to-rose-600 text-white px-6 rounded-xl font-medium hover:opacity-90 transition">Cari</button>
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div v-for="s in props.schedules" :key="s.schedule_id" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
          <div class="flex justify-between mb-2">
            <span class="font-display font-semibold">{{ s.train_name }}</span>
            <span class="text-[10px] font-mono uppercase tracking-widest px-2 py-1 rounded-full" :class="classBadge[s.train_class]">{{ s.train_class }}</span>
          </div>
          <div class="text-sm text-slate-500">{{ s.origin_station }} → {{ s.destination_station }}</div>
          <div class="text-sm text-slate-400 font-mono">{{ s.departure_time }} - {{ s.arrival_time }}</div>
        </div>
      </div>
    </main>
  </div>
</template>