<!-- resources/js/Pages/Passenger/Ticket.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import type { Booking, BookingStatus } from '@/types'

defineProps<{ booking: Booking }>()

const statusStyle: Record<BookingStatus, string> = {
  CONFIRMED: 'bg-emerald-100 text-emerald-700',
  CANCELLED: 'bg-rose-100 text-rose-700',
  COMPLETED: 'bg-blue-100 text-blue-700',
}
</script>

<template>
  <AppLayout>
    <div class="max-w-md mx-auto">
      <h1 class="font-display font-bold text-3xl mb-6 text-center">E-Tiket</h1>

      <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-orange-500 to-rose-500 text-white p-5">
          <div class="font-display font-semibold text-lg">{{ booking.schedule?.train?.train_name }}</div>
          <div class="text-sm text-white/80">{{ booking.schedule?.train?.train_class }}</div>
        </div>

        <div class="p-5 space-y-3 text-sm">
          <div class="flex justify-between"><span class="text-slate-500">Kode Booking</span><span class="font-mono font-medium">{{ booking.booking_code }}</span></div>
          <div class="flex justify-between"><span class="text-slate-500">Penumpang</span><span class="font-medium">{{ booking.passenger?.full_name }}</span></div>
          <div class="flex justify-between"><span class="text-slate-500">Rute</span><span class="font-medium">{{ booking.schedule?.origin_station }} → {{ booking.schedule?.destination_station }}</span></div>
          <div class="flex justify-between"><span class="text-slate-500">Berangkat</span><span class="font-mono">{{ booking.schedule?.departure_time }}</span></div>
          <div class="flex justify-between"><span class="text-slate-500">Kursi</span><span class="font-mono font-medium">{{ booking.seat_number }}</span></div>
          <div class="flex justify-between items-center pt-3 border-t border-slate-100">
            <span class="text-slate-500">Status</span>
            <span class="text-[10px] font-mono uppercase tracking-widest px-3 py-1 rounded-full" :class="statusStyle[booking.booking_status]">
              {{ booking.booking_status }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>