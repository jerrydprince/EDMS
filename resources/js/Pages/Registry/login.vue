<template>
 <div>
            <h1 class="text-3xl text-[#1D2939] font-semibold">Log into department</h1>
            <p class=" opacity-80 pt-3">Kinldy provide all your login details </p>
        </div>
        <form @submit.prevent="onSubmit">
      <div class="p-field">
        <label for="email">Email</label> <br/>
        <input  type="text" v-model="formData.email" class=' border-solid border-gray-300 mt-1 p-2 border rounded-md outline-none w-full text-sm' required>
      </div>
      <div class="p-field">
        <label for="password">Password</label> <br/>
        <input  type="text" v-model="formData.password" class=' border-solid border-gray-300 mt-1 p-2 border rounded-md outline-none w-full text-sm' required>
      </div>
      <Button label="Login" icon="pi pi-check" type="submit" />
    </form>
 </template>
  <script setup>
  import { ref, onMounted, computed } from "vue";
  import { useToast } from "primevue/usetoast";
const toast = useToast();
const formData = ref({
    email:"",
    password:""
})
const onSubmit = async () => {
    try {
        console.log('inside',formData.value);
        const response = await axios.post('/admin/registry/loginUser', formData.value);
        console.log('login', response.data);
        toast.add({
            severity: "success",
            summary: "You're logged in",
            detail: "You can view all files in this department.",
            life: 3000,
        });

    } catch (error) {
        console.error('Error fetching data:', error.message);
    }
}
</script>
<style>
form {
  font-family: Arial, sans-serif;
  background-color: #f5f5f5;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100vh;
  margin: 0;
}
.login-form {
  max-width: 400px;
  margin: 0 auto;
  padding: 2em;
  border-radius: 8px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  background-color: white;
}

.p-field {
  margin-bottom: 1em;
}
</style>
