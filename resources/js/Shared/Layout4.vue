<template>
    <div>
        <slot/>
    </div>
    <Toast :pt="{
      root: { class: 'bg-transparent mr-5 mt-3' },
      container: {
        class: 'bg-white border border-[#0E7A1D] border-solid rounded-xl drop-shadow-xl',
      },
      buttonContainer: {
        class:
          'border border-solid border-gray-500 rounded-full px-0 py-0 -mt-10 -mr-4',
      },
      closeButton: { class: 'p-0 px-2 pb-1' },
      summary: { class: 'text-gray-800 font-semibold text-lg' },
      detail: { class: 'font-light text-gray-500' },
    }">
      <template #icon="{ }">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
          <path
            d="M22 11.5799V12.4999C21.9988 14.6563 21.3005 16.7545 20.0093 18.4817C18.7182 20.2088 16.9033 21.4723 14.8354 22.0838C12.7674 22.6952 10.5573 22.6218 8.53447 21.8744C6.51168 21.1271 4.78465 19.746 3.61096 17.9369C2.43727 16.1279 1.87979 13.9879 2.02168 11.8362C2.16356 9.68443 2.99721 7.63619 4.39828 5.99694C5.79935 4.35768 7.69279 3.21525 9.79619 2.74001C11.8996 2.26477 14.1003 2.4822 16.07 3.35986M22 4.49986L12 14.5099L9.00001 11.5099"
            stroke="#0E7A1D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </template>
    </Toast>
</template>
