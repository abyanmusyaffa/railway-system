<!-- resources/js/Pages/Auth/Login.vue -->
<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'

interface LoginForm { email: string; password: string; remember: boolean }
const form = useForm<LoginForm>({ email: '', password: '', remember: false })

function submit(): void {
  form.post('/login', { onFinish: () => form.reset('password') })
}
</script>

<template>
  <div class="min-h-screen grid lg:grid-cols-2 font-body text-ink">
    <div class="hidden lg:flex flex-col justify-between p-12 bg-gradient-to-br from-orange-500 via-rose-500 to-violet-700 text-white">
      <span class="font-display font-bold text-xl">RAILWAY//SYS</span>
      <div>
        <h2 class="font-display font-bold text-4xl leading-tight mb-3">Perjalanan Anda,<br />dimulai di sini.</h2>
        <p class="text-white/80 max-w-sm">Pesan tiket, pantau jadwal, dan kelola perjalanan kereta dalam satu tempat.</p>
      </div>
      <span class="text-xs text-white/60 font-mono">© Railway Passenger &amp; Ticketing System</span>
    </div>

    <div class="flex items-center justify-center px-6 py-12 bg-paper">
      <div class="w-full max-w-sm">
        <h1 class="font-display font-bold text-3xl mb-1">Masuk</h1>
        <p class="text-slate-500 mb-8 text-sm">Masukkan kredensial Anda untuk melanjutkan.</p>

        <form @submit.prevent="submit" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Email</label>
            <input v-model="form.email" type="email"
              class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400" />
            <p v-if="form.errors.email" class="text-rose-600 text-xs mt-1">{{ form.errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-medium text-slate-600 mb-1">Kata Sandi</label>
            <input v-model="form.password" type="password"
              class="w-full border border-slate-200 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-orange-400" />
          </div>

          <label class="flex items-center gap-2 text-sm text-slate-500">
            <input v-model="form.remember" type="checkbox" class="rounded border-slate-300" />
            Ingat saya
          </label>

          <button :disabled="form.processing"
            class="w-full bg-gradient-to-r from-orange-600 to-rose-600 text-white font-medium py-3 rounded-xl hover:opacity-90 disabled:opacity-50 transition">
            {{ form.processing ? 'Memproses...' : 'Masuk' }}
          </button>
        </form>

        <p class="text-center text-sm text-slate-500 mt-6">
          Belum punya akun?
          <Link :href="'/register'" class="text-orange-600 font-medium hover:underline">Daftar</Link>
        </p>
      </div>
    </div>
  </div>
</template>