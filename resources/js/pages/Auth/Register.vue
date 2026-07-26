<!-- resources/js/Pages/Auth/Register.vue -->
<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'

interface RegisterForm {
  name: string; email: string; identity_number: string; phone_number: string
  password: string; password_confirmation: string
}
const form = useForm<RegisterForm>({
  name: '', email: '', identity_number: '', phone_number: '',
  password: '', password_confirmation: '',
})

function submit(): void {
  form.post('/register', { onFinish: () => form.reset('password', 'password_confirmation') })
}
</script>

<template>
  <div class="min-h-screen grid lg:grid-cols-2 font-body text-ink">
    <div class="hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-violet-700 via-rose-500 to-orange-500 text-white">
      <span class="font-display font-bold text-xl">RAILWAY//SYS</span>
      <h2 class="font-display font-bold text-4xl leading-tight">Satu akun,<br />segala perjalanan.</h2>
      <span class="text-xs text-white/60 font-mono">© Railway Passenger &amp; Ticketing System</span>
    </div>

    <div class="flex items-center justify-center px-6 py-12 bg-paper">
      <div class="w-full max-w-sm">
        <h1 class="font-display font-bold text-3xl mb-1">Buat Akun</h1>
        <p class="text-slate-500 mb-6 text-sm">Isi data diri untuk mulai memesan tiket.</p>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Nama Lengkap</label>
            <input v-model="form.name" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
            <input v-model="form.email" type="email" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400" />
            <p v-if="form.errors.email" class="text-rose-600 text-xs mt-1">{{ form.errors.email }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">No. Identitas (KTP/Paspor)</label>
            <input v-model="form.identity_number" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400" />
            <p v-if="form.errors.identity_number" class="text-rose-600 text-xs mt-1">{{ form.errors.identity_number }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">No. Telepon</label>
            <input v-model="form.phone_number" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Kata Sandi</label>
            <input v-model="form.password" type="password" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400" />
          </div>
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Konfirmasi Kata Sandi</label>
            <input v-model="form.password_confirmation" type="password" class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-violet-400" />
          </div>

          <button :disabled="form.processing"
            class="w-full bg-gradient-to-r from-violet-700 to-rose-600 text-white font-medium py-3 rounded-xl hover:opacity-90 disabled:opacity-50 transition">
            {{ form.processing ? 'Memproses...' : 'Daftar' }}
          </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
          Sudah punya akun?
          <Link :href="'/login'" class="text-violet-700 font-medium hover:underline">Masuk</Link>
        </p>
      </div>
    </div>
  </div>
</template>