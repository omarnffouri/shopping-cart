import Service from '@/services/service.js'
import {useAuthStore} from "./auth";

const state = () => ({
  messageCount: 0,
  userMessages: null,
})

const actions = {
  setProfileUserMessage(params){
    this.messageCount = params?.message_count
  },
  async getUserMessages (params) {
    const {token} = useAuthStore();
    const data = await Service.getRequest(params, token, 'getUserMessages')

    if(data.status === 200){
      this.userMessages = data.data.data
      this.messageCount = 0
    }else {
      return Promise.reject({statusCode: data.status, message: data.message })
    }
  }
}

export const useUserMessageStore = defineStore('userMessage', {
  state,
  actions
});
