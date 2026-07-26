<!-- resources/js/Pages/Passenger/Profile.vue -->
<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Passenger } from '@/types'

const props = defineProps<{ passenger: Passenger }>()

interface ProfileForm {
  full_name: string
  email: string
  phone_number: string
  address: string
  emergency_contact_name: string
  emergency_contact_phone: string
}
const form = useForm<ProfileForm>({
  full_name: props.passenger.full_name,
  email: props.passenger.email ?? '',
  phone_number: props.passenger.phone_number ?? '',
  address: props.passenger.detail?.address ?? '',
  emergency_contact_name: props.passenger.detail?.emergency_contact_name ?? '',
  emergency_contact_phone: props.passenger.detail?.emergency_contact_phone ?? '',
})

function submit(): void {
  form.post('/my/profile')
}
</script>

<template>
  <AppLayout>
    <div class="max-w-lg">
      <h1 class="font-display font-bold text-3xl mb-6">Profil Saya</h1>

      <form @submit.prevent="submit" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-5">
        <!-- Data dasar dari tabel passengers -->
        <div>
          <h2 class="font-mono text-[11px] uppercase tracking-widest text-slate-400 mb-3">Data Diri</h2>
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-slate-600 mb-1">Nama Lengkap</label>
              <input v-model="form.full_name" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
              <input v-model="form.email" type="email" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400" />
              <p v-if="form.errors.email" class="text-rose-600 text-xs mt-1">{{ form.errors.email }}</p>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-600 mb-1">No. Telepon</label>
              <input v-model="form.phone_number" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400" />
            </div>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs text-slate-400 mb-1">No. Identitas</label>
                <div class="px-4 py-2.5 rounded-xl bg-slate-50 text-slate-500 font-mono text-sm">{{ props.passenger.identity_number }}</div>
              </div>
              <div>
                <label class="block text-xs text-slate-400 mb-1">Tipe Penumpang</label>
                <div class="px-4 py-2.5 rounded-xl bg-slate-50 text-slate-500 text-sm">{{ props.passenger.passenger_type }}</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Data dari passenger_details -->
        <div>
          <h2 class="font-mono text-[11px] uppercase tracking-widest text-slate-400 mb-3">Kontak Darurat</h2>
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-slate-600 mb-1">Alamat</label>
              <textarea v-model="form.address" rows="2" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400"></textarea>
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-600 mb-1">Nama Kontak Darurat</label>
              <input v-model="form.emergency_contact_name" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400" />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-600 mb-1">Telepon Kontak Darurat</label>
              <input v-model="form.emergency_contact_phone" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400" />
            </div>
          </div>
        </div>

        <button class="bg-gradient-to-r from-orange-600 to-rose-600 text-white px-6 py-2.5 rounded-xl font-medium hover:opacity-90 transition">
          Simpan Perubahan
        </button>
      </form>
    </div>
  </AppLayout>
</template>