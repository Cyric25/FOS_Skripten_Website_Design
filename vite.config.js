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
        main: resolve(__dirname, 'src/js/main.js')
      },
      output: {
        // Output format
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: 'assets/[name].[ext]'
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
