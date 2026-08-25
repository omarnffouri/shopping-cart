
import Service from '@/services/service.js'
import { useAdminStore } from '~/store/admin';
import { useLanguageStore } from '~/store/language';

import {showError} from "nuxt/app";
import {useAuthStore} from "./auth";

const state = () => ({
  defaultImage: '',
  mediaStorage: '',
  imgSrcUrl: '',
  thumbPrefix: '',
  media: '',
  dataFetched: false,
})

const actions = {
  setProfileIndex(params){
    this.media = params?.media
    this.defaultImage = params?.default_image
    this.imgSrcUrl = params?.img_src_url
    this.thumbPrefix = params?.thumb_prefix
  },
  async settingSiteData(data) {
    if(!data) return false

    const cookieLang = useCookie('currentLanguage');

    const {setActivated} = useAdminStore()
    const {setLanguages, setDefaultLanguage, setCurrentLanguage,
      defaultLanguage, languages, setLangCode} = useLanguageStore()

    setActivated(data)
    setLanguages(data?.languages)

    if (data?.default_language) {
      setDefaultLanguage(data.default_language)
    }

    if (cookieLang.value && languages[cookieLang.value]) {
     setCurrentLanguage(languages[cookieLang.value])

    } else if (data.default_language) {
      setCurrentLanguage(data.default_language)
    }

    if (cookieLang.value !== defaultLanguage?.code) {
      setLangCode(cookieLang.value)
    }
  },
  async nuxtClientInit() {
    if(this.dataFetched) {
      return false
    }

    const config = useRuntimeConfig();
    const token = useCookie(config.public.auth_token_key);

    const cookieLang = useCookie('currentLanguage');

    const { $i18n } = useNuxtApp();
    const {getLangData} = useLanguageStore();
    const {setProfile} = useAdminStore();
    const {setToken, logUserOut} = useAuthStore();

    if (token.value) {
      setToken(token.value)
      try {
        const data = await Service.getRequest({lang: cookieLang.value}, token.value, 'profile', null);
        this.dataFetched = true;

        if (data.status === 200) {

          setProfile(data.data)
          await this.settingSiteData(data.data)

          if (data.data?.languages?.length) {
            return  await getLangData({
              i18n: $i18n,
              token: token.value
            })
          } else {
            return data
          }

        } else {
          return Promise.reject({statusCode: data.status, message: data.message})
        }

      } catch (e) {

        if(e?.status === 401) {
          logUserOut();
          return navigateTo('/login');
        } else {
          showError({
            statusCode: 400,
            message: e.message
          })
        }
      }
    } else {
      try {
        const data = await Service.unAuthGet({}, 'languages', null)
        this.dataFetched = true
        if (data.status === 200) {

          await this.settingSiteData(data.data)

          if (data?.data?.languages?.length) {
            return await getLangData({
              i18n: $i18n,
              token: null
            })
          } else {
            return data
          }
        } else {
          return Promise.reject({statusCode: data.status, message: data.message})
        }

      } catch (e) {
        if(e?.status === 401) {
          logUserOut();
          return navigateTo('/login');
        } else {
          showError({
            statusCode: 400,
            message: e.message
          })
        }
      }
    }
  }
}

export const useIndexStore = defineStore('index', {
  state,
  actions
});

