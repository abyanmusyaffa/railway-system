<!-- resources/js/Pages/Admin/Report.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'

interface RevenueRow {
  schedule_id: number
  train_name: string
  revenue: number
  total_bookings: number
}
interface OccupancyRow {
  schedule_id: number
  train_name: string
  total_seats: number
  booked_seats: number
}

const props = defineProps<{
  totalExecutiveTrains: number
  revenuePerSchedule: RevenueRow[]
  occupancy: OccupancyRow[]
}>()

function priceFmt(p: number): string {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(p)
}
function occupancyPct(o: OccupancyRow): number {
  return o.total_seats > 0 ? Math.round((o.booked_seats / o.total_seats) * 100) : 0
}
function occupancyColor(pct: number): string {
  if (pct >= 80) {
    return 'bg-rose-500'
  }
  
  if (pct >= 50) {
    return 'bg-amber-500'
  }

  return 'bg-emerald-500'
}
</script>

<template>
  <AppLayout>
    <h1 class="font-display font-bold text-3xl mb-6">Laporan & Analitik</h1>

    <div class="grid sm:grid-cols-3 gap-4 mb-8">
      <div class="rounded-2xl border border-slate-200 bg-gradient-to-br from-violet-600 to-violet-800 text-white p-5">
        <div class="text-xs uppercase tracking-widest text-violet-200 font-mono mb-1">Kereta Executive</div>
        <div class="text-4xl font-display font-bold">{{ props.totalExecutiveTrains }}</div>
      </div>
      <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="text-xs uppercase tracking-widest text-slate-400 font-mono mb-1">Total Booking</div>
        <div class="text-4xl font-display font-bold text-ink">
          {{ props.revenuePerSchedule.reduce((sum, r) => sum + r.total_bookings, 0) }}
        </div>
      </div>
      <div class="rounded-2xl border border-slate-200 bg-white p-5">
        <div class="text-xs uppercase tracking-widest text-slate-400 font-mono mb-1">Total Pendapatan</div>
        <div class="text-2xl font-display font-bold text-emerald-600">
          {{ priceFmt(props.revenuePerSchedule.reduce((sum, r) => sum + Number(r.revenue), 0)) }}
        </div>
      </div>
    </div>

    <div class="mb-8">
      <h2 class="font-display font-semibold text-lg mb-3">Pendapatan per Jadwal</h2>
      <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
        <table class="w-full text-sm">
          <thead class="bg-slate-50 text-slate-500 font-mono text-[11px] uppercase tracking-widest">
            <tr><th class="p-3 text-left">Kereta</th><th class="p-3 text-left">Total Booking</th><th class="p-3 text-left">Pendapatan</th></tr>
          </thead>
          <tbody>
            <tr v-for="r in props.revenuePerSchedule" :key="r.schedule_id" class="border-t border-slate-100">
              <td class="p-3 font-display">{{ r.train_name }}</td>
              <td class="p-3 font-mono">{{ r.total_bookings }}</td>
              <td class="p-3 font-mono text-emerald-600">{{ priceFmt(r.revenue) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div>
      <h2 class="font-display font-semibold text-lg mb-3">Okupansi Kursi</h2>
      <div class="grid sm:grid-cols-2 gap-4">
        <div v-for="o in props.occupancy" :key="o.schedule_id" class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
          <div class="flex justify-between text-sm mb-2">
            <span class="font-display font-medium">{{ o.train_name }}</span>
            <span class="font-mono text-slate-500">{{ o.booked_seats }}/{{ o.total_seats }}</span>
          </div>
          <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full" :class="occupancyColor(occupancyPct(o))" :style="{ width: `${occupancyPct(o)}%` }"></div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>