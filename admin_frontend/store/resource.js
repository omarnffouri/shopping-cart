

const state = () => ({
  countryList: null,
  phoneList: null,
  decimalFormatList: null,
  currencyList: null,
  languageList: null
})

const actions = {
  setDecimalFormatList (list) {
    this.decimalFormatList = list
  },
  setCountryList (list) {
    this.countryList = list
  },
  setPhoneList (list) {
    this.phoneList = list
  },
  setCurrencyList (list) {
    this.currencyList = list
  },
  setLanguageList (list) {
    this.languageList = list
  }
}

export const useResourceStore = defineStore('resource', {
  state,
  actions
});
