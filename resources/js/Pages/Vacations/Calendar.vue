<script setup>
import { ref, computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    vacations: Array,
});

const currentDate = ref(new Date());

const year = computed(() => currentDate.value.getFullYear());
const month = computed(() => currentDate.value.getMonth());

const monthNames = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];

const daysInMonth = computed(() => {
    return new Date(year.value, month.value + 1, 0).getDate();
});

const firstDayOfMonth = computed(() => {
    let day = new Date(year.value, month.value, 1).getDay();
    // 0: Sunday, 1: Monday, ..., 6: Saturday
    // We want Monday to be 0, ..., Sunday to be 6
    return day === 0 ? 6 : day - 1;
});

const prevMonth = () => {
    currentDate.value = new Date(year.value, month.value - 1, 1);
};

const nextMonth = () => {
    currentDate.value = new Date(year.value, month.value + 1, 1);
};

const calendarDays = computed(() => {
    const days = [];
    const prevMonthLastDay = new Date(year.value, month.value, 0).getDate();

    // Previous month's trailing days
    for (let i = firstDayOfMonth.value - 1; i >= 0; i--) {
        days.push({
            day: prevMonthLastDay - i,
            currentMonth: false,
            date: new Date(year.value, month.value - 1, prevMonthLastDay - i)
        });
    }

    // Current month's days
    for (let i = 1; i <= daysInMonth.value; i++) {
        days.push({
            day: i,
            currentMonth: true,
            date: new Date(year.value, month.value, i)
        });
    }

    // Next month's leading days
    const totalSlots = 42; // 6 weeks
    const remainingSlots = totalSlots - days.length;
    for (let i = 1; i <= remainingSlots; i++) {
        days.push({
            day: i,
            currentMonth: false,
            date: new Date(year.value, month.value + 1, i)
        });
    }

    return days;
});

const getVacationsForDay = (date) => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const dateString = `${year}-${month}-${day}`;
    return props.vacations.filter(vacation => {
        return dateString >= vacation.start_date && dateString <= vacation.end_date;
    });
};

const isToday = (date) => {
    const today = new Date();
    return date.getDate() === today.getDate() &&
        date.getMonth() === today.getMonth() &&
        date.getFullYear() === today.getFullYear();
};
</script>

<template>
    <AppLayout title="Vacation Calendar">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Vacation Calendar
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-medium text-gray-900">
                            {{ monthNames[month] }} {{ year }}
                        </h3>
                        <div class="flex space-x-2">
                            <button @click="prevMonth" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">
                                Previous
                            </button>
                            <button @click="nextMonth" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">
                                Next
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-7 gap-px bg-gray-200 border border-gray-200 rounded-lg overflow-hidden">
                        <!-- Weekdays -->
                        <div v-for="day in ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" :key="day"
                             class="bg-gray-100 py-2 text-center text-sm font-semibold text-gray-700">
                            {{ day }}
                        </div>

                        <!-- Calendar Days -->
                        <div v-for="(calendarDay, index) in calendarDays" :key="index"
                             class="min-h-32 bg-white p-2 flex flex-col group transition hover:bg-gray-50"
                             :class="{ 'bg-gray-50 text-gray-400': !calendarDay.currentMonth }">
                            <div class="flex justify-between items-start">
                                <span :class="{ 'bg-indigo-600 text-white rounded-full w-6 h-6 flex items-center justify-center': isToday(calendarDay.date) }">
                                    {{ calendarDay.day }}
                                </span>
                            </div>

                            <div class="mt-2 space-y-1 overflow-y-auto max-h-24">
                                <div v-for="vacation in getVacationsForDay(calendarDay.date)" :key="vacation.id"
                                     class="text-xs p-1 rounded bg-indigo-100 text-indigo-700 border border-indigo-200 truncate"
                                     :title="vacation.substitute_name">
                                    {{ vacation.user_name }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
