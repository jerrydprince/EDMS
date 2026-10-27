<template>
	<Dialog
		v-model:visible="visible"
		:closable="false"
		modal
		:pt="{
			root: { class: 'rounded-2xl overflow-hidden shadow-2xl border border-slate-100 bg-white' },
			content: { class: '!px-6 !pt-5 !pb-6 bg-white' },
			header: { class: '!hidden' }
		}"
		:style="{ width: '26rem' }"
		:breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
	>
		<div class="relative">
			<!-- Close Interface Icon -->
			<button 
				type="button" 
				@click="cancelDelete" 
				class="absolute top-0 right-0 w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors flex items-center justify-center cursor-pointer"
				aria-label="Close"
			>
				<Icon icon="solar:close-circle-linear" width="22" />
			</button>

			<section class="flex flex-col items-center pt-2">
				<div class="relative items-center justify-center flex mb-4">
					<img :src="grid" alt="" class="w-32 opacity-80">
					<img :src="caution" alt="" class="absolute w-12 h-12">
				</div>
				<div class="text-center w-full px-2">
					<h3 class="text-lg font-bold text-slate-900 mb-1.5">Deactivate Officer Account</h3>
					<p class="text-xs text-slate-500 leading-relaxed">
						Are you sure you want to delete this officer record? They will lose active registry access, desk delegation, and signing clearance.
					</p>
				</div>
			</section>

			<div class="flex items-center justify-center gap-3 mt-6 pt-4 border-t border-slate-100">
				<button 
					type="button" 
					@click="cancelDelete"
					class="w-full py-2.5 px-4 text-xs font-bold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-xl transition-all cursor-pointer shadow-2xs"
				>
					Cancel
				</button>
				<button 
					type="button" 
					@click="confirmDelete"
					class="w-full py-2.5 px-4 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-xl transition-all cursor-pointer shadow-sm shadow-rose-600/20"
				>
					Confirm Deletion
				</button>
			</div>
		</div>
	</Dialog>
</template>

<script setup>
import { defineEmits, ref } from "vue";
import { Icon } from "@iconify/vue";
import grid from "../../../../public/images/grid.svg";
import caution from "../../../../public/images/caution.svg";

const emit = defineEmits(['confirmed', 'cancelled']);
const visible = ref(true);

const confirmDelete = () => {
  emit('confirmed');
};

const cancelDelete = () => {
  emit('cancelled');
};
</script>

<style scoped>
</style>
