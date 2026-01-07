import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  build: {
    // Output to dist directory
    outDir: 'dist',
    // Clear output directory before build
    emptyOutDir: true,
    // Generate manifest for WordPress
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'src/js/main.js'),
        glossar: resolve(__dirname, 'src/js/glossar.js'),
        'glossar-editor': resolve(__dirname, 'src/js/glossar-editor.js'),
        'glossar-style': resolve(__dirname, 'src/css/glossar.css'),
        'page-manager': resolve(__dirname, 'src/js/page-manager.js'),
        'page-manager-style': resolve(__dirname, 'src/css/page-manager.css')
      },
      output: {
        // Output format
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          // Put CSS files in css/ directory
          if (assetInfo.name.endsWith('.css')) {
            return 'css/[name][extname]';
          }
          return 'assets/[name][extname]';
        }
      }
    }
  },
  // Development server settings
  server: {
    // Configure to work with Local by Flywheel
    host: 'localhost',
    port: 3000,
    strictPort: false,
    // CORS for WordPress
    cors: true
  }
});
