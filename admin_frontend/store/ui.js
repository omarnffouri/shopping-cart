
const state = () => ({
  sidebarOpen: localStorage.getItem('sidebar_open', 'true') !== 'false' ,
  rememberMe: localStorage.getItem('remember_me') || '',
  dashboardNotice: localStorage.getItem('dashboard_notice') || false,
  toastMessage: '',
  toastMessageStatus: false,
  toastError: false,
  errors: null,
})

const actions = {
  settingDashboardNotice ( params) {
    this.dashboardNotice = params
    localStorage.setItem('dashboard_notice', params)
  },
  settingRemember (params) {
    this.rememberMe = params
    localStorage.setItem('remember_me', params)
  },
  hideSidebar () {
    this.sidebarOpen = false
    localStorage.setItem('sidebar_open', this.sidebarOpen)
  },
  toggleSidebar () {
    this.sidebarOpen = !this.sidebarOpen
    localStorage.setItem('sidebar_open', this.sidebarOpen)
  },
  setToastMessage (message) {
    const {t} = useNuxtApp().$i18n
    message = message?.trim() ? message : t('util.saved')
    this.toastMessageStatus = true
    if(message?.trim()){
      this.toastMessage = message
    }
  },
  setToastError (message) {
    this.toastError = true
    this.toastMessageStatus = true
    this.toastMessage = message
  },
  hideToast () {
    this.toastMessageStatus = false
    this.toastError = false
    this.toastMessage = ''
  },
  setErrors (payload = null) {
    this.errors = payload
  },
}

export const useUiStore = defineStore('ui', {
  state,
  actions
});
