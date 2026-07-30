/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/Views/**/*.php",
    "./modules/**/*.php",
    "./public/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        brand: { 
          DEFAULT: '#2563eb', 
          light: '#3b82f6', 
          dark: '#1d4ed8' 
        }
      }
    },
  },
  plugins: [],
}
