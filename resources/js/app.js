 
 import Layout from "./Shared/Layout.vue"
 import Layout2 from "./Shared/Layout2.vue"
 import Layout3 from "./Shared/Layout3.vue"
 import Layout4 from "./Shared/Layout4.vue"

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import PrimeVue from "primevue/config";
import Tailwind from "primevue/passthrough/tailwind";
import ToastService from 'primevue/toastservice';
import "primeicons/primeicons.css";
import "./assets/Fonts/Satoshi_Complete/Fonts/WEB/css/satoshi.css"
import { registerGlobalComponents } from "./reusables/primeVue";
//import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import NProgress from 'nprogress'
import { router } from '@inertiajs/vue3'



 createInertiaApp({
  //   resolve: name => {
  //     const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
  //     return pages[`./Pages/${name}.vue`]
  //   },
    progress: {
    // The delay after which the progress bar will appear, in milliseconds...
    delay: 250,

    // The color of the progress bar...
    color: '#29d',

    // Whether to include the default NProgress styles...
    includeCSS: true,

    // Whether the NProgress spinner will be shown...
    showSpinner: false,
    },
    // resolve: (name) => {
    //     const page = resolvePageComponent(
    //     `./Pages/${name}.vue`,
    //     import.meta.glob("./Pages/**/*.vue")
    //     );
   
    resolve: name =>{
      const page = require(`./Pages/${name}.vue`).default;
      if(window.location.pathname.startsWith('/register')){
       page.layout = page.layout || Layout2;
      }else if(window.location.pathname.startsWith('/dashboard')){
        page.layout = page.layout || Layout3;
      }else if(window.location.pathname.startsWith('/admin')){
         page.layout =page.layout || Layout;
      }else if(window.location.pathname.startsWith('/password')){
        page.layout = page.layout || Layout4;
      }

      return page
    },
    

  setup({ el, App, props, plugin }) {
   const app = createApp({ render: () => h(App, props) }) 
        registerGlobalComponents(app)
      app.use(plugin)
      app.use(PrimeVue, { unstyled: true, pt: Tailwind })
      app.use(ToastService)
      app.mount(el)
  },
})

router.on('start', () => NProgress.start())
//router.on('finish', () => NProgress.done())

import './bootstrap';

//import Dropzone from "dropzone";

// Import all of Bootstrap's JS
//import * as bootstrap from 'bootstrap'
