/** @type {import('tailwindcss').Config} */
export default {
  darkMode: "class",
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
    "./node_modules/primevue/**/*.{vue,js,ts,jsx,tsx}",
    "./node_modules/flowbite/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        primary: "#0D6E1A",
				primary_bg: "#FCFCFD",
				border_primary: "#E4E4E4",
				success: "#ECFDF3",
				success_text: "#027A48",
				purple_light: "#F9F5FF",
				purple_text: "#6941C6",
				blue_primary: "#2F68C5",
      }
    },
  },
  plugins: [
    require('flowbite/plugin')
  ],
}

