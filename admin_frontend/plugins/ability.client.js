// plugins/pinia-casl.client.js
import {defineNuxtPlugin} from '#app';
import {ability, updateAbilityFromPermissions} from '@/composables/ability';
import {abilitiesPlugin} from '@casl/vue';
import {useAdminStore} from "../store/admin";
import {watch} from 'vue';

export default defineNuxtPlugin((nuxtApp) => {
  const adminStore = useAdminStore();

  // Use CASL Vue plugin
  nuxtApp.vueApp.use(abilitiesPlugin, ability);

    watch(
        () => adminStore.permissions, // The reactive data to watch
        (newPermissions) => {
            updateAbilityFromPermissions(newPermissions);
        },
        { immediate: true } // Update abilities on plugin initialization
    );

  // Add a global `$can` method
    nuxtApp.vueApp.config.globalProperties.$can = (action, subject) => {
        return ability.can(action, subject);
    };
});
