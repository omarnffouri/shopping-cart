import Service from '@/services/service.js'
import {useAuthStore} from "./auth";
import {useUiStore} from "./ui";
const state = () => ({
  payment: null
})

const actions = {
  async getPayment () {
    const {token} = useAuthStore();
    const data = await Service.getRequest({}, token, 'getPayment')

    if(data.status === 200){
      this.payment = data.data
    }else {
      return Promise.reject({statusCode: data.status, message: data.message })
    }
  },
  async setPayment (params) {
    const {setErrors, setToastMessage} = useUiStore();
    setErrors(null)
    const {token} = useAuthStore();

    const data = await Service.setRequest(params, token, 'setPayment')

    if (data.status === 200) {
      this.payment = data.data
      setToastMessage(data.message)
      return data
    } else if (data.status === 201) {
      setErrors(data.data)
    } else {
      return Promise.reject({statusCode: data.status, message: data.message})
    }
  }
}

export const usePaymentStore = defineStore('payment', {
  state,
  actions
});
