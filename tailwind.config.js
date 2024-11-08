/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [],
  content: ["./templates/*.php", "./templates/*/*.php"],
  theme: {
    extend: {
      fontFamily: {
        sans: ["Work Sans", "sans-serif"],
        heading: ["Syncopate", "sans-serif"],
      },
      screens: {
        '1xl': '1328px',
      },
      aspectRatio: {
        '4/3': '4 / 3',
      },
      width: {
        'sidebar': '275px',
        '105': '420px',
      },
      height: {
        '98': '392px'
      },
    },
  },
  plugins: [],
}

