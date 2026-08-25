import Service from '@/services/service.js'
import { useUiStore } from '~/store/ui';
import {useAuthStore} from "./auth";

const state = () => ({
  setting: null,
  storeData: null,
  base64SiteLogo: null,
})

const actions = {
  setProfileSetting (data) {
    this.setting = {...this.setting, ...data?.setting}
    this.storeData = {...this.storeData, ...data?.store}
  },

  updateStoreData (storeData) {
    this.storeData = {... this.storeData, ...storeData}
  },
  clearSetting () {
    this.setting = null
    this.storeData = null
    this.base64SiteLogo = null
  },
  async setConvertImage (params) {
    // This converted image is being used in generated PDF in order page
    const {token} = useAuthStore();

    const data = await Service.convertImage(params, token)
    if(data.status === 200){

      this.base64SiteLogo = data?.data

    }else {
      return Promise.reject({statusCode: data.status, message: data.message })
    }
  },
  async getSetting () {
    const {token} = useAuthStore();

    const data = await Service.getRequest({}, token, 'getSetting')
    if(data.status === 200){

      this.setting = {...this.setting, ...data.data}

    }else {
      return Promise.reject({statusCode: data.status, message: data.message })
    }
  },
  async setCurrency (params) {
    const {setErrors, setToastMessage} = useUiStore();
    const {token} = useAuthStore();
    setErrors(null)

    const data = await Service.setRequest(params, token, 'setCurrency')

    if (data.status === 200) {
      this.setting = {...this.setting, ...data.data}
      setToastMessage(data.message)
      return data
    } else if (data.status === 201) {
      setErrors(data.data)
    } else {
      return Promise.reject({statusCode: data.status, message: data.message})
    }
  },
  async setAddress (params) {
    const {setErrors, setToastMessage} = useUiStore();
    const {token} = useAuthStore();
    setErrors(null)

    const data = await Service.setRequest(params, token, 'setAddress')

    if (data.status === 200) {
      this.setting = {...this.setting, ...data.data}
      setToastMessage(data.message)
      return data
    } else if (data.status === 201) {
      setErrors(data.data)
    } else {
      return Promise.reject({statusCode: data.status, message: data.message})
    }
  },
}

export const useSettingStore = defineStore('setting', {
  state,
  actions
});
