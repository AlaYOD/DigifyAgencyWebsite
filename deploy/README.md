# Digify deployment notes

Build the client and SSR bundles on every deploy. Restart the Inertia SSR
process (`php artisan inertia:start-ssr`) after every deploy so it loads the
new bundle and page code.
