/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'banho': {
          'primary': '#1a1a1a',
          'secondary': '#dc2626',
          'accent': '#fbbf24',
          'light': '#f8fafc',
          'dark': '#0f172a',
        }
      },
      fontFamily: {
        'sans': ['Noto Sans JP', 'sans-serif'],
        'serif': ['Noto Serif JP', 'serif'],
      }
    },
  },
  plugins: [],
}