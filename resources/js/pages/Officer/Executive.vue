<!-- resources/js/Pages/Officer/Executive.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import type { Booking } from '@/types'

defineProps<{ bookings: Booking[] }>()
</script>

<template>
  <AppLayout>
    <h1 class="font-display font-bold text-3xl mb-6">Booking Kelas Eksekutif</h1>
    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
      <table class="w-full text-sm">
        <thead class="bg-violet-600 text-white font-mono text-[11px] uppercase tracking-widest">
          <tr><th class="p-3 text-left">Kode</th><th class="p-3 text-left">Penumpang</th><th class="p-3 text-left">Kursi</th><th class="p-3 text-left">Total</th></tr>
        </thead>
        <tbody>
          <tr v-for="b in bookings" :key="b.booking_id" class="border-t border-slate-100">
            <td class="p-3 font-mono">{{ b.booking_code }}</td>
            <td class="p-3">{{ b.passenger_id }}</td>
            <td class="p-3 font-mono">{{ b.seat_number }}</td>
            <td class="p-3 font-mono">{{ b.total_paid }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>