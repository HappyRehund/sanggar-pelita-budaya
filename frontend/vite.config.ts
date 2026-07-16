import { defineConfig } from 'vite';
import { svelte } from '@sveltejs/vite-plugin-svelte';

export default defineConfig({
  plugins: [
    svelte(),
  ],
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8080',
        changeOrigin: true,
      },
      '/uploads': {
        target: 'http://localhost:8080',
        changeOrigin: true,
      },
    },
  },
  build: {
    outDir: 'dist',
    emptyOutDir: true,
    rollupOptions: {
      output: {
        manualChunks: {
          vendor: ['svelte', 'gsap'],
          sanitizer: ['dompurify', 'marked'],
        },
      },
    },
  },
  resolve: {
    alias: {
      $lib: '/src/lib',
      $assets: '/src/assets',
      $components: '/src/lib/components',
      $routes: '/src/routes',
      $modules: '/src/modules',
    },
  },
});