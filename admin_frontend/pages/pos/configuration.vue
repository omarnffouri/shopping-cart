<template>
    <div class="tab-sidebar">
        <h4 class="title">{{ $t('ship.ps') }}</h4>
        <div class="form-wrapper">

            <form

                @submit.prevent="updateSetting"
            >
                <error-formatter/>

                <error-formatter
                    type="image"
                />

                <div
                    v-if="gettingData"
                    class="spinner-wrapper"
                >
                    <spinner
                        :radius="60"
                        color="primary"
                        class="mr-15"
                    />
                </div>

                <div v-if="!gettingData" class="input-wrapper b-b pb-15">
                    <label class="mb-15">
                        {{ $t('setting.sLogo') }}
                    </label>

                    <div
                        v-if="!gate || (gate && $can(gate, 'edit'))"
                        class=""
                    >
                        <file-upload
                            class="logo-upload upload-block mx-w-300x"
                            :image="result.image"
                            :file-uploading="fileUploading"
                            :btn-text="$t('setting.cLogo')"
                            @file-upload="uploadFile"
                        />
                    </div>

                    <img
                        v-else
                        :src="getImageURL(result.image)"
                    >
                </div>

                <div>
                    <div class="input-wrapper">

                        <label>{{ $t('ship.wdt') }}</label>
                        <input
                            type="text"
                            :placeholder="$t('ship.wdt')"
                            v-model="result.width"
                        >
                    </div>

                    <div class="input-wrapper">

                        <label>{{ $t('list.addr') }}</label>
                        <textarea
                            :placeholder="$t('list.addr')"
                            v-model="result.address"
                        ></textarea>
                    </div>

                    <div class="input-wrapper">

                        <label>{{ $t('ship.ht') }}</label>
                        <textarea
                            :placeholder="$t('ship.ht')"
                            v-model="result.header_text"
                        ></textarea>
                    </div>

                    <div class="input-wrapper">

                        <label>{{ $t('ship.ft') }}</label>
                        <textarea
                            :placeholder="$t('ship.ft')"
                            v-model="result.footer_text"
                        ></textarea>
                    </div>

                    <label
                        v-if="isSuperAdmin"
                        class="input-wrapper block">
                        <input type="checkbox"
                               v-model="result.is_default"
                               :true-value="1" :false-value="0"
                        >
                        {{ $t('ship.md') }}
                    </label>
                </div>

                <div class="dply-felx j-right">

                    <ajax-button
                        v-if="!gate || (gate && $can(gate, 'delete'))"
                        class="delete-btn"
                        type="button"
                        :text="$t('category.delete')"
                        :fetching-data="deletingData"
                        @clicked="deleteItem"
                    />

                    <ajax-button
                        v-if="!gate || (gate && $can(gate, 'edit'))"
                        class="primary-btn"
                        :text="$t('setting.sv')"
                        :fetching-data="updatingData"
                    />
                </div>

            </form>


        </div>
    </div>
</template>

<script>

import util from "~/mixin/util"
import Spinner from "~/components/Spinner"
import DataPage from "../../components/partials/DataPage";
import AjaxButton from "../../components/AjaxButton";
import FileUpload from "../../components/FileUpload";
import ErrorFormatter from "../../components/ErrorFormatter";
import {storeToRefs} from "pinia";
import {useCommonStore} from "../../store/common";
import {useAdminStore} from "../../store/admin";

definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
})

export default {
    setup() {
        const adminStore = useAdminStore()
        const {posPublicKey, isSuperAdmin} = storeToRefs(adminStore)

        const commonStore = useCommonStore()
        const {setRequest, getRequest, deleteData} = commonStore

        return {posPublicKey, setRequest, deleteData, getRequest, isSuperAdmin}
    },
    name: "configuration",

    data() {
        return {
            deletingData: false,
            updatingData: false,
            gettingData: false,
            fileUploading: false,
            gate: 'pos_setting',
            result: {
                id: '',
                width: '',
                image: '',
                address: '',
                footer_text: '',
                header_text: '',
                is_default: false
            }
        }
    },
    mixins: [util],
    components: {
        ErrorFormatter,
        FileUpload,
        AjaxButton,
        DataPage,
        Spinner,
    },
    computed: {},
    methods: {
        async updateSetting() {
            this.updatingData = true
            try {
                const data = await this.setRequest({params: this.result, api: 'setPosSetting'})

                if (data && data?.status !== 201) {
                    this.reloadPage()
                }
            } catch (e) {
                showError({
                    statusCode: 400,
                    message: e.message
                })
            }
            this.updatingData = false
        },
        async uploadFile(file, name = null) {
            this.fileUploading = true

            let params = {}
            if (file) {
                const fd = new FormData()
                fd.append('photo', file)
                params = fd
            } else {
                params['photo'] = name
            }

            try {
                const data = await this.setRequest({params: params, api: 'setPosSettingImage'})

                if (data && data?.status !== 201) {
                    this.reloadPage()
                }

            } catch (e) {
                showError({
                    statusCode: 400,
                    message: e.message
                })
            }
            this.fileUploading = false
        },
        async deleteItem() {
            if (confirm(this.$t('admin.dltMsg'))) {
                try {
                    this.deletingData = true
                    await this.deleteData({params: "", api: 'deletePosSetting'})
                    this.deletingData = false
                    this.reloadPage()
                } catch (e) {
                    showError({
                        statusCode: 400,
                        message: e.message
                    })
                }
            }
        },

    },
    async mounted() {
        this.gettingData = true
        try {
            const data = await this.getRequest({params: {}, api: 'getPosSetting'})

            if (data) {
                this.result = data
            }

        } catch (e) {
            showError({
                statusCode: 400,
                message: e.message
            })
        }
        this.gettingData = false
    }
}
</script>

<style scoped>

</style>
