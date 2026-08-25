import { useAuthStore } from '~/store/auth';
export default defineNuxtRouteMiddleware((to) => {

    const { authenticated, token } = storeToRefs(useAuthStore());

    const config = useRuntimeConfig();
    const tkn = useCookie(config.public.auth_token_key);

    const nuxtApp = useNuxtApp()

    if (!tkn?.value) {
        if (nuxtApp?.isClient) {
            const redirectionUrl = to.fullPath;
            localStorage.setItem('redirection_url', redirectionUrl);
        }

        authenticated.value = false;
        token.value = false;
        return navigateTo('/login');
    }
});
