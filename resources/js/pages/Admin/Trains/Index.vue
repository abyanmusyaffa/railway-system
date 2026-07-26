<!-- resources/js/Pages/Admin/Trains/Index.vue -->
<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Train, TrainClass } from '@/types'

const props = defineProps<{ trains: Train[] }>()

interface TrainForm { train_code: string; train_name: string; train_class: TrainClass; total_seats: number | null }
const emptyForm: TrainForm = { train_code: '', train_name: '', train_class: 'ECONOMY', total_seats: null }

const form = useForm<TrainForm>({ ...emptyForm })
const editingId = ref<number | null>(null)

function startEdit(train: Train): void {
  editingId.value = train.train_id
  form.train_code = train.train_code
  form.train_name = train.train_name
  form.train_class = train.train_class
  form.total_seats = train.total_seats
}

function cancelEdit(): void {
  editingId.value = null
  form.reset()
  Object.assign(form, emptyForm)
}

function submit(): void {
  if (editingId.value) {
    form.put('/admin/trains/' + editingId.value, {
      onSuccess: () => cancelEdit(),
    })
  } else {
    form.post('/admin/trains', { onSuccess: () => form.reset() })
  }
}

function destroy(id: number): void {
    if (confirm('Hapus kereta ini?')) {
        router.delete('/admin/trains/' + id)
    }
}
</script>

<template>
  <AppLayout>
    <h1 class="font-display font-bold text-3xl mb-6">Manajemen Kereta</h1>

    <form @submit.prevent="submit" class="grid grid-cols-4 gap-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="col-span-4 flex items-center justify-between" v-if="editingId">
        <span class="text-sm text-violet-600 font-medium">Mengedit kereta #{{ editingId }}</span>
        <button type="button" @click="cancelEdit" class="text-xs text-slate-400 hover:text-slate-600">Batal</button>
      </div>

      <input v-model="form.train_code" placeholder="Kode" class="border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
      <input v-model="form.train_name" placeholder="Nama" class="border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
      <select v-model="form.train_class" class="border border-slate-200 rounded-xl px-3 py-2">
        <option value="EXECUTIVE">EXECUTIVE</option>
        <option value="BUSINESS">BUSINESS</option>
        <option value="ECONOMY">ECONOMY</option>
      </select>
      <input v-model="form.total_seats" type="number" placeholder="Kapasitas" class="border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />

      <button class="col-span-4 text-white py-2.5 rounded-xl font-medium transition" :class="editingId ? 'bg-amber-500 hover:bg-amber-600' : 'bg-violet-600 hover:bg-violet-700'">
        {{ editingId ? 'Simpan Perubahan' : 'Tambah Kereta' }}
      </button>
    </form>

    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 font-mono text-[11px] uppercase tracking-widest">
          <tr><th class="p-3 text-left">Kode</th><th class="p-3 text-left">Nama</th><th class="p-3 text-left">Kelas</th><th class="p-3 text-left">Kapasitas</th><th class="p-3"></th></tr>
        </thead>
        <tbody>
          <tr v-for="t in props.trains" :key="t.train_id" class="border-t border-slate-100" :class="{ 'bg-violet-50/50': editingId === t.train_id }">
            <td class="p-3 font-mono">{{ t.train_code }}</td>
            <td class="p-3 font-display">{{ t.train_name }}</td>
            <td class="p-3">{{ t.train_class }}</td>
            <td class="p-3 font-mono">{{ t.total_seats }}</td>
            <td class="p-3 flex gap-3">
              <button @click="startEdit(t)" class="text-violet-600 hover:underline text-xs font-mono uppercase">Edit</button>
              <button @click="destroy(t.train_id)" class="text-rose-600 hover:underline text-xs font-mono uppercase">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>