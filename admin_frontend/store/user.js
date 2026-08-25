

const state = () => ({
  userAddresses: {},
    currentUser: null,
})
const getters = {
  userAddresses: ({userAddresses}) => userAddresses,
}

const actions = {
    setCurrentUser(user) {
        this.currentUser = user;
    },
  async setUserAddresses ( params) {
    this.userAddresses = {...this.userAddresses, ...{[params.user]: params.addresses}}
  },
  async deleteUserAddress ({user, addressId }) {
    const index = this.userAddresses[user].findIndex(i => {
      return i.id === addressId
    })
    if(index > -1){
      this.userAddresses[user]?.splice(index, 1)
    }
  },
  async updateUserAddress ({user, address}) {

    const i = this.userAddresses[user]?.findIndex(i => {
      return i?.id === address?.id
    })

    if(i > -1){
      this.userAddresses[user][i] = address
    } else {

      if(!this.userAddresses[user]){
        this.userAddresses[user] = []
      }
      this.userAddresses[user].push(address)
    }
  }
}

export const useUserStore = defineStore('user', {
  state,
  actions
});
