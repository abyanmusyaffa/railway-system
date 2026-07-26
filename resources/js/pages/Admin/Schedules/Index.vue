<!-- resources/js/Pages/Admin/Schedules/Index.vue -->
<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Schedule, Train } from '@/types'

const props = defineProps<{ schedules: Schedule[]; trains: Pick<Train, 'train_id' | 'train_name'>[] }>()

interface ScheduleForm {
  train_id: number | null
  origin_station: string
  destination_station: string
  departure_time: string
  arrival_time: string
  base_ticket_price: number | null
}
const emptyForm: ScheduleForm = {
  train_id: null, origin_station: '', destination_station: '',
  departure_time: '', arrival_time: '', base_ticket_price: null,
}

const form = useForm<ScheduleForm>({ ...emptyForm })
const editingId = ref<number | null>(null)

function startEdit(s: Schedule): void {
  editingId.value = s.schedule_id
  form.train_id = s.train_id
  form.origin_station = s.origin_station
  form.destination_station = s.destination_station
  form.departure_time = s.departure_time.slice(0, 16) // format datetime-local
  form.arrival_time = s.arrival_time.slice(0, 16)
  form.base_ticket_price = s.base_ticket_price
}

function cancelEdit(): void {
  editingId.value = null
  Object.assign(form, emptyForm)
}

function submit(): void {
  if (editingId.value) {
    form.put('/admin/schedules/'+ editingId.value, { onSuccess: () => cancelEdit() })
  } else {
    form.post('/admin/schedules/', { onSuccess: () => form.reset() })
  }
}

function destroy(id: number): void {
  if (confirm('Hapus jadwal ini?')) {
    router.delete('/admin/schedules/' + id)
  }
}
function priceFmt(p: number): string {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(p)
}
</script>

<template>
  <AppLayout>
    <h1 class="font-display font-bold text-3xl mb-6">Manajemen Jadwal</h1>

    <form @submit.prevent="submit" class="grid sm:grid-cols-3 gap-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="sm:col-span-3 flex items-center justify-between" v-if="editingId">
        <span class="text-sm text-violet-600 font-medium">Mengedit jadwal #{{ editingId }}</span>
        <button type="button" @click="cancelEdit" class="text-xs text-slate-400 hover:text-slate-600">Batal</button>
      </div>

      <select v-model="form.train_id" class="border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400">
        <option :value="null" disabled>Pilih Kereta</option>
        <option v-for="t in props.trains" :key="t.train_id" :value="t.train_id">{{ t.train_name }}</option>
      </select>
      <input v-model="form.origin_station" placeholder="Stasiun asal" class="border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
      <input v-model="form.destination_station" placeholder="Stasiun tujuan" class="border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />

      <div>
        <label class="block text-xs text-slate-500 mb-1">Berangkat</label>
        <input v-model="form.departure_time" type="datetime-local" class="w-full border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Tiba</label>
        <input v-model="form.arrival_time" type="datetime-local" class="w-full border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
      </div>
      <div>
        <label class="block text-xs text-slate-500 mb-1">Harga Tiket</label>
        <input v-model="form.base_ticket_price" type="number" class="w-full border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
      </div>

      <button class="sm:col-span-3 text-white py-2.5 rounded-xl font-medium transition" :class="editingId ? 'bg-amber-500 hover:bg-amber-600' : 'bg-violet-600 hover:bg-violet-700'">
        {{ editingId ? 'Simpan Perubahan' : 'Tambah Jadwal' }}
      </button>
    </form>

    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 font-mono text-[11px] uppercase tracking-widest">
          <tr><th class="p-3 text-left">Kereta</th><th class="p-3 text-left">Rute</th><th class="p-3 text-left">Berangkat</th><th class="p-3 text-left">Harga</th><th class="p-3"></th></tr>
        </thead>
        <tbody>
          <tr v-for="s in props.schedules" :key="s.schedule_id" class="border-t border-slate-100" :class="{ 'bg-violet-50/50': editingId === s.schedule_id }">
            <td class="p-3 font-display">{{ s.train?.train_name }}</td>
            <td class="p-3">{{ s.origin_station }} → {{ s.destination_station }}</td>
            <td class="p-3 font-mono">{{ s.departure_time }}</td>
            <td class="p-3 font-mono">{{ priceFmt(s.base_ticket_price) }}</td>
            <td class="p-3 flex gap-3">
              <button @click="startEdit(s)" class="text-violet-600 hover:underline text-xs font-mono uppercase">Edit</button>
              <button @click="destroy(s.schedule_id)" class="text-rose-600 hover:underline text-xs font-mono uppercase">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>