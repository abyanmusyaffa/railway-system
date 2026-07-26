<!-- resources/js/Pages/Passenger/History.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Booking, BookingStatus } from '@/types'

defineProps<{ bookings: Booking[] }>()

const statusStyle: Record<BookingStatus, string> = {
  CONFIRMED: 'bg-emerald-100 text-emerald-700',
  CANCELLED: 'bg-rose-100 text-rose-700',
  COMPLETED: 'bg-blue-100 text-blue-700',
}
</script>

<template>
  <AppLayout>
    <h1 class="font-display font-bold text-3xl mb-6">Riwayat Perjalanan Saya</h1>

    <div class="grid gap-4">
      <Link
        v-for="b in bookings"
        :key="b.booking_id"
        :href="`/my/bookings/${b.booking_id}/ticket`"
        class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white p-5 shadow-sm hover:shadow-md hover:border-orange-300 transition"
      >
        <div>
          <div class="font-display font-semibold">{{ b.schedule?.train?.train_name }} — <span class="font-mono text-sm text-slate-400">{{ b.booking_code }}</span></div>
          <div class="text-sm text-slate-500">
            {{ b.schedule?.origin_station }} → {{ b.schedule?.destination_station }} · Kursi {{ b.seat_number }}
          </div>
        </div>
        <span class="text-[10px] font-mono uppercase tracking-widest px-3 py-1 rounded-full" :class="statusStyle[b.booking_status]">
          {{ b.booking_status }}
        </span>
      </Link>

      <p v-if="bookings.length === 0" class="text-center text-slate-400 py-12">Belum ada riwayat perjalanan.</p>
    </div>
  </AppLayout>
</template>