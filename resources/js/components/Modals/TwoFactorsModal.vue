<template>
	<Dialog
		v-model:visible="visible"
        :closable="false"
		modal
		:pt="{
      content: { class: 'px-0 bg-white pb-6' },
    }"
		:style="{ width: '35rem', padding: '10px 0px' }"
		:breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        class=" .custom-dialog"
	>
	
		<section class="-mb-6  flex flex-col items-center ">
            <div class=" bg-white p-7 -mt-12  relative">
            <img src="/assets/images/grid.svg" alt="" >
        </div>
        <div class=" border rounded-full p-3 absolute bg-[#F9FAFB] top-20"   >
      <Icon icon="lucide:lock" width="50" />
    </div>
    <div v-if=" active === 1" >
        <h1 class=" font-semibold text-2xl  text-center ">Enable 2FA</h1>
        <p class=" text-[#667085] my-3 text-center">Add an extra layer of security to your account.</p>
            <div class=" bg-[#FFFCF5] border-dashed border-[2px] border-[#FEC84B] rounded-md mx-7 p-3">
            <div class="mx-7  ">
                <ul class=" list-disc  text-[#7A2E0E]  ">
                    <li>You would be required to verify your account whenever you log out or sign in from a different device</li>
                    <li class=" mt-5">Authentication would be done by sending a verification code to your email address <span class=" text-[#3B82F6]"> jondoe@ansel.com </span></li>
                </ul>
            </div>
        </div>
    </div>
    <div v-if="active === 2" >
        <h1 class=" font-semibold text-2xl  text-center ">Enable 2FA</h1>
        <p class=" text-[#667085] my-3 text-center">Add an extra layer of security to your account.</p>
        <div class=" w-[500px] ">
        
       <label for="">Password</label> <br>
       <div class="border border-gray-300 rounded-md flex items-center mt-2">
        <div class=" p-2"  >
          <Icon icon="lucide:lock" width="30" />
        </div>
        <input
      v-model="password"
      :type="passwordFieldType"
      class="w-full outline-none"
      placeholder="Enter Password"
    />
       <div class="pr-2">
      <button @click="togglePasswordVisibility">
        <Icon
          v-if="passwordFieldType === 'password'"
          icon="octicon:eye-closed-16"
          width="30"
          class="pr-2"
        />
        <Icon
          v-if="passwordFieldType === 'text'"
          icon="ph:eye-thin"
          width="30"
        />
      </button>
    </div>
    </div>
    </div>
    </div>
        <div class=" flex gap-3 my-5" v-if=" active<3">
             <div class=" h-4 w-4 rounded-full border" :class="{'bg-[#3B82F6]' : active===1 }"></div>
             <div class=" h-4 w-4 rounded-full border" :class="{'bg-[#3B82F6]' : active===2 }"></div>
        </div>



        <div v-if="active === 3" >
        <div class=" px-7">
        <h1 class=" font-semibold text-2xl  text-center ">Verify Your Identity</h1>
        <p class=" text-[#667085] my-3 text-center"> To ensure security, please enter your current password before proceeding to change it.</p>
        <div class=" w-[500px] ">
        
       <label for="">Password</label> <br>
       <div class="border border-gray-300 rounded-md flex items-center mt-2">
        <div class=" p-2"  >
          <Icon icon="lucide:lock" width="30" />
        </div>
        <input
      v-model="password"
      :type="passwordFieldType"
      class="w-full outline-none"
      placeholder="Enter Password"
    />
       <div class="pr-2">
      <button @click="togglePasswordVisibility">
        <Icon
          v-if="passwordFieldType === 'password'"
          icon="octicon:eye-closed-16"
          width="30"
          class="pr-2"
        />
        <Icon
          v-if="passwordFieldType === 'text'"
          icon="ph:eye-thin"
          width="30"
        />
      </button>
    </div>
    </div>
