/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./*.html",
    "./assets/**/*.js"
  ],
  theme: {
    extend: {
      colors: {
        'siagri-dark': '#164a41',
        'siagri-nature': '#4d774e',
        'siagri-gold': '#f1b24a',
      }
    },
  },
  plugins: [],
}