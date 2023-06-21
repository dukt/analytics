var tailwindcss = require('tailwindcss');

module.exports = {
  plugins: [
    require('postcss-import'),
    require('tailwindcss/nesting'),
    tailwindcss('./tailwind.config.js'),
    require('autoprefixer')
  ],
}
