/* Tailwind (CDN) configuration shared by every page — maps the INSPIRE design tokens to utility classes.
   Load AFTER https://cdn.tailwindcss.com :   <script src="/design/tailwind.js"></script>
   Utilities:  bg-brand-600  text-navy-800  bg-sun  rounded-i-xl  shadow-i-md  font-sans …           */
tailwind.config = {
  theme: {
    extend: {
      fontFamily: { sans: ['Montserrat', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'] },
      colors: {
        // primary scale = the logo's blues (existing "brand-*" classes pick it up)
        brand: { 50: '#f0f7ff', 100: '#dcedfe', 200: '#b9dcfd', 300: '#7cc0fa', 400: '#3aa0f2', 500: '#0682e4', 600: '#006de0', 700: '#0057b8' },
        navy:  { 600: '#1860b0', 700: '#0a4a97', 800: '#00387a', 900: '#00265a', 950: '#001a3a' },
        sky:   '#019eff',
        ink:   '#0e2442',
        // spectrum (coloured nodes of the logo)
        sun: '#fdba07', tangerine: '#ff6c0b', ruby: '#e31e27', leaf: '#3d932e', plum: '#72299c',
        // cool, blue-tinted neutrals replace Tailwind's grey so every page shares one tone
        gray: { 50: '#f6faff', 100: '#eef4fb', 200: '#dce8f5', 300: '#c3d5ea', 400: '#8497b1', 500: '#5b6f8d', 600: '#4a5f7e', 700: '#26405f', 800: '#14304f', 900: '#0e2442' },
      },
      borderRadius: { 'i-md': '12px', 'i-lg': '18px', 'i-xl': '26px', 'i-2xl': '34px' },
      boxShadow: {
        'i-sm': '0 2px 8px rgba(0,56,122,.07)', 'i-md': '0 10px 28px rgba(0,56,122,.09)', 'i-lg': '0 24px 56px rgba(0,56,122,.13)',
        'i-primary': '0 8px 22px rgba(0,109,224,.28)',
      },
    },
  },
};
