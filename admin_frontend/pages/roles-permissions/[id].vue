<template>
  <PartialsDataPage
    v-if="allPermissions"
    ref="dataPageRef"
    set-api="setRole"
    get-api="getRole"
    route-name="roles-permissions"
    empty-store-variable="allRoles"
    :name="$t('dataPage.rPer')"
    :validation-keys="['name']"
    :result="result"
    gate="role"
    @result="result = $event"
  >
    <template v-slot:form="{hasError}">
      <div class="input-wrapper">

        <label>{{ $t('user.name') }}</label>
        <input
          type="text"
          :placeholder="$t('user.name')"
          name="title"
          v-model="result.name"
          ref="title"
          :class="{invalid: !!!result.name && hasError}"
        >
        <span
          class="error"
          v-if="!!!result.name && hasError"
        >
          {{ $t('category.req', { type: $t('user.name')}) }}
        </span>
      </div>

      <div class="input-wrapper">
        <label>{{ $t('user.per') }}</label>
        <div class="b-b mb-10 mb-md-15 pb-10">
          <input
            class="styled-checkbox"
            :id="`styled-checkbox-all`"
            type="checkbox"
            v-model="allSelected"
            @change="selectAll"
          >
          <label
            :for="`styled-checkbox-all`"
            class="mtb-5"
          >
            {{ $t('index.all') }}
          </label>
        </div>

        <div
          v-for="(value, key, index) in groupBy(allPermissions, 'group_name')"
          :key="index"
          class="permission-group"
        >
          <div>
            <input
              v-model="groupPermissions"
              class="styled-checkbox"
              :id="`styled-checkbox-${key}`"
              type="checkbox"
              :value="key"
              @change="selectGroup(key, $event)"
            >
            <label
              :for="`styled-checkbox-${key}`"
              class="mtb-5 mt-md-15"
            >
              {{ formatGroupName(key) }}
            </label>
          </div>
          <div
            class="permission-item "
          >
            <span
              v-for="(i, ind) in value"
              :key="`${index}-${ind}`"
              class="mr-15"
            >
              <input
                v-model="selectedPermissions"
                class="styled-checkbox"
                :id="`checkbox-${index}-${ind}`"
                type="checkbox"
                :value="i.id"
                @change="permissionChanged"
              >
              <label
                :for="`checkbox-${index}-${ind}`"
                class="mtb-7-5"
              >
                {{ formatPermissionName(i.name) }}
              </label>
            </span>
          </div>
        </div>
      </div>
    </template>
  </PartialsDataPage>
</template>

<script setup>

  import {useCommonStore} from "~/store/common";
  import {storeToRefs} from "pinia";
  import {onMounted} from "vue";

  definePageMeta({
    middleware: ['common-middleware', 'auth'],
    layout: 'default',
  });

  const commonStore = useCommonStore();
  const {allPermissions} = storeToRefs(commonStore);
  const {getAllList} = commonStore;
  const allSelected = ref(false);

  const groupPermissions = ref([]);
  const selectedPermissions = ref([]);
  const result = ref({
    id: '',
    name: '',
    updated_permissions: [],
    permissions: []
  });

  const resultPermissions = computed(() => {
    return result.value?.permissions;
  });

  watch(resultPermissions, (value) => {
    selectedPermissions.value = value?.map(i => {
      return i.id;
    });
  });

  const groupBy = (arr, group) => {
    return arr.reduce((acc, obj) => {
      const key = obj[group];
      if (!acc[key]) {
        acc[key] = [];
      }
      // Add object to list for given key's value
      acc[key].push(obj);
      return acc;
    }, {});
  };

  const formatPermissionName = (name) => {
    return capitalizeFirstLetter(name?.replace('_', ' ').split('.')[1]);
  };

  const formatGroupName = (name) => {
    return capitalizeFirstLetter(name?.replace(/\.|_/g, ' '));
  };

  const capitalizeFirstLetter = (string) => {
    return string?.charAt(0).toUpperCase() + string?.slice(1);
  };

  const selectAll = () => {
    groupPermissions.value = [];
    selectedPermissions.value = [];
    if (allSelected.value) {
      for (let i in allPermissions.value) {
        selectedPermissions.value.push(allPermissions.value[i].id);
        groupPermissions.value.push(allPermissions.value[i].group_name);
      }
    }
    groupPermissions.value = [...new Set(groupPermissions.value)]
    result.value.updated_permissions = selectedPermissions.value;
  };

  const selectGroup = (data, event) => {
    const current = allPermissions.value.filter(i => {
      return i.group_name === data;
    }).map(i => {
      return i.id;
    })

    if (event.target.checked) {
      selectedPermissions.value = [...new Set([...selectedPermissions.value, ...current])];
    } else {
      selectedPermissions.value = selectedPermissions.value.filter(i => {
        return current.indexOf(i) === -1
      });
    }
    result.value.updated_permissions = selectedPermissions.value;
  };

  const permissionChanged = () => {
    groupPermissions.value = []
    allSelected.value = false
    result.value.updated_permissions = selectedPermissions.value;
  };

  onMounted(async () => {
    if (!allPermissions.value) {
      await getAllList({api: 'allPermissions', action: 'setAllPermissions'});
    }
  });

</script>
