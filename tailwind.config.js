module.exports = {
  prefix: "da-",
  content: [
    "./index.html",
    "./src/web/assets/analytics/src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  corePlugins: {
    preflight: false,
  },
  theme: {
    extend: {},
  },
  variants: {
    extend: {},
  },
}
