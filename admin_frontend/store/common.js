import Service from '@/services/service.js'
import { useLanguageStore } from '~/store/language';
import { useUiStore } from '~/store/ui';
import {useAuthStore} from "./auth";
import {storeToRefs} from "pinia";

const state = () => ({
  allCategories: null,
  allTaxRules: null,
  allAttributes: null,
  allAttributeValues: null,
  allBrands: null,
  allProductCollections: null,
  allBundleDeals: null,
  allShippingRules: null,
  allPages: null,
  allPermissions: null,
  allSubscriptionEmailFormats: null,
  allRoles: null
})

const actions = {
  setAllPermissions(allPermissions) {
    this.allPermissions = allPermissions
  },
  setAllRoles(allRoles) {
    this.allRoles = {}
    allRoles.forEach((item) => {
      this.allRoles = {...this.allRoles, ...{[item.name]: {title: item.name}}}
    })
  },
  setAllPages(allPages) {
    this.allPages = {}
    allPages.forEach((item) => {
      this.allPages = {...this.allPages, ...{[item.id]: {title: item.title}}}
    })
  },
  setAllCategories(allCategories) {
    this.allCategories = {}
    allCategories.forEach((item) => {
      this.allCategories = {...this.allCategories, ...{[item.id]: {title: item.title}}}
    })
  },

  setAllShippingRules(allShippingRules) {
    this.allShippingRules = {}
    allShippingRules.forEach((item) => {
      this.allShippingRules = {...this.allShippingRules, ...{[item.id]: {title: item.title}}}
    })
  },
  setAllProductCollection(allProductCollections) {
    this.allProductCollections = allProductCollections
  },
  setAllBundleDeals(allBundleDeals) {
    this.allBundleDeals = {}
    allBundleDeals.forEach((item) => {
      this.allBundleDeals = {...this.allBundleDeals, ...{[item.id]: {title: item.title}}}
    })
  },
  setAllAttributes(allAttributes) {
    this.allAttributes = allAttributes
    let val = []
    allAttributes.forEach(i=>{
      val = [...val, ...i.values]
    })
    this.allAttributeValues = val
  },
  setAllBrands(allBrands) {
    this.allBrands = {}
    allBrands.forEach((item) => {
      this.allBrands = {...this.allBrands, ...{[item.id]: {title: item.title}}}
    })
  },
  setAllTaxRules(allTaxRules) {
    this.allTaxRules = {}
    allTaxRules.forEach((item) => {
      this.allTaxRules = {...this.allTaxRules, ...{[item.id]: {title: item.title}}}
    })
  },
  setAllSubscriptionEmailFormats(allSubscriptionEmailFormats) {
    this.allSubscriptionEmailFormats = allSubscriptionEmailFormats
  },
  emptyAllList(storeAllVariable) {
    if(storeAllVariable){
      this[storeAllVariable] = null
    }
  },
  async getAllList({api, action}) {
    const {langCode} = storeToRefs(useLanguageStore());
    const {token} = storeToRefs(useAuthStore());

    try {
      const data = await Service.getRequest({}, token.value, api, langCode.value);

      if (data.status === 200) {
        if(this[action]){
          this[action](data.data)
        }

      } else {
        showError({
          statusCode: 400,
          message: data.message
        });
      }
    } catch (e) {

      showError({
        statusCode: 400,
        message: e.message
      })
    }
  },
  async setWysiwygImage(params) {
    const {setErrors, setToastMessage} = useUiStore();
    const {token} = storeToRefs(useAuthStore());

    setErrors(null);

    try {

      const data = await Service.setRequest(params, token.value, 'setWysiwygImage')
      if (data.status === 200) {
        setToastMessage(data.message)
        return data.data
      } else if (data.status === 201) {
        setErrors(data.data)
      } else {

        showError({
          statusCode: 400,
          message:  data?.message
        })
      }
    } catch (e) {

      showError({
        statusCode: 400,
        message: e.message
      })
    }

  },
  async getDropdownList() {
    const {langCode} = storeToRefs(useLanguageStore());
    const {token} = storeToRefs(useAuthStore());

    try {

      const data = await Service.getRequest({}, token.value, 'getDropdownList', langCode.value)

      if (data.status === 200) {
        const result = data.data

        this.setAllCategories(result.categories)
        this.setAllShippingRules(result.shipping_rules)
        this.setAllProductCollection(result.product_collections)
        this.setAllBundleDeals(result.bundle_deals)
        this.setAllAttributes(result.attributes)
        this.setAllBrands(result.brands)
        this.setAllTaxRules(result.tax_rules)

      } else {
        showError({
          statusCode: 400,
          message:  data?.message
        })
      }

    } catch (e) {

      showError({
        statusCode: 400,
        message: e.message
      })
    }
  },
  async unAuthPost({params, api}) {
    const {langCode} = storeToRefs(useLanguageStore());
    const {setErrors} = useUiStore();

    try {
      const data = await Service.unAuthPost(params, api, langCode.value)

      if (data.status === 200) {
        return data.data
      } else if (data.status === 201) {
        setErrors(data.data)
        return data
      } else {
        showError({
          statusCode: 400,
          message:  data?.message
        })
      }

    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }
  },
  async unAuthGet({params, api}) {
    const {langCode} = storeToRefs(useLanguageStore());

    try {
      const data = await Service.unAuthGet(params, api, langCode.value)

      if (data.status === 200) {
        return data
      } else {
        showError({
          statusCode: 400,
          message:  data?.message
        })
      }

    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }

  },
  async getRequest({params, api}) {
    const {langCode} = storeToRefs(useLanguageStore());
    const {token} = storeToRefs(useAuthStore());

    try {
      const data = await Service.getRequest(params, token.value, api, langCode.value)

      if (data.status === 200) {
        return data.data
      } else {
        showError({
          statusCode: 400,
          message:  data?.message
        })
      }
    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }

  },
  async downloadRequest({params, api}) {
    const {langCode} = storeToRefs(useLanguageStore());
    const {token} = storeToRefs(useAuthStore());

    try {

      const response = await Service.downloadRequest(params, token.value, api, langCode.value)

      if (response) {

        const isJsonBlob = (data) => data instanceof Blob && data.type === "application/json";
        const responseData = isJsonBlob(response) ? await (response)?.text() : response || {};
        const responseJson = (typeof responseData === "string") ? JSON.parse(responseData) : responseData;

        if(responseJson?.status === 201) {
          return Promise.reject({statusCode: responseJson?.status, message: responseJson?.message})

        } else {
          return responseJson
        }
      } else {
        showError({
          statusCode: 400,
          message:  response?.message
        })
      }

    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }
  },
  async setRequest({params, api}) {
    const {langCode} = storeToRefs(useLanguageStore());
    const {token} = storeToRefs(useAuthStore());

    const {setErrors, setToastMessage} = useUiStore();
    try {
      const data = await Service.setRequest(params, token.value, api, langCode.value)

      if (data.status === 200) {
        setToastMessage(data.message)
        return data.data
      } else if (data.status === 201) {
        setErrors(data.data)
        return false
      } else {
        showError({
          statusCode: 400,
          message: data?.message
        })
      }
    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }

  },
  async deleteData({params, api, id}) {
    const {langCode} = storeToRefs(useLanguageStore());
    const {token} = storeToRefs(useAuthStore());

    const {setToastMessage} = useUiStore();
    try {
      const data = await Service.deleteData(params, token.value, api, langCode.value, id)
      if (data.status === 200) {
        setToastMessage(data?.message);
        return data.data
      } else {
        showError({
          statusCode: 400,
          message: data?.message
        })
      }
    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }

  },
  async getById({id, params, api}) {
    const {langCode} = storeToRefs(useLanguageStore());
    const {token} = storeToRefs(useAuthStore());

    try {

      const data = await Service.getById(id, params, token.value, api, langCode.value)
      if (data.status === 200) {
        return data.data
      } else {
        showError({
          statusCode: 400,
          message: data?.message
        })
      }

    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }
  },
  async setById({id, params, api}) {
    const {langCode} = storeToRefs(useLanguageStore());
    const {token} = storeToRefs(useAuthStore());
    const {setErrors, setToastMessage} = useUiStore();

    setErrors(null)

    try {
      const data = await Service.setById(id, params, token.value, api, langCode.value)

      if (data.status === 200) {
        setToastMessage(data.message)
        return data.data
      } else if (data.status === 201) {
        setErrors(data.data)
      } else {
        showError({
          statusCode: 400,
          message:data?.message
        })
      }

    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }
  },
  async setImageById({id, params, api}) {
    const {setErrors, setToastMessage} = useUiStore();
    const {token} = storeToRefs(useAuthStore());
    setErrors(null)

    try {

      const data = await Service.setImageById(id, params, token.value, api)
      if (data.status === 200) {
        setToastMessage(data.message)
        return data.data
      } else if (data.status === 201) {
        setErrors(data.data)
      } else {
        showError({
          statusCode: 400,
          message:data?.message
        })
      }
    } catch (e) {
      showError({
        statusCode: 400,
        message: e.message
      })
    }
  },
}

export const useCommonStore = defineStore('common', {
  state,
  actions
});

