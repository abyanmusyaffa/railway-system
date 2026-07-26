<!-- resources/js/Pages/Officer/Monitor.vue -->
<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Booking } from '@/types'

defineProps<{ bookings: Booking[] }>()

function complete(id: number): void {
    router.patch('/officer/monitor/' + id + '/complete')
}

function runArchive(): void {
  router.post('/officer/archive')
}
</script>

<template>
  <AppLayout>
    <div class="flex items-center justify-between mb-6">
      <h1 class="font-display font-bold text-3xl">Monitoring Booking</h1>
      <button @click="runArchive" class="bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-amber-600 transition">
        Arsipkan Booking Dibatalkan
      </button>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 font-mono text-[11px] uppercase tracking-widest">
          <tr><th class="p-3 text-left">Kode</th><th class="p-3 text-left">Penumpang</th><th class="p-3 text-left">Kursi</th><th class="p-3 text-left">Total</th><th class="p-3"></th></tr>
        </thead>
        <tbody>
          <tr v-for="b in bookings" :key="b.booking_id" class="border-t border-slate-100">
            <td class="p-3 font-mono">{{ b.booking_code }}</td>
            <td class="p-3">{{ b.passenger_id }}</td>
            <td class="p-3 font-mono">{{ b.seat_number }}</td>
            <td class="p-3 font-mono">{{ b.total_paid }}</td>
            <td class="p-3"><button @click="complete(b.booking_id)" class="text-emerald-600 hover:underline font-medium">Tandai Selesai</button></td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>