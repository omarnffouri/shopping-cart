import { defineNuxtPlugin } from '#app';
import flatPickr from 'vue-flatpickr-component';

export default defineNuxtPlugin((nuxtApp) => {
    nuxtApp.vueApp.component('FlatPickr', flatPickr);
});
