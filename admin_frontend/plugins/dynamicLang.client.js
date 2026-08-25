export default defineNuxtPlugin((nuxtApp) => {
    const { $i18n } = nuxtApp;
    const i18nRedirected = useCookie('i18n_redirected');
    const cookieLang = useCookie('cookieLang');
    const cookieLanguage = cookieLang.value;


    // Check if the cookie language exists
    if (!cookieLanguage) return;


    // Set the i18n locale to match the cookie value
    if (i18nRedirected !== cookieLanguage) {
        $i18n.setLocale(cookieLanguage);
    }
});
