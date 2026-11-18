/* Tailwind 3 build only for the custom admin pages (resources/views/filament).
   Filament's own CSS contains just the classes Filament uses, so utilities like bg-green-50 or grid-cols-6 from our pages were missing.
   No preflight here: Filament has its own reset.
   Build:  npm run build:admin-css  ->  public/design/admin-pages.css (committed) */
const site = require('./tailwind.config.cjs');

module.exports = {
  content: ['./resources/views/filament/**/*.blade.php'],
  corePlugins: { preflight: false },
  theme: site.theme,
};
