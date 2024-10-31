<template>
  <div class="lg:px-7 px-5 flex items-center justify-between pt-5">
    <div class="flex items-center gap-5">
      <div class="border rounded-full p-3">
        <Icon icon="mdi:code" width="30" />
      </div>
      <div>
        <h1 class="text-2xl md:text-3xl text-[#1D2939]">Development Team</h1>
        <p class="text-sm md:text-base text-gray-500">
          Meet the talented professionals who brought this project to life.
        </p>
      </div>
    </div>
  </div>
  <Divider />
  <!-- ================  About Team ========= -->
  <div class="pr-5 md:pr-0 pl-5 md:pl-7 mb-16 md:mb-0">
 
    <h1 class="text-2xl font-semibold text-[#1D2939] mt-10">About Team</h1>
    <Divider />
<p class=" text-[14px] pr-7 text-gray-500">This application was built by        Ansel Computers LTD in 2024. The development and design were expertly handled by the dedicated team at Ansel Computers LTD. Our goal was to create a robust, user-friendly solution tailored to meet your needs</p>


<h1 class="text-2xl font-semibold text-[#1D2939] mt-10">Team Members</h1>
<Divider />

<div class=" grid grid-cols-2 md:grid-cols-3  gap-2 lg:grid-cols-4 mb-10 ">
    <div v-for="(item,index) in team"  :key="index" class=" mt-10">
       <div>
        <img :src="item.img" alt="">
        <h1 class=" text-gray-800 text-[14px] font-semibold mt-3">{{ item.name }}</h1>
        <p class=" text-gray-500 text-[12px]">{{ item.title }}</p>
       </div>
    </div>
</div>

       <!-- /////     footer    ///// -->

       <div class=" mr-5 md:mr-7 mb-12   bg-gray-100 p-5  md:p-10 border border-solid border-gray-300 rounded-xl">
          <div class=" flex items-center w-full justify-between"> 
               <h1 class=" bg-white  border-solid border-gray-300 text-[12px] md:text-[18px] px-5 py-2 rounded-3xl">Get In Touch</h1>
               <div class=" flex items-center gap-5">
                <Icon icon="octicon:mail-24" />
                <Icon icon="akar-icons:linkedin-fill" />
               </div>
          </div>

          <div class=" flex flex-col md:flex-row w-full mt-10  gap-14 justify-between">
             <div>
                <h1 class=" text-gray-700 text-lg font-semibold">Visit us</h1>
                <p class=" text-gray-500 text-sm leading-[18px]">Suite 27 Upstairs, Zone 3 Shopping Complex, Wuse Abuja.</p>
             </div>
             <div>
                <h1 class=" text-gray-700 text-lg font-semibold">Call Us</h1>
                <p class=" text-gray-500 text-sm leading-[18px] whitespace-nowrap">Mon - Fri from 8am to 5pm <br> (+234) 803 302 1969</p>
             </div>
             <div>
                <h1 class=" text-gray-700 text-lg font-semibold">Email Address</h1>
                <p class=" text-gray-500 text-sm leading-[18px]">support@anselcomputers.ng</p>
             </div>
          </div>
       </div>
  </div>
</template>

<script  setup>
import jerry from "../../../assets/images/developer/jerry.svg"
import emma from "../../../assets/images/developer/emma.svg"
import dave from "../../../assets/images/developer/dave.svg"
import okafor from "../../../assets/images/developer/okafor.svg"
import james from "../../../assets/images/developer/james.svg"
import fidelis from "../../../assets/images/developer/fidelis.svg"

const team =[
    {
        img: jerry,
        name: "Jerry Ikediashi",
        title: "Project Lead"
    },
    {
        img: emma,
        name: "Falaju Emmanuel",
        title: "Graphic Designer"
    },
    {
        img: dave,
        name: "Fayeye David",
        title: "Product Designer (UI/UX)"
    },
    {
        img: okafor,
        name: "Emeka Okafor",
        title: "Software Developer"
    },
    {
        img: james,
        name: "James Oluwabukola",
        title: "Full Stack Developer"
    },
    {
        img: fidelis,
        name: "Fidelis Okoh",
        title: "Software Developer"
    },
]
</script>

<style>
</style>