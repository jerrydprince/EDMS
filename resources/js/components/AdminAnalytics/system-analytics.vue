<template>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 w-full mb-6">
        <!-- System Status Card -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col items-center justify-center transition-transform hover:-translate-y-1 duration-300">
            <div v-if="props.stat.is_online === 'Online'" class="flex flex-col items-center">
                <div class="relative flex h-20 w-20 items-center justify-center mb-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-30"></span>
                    <div class="relative h-16 w-16 rounded-full bg-green-100 text-green-500 flex items-center justify-center border-4 border-green-50 dark:border-green-900">
                        <Icon icon="eva:flash-fill" width="32" />
                    </div>
                </div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight">System Operational</h2>
                <span class="mt-3 px-4 py-1.5 bg-green-50 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-full text-sm font-semibold border border-green-200 dark:border-green-800 shadow-sm">
                    <Icon icon="oui:dot" class="inline mr-1" /> Online
                </span>
            </div>
            <div v-else class="flex flex-col items-center">
                <div class="relative flex h-20 w-20 items-center justify-center mb-4">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-30"></span>
                    <div class="relative h-16 w-16 rounded-full bg-red-100 text-red-500 flex items-center justify-center border-4 border-red-50 dark:border-red-900">
                        <Icon icon="ion:cloud-offline-outline" width="32" />
                    </div>
                </div>
                <h2 class="text-xl font-bold text-gray-800 dark:text-white tracking-tight">System Offline</h2>
                <span class="mt-3 px-4 py-1.5 bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-full text-sm font-semibold border border-red-200 dark:border-red-800 shadow-sm">
                    <Icon icon="oui:dot" class="inline mr-1" /> Offline
                </span>
            </div>
        </div>

        <!-- Uptime Percentage Card -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-center transition-transform hover:-translate-y-1 duration-300 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 text-blue-50 dark:text-blue-900/20 opacity-50">
                <Icon icon="lucide:activity" width="120" />
            </div>
            <div class="flex items-center gap-3 mb-4 text-gray-500 dark:text-gray-400 relative z-10">
                <div class="p-2.5 bg-blue-50 dark:bg-blue-900/30 text-blue-500 rounded-xl">
                    <Icon icon="lucide:activity" width="20" />
                </div>
                <span class="font-medium">Uptime (24h)</span>
            </div>
            <div class="text-5xl font-extrabold text-gray-800 dark:text-white my-2 relative z-10 tracking-tight">{{ props.stat.uptime_percentage }}<span class="text-3xl text-gray-400">%</span></div>
            <div class="flex items-center gap-2 mt-2 relative z-10">
                <span class="text-sm font-medium text-green-500 bg-green-50 dark:bg-green-900/30 px-2 py-0.5 rounded-md">+0.01%</span>
                <p class="text-sm text-gray-400 font-medium">Target: 99.99% SLA</p>
            </div>
        </div>

        <!-- Downtime Card -->
        <div class="bg-white dark:bg-gray-800 rounded-3xl p-8 shadow-sm border border-gray-100 dark:border-gray-700 flex flex-col justify-center transition-transform hover:-translate-y-1 duration-300 relative overflow-hidden">
            <div class="absolute -right-6 -top-6 text-orange-50 dark:text-orange-900/20 opacity-50">
                <Icon icon="lucide:clock" width="120" />
            </div>
            <div class="flex items-center gap-3 mb-4 text-gray-500 dark:text-gray-400 relative z-10">
                <div class="p-2.5 bg-orange-50 dark:bg-orange-900/30 text-orange-500 rounded-xl">
                    <Icon icon="lucide:clock" width="20" />
                </div>
                <span class="font-medium">Total Downtime (24h)</span>
            </div>
            <div class="text-5xl font-extrabold text-gray-800 dark:text-white my-2 relative z-10 tracking-tight">{{ props.stat.downtime }}</div>
            <p class="text-sm text-gray-400 font-medium mt-2 relative z-10">Total time spent offline</p>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from "vue";
const props = defineProps(["stat"]);
</script>
