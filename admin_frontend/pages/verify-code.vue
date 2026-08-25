<template>
    <form
            @submit.prevent="checkForm()"
            :class="{'has-error': hasError}"
            class="login-form"
    >
        <h4
                class="mb-30 mb-sm-15"
        >
            {{ $t('profile.putCode') }}
        </h4>
        <error-formatter/>

        <div class="input-wrapper">
            <div class="icon-input">
                <i class="icon email-icon">&nbsp;</i>
                <input
                        disabled
                        type="text"
                        :placeholder="$t('fSale.email')"
                        v-model.trim="email"
                        :class="{invalid: !!!email || isInvalidEmail}"
                >
            </div>

            <span
                    class="error"
                    v-if="!!!email && hasError"
            >
        {{ $t('category.req', { type: $t('fSale.email')}) }}
      </span>
            <span
                    class="error"
                    v-else-if="isInvalidEmail && hasError"
            >
        {{ $t('user.isValid', { type: $t('fSale.email') }) }}
      </span>
        </div>

        <div class="input-wrapper">
            <div class="icon-input">
                <i class="icon code-icon">&nbsp;</i>
                <input
                        type="text"
                        :placeholder="$t('profile.cfe')"
                        v-model.trim="code"
                        :class="{invalid: !!!code }"
                >
            </div>

            <span
                    class="error"
                    v-if="!!!code && hasError"
            >
        {{ $t('category.req', { type: $t('setting.code')}) }}
      </span>
        </div>

        <div
                class="input-wrapper"
        >
            <password-field
                    :value="password"
                    :is-invalid="isLengthInvalid"
                    @change-password="password = $event"
            />
            <span
                    class="error"
                    v-if="!password && hasError"
            >
        {{ $t('category.req', { type: $t('user.pass')}) }}
      </span>
            <span
                    class="error"
                    v-else-if="invalidPassword && hasError"
            >
       {{ $t('user.inPass') }}
      </span>
        </div>

        <div
                class="input-wrapper"
        >
            <password-field
                    :value="confirmPassword"
                    @change-password="confirmPassword = $event"
            />
            <span
                    class="error"
                    v-if="!confirmPassword && hasError"
            >
        {{ $t('category.req', { type: $t('user.pass')}) }}
      </span>
            <span
                    class="error"
                    v-else-if="confirmPassword !== password && hasError"
            >
        {{ $t('user.nMatch') }}
      </span>
        </div>

        <div class="dply-felx j-right mt-15">
            <nuxt-link
                    to="/login"
                    class="link"
            >
                {{ $t('profile.ltya') }}
            </nuxt-link>
        </div>

        <ajax-button
                class="mt-20 primary-btn"
                :fetching-data="formSubmitting"
                :loading-text="$t('dataPage.verify')"
                :text="$t('dataPage.up')"
                :activate-btn="true"
        />

    </form>
</template>

<script setup>


    import {useCommonStore} from "~/store/common";
    import {useUiStore} from "~/store/ui";
    import {useValidationHelper} from "~/composables/useValidationHelper";
    import {onMounted} from "vue";

    definePageMeta({
        middleware: ['common-middleware', 'non-auth'],
        layout: 'login-layout',
    });

    const uiStore = useUiStore();
    const {setErrors} = uiStore;

    const commonStore = useCommonStore();
    const {setRequest} = commonStore;


    const password = ref('');
    const code = ref('');
    const confirmPassword = ref('');
    const hasError = ref(false);
    const formSubmitting = ref(false);

    const route = useRoute();

    const email = computed(() => {
        return route?.query?.email
    });

    const {isValidEmail, isValidLength} = useValidationHelper();

    const isInvalidEmail = computed(() => {
        return (email.value && !isValidEmail(email.value));
    });

    const isLengthInvalid = computed(() => {
        return (password.value && !isValidLength(password.value));
    });

    const invalidPassword = computed(() => {
        return !isValidLength(password.value);
    });

    const passwordValid = computed(() => {
        return password.value && !invalidPassword.value;
    });


    const checkForm = async () => {
        hasError.value = false;

        if (!email.value || isInvalidEmail.value || !code.value || !password.value || (password.value !== confirmPassword.value)) {
            hasError.value = true;
            return false;
        }

        formSubmitting.value = true;

        const data = await setRequest({
            params: {
                password: password.value,
                email: email.value,
                code: code.value
            },
            api: 'verifyCode'
        });

        if (data) {
            navigateTo('/login');
        }

        formSubmitting.value = false;
    };

    onMounted(() => {
        setErrors();
    })

</script>

