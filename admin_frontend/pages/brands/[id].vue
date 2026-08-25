<template>
    <PartialsDataPage
            v-if="$can('brand', 'create') || $can('brand', 'view')"
            ref="dataPage"
            set-api="setBrand"
            get-api="getBrand"
            empty-store-variable="allBrands"
            set-image-api="setBrandImage"
            route-name="brands"
            :name="$t('prod.brand')"
            gate="brand"
            :validation-keys="['title', 'slug']"
            :file-keys="['id', 'status']"
            :result="result"
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
                        ref="title"
                        @change="slugChange"
                        :class="{invalid: !!!result.title && hasError}"
                >
                <span
                        class="error"
                        v-if="!!!result.title && hasError"
                >
          {{ $t('category.req', { type: $t('index.title')}) }}
        </span>
            </div>


            <div class="input-wrapper">
                <label>{{ $t('category.slug') }}</label>
                <input
                        type="text"
                        :placeholder="$t('category.slug')"
                        name="slug"
                        v-model="result.slug"
                        ref="slug"
                        :class="{invalid: !!!result.slug && hasError}"
                >
                <span
                        class="error"
                        v-if="!!!result.slug && hasError"
                >
          {{ $t('category.req', { type: $t('category.slug')}) }}
        </span>
            </div>

            <div class="input-wrapper">
                <div class="dply-felx j-left mb-20 mb-sm-15">
          <span class="mr-15">
            {{$t('category.featured')}}
          </span>

                    <dropdown
                            :selectedKey="`${result.featured}`"
                            :options="featuredObj"
                            @clicked="featuredSelected"
                    />
                </div>
            </div>


            <div class="input-wrapper">
                <div class="dply-felx j-left mb-20 mb-sm-15">
          <span class="mr-15">
            {{ $t('category.status') }}
          </span>

                    <dropdown
                            :selectedKey="`${result.status}`"
                            :options="statusObj"
                            @clicked="dropdownSelected"
                    />
                </div>
            </div>

        </template>
    </PartialsDataPage>
</template>

<script setup>
    import {useLanguageStore} from '~/store/language';
    import {storeToRefs} from "pinia";
    import {useConstants} from "../../composables/useConstants";

    definePageMeta({
        middleware: ['common-middleware', 'auth'],
        layout: 'default',
    });

    const languageStore = useLanguageStore();
    const {currentLanguage} = storeToRefs(languageStore);

    const result = ref({
        id: '',
        title: '',
        slug: '',
        featured: 2,
        status: 2,
        image: ''
    });

    const {statusObj,featuredObj} = useConstants();

    const featuredSelected = (data) => {
        result.value.featured = data.key;
    };

    const dropdownSelected = (data) => {
        result.value.status = data.key
    };

    const setResult = (event) => {
        result.value = event
    };

</script>
