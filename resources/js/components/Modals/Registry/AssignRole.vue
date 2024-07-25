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
            <Icon icon="mingcute:user-add-line" width="30" />
           </div>
           <div>
            <h1 class=" text-2xl font-medium">Assign Role</h1>
            <p class=" text-sm">Assign branch registrars.</p>
           </div>
         </div>

         <div class=" bg-[#F2F4F7] p-2 rounded-full cursor-pointer"  @click="closeModal">
            <Icon icon="mdi:times" width="20" />
         </div>
       </div>

     </template>
		<Divider class=" -mt-2"/>

        <section class=" px-6">
            <div class=" flex items-start gap-1">
            <label class=" text-[#344054] font-semibold">Name</label>
            <span class=" text-red-600">*</span>
        </div>
        <div class=" relative">
            <input v-model="Users" type="text" class=' border-solid border-gray-300 mt-1 p-2 border rounded-md outline-none w-full text-sm' placeholder="Enter User Name"
            @input="filterRecipients"
            required>
            <div class=" border p-4 border-solid py-3 bg-white rounded-md absolute z-50 w-full shadow-2xl  mt-2"
            v-if="Users && Suggestions.length > 0"
            >
                <div
                        class="flex items-center p-2 px-3 gap-2 hover:bg-[#f8f8f9]"
                        v-for="(suggestion, index) in Suggestions"
                        :key="index"
                        @click="updateUsers(suggestion)"
                      >
                        <img :src="suggestion.img" alt="" class="w-8" />
                        <p class="text-sm w-fit">{{ suggestion.name }}</p>
                      </div>
            </div>
        </div>

        <div class=" mt-3">
        <div class=" flex items-start gap-1">
            <label class=" text-[#344054] font-semibold">Access Level </label>
            <span class=" text-red-600">*</span>
        </div>
        <Dropdown
            :pt="{
              root: {
                class: 'w-full flex border border-solid border-gray-300 rounded-lg'
              }
            }"
            v-model="selectedCity"
            :options="strings"
            optionLabel="name"
            placeholder="Select access level"
            class="mt-1 w-full"
          />
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
import image from "../../../assets/images/members/1.svg";
const props = defineProps(["modelValue"]);
const emit = defineEmits(["update:modelValue"]);

const selectedCity = ref();
const strings = ref([
  { name: "Super Registrar" },
  { name: "Departmental Registrar" },
  { name: "Divisional Registrar" },
  { name: "Unit Registrar" },
]);
const Users = ref('')

const Recipients = ref([
{
    name: "Jenny Williams",
    img: image,
  },
  {
    name: "Dave Miye",
    img: image,
  },
]);
const Suggestions = ref([]);
const filterRecipients = () => {
  const searchTerm = Users.value.toLowerCase();
  Suggestions.value = Recipients.value.filter((recipient) =>
    recipient.name.toLowerCase().includes(searchTerm)
  );
};


const updateUsers = (suggestion) => {
    Users.value = suggestion.name
    Suggestions.value = []
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
  Users.value =  ''
};

const submit = () => {
  emit("update:modelValue", false);
  Users.value =  ''
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
