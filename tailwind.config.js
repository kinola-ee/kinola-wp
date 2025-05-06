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
        '105': '26.25rem',
      },
      height: {
        '98': '24.5rem'
      },
      colors: {
        'primary100': '#171615',
        'primary80': '#454544',
        'primary60': '#747373',
        'primary40': '#a2a2a1',
        'primary20': '#d1d0d0',
        'primary5': '#f3f3f3',
        'accentI100': '#5f0ce7',
        'accentI80': '#7f3dec',
        'accentI40': '#bf9ef6',
        'accentI20': '#ccb1f8',
        'accentII100': '#34cc3b',
        'accentIII100': '#2a40ff',
      },
      fontSize: {
        '1xl': '1.375rem',
      },
      outlineWidth: {
        '6': '6px'
      },
    },
  },
  plugins: [],
}

