import { defineNuxtConfig } from 'nuxt/config'
import configJson from './jsconfig.json'

const isDemo = process.env.IS_DEMO === 'true'
let apiBase = !process.env.API_BASE?.trim() ? '/' : process.env.API_BASE
apiBase += configJson.api.url

export default defineNuxtConfig({
  nitro: {
    preset: 'node-server'
  },
  vite: {
    server: {
      hmr: {
        overlay: false,
      },
      watch: {
        usePolling: false, // Good, but might be ignored
        interval: 10000,    // Reduce watch frequency
        ignored: ['**/node_modules/**', '**/.git/**', '**/.nuxt/**'],
      },
      fs: {
        strict: true,
      },
    },
  },
  devtools: {enabled: false},
  compatibilityDate: '2024-11-01',

  // Disable server-side rendering
  ssr: false,
  // Global page headers
  app: {
    baseURL: process.env.ADMIN_FOLDER,
    head: {
      title: 'Admin panel',
      htmlAttrs: {
        lang: 'en'
      },
      meta: [
        { charset: 'utf-8' },
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
        { hid: 'description', name: 'description', content: '' },
        { name: 'format-detection', content: 'telephone=no' }
      ],
      link: [
        { rel: 'icon', type: 'image/x-icon', href: '/favicon.png' }
      ]
    }
  },
  css: [
    '~/assets/styles/styles.styl',
    'flatpickr/dist/flatpickr.css'
  ],
  runtimeConfig: {
    public: {
      apiBase: process.env.API_BASE,
      isDemo: isDemo,
      auth_token_key: 'ishop_admin_auth',
    }
  },
  components: true,
  i18n: {
    compilation: {
      strictMessage: false,
    },
    locales: [
      {code: 'en'},
      {code: 'fr'},
      {code: 'ar'},
      {code: 'tr'},
      {code: 'hi'},
    ],
    lazy: true,
    vueI18n: '~/lang/config.js',
    strategy: 'no_prefix',
    detectBrowserLanguage: {
      useCookie: true,
      redirectOn: 'root',
      cookieKey: 'i18n_redirected',
      alwaysRedirect: false,
      fallbackLocale: null,
    },
    defaultLocale:null
  },
  // Build Configuration
  build: {
    transpile: []
  },

  modules: ['@pinia/nuxt', '@nuxtjs/i18n'],
})
