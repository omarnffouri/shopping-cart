import Service from '@/services/service.js'

const state = () => ({
  langData: null,
  langCode: null,
  languages: {},
  defaultLanguage: {
    name: 'English',
    code: 'en'
  },
  currentLanguage: {
    name: 'English',
    code: 'en'
  },
});

const actions = {
  setLangCode(payload) {
    this.langCode = payload
  },
  setDefaultLanguage(payload) {
    this.defaultLanguage = payload
  },
  setCurrentLanguage(payload) {
    this.currentLanguage = payload
  },
  setLanguages(payload) {
    payload.forEach(i => {
      this.languages[i.code] = i
    })
  },
  async getLangData({i18n, token}) {
    if(!this?.currentLanguage?.predefined) {
      try {
        const data = await Service.getRequest({locale_code: this.currentLanguage?.code}, token, 'localization')

        if(data?.status ){
          if(data?.status === 200){
            this.langData = data.data

            i18n.setLocaleMessage(this?.currentLanguage?.code, data?.data);
          }
          return data
        } else {
          return Promise.reject({
            message: "API is down."
          })
        }
      }catch (e) {
        return Promise.reject({
          message: e.message
        })
      }
    }
  }
};

export const useLanguageStore = defineStore('language', {
  state,
  actions
});
