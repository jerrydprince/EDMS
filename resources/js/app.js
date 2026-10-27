 
 import Layout from "./Shared/Layout.vue"
 import Layout2 from "./Shared/Layout2.vue"
 import Layout3 from "./Shared/Layout3.vue"
 import Layout4 from "./Shared/Layout4.vue"
 import store from "./store/store.js"

import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import PrimeVue from "primevue/config";
import Tailwind from "primevue/passthrough/tailwind";
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import "primeicons/primeicons.css";
import PDFEditor from "./components/PDFViewer/PDFEditor.vue";
import "./assets/Fonts/Satoshi_Complete/Fonts/WEB/css/satoshi.css"
import { registerGlobalComponents } from "./reusables/primeVue";
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import NProgress from 'nprogress'
import { router } from '@inertiajs/vue3'
import "../css/app.css"
import 'vue3-emoji-picker/css'



if (document.getElementById('app')) {
  createInertiaApp({
    progress: {
      delay: 250,
      color: '#29d',
      includeCSS: true,
      showSpinner: false,
    },
    resolve: async (name) => {
      const page = await resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob("./Pages/**/*.vue")
      );
      
      // Assign layout based on route
      if(window.location.pathname.startsWith('/register')){
        page.default.layout = page.default.layout || Layout2;
      }else if(window.location.pathname.startsWith('/edms')){
        page.default.layout = page.default.layout || Layout3;
      }else if(window.location.pathname.startsWith('/dashboard') || window.location.pathname.startsWith('/settings') || window.location.pathname.startsWith('/finance-and-accounts') || window.location.pathname === '/'){
        page.default.layout = page.default.layout || Layout;
      }else if(window.location.pathname.startsWith('/password')){
        page.default.layout = page.default.layout || Layout4;
      }

      return page;
    },

    setup({ el, App, props, plugin }) {
      const app = createApp({ render: () => h(App, props) })
      app.component('PDFEditor', PDFEditor) 
      registerGlobalComponents(app)
      app.use(plugin)
      app.use(PrimeVue, { unstyled: true, pt: Tailwind })
      app.use(ToastService)
      app.use(ConfirmationService)
      app.use(store)
      if (!app.config.globalProperties.$t) {
          app.config.globalProperties.$t = (key) => key;
      }
      app.config.devtools = true;
      app.mount(el)
    },
  })

  router.on('start', () => NProgress.start())
  router.on('finish', () => NProgress.done())
  router.on('cancel', () => NProgress.done())
  router.on('error', () => NProgress.done())
  router.on('exception', () => NProgress.done())
  router.on('navigate', () => {
      document.body.classList.remove('p-overflow-hidden');
      document.querySelectorAll('.p-component-overlay, .p-dialog-mask').forEach(el => {
          if (!el.closest('.p-dialog[aria-modal="true"]') && !document.querySelector('.p-dialog:not([style*="display: none"])')) {
              el.remove();
          }
      });
  })
}

import './bootstrap';

//import Dropzone from "dropzone";

// Import all of Bootstrap's JS
//import * as bootstrap from 'bootstrap'

window.addEventListener('error', function(e) { 
    fetch('/api/log-error', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify({ message: e.message, filename: e.filename, lineno: e.lineno, colno: e.colno, error: e.error ? e.error.stack : '' }) 
    }); 
}); 
const originalConsoleError = console.error; 
console.error = function(...args) { 
    fetch('/api/log-error', { 
        method: 'POST', 
        headers: { 'Content-Type': 'application/json' }, 
        body: JSON.stringify({ message: args.map(a => a instanceof Error ? `${a.name}: ${a.message}\n${a.stack}` : (typeof a === 'object' && a !== null ? (a.message ? `${a.message} ${JSON.stringify(a)}` : JSON.stringify(a)) : String(a))).join(' ') }) 
    }); 
    originalConsoleError.apply(console, args); 
};