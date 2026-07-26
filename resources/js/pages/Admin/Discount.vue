<!-- resources/js/Pages/Admin/Discount.vue -->
<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Schedule, PageProps } from '@/types'

const props = defineProps<{ schedules: Schedule[] }>()
const page = usePage<PageProps>()
const finalPrice = computed<number | undefined>(() => page.props.flash?.final_price)

interface DiscountForm { schedule_id: number | null; discount_rate: number | null }
const form = useForm<DiscountForm>({ schedule_id: null, discount_rate: null })

function submit(): void {
  form.post('/admin/discount')
}
function priceFmt(p: number): string {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(p)
}
</script>

<template>
  <AppLayout>
    <div class="max-w-md">
      <h1 class="font-display font-bold text-3xl mb-6">Simulasi Diskon Tiket</h1>

      <form @submit.prevent="submit" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <div>
          <label class="block text-sm font-medium text-slate-600 mb-1">Jadwal</label>
          <select v-model="form.schedule_id" class="w-full border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400">
            <option :value="null" disabled>Pilih Jadwal</option>
            <option v-for="s in props.schedules" :key="s.schedule_id" :value="s.schedule_id">
              {{ s.train?.train_name }} — {{ s.origin_station }} → {{ s.destination_station }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-slate-600 mb-1">Diskon (%)</label>
          <input v-model="form.discount_rate" type="number" min="0" max="100" placeholder="10"
            class="w-full border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
        </div>
        <button class="w-full bg-violet-600 text-white py-2.5 rounded-xl font-medium hover:bg-violet-700 transition">
          Hitung
        </button>
      </form>

      <div v-if="finalPrice !== undefined" class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5">
        <div class="text-xs uppercase tracking-widest text-emerald-600 font-mono mb-1">Harga Setelah Diskon</div>
        <div class="text-3xl font-display font-bold text-emerald-700">{{ priceFmt(finalPrice) }}</div>
      </div>
    </div>
  </AppLayout>
</template>