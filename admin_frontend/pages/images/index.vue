<template>

  <div>
    <h5 class="mb-20">{{ $t('list.show', { from: 1, to: imageCount, total: imageCount })}}</h5>


    <div
      v-if="loading"
      class="spinner-wrapper"
    >
      <spinner
        :radius="100"
        color="primary"
        class="mr-15"
      />
    </div>

    <div v-else class="image-container">
      <div
        v-for="(data, index) in thumbs"
        :key="index"
        class="card"
      >

        <ImageLazy
          class="mr-20"
          :lazy-src="getImageURL(data)"
          :alt="thumbToMain(data)"
        />
        {{ thumbToMain(data) }}
        <button
          class=""
          @click.prevent="deleteImage(index)">
          ✖
        </button>


      </div>
    </div>
  </div>

</template>

<script>
  import { useCommonStore } from '~/store/common';
  import util from '~/mixin/util'
  import Spinner from "../../components/Spinner";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  })

  export default {
    setup(){
      const {deleteData, getRequest} = useCommonStore()
      return {deleteData, getRequest}
    },
    name: "images",

    data() {
      return {
        loading: false,
        imageList: []
      }
    },
    directives: {},
    components: {
      Spinner
    },
    mixins: [util],
    computed: {
      imageCount(){
        return this.thumbs.length
      },
      thumbs(){
        return this.imageList?.filter((str) => str.startsWith("thumb-"));
      }
    },
    methods: {
      async deleteImage(index){
        if (confirm(this.$t('admin.dltMsg'))) {
          try {
            this.loading = true
            await this.deleteData({params: this.thumbToMain(this.thumbs[index]), api: 'imgDelete', id: this.thumbToMain(this.thumbs[index])})
            await this.fetchingData()
            this.loading = false

          }catch (e) {
             showError({
            statusCode: 400,
            message: e.message
          })
          }
        }
      },
      thumbToMain(image) {
        image = image.replace('thumb-', '')
        return image
      },
      async fetchingData(){

        this.loading = true
        this.imageList = await this.getRequest({
          params: { },
          api: 'imgAll'
        })
        this.loading = false
      }
    },
    async mounted() {
     await this.fetchingData()
    },

  }
</script>
