module.exports = {
  content: [
    './resources/**/*.{blade.php,js,ts,vue,css}',
    './vendor/**/*.{php,blade.php,js,vue,css}'
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          50: '#FDF2F8',
          100: '#FCE7F3',
          200: '#F9D0E7',
          300: '#F4B0D5',
          400: '#EC74AE',
          500: '#DD3888',
          600: '#BE185D',
          700: '#9D174D',
          800: '#831943',
          900: '#701A3C',
          950: '#450A21'
        }
      }
    }
  },
  plugins: []
};
