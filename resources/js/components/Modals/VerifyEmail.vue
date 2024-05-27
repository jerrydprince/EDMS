<template>
	<Dialog
		v-model:visible="visible"
        :closable="false"
		modal
		:pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
		:style="{ width: '30rem', padding: '10px 0px' }"
		:breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        class=" .custom-dialog"
	>
    <template #header>

        <div class=" flex flex-col items-center justify-center relative w-full">
        <img src="/assets/images/grid.svg" alt="" />
        <div class=" p-2 rounded border border-solid bg-white absolute" v-if="activePage===1">
            <Icon icon="mdi:email-outline" width="40" />
        </div>

        <div class=" p-2 rounded-full border border-solid bg-[#12B76A] text-white absolute" v-else>
            <Icon icon="icon-park-solid:correct" width="40" />
        </div>
      </div>
    </template>
	
		<section class=" -mt-12 flex flex-col items-center ">
          
            <div class=" text-center     bg-white p-5" v-if=" activePage ===1" >
                <h1 class=" text-3xl font-semibold">Verify Email Address</h1>
                <p  class=" opacity-70 my-2">Please enter OTP sent to the email address you provided to verify email address</p>
            </div>

            <div class=" text-center     bg-white p-5" v-else>
                <h1 class=" text-3xl font-semibold">Email Verified</h1>
                <p  class=" opacity-70 my-2">Email address as been successfully verified.</p>
            </div>
            <div class=" flex gap-3.5 " v-if="activePage ===1">
			<input type="text" placeholder="0"  class=" border-[2px] outline-none w-14 text-center text-[25px] p-0 rounded-2xl ">
            <input type="text" placeholder="0" class=" border-[2px] outline-none w-14 text-center text-[25px] rounded-2xl ">
            <input type="text" placeholder="0" class=" border-[2px] outline-none w-14 text-center text-[25px] rounded-2xl ">
            <span class=" text-[25px] opacity-50 mb-8">_</span>
            <input type="text" placeholder="0" class=" border-[2px] outline-none w-14 text-center text-[25px] rounded-2xl ">
            <input type="text" placeholder="0" class=" border-[2px] outline-none w-14 text-center text-[25px] rounded-2xl ">
            <input type="text" placeholder="0" class=" border-[2px] outline-none w-14 text-center text-[25px] rounded-2xl ">
        </div>

		</section>
		<template #footer>
		
            <div class=" w-full flex flex-col items-center justify-between  mt-5 mb-5 " :class="{ 'mt-0' : activePage ===2}">
               
			<button class=" bg-[#3575DD] font-medium text-white p-3 rounded-md w-full text-center " v-if="activePage===1" @click="activePage = 2 ">Verify your acccount </button>
            <button class=" bg-[#3575DD] font-medium text-white p-3 rounded-md w-full text-center " @click="submit" v-if="activePage ===2">Confirm  </button>
            <button class="  font-medium w-full text-center mt-5 justify-center gap-2 text-[#3575DD] flex items-center " v-if="activePage ===1"> 
                <Icon icon="basil:refresh-solid" width="25" />
                <p>Resend Code</p>
            </button>

    <!-- <div class=" w-full" v-else>
        <button class=" bg-[#3575DD] font-medium text-white p-3 rounded-md w-full text-center " @click="submit">Confirm</button>
    </div> -->

        </div>
		</template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed ,ref} from "vue";
import { useToast } from "primevue/usetoast";
const activePage = ref(1)
const toast = useToast();
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const closeModal = () => {
  emit("update:modelValue", false);
};

const submit = () => {
    activePage.value = 1
  emit("update:modelValue", false);
};
</script>
 <style scoped>
  .custom-dialog .p-dialog-header {
    display: none; /* Hide the header */
  }
</style>