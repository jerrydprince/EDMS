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
            <input type="text" id="title" v-model="title" class=' border-solid border-gray-300 mt-1 p-2 border rounded-md outline-none w-full text-sm' placeholder="Title of workflow" required>
        
            <div class=" text-[#344054] font-semibold mt-5">
                <h1>Workflow Emoji</h1>
                <p class=" opacity-60 font-normal">Select emoji that describes workflow</p>
                <div class=" grid grid-cols-7 gap-5 my-5">
                  <div v-for="(emoji,index) in emojis"  :key="index" class=" relative " @click="handleSelectedEmoji(index, emoji.icon)" >
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
            @click="createWorkflow"
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
const emit = defineEmits(["update:modelValue", "reloadPage"]);
const selectedIndex = ref(null)
const title = ref('');
const selectedEmoji = ref('')
const toast = useToast();
const emojis = [
    {
        icon: `<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
<rect width="32" height="32" rx="16" fill="#F9FAFB"/>
<path d="M24 17.2964C24 16.9907 22.7133 17.0742 22.6111 16.852C22.5089 16.6298 24.0369 14.8147 23.8147 14.4076C23.5924 14.0004 21.5542 14.9262 21.4258 14.7222C21.2978 14.5187 21.5556 13.1667 21.3333 13C21.1111 12.8333 19 15.1849 18.7036 14.9076C18.4076 14.6298 19.3889 10.2409 19.1862 10.1529C18.9836 10.0649 17.6667 10.7596 17.5004 10.7227C17.3342 10.6853 16.3031 8 16 8C15.6969 8 14.6658 10.6853 14.4996 10.7222C14.3333 10.7591 13.0164 10.0644 12.8138 10.1524C12.6111 10.2404 13.5924 14.6293 13.296 14.9071C13 15.1849 10.8889 12.8333 10.6667 13C10.4444 13.1667 10.7022 14.5187 10.5742 14.7222C10.4458 14.9258 8.40756 14 8.18533 14.4076C7.96311 14.8151 9.49156 16.6298 9.38889 16.852C9.28622 17.0742 8 16.9907 8 17.2964C8 17.6022 11.7502 19.6231 12 19.8613C12.2498 20.0991 10.7049 21.1067 10.9258 21.2964C11.1818 21.5169 12.632 20.9276 15.5556 20.8711V23.5556C15.5556 23.8013 15.7547 24 16 24C16.2458 24 16.4444 23.8013 16.4444 23.5556V20.8711C19.3676 20.9276 20.8182 21.5169 21.0742 21.2964C21.2947 21.1062 19.7502 20.0991 20 19.8613C20.2498 19.6231 24 17.6018 24 17.2964Z" fill="#DD2E44"/>
</svg>
`
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

const handleSelectedEmoji = (index, emoji) =>{
    selectedIndex.value = index;
    selectedEmoji.value = emoji;
}

const createWorkflow = ()=>{
  if(title !=null && title.value ===""){  
    document.getElementById('title').focus();
    return false;
  }

  let data = {
    title: title.value,
    emoji: selectedIndex.value
  }

  axios.post('/admin/workflow', data).then((response)=>{
    toast.add({
      severity: "success",
      summary: 'Workflow created successfully',
      detail: "",
      life: 3000,
    });
  })

  emit('reloadPage')
  title.value = ''
  closeModal()
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