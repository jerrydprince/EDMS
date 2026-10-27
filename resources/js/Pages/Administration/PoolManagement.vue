<template>
  <main class="min-h-screen bg-slate-50/70 p-4 sm:p-6 lg:p-8 font-sans">
    <AdministrationActionDesk
      initial-tab="pool"
      :user="$page.props.user"
      :department="$page.props.userDepartment"
    />
  </main>
</template>

<script setup>
import AdministrationActionDesk from '../../components/Administration/AdministrationActionDesk.vue';
</script>
