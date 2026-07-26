<!-- resources/js/Components/TicketStub.vue -->
<script setup>
defineProps({
  title: String,       // nama kereta / judul utama
  subtitle: String,    // rute atau info sekunder
  code: String,        // booking_code / schedule id
  value: String,       // harga / status, ditampilkan mono besar di stub
  badge: String,       // label kelas / status
  badgeColor: { type: String, default: 'signal' }, // 'signal' | 'rail' | 'slate2'
})
</script>

<template>
  <div class="ticket-stub shadow-sm border border-ink/10 flex overflow-hidden">
    <div class="flex-1 p-5">
      <div class="flex items-center gap-2 mb-1">
        <h3 class="font-display font-semibold text-lg">{{ title }}</h3>
        <span
          v-if="badge"
          class="font-mono text-[10px] uppercase tracking-widest px-2 py-0.5 rounded-full"
          :class="{
            'bg-signal/10 text-signal': badgeColor === 'signal',
            'bg-rail/10 text-rail': badgeColor === 'rail',
            'bg-slate2/10 text-slate2': badgeColor === 'slate2',
          }"
        >
          {{ badge }}
        </span>
      </div>
      <p class="text-sm text-slate2">{{ subtitle }}</p>
      <slot name="extra" />
    </div>

    <div class="ticket-stub__divider w-32 flex flex-col items-center justify-center bg-paper/60 relative px-3 py-5">
      <span class="ticket-stub__notch -top-2"></span>
      <span class="ticket-stub__notch -bottom-2"></span>
      <span class="font-mono text-[10px] uppercase tracking-widest text-slate2 mb-1">{{ code }}</span>
      <span class="font-mono font-medium text-ink text-center">{{ value }}</span>
    </div>
  </div>
</template>