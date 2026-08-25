<template>
  <PartialsListPage
    ref="listPageRef"
    list-api="getBanners"
    delete-api="deleteBanner"
    route-name="banners"
    :name="$t('admin.banner')"
    gate="banner"
    :add-button="false"
    class="banner-table"
  >
    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th>{{ $t('admin.img') }}</th>
        <th>{{ $t('admin.type') }}</th>
        <th>{{ $t('category.slug') }}</th>
        <th>{{ $t('admin.closable') }}</th>
        <th>{{ $t('category.status') }}</th>
        <th>{{ $t('admin.sType') }}</th>
        <th/>
      </tr>

      <tr v-for="(value, index) in list" :key="index">
        <td>
          <nuxt-link
            :to="`/banners/${value.id}`"
          >
            <ImageLazy
              :lazy-src="getThumbImageURL(value.image)"
              alt=""
            />
          </nuxt-link>

        </td>
        <td>{{ bannerUsed[value.type] }}</td>
        <td>{{ value.slug }}</td>
        <td>{{ getFeatured(value.closable) }}</td>
        <td
          class="status"
          :class="{active: value.status == 1 }"
        >
          <span>{{ getStatus(value.status) }}</span>
        </td>
        <td>{{ productSourceObj[value.source_type].title }}</td>
        <td>
          <button
            v-if="$can('banner', 'edit')"
            @click.prevent="editNode(value)" class="lite-btn"> {{ $t('category.edit') }}
          </button>
        </td>
      </tr>
    </template>
  </PartialsListPage>
  <!--main-slider-->
</template>

<script setup>
  import {useListHelper} from "~/composables/useListHelper";
  import {useUtils} from "~/composables/useUtils";
  import {useConstants} from "~/composables/useConstants";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const loading = ref(false);
  const result = ref([]);

  const {bannerUsed, productSourceObj} = useConstants();
  const {getFeatured, getStatus, getThumbImageURL} = useUtils();
  const {listPageRef, editNode} = useListHelper();
</script>
