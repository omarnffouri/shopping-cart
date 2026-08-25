import {useAuthStore} from "../store/auth";
import {storeToRefs} from "pinia";

export default defineNuxtPlugin((nuxtApp) => {
    const {token} = storeToRefs(useAuthStore());

    const config = useRuntimeConfig();
    const tkn = useCookie(config.public.auth_token_key);

    if (tkn.value) {
        token.value = tkn.value
    }
})

