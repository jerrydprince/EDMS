<template>
  <div class="p-6 lg:p-8 max-w-5xl">
    
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-200 dark:border-gray-800">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center text-primary dark:text-emerald-400">
          <Icon icon="lucide:terminal" class="text-2xl" />
        </div>
        <div>
          <h1 class="text-2xl font-bold text-slate-900 dark:text-white">System Diagnostics & Development</h1>
          <p class="text-sm text-slate-500 dark:text-gray-400">Live runtime telemetry, platform architecture, and engineering team credits.</p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          System Operational
        </span>
      </div>
    </div>

    <!-- Live System Telemetry Card -->
    <div class="my-6 p-6 rounded-3xl border border-slate-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm space-y-4">
      <div class="flex items-center justify-between border-b border-slate-100 dark:border-gray-800 pb-3">
        <div class="flex items-center gap-2">
          <Icon icon="lucide:cpu" class="text-primary text-base" />
          <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Enterprise Server Diagnostics</h2>
        </div>
        <span class="text-[11px] font-mono text-slate-400">PHP {{ telemetry.php_version || '8.2+' }} | Laravel {{ telemetry.laravel_version || '10.x' }}</span>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-gray-800/60 border border-slate-200/80 dark:border-gray-700">
          <span class="text-[11px] text-slate-400 block mb-0.5">Environment</span>
          <span class="text-xs font-mono font-bold text-slate-800 dark:text-gray-200 uppercase">{{ telemetry.app_env || 'Production' }}</span>
        </div>

        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-gray-800/60 border border-slate-200/80 dark:border-gray-700">
          <span class="text-[11px] text-slate-400 block mb-0.5">Database Driver</span>
          <span class="text-xs font-mono font-bold text-slate-800 dark:text-gray-200 uppercase">{{ telemetry.database_driver || 'MySQL' }}</span>
        </div>

        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-gray-800/60 border border-slate-200/80 dark:border-gray-700">
          <span class="text-[11px] text-slate-400 block mb-0.5">Disk Storage Free</span>
          <span class="text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ telemetry.storage_free || 'Adequate' }}</span>
        </div>

        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-gray-800/60 border border-slate-200/80 dark:border-gray-700">
          <span class="text-[11px] text-slate-400 block mb-0.5">Host Operating System</span>
          <span class="text-xs font-mono font-bold text-slate-800 dark:text-gray-200 truncate block">{{ telemetry.server_os || 'Enterprise Linux / Win' }}</span>
        </div>
      </div>
    </div>

    <!-- Software Architecture & About -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/80 dark:border-gray-800 p-6 space-y-3 mb-6">
      <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-gray-800">
        <Icon icon="lucide:code-2" class="text-primary text-base" />
        <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Solution Architecture</h2>
      </div>
      <p class="text-xs text-slate-600 dark:text-gray-300 leading-relaxed">
        The Electronic Document Management System (EDMS Enterprise) is architected and maintained by Ansel Computers Limited. Built on high-concurrency micro-event dispatch, robust audit log streaming, and cryptographic digital signatures, this system empowers governmental agencies and private enterprises with seamless paperless registries, workflow approvals, and audit trail fidelity.
      </p>
    </div>

    <!-- Engineering & Product Team -->
    <div class="bg-white dark:bg-gray-900 rounded-2xl border border-slate-200/80 dark:border-gray-800 p-6 space-y-5">
      <div class="flex items-center gap-2 pb-2 border-b border-slate-100 dark:border-gray-800">
        <Icon icon="lucide:users" class="text-primary text-base" />
        <h2 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Engineering & Design Team</h2>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div 
          v-for="(member, idx) in team" 
          :key="idx"
          class="flex flex-col items-center text-center p-3 rounded-2xl bg-slate-50 dark:bg-gray-800/60 border border-slate-200/80 dark:border-gray-700/80 hover:shadow-xs transition"
        >
          <img 
            :src="member.img" 
            :alt="member.name" 
            class="w-16 h-16 rounded-2xl object-cover bg-white dark:bg-gray-700 border border-slate-200 dark:border-gray-600 mb-2 p-0.5"
          />
          <h3 class="text-xs font-bold text-slate-900 dark:text-white truncate w-full">{{ member.name }}</h3>
          <p class="text-[10px] text-slate-400 truncate w-full">{{ member.title }}</p>
        </div>
      </div>
    </div>

    <!-- Contact & Vendor Details -->
    <div class="mt-6 p-6 rounded-2xl bg-slate-900 text-white flex flex-col md:flex-row items-center justify-between gap-6">
      <div>
        <div class="flex items-center gap-2 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-1">
          <Icon icon="lucide:shield-check" />
          <span>Vendor Support & Licensing</span>
        </div>
        <h3 class="text-base font-bold">Ansel Computers Limited</h3>
        <p class="text-xs text-slate-400 mt-1">Suite 27 Upstairs, Zone 3 Shopping Complex, Wuse Abuja, Nigeria.</p>
      </div>

      <div class="flex flex-col sm:flex-row items-center gap-4 text-xs font-mono">
        <div class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300">
          Tel: (+234) 803 302 1969
        </div>
        <div class="px-4 py-2 rounded-xl bg-slate-800 border border-slate-700 text-slate-300">
          support@anselcomputers.ng
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

import jerry from "../../../assets/images/developer/jerry.svg";
import emma from "../../../assets/images/developer/emma.svg";
import dave from "../../../assets/images/developer/dave.svg";
import okafor from "../../../assets/images/developer/okafor.svg";
import james from "../../../assets/images/developer/james.svg";
import fidelis from "../../../assets/images/developer/fidelis.svg";

const props = defineProps({
  systemInfo: Object
});

const page = usePage();

const telemetry = computed(() => {
  return props.systemInfo || page.props.systemInfo || {};
});

const team = [
  { img: jerry, name: "Jerry Ikediashi", title: "Project Lead" },
  { img: emma, name: "Falaju Emmanuel", title: "Graphic Designer" },
  { img: dave, name: "Fayeye David", title: "Product Designer" },
  { img: okafor, name: "Emeka Okafor", title: "Software Developer" },
  { img: james, name: "James Oluwabukola", title: "Full Stack Developer" },
  { img: fidelis, name: "Fidelis Okoh", title: "Software Developer" }
];
</script>
