/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.js",
    "./resources/**/*.blade.php",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#6482AD',
          light: '#7FA1C3',
          dark: '#284061'
        },
        cream: {
          DEFAULT: '#E2DAD6',
          light: '#F5EDED'
        },
        white: {
          DEFAULT: '#FDFDFD',
          light: '#FFFFFF',
          medium: '#D3D3D3',
          dark: '#909090'
        },
        black: {
          DEFAULT: '#222222',
          light: '#444444',
          dark: '#000000'
        }
      },
      fontFamily: {
        poppins: ['Poppins', 'Arial', 'sans-serif'],
        roboto: ['Roboto', 'Arial', 'sans-serif']
      },
      backgroundImage: {
        banner: "url('/images/banner.jpg')"
      }
    },
  },
  plugins: [],
}

