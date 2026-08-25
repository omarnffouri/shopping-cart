import {useLanguageStore} from "../store/language";

export default defineNuxtRouteMiddleware(() => {

/*
    const { $i18n } = useNuxtApp();
    const { langCode } = storeToRefs(useLanguageStore());


    if(langCode.value){
        $i18n.setLocale(langCode.value)
    }else{

        const cookies = document.cookie

        const cookieLang = cookies && cookies.includes('currentLanguage=')
            ? cookies.split('currentLanguage=')[1].split(';')[0] : '';

        $i18n.setLocale(cookieLang)
    }


 */



});


