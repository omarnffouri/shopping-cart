<template>
  <PartialsListPage
    v-if="$can('admin', 'view')"
    ref="listPageRef"
    list-api="getAdmins"
    delete-api="deleteAdmin"
    route-name="admins-vendors"
    :name="$t('user.admVendUp')"
    :order-options="orderOptions"
    gate="admin"
  >
    <template v-slot:table="{list}">
      <tr class="lite-bold">
        <th>{{ $t('user.name') }}</th>
        <th>{{ $t('user.uName') }}</th>
        <th>{{ $t('fSale.email') }}</th>
        <th>{{ $t('user.role') }}</th>
        <th>{{ $t('user.verified') }}</th>
        <th>{{ $t('title.ac') }}</th>
        <th>{{ $t('category.created') }}</th>
        <th>&nbsp;</th>
      </tr>

      <tr
        v-for="(value, index) in list"
        :key="index"
        :class="{'new-data': !parseInt(value.viewed)}"
      >
        <td class="">
          <nuxt-link
            class="link"
            :to="`/admins-vendors/${value.id}`"
          >
            <h5 class="mx-w-300x">{{ value.name }}</h5>
          </nuxt-link>
        </td>
        <td>{{ value.username }}</td>
        <td>{{ value.email }}</td>
        <td>
          <span
            v-for="(i, n) in value.roles"
            :key="n"
          >
            {{ i.name }}
          </span>
        </td>

        <td><span>{{ getVerificationStatus(value.verified) }}</span></td>

        <td>{{ getBoolean(value.active) }}</td>
        <td>{{ value.created }}</td>
        <td>
          <button
            v-if="$can('admin', 'edit')"
            @click.prevent="editNode(value)"
            class="lite-btn"
          >
            {{ $t('category.edit') }}
          </button>
          <button
            v-if="$can('admin', 'delete')"
            @click.prevent="deleteNode(value)"
            class="delete-btn lite-btn"
          >
            {{ $t('category.delete') }}
          </button>
        </td>
      </tr>
    </template>
  </PartialsListPage>
</template>

<script setup>
  import {useListHelper} from "~/composables/useListHelper";
  import {useUtils} from "~/composables/useUtils";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const {t} = useI18n();

  const orderOptions = ref({
    name: {title: t('user.name')},
    email: {title: t('fSale.email')},
    username: {title: t('user.uName')},
    created_at: {title: t('category.date')},
  });

  const {getVerificationStatus, getBoolean} = useUtils();
  const {listPageRef, editNode, deleteNode} = useListHelper();

</script>
