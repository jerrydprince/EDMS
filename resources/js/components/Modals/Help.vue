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
	<template  #header>
        
        <div class=" flex w-full flex-col items-center justify-between">
            <div class=" flex items-center gap-2 text-primary border rounded-2xl px-2 py-1">
                <Icon icon="mdi:message-outline" />
                <p>Message Us</p>
            </div>
            <h1 class=" font-semibold text-xl pt-3">How can we help you?</h1>
        </div>
    </template>
    <Divider class=" -mt-1"/>

    <section class=" px-6">
        <div class=" flex items-center justify-between">
       <div class=" w-[47%]">
         <label for="" class=" font-normal">First Name</label>
         <input type="text" class=" border outline-none p-2 rounded-md mt-2"  placeholder="Enter first name">
       </div>

       <div class=" w-[47%]">
         <label for="" class=" font-normal">Last Name</label>
         <input type="text" class=" border outline-none p-2 rounded-md mt-2"  placeholder="Enter last name">
       </div>
    </div>

    <div class=" mt-5">
        <Label class=" font-normal">Email</Label>
        <div class=" flex items-center border border-gray-300 rounded-md overflow-hidden pl-2">
            <Icon icon="lucide:user" width="20" />
            <input type="text" class=" p-2 outline-none w-full" placeholder="Enter email address">
        </div>
    </div>

    <div class=" mt-5">
        <h1>Message</h1>
        <textarea name="" id="" cols="20" rows="3" class=" mt-1 p-2 outline-none resize-none border rounded-md w-full"></textarea>
    </div>
    </section>
<Divider/>
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
  
  emit("update:modelValue", false);
};

const submit = () => {
    
  emit("update:modelValue", false);
  toast.add({
    severity: "success",
    summary: 'Message Recieved',
    detail: "Our support team would be in contact with you shortly.Our support team would be in contact with you shortly.",
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