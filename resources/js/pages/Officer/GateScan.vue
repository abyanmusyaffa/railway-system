<!-- resources/js/Pages/Officer/GateScan.vue -->
<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Passenger, ActionType, Booking } from '@/types'

const props = defineProps<{ query: string; passengers: Passenger[] }>()

const search = ref<string>(props.query)
const gateNumber = ref<string>('G1')

// debounce sederhana biar tidak nge-request tiap ketikan
let timeout: ReturnType<typeof setTimeout> | undefined
watch(search, (value) => {
  clearTimeout(timeout)
  timeout = setTimeout(() => {
    router.get('/officer/gate-scan', { q: value }, { preserveState: true, replace: true })
  }, 350)
})

const actionForm = useForm<{ passenger_id: number | null; action_type: ActionType; gate_number: string }>({
  passenger_id: null,
  action_type: 'GATE_IN',
  gate_number: gateNumber.value,
})

function scan(passengerId: number, actionType: ActionType): void {
  actionForm.passenger_id = passengerId
  actionForm.action_type = actionType
  actionForm.gate_number = gateNumber.value
  actionForm.post(route('officer.gatescan.store'), { preserveScroll: true, preserveState: true })
}

const actionLabel: Record<ActionType, string> = {
  GATE_IN: 'Gate In',
  GATE_OUT: 'Gate Out',
  BOARDING: 'Boarding',
}
const actionColor: Record<ActionType, string> = {
  GATE_IN: 'bg-teal-600 hover:bg-teal-700',
  GATE_OUT: 'bg-slate-600 hover:bg-slate-700',
  BOARDING: 'bg-blue-600 hover:bg-blue-700',
}

function statusBadge(b: Booking): string {
  return b.booking_status === 'COMPLETED' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700'
}
</script>

<template>
  <AppLayout>
    <h1 class="font-display font-bold text-3xl mb-2">Gate Scan</h1>
    <p class="text-slate-500 text-sm mb-6">Cari penumpang, lalu catat aksi sesuai booking aktifnya.</p>

    <!-- Gate selector - dipilih sekali per sesi tugas -->
    <div class="flex items-center gap-3 mb-6 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm w-fit">
      <span class="text-sm text-slate-500">Anda bertugas di gate:</span>
      <select v-model="gateNumber" class="border border-slate-200 rounded-lg px-3 py-1.5 font-mono text-sm">
        <option v-for="n in 20" :key="n" :value="`G${n}`">G{{ n }}</option>
      </select>
    </div>

    <input
      v-model="search"
      type="text"
      placeholder="Cari nama atau No. Identitas penumpang..."
      class="w-full border border-slate-200 rounded-xl px-4 py-3 mb-6 focus:outline-none focus:ring-2 focus:ring-teal-400"
    />

    <div v-if="search.trim() === ''" class="text-center text-slate-400 py-16">
      Ketik nama atau No. Identitas untuk mulai mencari penumpang.
    </div>

    <div v-else-if="passengers.length === 0" class="text-center text-slate-400 py-16">
      Tidak ditemukan penumpang dengan kata kunci "{{ search }}".
    </div>

    <div v-else class="space-y-4">
      <div v-for="p in passengers" :key="p.passenger_id" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="flex justify-between items-start mb-3">
          <div>
            <div class="font-display font-semibold">{{ p.full_name }}</div>
            <div class="text-xs text-slate-400 font-mono">{{ p.identity_number }}</div>
          </div>
        </div>

        <div v-if="!p.bookings?.length" class="text-sm text-slate-400 italic">Tidak ada booking aktif.</div>

        <div v-for="b in p.bookings" :key="b.booking_id" class="border-t border-slate-100 pt-3 mt-3 first:border-0 first:pt-0 first:mt-0">
          <div class="flex justify-between items-center mb-2">
            <div class="text-sm">
              <span class="font-medium">{{ b.schedule?.train?.train_name }}</span>
              <span class="text-slate-400"> · {{ b.schedule?.origin_station }} → {{ b.schedule?.destination_station }} · Kursi {{ b.seat_number }}</span>
            </div>
            <span class="text-[10px] font-mono uppercase px-2 py-0.5 rounded-full" :class="statusBadge(b)">{{ b.booking_status }}</span>
          </div>

          <div class="flex gap-2">
            <button
              v-for="action in (['GATE_IN', 'GATE_OUT', 'BOARDING'] as ActionType[])"
              :key="action"
              @click="scan(p.passenger_id, action)"
              :disabled="actionForm.processing"
              class="text-white text-xs font-medium px-3 py-1.5 rounded-lg transition disabled:opacity-40"
              :class="actionColor[action]"
            >
              {{ actionLabel[action] }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>