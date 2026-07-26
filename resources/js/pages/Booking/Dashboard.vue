<!-- resources/js/Pages/Booking/Dashboard.vue -->
<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { PageProps, TrainClass } from '@/types';

interface ScheduleItem {
    schedule_id: number;
    train_name: string;
    train_class: TrainClass;
    origin_station: string;
    destination_station: string;
    departure_time: string;
    base_ticket_price: number;
    seats_available: number;
}

const props = defineProps<{ schedules: ScheduleItem[] }>();
const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);

const step = ref<'select' | 'confirm'>('select');
const search = ref('');
const classFilter = ref<'ALL' | TrainClass>('ALL');
const selected = ref<ScheduleItem | null>(null);

const filtered = computed<ScheduleItem[]>(() =>
    props.schedules.filter((s) => {
        const haystack =
            `${s.origin_station} ${s.destination_station} ${s.train_name}`.toLowerCase();
        const matchesSearch =
            search.value === '' ||
            haystack.includes(search.value.toLowerCase());
        const matchesClass =
            classFilter.value === 'ALL' || s.train_class === classFilter.value;
        
            return matchesSearch && matchesClass;
    }),
);

interface BookingForm {
    schedule_id: number | null;
}
const form = useForm<BookingForm>({ schedule_id: null });

function choose(schedule: ScheduleItem): void {
    if (schedule.seats_available <= 0) {
      return;
    }
    
    selected.value = schedule;
    form.schedule_id = schedule.schedule_id;
    step.value = 'confirm';
}

function goBack(): void {
    step.value = 'select';
    selected.value = null;
}

function confirmBooking(): void {
    form.post('/booking');
}

function priceFmt(p: number): string {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
    }).format(p);
}

const classBadge: Record<TrainClass, string> = {
    EXECUTIVE: 'bg-violet-100 text-violet-700',
    BUSINESS: 'bg-blue-100 text-blue-700',
    ECONOMY: 'bg-teal-100 text-teal-700',
};
</script>

<template>
    <AppLayout>
        <div class="mb-6 flex items-center justify-between">
            <h1 class="font-display text-3xl font-bold">Pesan Tiket</h1>
            <div class="text-sm text-slate-500">
                Booking atas nama
                <span class="font-medium text-ink">{{ user?.name }}</span>
            </div>
        </div>

        <!-- STEP 1: cari & pilih jadwal -->
        <div v-if="step === 'select'">
            <div class="mb-6 flex flex-col gap-3 sm:flex-row">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Cari stasiun atau nama kereta..."
                    class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 focus:ring-2 focus:ring-orange-400 focus:outline-none"
                />
                <select
                    v-model="classFilter"
                    class="rounded-xl border border-slate-200 px-4 py-2.5"
                >
                    <option value="ALL">Semua Kelas</option>
                    <option value="EXECUTIVE">Executive</option>
                    <option value="BUSINESS">Business</option>
                    <option value="ECONOMY">Economy</option>
                </select>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <button
                    v-for="s in filtered"
                    :key="s.schedule_id"
                    type="button"
                    :disabled="s.seats_available <= 0"
                    @click="choose(s)"
                    class="rounded-2xl border border-slate-200 bg-white p-5 text-left shadow-sm transition hover:border-orange-300 hover:shadow-md disabled:cursor-not-allowed disabled:opacity-40"
                >
                    <div class="mb-2 flex items-center justify-between">
                        <span class="font-display text-lg font-semibold">{{
                            s.train_name
                        }}</span>
                        <span
                            class="rounded-full px-2 py-1 font-mono text-[10px] tracking-widest uppercase"
                            :class="classBadge[s.train_class]"
                        >
                            {{ s.train_class }}
                        </span>
                    </div>
                    <div class="mb-3 text-sm text-slate-500">
                        {{ s.origin_station }} → {{ s.destination_station }}
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="font-mono font-semibold text-orange-600">{{
                            priceFmt(s.base_ticket_price)
                        }}</span>
                        <span
                            class="text-xs"
                            :class="
                                s.seats_available <= 5
                                    ? 'text-rose-500'
                                    : 'text-slate-400'
                            "
                        >
                            {{
                                s.seats_available > 0
                                    ? `${s.seats_available} kursi tersisa`
                                    : 'Penuh'
                            }}
                        </span>
                    </div>
                </button>
            </div>

            <p
                v-if="filtered.length === 0"
                class="py-12 text-center text-slate-400"
            >
                Tidak ada jadwal yang cocok.
            </p>
        </div>

        <!-- STEP 2: konfirmasi -->
        <div
            v-else-if="step === 'confirm' && selected"
            class="mx-auto max-w-md"
        >
            <button
                type="button"
                @click="goBack"
                class="mb-4 text-sm text-slate-500 hover:text-orange-600"
            >
                &larr; Pilih jadwal lain
            </button>

            <div
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <h2 class="mb-4 font-display text-xl font-semibold">
                    Konfirmasi Booking
                </h2>

                <dl class="mb-6 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Kereta</dt>
                        <dd class="font-medium">{{ selected.train_name }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Rute</dt>
                        <dd class="font-medium">
                            {{ selected.origin_station }} →
                            {{ selected.destination_station }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Berangkat</dt>
                        <dd class="font-mono">{{ selected.departure_time }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Penumpang</dt>
                        <dd class="font-medium">{{ user?.name }}</dd>
                    </div>
                    <div
                        class="flex justify-between border-t border-slate-100 pt-2 text-base"
                    >
                        <dt class="text-slate-500">Total</dt>
                        <dd class="font-mono font-semibold text-orange-600">
                            {{ priceFmt(selected.base_ticket_price) }}
                        </dd>
                    </div>
                </dl>

                <!-- <div
                    v-if="form.errors.booking"
                    class="mb-3 text-sm text-rose-600"
                >
                    {{ form.errors.booking }}
                </div> -->

                <button
                    @click="confirmBooking"
                    :disabled="form.processing"
                    class="w-full rounded-xl bg-gradient-to-r from-orange-600 to-rose-600 py-3 font-medium text-white transition hover:opacity-90 disabled:opacity-50"
                >
                    {{
                        form.processing ? 'Memproses...' : 'Konfirmasi & Bayar'
                    }}
                </button>
            </div>
        </div>
    </AppLayout>
</template>
