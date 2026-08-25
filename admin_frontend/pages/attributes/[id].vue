<template>
    <PartialsDataPage
            ref="dataPage"
            set-api="setAttribute"
            get-api="getAttribute"
            empty-store-variable="allAttributes"
            route-name="attributes"
            :name="$t('fSale.attr')"
            :validation-keys="['title']"
            :result="result"
            gate="attribute"
            @result="setResult"
    >
        <template v-slot:form="{hasError}">

            <div class="input-wrapper">

                <label>{{ $t('index.title') }}</label>
                <input
                        type="text"
                        :placeholder="$t('index.title')"
                        name="title"
                        v-model="result.title"
                        :class="{invalid: !!!result.title && hasError}"
                >
                <span
                        class="error"
                        v-if="!!!result.title && hasError"
                >
          {{ $t('category.req', { type: $t('index.title')}) }}
        </span>
            </div>

            <p class="info-msg mb-20 mb-sm-15">{{ $t('brand.delAttr') }}</p>

            <div class=" input-wrapper mb-5">
                <label>{{ $t('brand.aVal') }}</label>
                <div class="attribute-value-wrapper mlr--7-5">
                    <input
                            v-for="(a, i) in result.values"
                            type="text"
                            placeholder="Name"
                            v-model="result.values[i].title"
                            class="mlr-7-5"
                    >
                    <button
                            class="lite-btn mlr-7-5"
                            @click.prevent="addAttributeValue"
                    >
                        {{ $t('brand.addAttr') }}
                    </button>
                </div>

            </div>
        </template>
    </PartialsDataPage>
</template>

<script setup>


    definePageMeta({
        middleware: ['common-middleware', 'auth'],
        layout: 'default',
    });

    const result = ref({
        id: '',
        title: '',
        values: []
    });

    const setResult = (event) => {
        result.value = event
    };

    const addAttributeValue = () => {
        result.value.values.push({title: ''})
    };

</script>
