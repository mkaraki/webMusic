import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { VitePWA } from 'vite-plugin-pwa'

// https://vitejs.dev/config/
export default defineConfig({
  base: '/app/',
  plugins: [
    vue(),
    VitePWA({
      registerType: 'autoUpdate',
      manifest: {
        name: 'webMusic',
        short_name: 'webMusic',
        theme_color: '#242424',
        background_color: '#242424',
        icons: [
          {
            src: 'favicon.svg',
            type: 'image/svg+xml'
          }
        ]
      }
    })
  ]
})
