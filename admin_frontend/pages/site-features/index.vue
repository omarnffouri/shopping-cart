<template>
  <PartialsListPage
    v-if="$can('home_slider', 'view')"
    ref="listPageRef"
    list-api="getSiteFeatures"
    delete-api="deleteSiteFeature"
    route-name="site-features"
    :name="$t('title.sf')"
    gate="home_slider"
    :order-options="orderOptions"
    @delete-bulk="deleteBulk"
    @list="setItemList"
  >
    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th>
          <input type="checkbox" @change="checkAll">
        </th>
        <th>{{ $t('admin.img') }}</th>
        <th>{{ $t('index.title') }}</th>
        <th>{{ $t('category.status') }}</th>
        <th>{{ $t('category.created') }}</th>
        <th/>
      </tr>

      <tr v-for="(value, index) in list" :key="index">
        <td>
          <input type="checkbox" :value="value.id" v-model="cbList">
        </td>

        <td>
          <nuxt-link
            :to="`/site-features/${value.id}`"
          >
            <ImageLazy
              class="mx-w-70x"
              :lazy-src="getThumbImageURL(value.image)"
              alt=""
            />
          </nuxt-link>
        </td>

        <td>
          <div v-html="value.detail"></div>
        </td>
        <td
          class="status"
          :class="{active: value.status == 1 }"
        >
          <span>{{ getStatus(value.status) }}</span>
        </td>
        <td>{{ value.created }}</td>
        <td>
          <button
            v-if="$can('home_slider', 'edit')"
            @click.prevent="editNode(value)" class="lite-btn"
          >
            {{ $t('category.edit') }}
          </button>
          <button
            v-if="$can('home_slider', 'delete')"
            @click.prevent="deleteNode(value)"
            class="delete-btn lite-btn"
          >
            {{ $t('category.delete') }}
          </button>
        </td>
      </tr>
    </template>
  </PartialsListPage>
  <!--main-slider-->
</template>

<script setup>

  import {useUtils} from "~/composables/useUtils";
  import {useListHelper} from "~/composables/useListHelper";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {t} = useI18n();

  const orderOptions = ref({
    detail: {title: t('title.det')},
    created_at: {title: t('category.date')}
  });

  const loading = ref(false);
  const result = ref([]);

  const {getStatus, getThumbImageURL} = useUtils();
  const {cbList, deleteBulk, listPageRef, setItemList, checkAll, editNode, deleteNode} = useListHelper();

</script>

