<template>
    <PartialsDataPage
            ref="dataPage"
            set-api="setCategory"
            get-api="getCategory"
            set-image-api="setCategoryImage"
            route-name="categories"
            :name="$t('category.catUp')"
            gate="category"
            :validation-keys="['title', 'slug', 'meta_title', 'meta_description']"
            :file-keys="['id', 'status']"
            :result="result"
            @result="resultData"
    >
        <template v-slot:form="{hasError}">
            <div class="input-wrapper">

                <label>{{ $t('index.title') }}</label>
                <input
                        type="text"
                        :placeholder="$t('index.title')"
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
                <div class="dply-felx j-left mb-20 mb-sm-15">
                    <span class="mr-15">{{$t('title.pc')}}</span>
                    <dropdown
                            v-if="allCategories"
                            :default-null="true"
                            :selectedKey="`${result.parent}`"
                            :options="allCategories"
                            @clicked="categorySelected"
                    />
                </div>
            </div>

            <div class="input-wrapper">

                <label>{{ $t('category.slug') }}</label>
                <input
                        type="text"
                        :placeholder="$t('category.slug')"
                        v-model="result.slug"
                        ref="title"
                        :class="{invalid: !!!result.slug && hasError}"
                >
                <span class="error" v-if="!!!result.slug && hasError">
                    {{ $t('category.req', { type: $t('category.slug')}) }}
                </span>
            </div>


            <div class="input-wrapper">
                <label>{{ $t('category.mTitle') }}</label>
                <input
                        type="text"
                        :placeholder="$t('category.mTitle')"
                        v-model="result.meta_title"
                        :class="{invalid: !!!result.meta_title && hasError}"
                >
                <span class="error"
                      v-if="!!!result.meta_title && hasError"
                >
          {{ $t('category.req', { type: $t('category.mTitle')}) }}
        </span>
            </div>

            <div class="input-wrapper">
                <label>{{ $t('category.mDesc') }}</label>
                <textarea
                        :placeholder="$t('category.mDesc')"
                        v-model="result.meta_description"
                        :class="{invalid: !!!result.meta_description && hasError}"
                />
                <span class="error"
                      v-if="!!!result.meta_description && hasError"
                >
          {{ $t('category.req', { type: $t('category.mDesc')}) }}
        </span>
            </div>

            <div class="input-wrapper">
                <label>{{ $t('ship.mk') }} ({{ $t('ship.csk') }})</label>
                <textarea
                        :placeholder="$t('ship.mk')"
                        v-model="result.meta_keywords"
                        :class="{invalid: !!!result.meta_keywords && hasError}"
                />
                <span class="error"
                      v-if="!!!result.meta_keywords && hasError"
                >
                {{ $t('category.req', { type: $t('ship.mk')}) }}
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
                        {{ $t('title.sif') }}
                    </span>
                    <dropdown
                            :selectedKey="`${result.in_footer}`"
                            :options="featuredObj"
                            @clicked="inFooterSelected"
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

    import {useCommonStore} from "~/store/common";
    import {storeToRefs} from "pinia";
    import {useLanguageStore} from "~/store/language";
    import {onMounted} from "vue";
    import {useUtils} from "~/composables/useUtils";
    import {useConstants} from "../../composables/useConstants";

    definePageMeta({
        middleware: ['common-middleware', 'auth'],
        layout: 'default',
    });

    const commonStore = useCommonStore();
    const {getAllList, emptyAllList} = commonStore;
    const {allCategories} = storeToRefs(commonStore);

    const languageStore = useLanguageStore();
    const {currentLanguage} = storeToRefs(languageStore);

    const result = ref({
        id: '',
        title: '',
        status: 2,
        featured: 2,
        parent: '',
        slug: '',
        meta_description: '',
        meta_keywords: '',
        in_footer: 2,
        meta_title: '',
        image: ''
    });

    const route = useRoute();
    const {convertToSlug} = useUtils();

    const {statusObj, featuredObj} = useConstants();

    const resultData = (evt) => {
        if (route?.params?.id === 'add') {
            emptyAllList('allCategories')
        }
        result.value = evt
    };

    const inFooterSelected = (data) => {
        result.value.in_footer = data.key
    };

    const featuredSelected = (data) => {
        result.value.featured = data.key
    };

    const categorySelected = (data) => {
        result.value.parent = data.key
    };

    const titleChanged = () => {
        result.value.slug = convertToSlug(result.value.title)
    };

    const dropdownSelected = (data) => {
        result.value.status = data.key
    };

    onMounted(async () => {
        if (!allCategories.value) {
            getAllList({api: 'getAllCategories', action: 'setAllCategories'})
        }
    });
</script>

