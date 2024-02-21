<template>
  <main class="flex items-center justify-center w-full h-[35rem]">
    <div class="text-center m-auto">
      <Emptystate class="m-auto" />
      <p class="mb-1 mt-3">No Records Yet</p>
      <p class="text-gray-500 font-light">Departments, Divisions & Units created show up here</p>
      <slot></slot>
    </div>
  </main>
</template>

<script setup>
import Emptystate from '../assets/Emptystate.vue';
</script>
