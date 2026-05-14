import { defineConfig } from 'vite';
import { resolve } from 'path';

export default defineConfig({
  root: 'resources',
  base: '/assets/build/',
  build: {
    outDir: resolve(__dirname, 'public/build'),
    emptyOutDir: true,
    manifest: true,
    rollupOptions: {
      input: {
        main: resolve(__dirname, 'resources/js/main.js'),
        style: resolve(__dirname, 'resources/css/main.scss'),
      },
      output: {
        entryFileNames: 'js/[name]-[hash].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          const info = assetInfo.name.split('.');
          const ext = info[info.length - 1];
          if (/\.(css|scss|sass)$/i.test(assetInfo.name)) {
            return 'css/[name]-[hash][extname]';
          }
          return 'assets/[name]-[hash][extname]';
        },
      },
    },
  },
  server: {
    port: 5173,
    strictPort: true,
    proxy: {
      '/api': 'http://localhost/suip',
    },
  },
  css: {
    devSourcemap: true,
    preprocessorOptions: {
      scss: {
        additionalData: `
          $primary-color: #667eea;
          $secondary-color: #764ba2;
        `,
      },
    },
  },
});
