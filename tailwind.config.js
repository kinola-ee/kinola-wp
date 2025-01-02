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
      colors: {
        'accentI100': '#5f0ce7',
        'accentI80': '#7f3dec',
        'accentI40': '#bf9ef6',
        'accentI20': '#ccb1f8',
      },
      fontSize: {
        '1xl': '1.375rem',
      },
    },
  },
  plugins: [],
}

