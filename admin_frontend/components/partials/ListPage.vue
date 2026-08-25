<template>
  <div :class="{loading: loading}">
    <div v-if="deleting" class="spinner-wrapper">
      <spinner :radius="60" color="primary" class="mr-15"/>
    </div>

    <slot name="table-top" v-bind:orderOptions="propOrderOptions">
      <table-top
        :title="name"
        :gate="gate"
        :add-button="addButton"
        :order-by-options="propOrderOptions"
        @delete-bulk="deleteBulk"
      >
        <template v-slot:filter>
          <slot name="filter"/>
        </template>
        <template v-slot:bulk-action>
          <slot name="bulk-action"/>
        </template>
        <slot name="add-button"/>

      </table-top>
    </slot>

    <transition
      v-if="!gate || $can(gate, 'view')"
      name="fade" mode="out-in"
    >
      <div v-if="!loading">
        <h5 class="mt-20 mt-sm-15">{{ resultText }}</h5>

        <div class="card">
        <div class="table-wrapper">
          <table class="mn-w-600x">
            <slot
              name="table"
              v-bind:list="list"
            />
          </table>
        </div>
        </div>

        <pagination
          :total-page="totalPage"
        />
      </div>
      <shimmer
        v-else
      />
    </transition>

  </div>
</template>

<script>
  import { useLanguageStore } from '~/store/language';
  import { useCommonStore } from '~/store/common';

  import Shimmer from '~/components/Shimmer'
  import util from '~/mixin/util'
  import Pagination from "~/components/partials/Pagination"
  import TableTop from "~/components/partials/TableTop"
  import routeParamHelper from "~/mixin/routeParamHelper"
  import Spinner from "~/components/Spinner"
  import {storeToRefs} from "pinia";
  import {showError} from "nuxt/app";
  import {useI18n} from "vue-i18n";

  export default {
    setup(props) {
      const { t } = useI18n()

      const propOrderOptions = props.orderOptions || {
        created_at: { title: t('category.date') },
        title: { title: t('index.title') },
        status: { title: t('category.status') }
      }

      const {deleteData, getRequest, emptyAllList} = useCommonStore()

      const languageStore = useLanguageStore()
      const {currentLanguage} = storeToRefs(languageStore)

      return {currentLanguage, deleteData, getRequest, emptyAllList, propOrderOptions}
    },
    name: "ListPage",
    props: {
      addButton: {
        type: Boolean,
        default: true
      },
      name: {
        type: String,
        default: ''
      },
      gate: {
        type: String,
        default: null
      },
      listApi: {
        type: String,
        default: ''
      },
      deleteApi: {
        type: String,
        default: ''
      },
      routeName: {
        type: String,
        default: ''
      },
      emptyStoreVariable: {
        type: String,
        default: null
      },
      orderOptions: {
        type: Object,
        default: null
      },
    },
    data(){
      return {
        deleting: false,
        loading: true,
        result: null,
      }
    },
    components: {
      Spinner,
      Pagination,
      TableTop,
      Shimmer
    },
    mixins: [util, routeParamHelper],
    computed: {
      resultText() {
        if (this.result) {
          if(this.result?.total > 0){
            return this.$t('list.show', { from: this.result?.from, to: this.result?.to, total: this.result?.total })
          }
          return this.$t('list.noData', { data: this.name})
        }
        return this.$t('list.loadn') + '...'
      },
      list() {
        return this.result?.data
      },
      totalPage() {
        return this.result?.last_page
      },
    },
    methods: {
      deleteBulk(){
        this.$emit('delete-bulk')
      },
      async fetchingData() {
        try {
          this.settingRouteParam()
          this.loading = true
          this.result = await this.getRequest({
            params: {
              ...this.$route.query,
              ...this.listParams,
              ...{time_zone: this.timeZone}
            },
            api: this.listApi
          })

          this.$emit('list', this.list)

          this.loading = false
        } catch (e) {
          showError({
            statusCode: 400,
            message: e.message
          })
        }
      },
      editItem(id) {
        return this.$router.push(`/${this.routeName}/${id}`)
      },
      async deleteItem(id) {
        if (confirm(this.$t('admin.dltMsg'))) {

            this.deleting = true
            await this.deleteData({params: id, api: this.deleteApi, id: id })
            this.emptyAllList(this.emptyStoreVariable)
            this.$emit('deleted')
            this.deleting = false
            await this.fetchingData()
        }
      },

    },
    mounted() {
      if(!this.gate || this.$can(this.gate, 'view')){
        this.fetchingData()
      }
    }
  }
</script>
