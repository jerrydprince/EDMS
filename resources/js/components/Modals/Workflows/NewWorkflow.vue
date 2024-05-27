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
            <Icon icon="ep:user" width="30" />
           </div>
           <div>
            <h1 class=" text-2xl font-medium">Create New Workflow</h1>
            <p class=" text-sm">Optimize task management with streamlined workflow</p>
           </div>
         </div>

         <div class=" bg-[#F2F4F7] p-2 rounded-full cursor-pointer"  @click="closeModal">
            <Icon icon="mdi:times" width="20" />
         </div>
       </div>
         
     </template>
		<Divider class=" -mt-2"/>

        <section class=" px-6">
            <label class=" text-[#344054] font-semibold">Workflow Name</label>
            <input type="text" class=' border-solid border-gray-300 mt-1 p-2 border rounded-md outline-none w-full text-sm' placeholder="Title of workflow" required>
        
            <div class=" text-[#344054] font-semibold mt-5">
                <h1>Workflow Emoji</h1>
                <p class=" opacity-60 font-normal">Select emoji that describes workflow</p>
                <div class=" grid grid-cols-7 gap-5 my-5">
                  <div v-for="(emoji,index) in emojis"  :key="index" class=" relative " @click="handleSelectedEmoji(index)" >
                    <div class=" p-1 bg-[#3575DD] rounded-full w-fit absolute right-1 opacity-0" :class="{'opacity-100' : selectedIndex === index}">
                        <Icon icon="icon-park-solid:correct" class=" text-white" width="10" />
                    </div>
                    <div class=" bg-gray-100 rounded-full p-3 w-fit border "    :style="{ borderColor:  selectedIndex === index ? '#3575DD' : '#F9FAFB' }" >
                    <Icon :icon="emoji.icon"  width="25" />
                </div>
                  </div>
                </div>
            </div>

     
        </section>
        <Divider class=" -mb-2"/>
		<template #footer>
			
            <Button
            @click="submit"
				label="Create Workflow"
				class=""
				:pt="{
          root: {
            class:
              'py-2  px-10 border border-gray-300 text-[#344054] font-light  rounded-lg',
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
const selectedIndex = ref(null)
const emojis = [
    {
        icon: "noto:eight-pointed-star"
    },
    {
        icon: "noto:eight-pointed-star"
    },
    {
        icon: "noto:eight-pointed-star"
    },
    {
        icon: "noto:eight-pointed-star"
    },
    {
        icon: "noto:eight-pointed-star"
    },
    {
        icon: "noto:eight-pointed-star"
    },

]

const handleSelectedEmoji = (index) =>{
    selectedIndex.value = index
}

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
  selectedIndex.value = null
};

const submit = () => {
  emit("update:modelValue", false);
    selectedIndex.value = null
//   toast.add({
//     severity: "success",
//     summary: '',
//     detail: "",
//     life: 3000,
//   });
};
</script>
 <style scoped>
  .custom-dialog .p-dialog-header {
    display: none; 
    width: 20%;
  }
  .p-inputtextarea {
    outline: none;
    box-shadow: none !important;
    resize: none;
    width: 100%
}
.p-inputtextarea:focus {
    border-color: #D0D5DD; /* Hide border */
}
</style>