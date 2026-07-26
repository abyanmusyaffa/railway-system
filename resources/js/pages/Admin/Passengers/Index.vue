<!-- resources/js/Pages/Admin/Passengers/Index.vue -->
<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Passenger, PassengerType } from '@/types'

interface Paginated<T> {
  data: T[]
  links: { url: string | null; label: string; active: boolean }[]
}
const props = defineProps<{ passengers: Paginated<Passenger> }>()

interface PassengerForm {
  identity_number: string
  full_name: string
  email: string
  phone_number: string
  passenger_type: PassengerType
}
const emptyForm: PassengerForm = { identity_number: '', full_name: '', email: '', phone_number: '', passenger_type: 'ADULT' }

const form = useForm<PassengerForm>({ ...emptyForm })
const editingId = ref<number | null>(null)

function startEdit(p: Passenger): void {
  editingId.value = p.passenger_id
  form.identity_number = p.identity_number
  form.full_name = p.full_name
  form.email = p.email ?? ''
  form.phone_number = p.phone_number ?? ''
  form.passenger_type = p.passenger_type
}

function cancelEdit(): void {
  editingId.value = null
  Object.assign(form, emptyForm)
}

function submit(): void {
  if (editingId.value) {
    form.put('/admin/passengers/' + editingId.value, { onSuccess: () => cancelEdit() })
  } else {
    form.post('/admin/passengers', { onSuccess: () => form.reset() })
  }
}

function destroy(id: number): void {
  if (confirm('Hapus penumpang ini? Akun login & seluruh booking terkait ikut terhapus.')) {
    router.delete('/admin/passengers/' + id)
  }
}

const typeBadge: Record<PassengerType, string> = {
  ADULT: 'bg-slate-100 text-slate-600',
  INFANT: 'bg-orange-100 text-orange-700',
}
</script>

<template>
  <AppLayout>
    <h1 class="font-display font-bold text-3xl mb-6">Manajemen Data Penumpang</h1>

    <form @submit.prevent="submit" class="grid sm:grid-cols-5 gap-3 mb-8 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="sm:col-span-5 flex items-center justify-between" v-if="editingId">
        <span class="text-sm text-violet-600 font-medium">Mengedit penumpang #{{ editingId }}</span>
        <button type="button" @click="cancelEdit" class="text-xs text-slate-400 hover:text-slate-600">Batal</button>
      </div>

      <input v-model="form.identity_number" placeholder="No. Identitas" class="border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
      <input v-model="form.full_name" placeholder="Nama Lengkap" class="border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
      <input v-model="form.email" placeholder="Email" class="border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
      <input v-model="form.phone_number" placeholder="Telepon" class="border border-slate-200 rounded-xl px-3 py-2 focus:outline-none focus:ring-2 focus:ring-violet-400" />
      <select v-model="form.passenger_type" class="border border-slate-200 rounded-xl px-3 py-2">
        <option value="ADULT">ADULT</option>
        <option value="INFANT">INFANT</option>
      </select>

      <p v-if="!editingId" class="sm:col-span-5 text-xs text-slate-400">
        Akun login otomatis dibuat dengan password default = No. Identitas.
      </p>

      <button class="sm:col-span-5 text-white py-2.5 rounded-xl font-medium transition" :class="editingId ? 'bg-amber-500 hover:bg-amber-600' : 'bg-violet-600 hover:bg-violet-700'">
        {{ editingId ? 'Simpan Perubahan' : 'Tambah Penumpang' }}
      </button>
    </form>

    <div class="rounded-2xl border border-slate-200 bg-white overflow-hidden shadow-sm">
      <table class="w-full text-sm">
        <thead class="bg-slate-50 text-slate-500 font-mono text-[11px] uppercase tracking-widest">
          <tr><th class="p-3 text-left">Nama</th><th class="p-3 text-left">Email</th><th class="p-3 text-left">Tipe</th><th class="p-3"></th></tr>
        </thead>
        <tbody>
          <tr v-for="p in props.passengers.data" :key="p.passenger_id" class="border-t border-slate-100" :class="{ 'bg-violet-50/50': editingId === p.passenger_id }">
            <td class="p-3 font-display">{{ p.full_name }}</td>
            <td class="p-3 text-slate-500">{{ p.email }}</td>
            <td class="p-3"><span class="text-[10px] font-mono uppercase px-2 py-1 rounded-full" :class="typeBadge[p.passenger_type]">{{ p.passenger_type }}</span></td>
            <td class="p-3 flex gap-3">
              <button @click="startEdit(p)" class="text-violet-600 hover:underline text-xs font-mono uppercase">Edit</button>
              <button @click="destroy(p.passenger_id)" class="text-rose-600 hover:underline text-xs font-mono uppercase">Hapus</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="flex justify-center gap-1 mt-6">
      <button
        v-for="link in props.passengers.links"
        :key="link.label"
        :disabled="!link.url"
        @click="link.url && router.get(link.url, {}, { preserveScroll: true })"
        v-html="link.label"
        class="px-3 py-1.5 rounded-lg text-sm"
        :class="link.active ? 'bg-violet-600 text-white' : 'text-slate-500 hover:bg-slate-100 disabled:opacity-30'"
      />
    </div>
  </AppLayout>
</template>