</div>
    </div>
    
    </div>

    <div v-if="active === 4" >
        <div class=" px-7 flex flex-col items-center justify-center">
            <div class=" w-[80%]">
        <h1 class=" font-semibold text-2xl  text-center ">Change Password</h1>
        <p class=" text-[#667085] my-3 text-center"> Create a secure, complex password with letters, numbers, and symbols.</p>
    </div>
        <div class=" w-[500px] ">
        
       <label for="">Enter Password</label> <br>
       <div class="border border-gray-300 rounded-md flex items-center mt-2">
        <div class=" p-2"  >
          <Icon icon="lucide:lock" width="30" />
        </div>
        <input
      v-model="password"
      :type="passwordFieldType"
      class="w-full outline-none"
      placeholder="Enter Password"
    />
       <div class="pr-2">
      <button @click="togglePasswordVisibility">
        <Icon
          v-if="passwordFieldType === 'password'"
          icon="octicon:eye-closed-16"
          width="30"
          class="pr-2"
        />
        <Icon
          v-if="passwordFieldType === 'text'"
          icon="ph:eye-thin"
          width="30"
        />
      </button>
    </div>
    </div>
</div>
<div class=" w-[500px] mt-5">
        
        <label for="">Re-Enter Password</label> <br>
        <div class="border border-gray-300 rounded-md flex items-center mt-2">
            <div class=" p-2"  >
          <Icon icon="lucide:lock" width="30" />
        </div>
        <input
      v-model="password"
      :type="passwordFieldType"
      class="w-full outline-none"
      placeholder="Enter Password"
    />
       <div class="pr-2">
      <button @click="togglePasswordVisibility">
        <Icon
          v-if="passwordFieldType === 'password'"
          icon="octicon:eye-closed-16"
          width="30"
          class="pr-2"
        />
        <Icon
          v-if="passwordFieldType === 'text'"
          icon="ph:eye-thin"
          width="30"
        />
      </button>
    </div>
     </div>
 </div>
    </div>
    
    </div>
    <Divider/>
		</section>
       
		<template #footer>
			<Button
				class="mr-4"
				:pt="{
          root: {
            class: 'py-2 px-8 border-gray-200 border border-solid rounded-lg',
          },
        }"
				@click="closeModal"
				label="Cancel"
				severity="secondary"
				outlined
			/>
            
			<Button
            v-if="active ===1"
            @click="active =2"
				label="Next"
				class=""
				:pt="{
          root: {
            class:
              'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
          },
        }"
				
			/>
            <Button
            v-if="active === 2"
            @click="active =3"
				label="Enable 2FA"
				class=""
				:pt="{
          root: {
            class:
              'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
          },
        }"
				
			/>

            <Button
            v-if="active === 3"
            @click="active = 4"
				label="Continue"
				class=""
				:pt="{
          root: {
            class:
              'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
          },
        }"
				
			/>

            <Button
            v-if="active === 4"
            @click="submit"
				label="Confirm"
				class=""
				:pt="{
          root: {
            class:
              'py-2 bg-blue_primary px-10 border-0 text-white font-light hover:bg-blue_primary/90 rounded-lg',
          },
        }"
				
			/>
		</template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed ,ref} from "vue";
import { useToast } from "primevue/usetoast";


const active = ref(1)

const toast = useToast();
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);


const showPassword = ref(false);

    // Computed property to determine the input type
    const passwordFieldType = computed(() => (showPassword.value ? 'text' : 'password'));

    // Method to toggle password visibility
    const togglePasswordVisibility = () => {
      showPassword.value = !showPassword.value;
    };

const visible = computed({
  get() {
    return props.modelValue;
  },
  set(value) {
    emit("update:modelValue", value);
  },
});

const closeModal = () => {
    active.value = 1
  emit("update:modelValue", false);
};

const submit = () => {
    active.value = 1
  emit("update:modelValue", false);
  toast.add({
    severity: "success",
    summary: 'Password Changed',
    detail: "Your password has been updated successfully.",
    life: 3000,
  });
};
</script>
 <style scoped>
  .custom-dialog .p-dialog-header {
    display: none; 
    width: 20%;
  }
</style>