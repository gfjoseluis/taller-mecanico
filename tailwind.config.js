/** Compilado local (sin internet en el local): npm run css */
module.exports = {
  content: ['./resources/views/**/*.blade.php', './app/Livewire/**/*.php'],
  theme: {
    extend: {
      colors: {
        grafito: {DEFAULT: '#1B1E24', soft: '#3A4048'},
        humo: '#EDEFF2',
        senal: '#F5B301',
        oxido: '#B3402A',
        taller: '#2E7D4F',
      },
      fontFamily: {
        display: ['"Barlow Condensed"', '"Arial Narrow"', 'sans-serif'],
        body: ['Barlow', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
