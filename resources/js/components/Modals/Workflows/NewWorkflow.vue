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
                    <div class=" p-1 bg-primary rounded-full w-fit absolute right-1 opacity-0" :class="{'opacity-100' : selectedIndex === index}">
                        <Icon icon="icon-park-solid:correct" class=" text-white" width="10" />
                    </div>
                    <div class=" bg-gray-100 rounded-full p-3 w-fit border "    :style="{ borderColor:  selectedIndex === index ? '#0D6E1A' : '#F9FAFB' }" >
                    <img :src="emoji.image" alt="">
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
import Emoji_1 from '../../../assets/images/Emojis/Emoji_1.svg'
import Emoji_2 from '../../../assets/images/Emojis/Emoji_2.svg'
import Emoji_3 from '../../../assets/images/Emojis/Emoji_3.svg'
import Emoji_4 from '../../../assets/images/Emojis/Emoji_4.svg'
import Emoji_5 from '../../../assets/images/Emojis/Emoji_5.svg'
import Emoji_6 from '../../../assets/images/Emojis/Emoji_6.svg'
import Emoji_7 from '../../../assets/images/Emojis/Emoji_7.svg'
import Emoji_8 from '../../../assets/images/Emojis/Emoji_8.svg'
import Emoji_9 from '../../../assets/images/Emojis/Emoji_9.svg'
import Emoji_10 from '../../../assets/images/Emojis/Emoji_10.svg'
import Emoji_11 from '../../../assets/images/Emojis/Emoji_11.svg'
import Emoji_12 from '../../../assets/images/Emojis/Emoji_12.svg'
import Emoji_13 from '../../../assets/images/Emojis/Emoji_13.svg'
import Emoji_14 from '../../../assets/images/Emojis/Emoji_14.svg'

const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);
const selectedIndex = ref(null)
const emojis = [
    {
        image:Emoji_1
    },
    {
      image:Emoji_2
    },
    {
      image:Emoji_3
    },
    {
      image:Emoji_4
    },
    {
      image:Emoji_5
    },
    {
      image:Emoji_6
    },
    {
      image:Emoji_7
    },
    {
      image:Emoji_8
    },
    {
      image:Emoji_9
    },
    {
      image:Emoji_10
    },
    {
      image:Emoji_11
    },
    {
      image:Emoji_12
    },
    {
      image:Emoji_13
    },
    {
      image:Emoji_14
    }
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
