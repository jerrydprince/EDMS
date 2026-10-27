import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react(), tailwindcss()],
  server: {
    port: 5555,
    strictPort: true,
  },
  css: {
    postcss: {} // Prevents Vite from picking up the parent EDMS postcss.config.js
  }
})
