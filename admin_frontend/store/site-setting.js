
const state = () => ({
  siteSetting: null
})

const actions = {
  setProfileSiteSetting(params){
    this.siteSetting = {...this.siteSetting, ...params?.site_setting}
  },
}

export const useSiteSettingStore = defineStore('sitesSetting', {
  state,
  actions
});
