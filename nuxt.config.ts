
export default defineNuxtConfig({
    modules: [
        '@nuxtjs/tailwindcss', // https://tailwindcss.com/docs/guides/nuxtjs
        '@pinia/nuxt', // https://pinia.vuejs.org/nuxt.html
        '@nuxtjs/i18n', // https://i18n.nuxtjs.org/
    ],

    css: ['@/assets/css/main.css'],

    build: { transpile: ['@headlessui/vue'] },

    routeRules: {},

    typescript: {
        tsConfig: {
            compilerOptions: {
                strict: true,
                types: ['@pinia/nuxt'],
            },
        },
    },
})
