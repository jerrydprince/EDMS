<template>
  <div class="flex flex-col items-center justify-center w-full h-[91vh] bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/20 px-6 py-12 text-center select-none overflow-hidden relative">
    <!-- Background decorative blur blobs -->
    <div class="absolute -top-24 -left-24 w-72 h-72 bg-blue-200/40 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-indigo-200/40 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative z-10 max-w-md flex flex-col items-center">
      <!-- Icon with pulse ring -->
      <div class="relative mb-6">
        <div class="absolute inset-0 rounded-3xl bg-primary/20 blur-xl animate-pulse"></div>
        <div class="relative w-20 h-20 rounded-3xl bg-white shadow-xl shadow-primary/10 border border-blue-100 flex items-center justify-center transform hover:scale-105 transition-transform duration-300">
          <Icon icon="fluent:chat-bubbles-question-24-regular" class="text-4xl text-primary" />
        </div>
      </div>

      <!-- Welcome Title & Subtitle -->
      <h2 class="text-2xl font-bold text-gray-900 tracking-tight mb-2">
        EDMS Collaboration Hub
      </h2>
      <p class="text-sm text-gray-500 leading-relaxed max-w-sm mb-8">
        Connect in real-time with colleagues across Departments, Divisions, and Units. Share files, reference tasks, and streamline workflow discussions.
      </p>

      <!-- Quick Feature Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 w-full mb-8">
        <div class="p-3.5 rounded-2xl bg-white/80 backdrop-blur-sm border border-gray-100 shadow-xs hover:shadow-md hover:border-blue-200 transition-all flex flex-col items-center text-center">
          <div class="w-8 h-8 rounded-full bg-blue-50 text-primary flex items-center justify-center mb-2">
            <Icon icon="lucide:building-2" class="text-base" />
          </div>
          <span class="text-xs font-semibold text-gray-800">Branch Groups</span>
          <span class="text-[11px] text-gray-400 mt-0.5">Org hierarchies</span>
        </div>

        <div class="p-3.5 rounded-2xl bg-white/80 backdrop-blur-sm border border-gray-100 shadow-xs hover:shadow-md hover:border-blue-200 transition-all flex flex-col items-center text-center">
          <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center mb-2">
            <Icon icon="lucide:message-square" class="text-base" />
          </div>
          <span class="text-xs font-semibold text-gray-800">Direct Messages</span>
          <span class="text-[11px] text-gray-400 mt-0.5">1-on-1 staff chat</span>
        </div>

        <div class="p-3.5 rounded-2xl bg-white/80 backdrop-blur-sm border border-gray-100 shadow-xs hover:shadow-md hover:border-blue-200 transition-all flex flex-col items-center text-center">
          <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center mb-2">
            <Icon icon="lucide:file-text" class="text-base" />
          </div>
          <span class="text-xs font-semibold text-gray-800">EDMS Media</span>
          <span class="text-[11px] text-gray-400 mt-0.5">Secure file share</span>
        </div>
      </div>

      <!-- Action hint banner -->
      <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/90 border border-gray-200/80 shadow-xs text-xs text-gray-600">
        <span class="w-2 h-2 rounded-full bg-primary animate-ping"></span>
        <span>Select any conversation or group on the left to start chatting</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Icon } from '@iconify/vue';
</script>
