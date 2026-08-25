import Service from '@/services/service.js'
import { useIndexStore } from '~/store/index';
import { useSettingStore } from '~/store/setting';
import { useSiteSettingStore } from '~/store/site-setting';
import { useUserMessageStore } from '~/store/user-message';
import { useUiStore } from '~/store/ui';
import {useAuthStore} from "./auth";

const state = () => ({
  posPublicKey: null,
  posSetting: null,
  profile: null,
  permissions: [],
  activated: false,
  publicKey: null,
  sidebarPermissions: null,
  isVendor: null,
  isSuperAdmin: null,
})

const actions = {
  setActivated(param){
    this.activated = param?.activated
    this.publicKey = param?.public_key
  },
  setProfile( params) {
    const {setProfileIndex} = useIndexStore();
    const {setProfileSetting} = useSettingStore();
    const {setProfileSiteSetting} = useSiteSettingStore();
    const {setProfileUserMessage} = useUserMessageStore();

    this.profile = params?.user
    this.posSetting = params?.pos_setting
    this.posPublicKey = params?.pos_public_key
    this.mediaStorage = params?.media_storage

    setProfileIndex(params)

    this.permissions = params?.permissions

    if(!this.sidebarPermissions){
      this.sidebarPermissions = {}
    }
    params?.permissions.forEach(i => {
      this.sidebarPermissions[i] = i
    })

    this.isVendor = params?.vendor
    this.isSuperAdmin = params?.super_admin
    this.activated = params?.activated

    setProfileSetting(params)
    setProfileSiteSetting(params)
    setProfileUserMessage(params)
  },
  async updateProfile (params) {
    const {setErrors, setToastMessage} = useUiStore();
    setErrors(null)
    const {token} = useAuthStore();

    const data = await Service.setRequest(params, token, 'setProfile')

    if(data.status === 200){

      this.profile = data.data

      setToastMessage(null)
      return data
    } else if(data.status === 201) {
      setErrors(data.data)
    } else {
      return Promise.reject({statusCode: data.status, message: data.message })
    }
  },
  async setPassword (params) {
    const {setErrors, setToastMessage} = useUiStore();
    const {token} = useAuthStore();
    setErrors(null)

    const data = await Service.setRequest(params, token, 'setPassword')
    if(data.status === 200){

      setToastMessage(data.message)
      return data
    } else if(data.status === 201) {
      setErrors(data.data)
      return data
    } else {
      return Promise.reject({statusCode: data.status, message: data.message })
    }
  },
}

export const useAdminStore = defineStore('admin', {
  state,
  actions
});
