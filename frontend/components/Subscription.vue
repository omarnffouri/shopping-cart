<template>
  <div
    class="subscription-wrapper pt-20 pb-15"
  >
    <div class="container">
      <div
        class="flex sided block-md"
      >
        <div class="mn-w-50 mb-10">
          <h3 class="bold change_color" >{{ $t('home.subscribeNewsletter') }}</h3>
          <p class="color-lite change_color">{{ $t('home.getLatestEmail') }}</p>
        </div>
        <div class="subcribe_div">
          <form v-if="!messageSent" @submit.prevent="formSubmit" class="subscription-form">
            <div class="input-group" :class="{ 'has-error': invalidEmail &&  hasFormError }">
              <i class="icon email-icon" />
              <input
                  type="text"
                  v-model="email"
                  :placeholder="$t('contact.your', { type: $t('contact.email') })"
                  class="email-input"
                  :class="{ 'input-padding-error': hasFormError && inputErrorText }"
              >
              <span v-if="hasFormError && inputErrorText" class="input-error">
                {{ inputErrorText }}
              </span>
            </div>
            <ajax-button
                class="primary-btn subcribe_btn"
                :fetching-data="formSubmitting"
                :text="$t('home.subscribe')"
            />
          </form>
          <div class="success-msg-container" v-else>
            <div class="success-msg">
              <i class="icon tick-icon" />
              <h4>{{ $t('home.subscribeSuccessMsg') }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import validation from '~/mixin/validation'
  import {useCommonStore} from "~/store/common";

  export default {
    setup(){
      const commonStore = useCommonStore()
      const {unAuthPost} = commonStore

      return {unAuthPost}
    },

  name: 'Subscription',
  data() {
    return {
      errors: [],
      formSubmitting: false,
      email: '',
      hasFormError: false,
      messageSent: false
    }
  },
  components: {},
  props: {},
  mixins: [validation],
  computed: {
    invalidEmail() {
      return !this.isValidEmail(this.email)
    },
    inputErrorText() {
      if (!this.hasFormError) return ''

      if (!this.email) {
        return this.$t('addressPopup.isRequired', { type: this.$t('addressPopup.email') })
      }

      if (this.invalidEmail) {
        return this.$t('contact.invalidEmail')
      }

      if (Array.isArray(this.errors) && this.errors.length) {
        return this.errors[0]
      }

      return ''
    }
  },
  methods: {
    async formSubmit() {
      if (this.email && !this.invalidEmail) {
        this.formSubmitting = true

            const data = await this.unAuthPost({
              params: {email: this.email},
              api: 'emailSubscription'
            })

            if (data?.status === 200) {
              this.messageSent = true
              this.hasFormError = false
            } else {
              this.hasFormError = true
              this.errors = data?.data?.form
            }

          this.formSubmitting = false

        } else {
          this.hasFormError = true
        }
      },
    },
  }
</script>

<style scoped>
.change_color {
  color: #565454;
}

/* ── Wrapper pill (the white rounded container) ── */
.subcribe_div {
  background: white;
  min-height: 44px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  width: 100%;
  max-width: 500px;
  padding: 2px;
  overflow: hidden;
}

/* ── Form layout ── */
.subscription-form {
  display: flex;
  width: 100%;
  align-items: center;
}

.input-group {
  position: relative;
  display: flex;
  align-items: center;
  flex-grow: 1;
  padding-left: 15px;
}

.input-group.has-error {
  border: 1px solid red;
  border-radius: 10px;
}

.email-input {
  border: none;
  background: transparent;
  outline: none;
  width: 100%;
  padding: 10px;
  font-size: 14px;
  box-shadow: none;
}

/* ── Inline input error ── */
.input-error {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  max-width: calc(100% - 60px);
  font-size: 12px;
  color: red;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  pointer-events: none;
}

/* ── Subscribe button ── */
.subcribe_btn {
  height: 40px;
  border-radius: 8px;
  white-space: nowrap;
  margin-right: 2px;
  padding: 10px;
}

.success-msg-container {
  width: 100%;
  display: flex;
  align-items: center;
  /*padding: 6px 14px;*/
  min-height: 44px;
  box-sizing: border-box;
}

.success-msg {
  display: flex;
  align-items: center;
  gap: 8px;
  width: 100%;
  flex-wrap: nowrap;
}

.success-msg i {
  flex-shrink: 0;
}

.success-msg h4 {
  margin: 0;
  font-size: 14px;
  line-height: 1.4;
  word-break: break-word;
  white-space: normal;
  overflow: hidden;
}

.error-container {
  display: flex;
  flex-direction: column;
}

/* ── Tablet (≤ 768px) ── */
@media (max-width: 768px) {
  .subcribe_div {
    max-width: 100%;
  }

  .success-msg h4 {
    font-size: 13px;
  }
}

/* ── Mobile (≤ 576px) ── */
@media (max-width: 576px) {
  /* Pill loses its bg — individual elements carry their own white bg */
  .subcribe_div {
    min-height: auto;
    background: transparent;
    padding: 0;
  }

  .subscription-form {
    flex-direction: column;
    gap: 15px;
  }

  .input-group {
    background: white;
    border-radius: 10px;
    width: 100%;
    padding: 2px 15px;
  }

  .subcribe_btn {
    width: 100%;
    margin: 0;
    height: 44px;
  }

  .success-msg-container {
    background: white;
    border-radius: 10px;
    /*padding: 12px 16px;*/
    min-height: 48px;
    justify-content: flex-start;
  }

  .success-msg {
    gap: 10px;
  }

  .success-msg h4 {
    font-size: 13px;
    white-space: normal;
    word-break: break-word;
  }
}
</style>