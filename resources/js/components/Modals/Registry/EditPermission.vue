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
	 <template #header>
       <div class=" flex items-start justify-between w-full">
         <div class=" flex items-center gap-3">
           <div class=" p-3 border rounded-full">
            <Icon icon="ant-design:edit-outlined" width="30" />
           </div>
           <div>
            <h1 class=" text-2xl font-medium">Edit User Permission</h1>
            <p class=" text-sm">Customize and manage user’s access & permissions </p>
           </div>
         </div>

       
       </div>
         
     </template>
		<Divider class=" -mt-2"/>
<section class=" px-7">
    <div v-for="(permission, index) in permissions" :key="index">
            <div class="flex items-center justify-between mb-5">
              <div>
                <h1 class=" font-semibold">{{ permission.head }}</h1>
                <p class="font-light text-base">{{ permission.desc }}</p>
              </div>
              <label class="switch">
                <input type="checkbox"  v-model="toggle" :value="permission.id" />
                <span class="slider round"></span>
              </label>
            </div>
          </div>
</section>
   
        <Divider class=" -mb-2"/>
		<template #footer>
			
            <Button
            @click="submit"
				label="Assign User"
				class=""
				:pt="{
          root: {
            class:
              'py-2  text-white bg-primary rounded-lg py-2 px-7 font-light  rounded-lg',
          },
        }"
				
			/>
		</template>
	</Dialog>
</template>

<script setup>
import { defineEmits, defineProps, computed ,ref} from "vue";
import { useToast } from "primevue/usetoast";
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);
const toggle = ref([])
const permissions = ref([

      {
        id: 1010,
        head: "View Registry",
        desc: "Grants unrestricted access view movement of documents in the registry,",
      
      },
      {
        id: 1011,
        head: "Create New Entry",
        desc: "Grants unrestricted access view movement of documents in the registry,",
      
      },
      
      {
        id: 1012,
        head: "Add Destination",
        desc: "Grants unrestricted access view movement of documents in the registry,",
      
      },

])



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
//   toast.add({
//     severity: "success",
//     summary: '',
//     detail: "",
//     life: 3000,
//   });
};
</script>
<style scoped>
.switch {
  position: relative;
  display: inline-block;
  width: 60px;
  height: 34px;
}

.switch input { 
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  -webkit-transition: .4s;
  transition: .4s;
}

.slider:before {
  position: absolute;
  content: "";
  height: 26px;
  width: 26px;
  left: 4px;
  bottom: 4px;
  background-color: white;
  -webkit-transition: .4s;
  transition: .4s;
}

input:checked + .slider {
  background-color: #0D6E1A;
}

input:focus + .slider {
  box-shadow: 0 0 1px #0D6E1A;
}

input:checked + .slider:before {
  -webkit-transform: translateX(26px);
  -ms-transform: translateX(26px);
  transform: translateX(26px);
}

/* Rounded sliders */
.slider.round {
  border-radius: 34px;
}

.slider.round:before {
  border-radius: 50%;
}
</style>
