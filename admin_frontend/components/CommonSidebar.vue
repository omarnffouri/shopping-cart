<template>
    <div v-if="sidebarPermissions" class="sidebar">
        <div v-if="childrenOpened"
             class="child-layer"
             @click="closeChild"
        />
        <ul class="sb sb-2">

            <li v-for="(value, index) in sidebarsArr"
                :key="index"
                :class="[{'child-open': value.open}, {'child-active': value.childActive}, {active: isCurrentRoute(value)}]"
            >
                <nuxt-link
                        v-if="hasParentPermission(value)"
                        :to="getRoute(value)"
                        :title="value.title"
                        @click="clicked($event, value)"
                >
                    <i class="icon"
                       :class="value.icon"
                    />
                    <span class="dply-felx grow">
                        <span class="dply-felx grow">
                            {{ value.title }}
                            <span v-if="checkBadge(value)" class="plugin-badge">{{ $t('ship.pls') }}</span>
                        </span>

                        <i v-if="value.children"
                           class="icon arrow-down"
                        />
                    </span>
                </nuxt-link>

                <ul v-if="value.children && value.open"
                    class="child"
                >
                    <li v-for="(child, i) in value.children"
                        :key="i"
                        :class="{active: isCurrentRoute(child)}"
                    >
                        <nuxt-link
                                v-if="hasPermission(child)"
                                :title="child.title"
                                :to="getRoute(child)"
                                @click="clicked($event, child)"
                        >

                            <i class="icon"
                               :class="child.icon"
                            />
                            <span class="">{{ child.title }}</span>
                        </nuxt-link>
                    </li>
                </ul>
            </li>

        </ul>

        <button class="minimize-btn centered-flex" @click.prevent="toggleSidebar"/>

        <transition name="fade" mode="out-in">
            <div v-if="sidebarOpen"
                 class="layer"
                 @click.prevent="hideSidebar"
            />
        </transition>
    </div>
</template>

<script setup>
  import {useUiStore} from "~/store/ui";
  import {useAdminStore} from "~/store/admin";
  import {storeToRefs} from "pinia";
  import {onMounted} from "vue";

  const adminStore = useAdminStore();
  const uiStore = useUiStore();
  const {toggleSidebar, hideSidebar} = uiStore;

  const {sidebarPermissions, isVendor} = storeToRefs(adminStore);
  const {sidebarOpen} = storeToRefs(uiStore);

  const isDemo = computed(() => {
    const config = useRuntimeConfig();
    return config.public.isDemo;
  });

  const {t} = useI18n();

  const sidebarsArr = ref([
    {
      path: '',
      title: t('error.das'),
      icon: 'dashboard',
      gate: 'dashboard'
    },
    {
      path: 'product',
      title: t('fSale.product'),
      icon: 'products',
      open: false,
      children: [
        {
          path: 'categories',
          title: t('error.cat'),
          icon: 'categories',
          gate: 'category'
        },
        {
          path: 'brands',
          title: t('error.brands'),
          icon: 'brands',
          gate: 'brand'
        },
        {
          path: 'attributes',
          title: t('list.attr'),
          icon: 'attributes',
          gate: 'attribute'
        },
        {
          path: 'tax-rules',
          title: t('error.tr'),
          icon: 'tax-rules',
          gate: 'tax_rule'
        },
        {
          path: 'shipping-rules',
          title: t('brand.deliveryTypes'),
          icon: 'shipping-rules',
          gate: 'shipping_rule'
        },
        {
          path: 'product-collections',
          title: t('error.col'),
          icon: 'product-collections',
          gate: 'product_collection'
        },
        {
          path: 'bundle-deals',
          title: t('error.bd'),
          icon: 'bundle-deals',
          gate: 'bundle_deal'
        },
        {
          path: 'vouchers',
          title: t('error.vou'),
          icon: 'vouchers',
          gate: 'voucher'
        },
        {
          path: 'products',
          title: t('error.prod'),
          icon: 'products',
          form: ['product', 'ratingReviews'],
          gate: 'product'
        }
      ]
    },
    {
      path: 'flash-sales',
      title: t('error.fs'),
      icon: 'flash-sales',
      form: ['flash-sales'],
      gate: 'flash_sale'
    },
    {
      path: 'pos',
      title: t('ship.pos'),
      icon: 'pos',
      open: false,
      gate: 'pos',
      badge: !!isDemo.value,
      children: [
        {
          path: 'pos/manager',
          title: t('ship.pm'),
          icon: 'pos-manager',
          gate: 'pos'
        },
        {
          path: 'pos/order',
          title: t('ship.po'),
          icon: 'orders',
          gate: 'pos'
        },
        {
          path: 'pos/configuration',
          title: t('ship.pc'),
          icon: 'setting',
          gate: 'pos_setting'
        }
      ]
    },
    {
      path: 'orders',
      title: t('error.orders'),
      icon: 'orders',
      form: ['order'],
      gate: 'order',
      exact: true
    },
    {
      path: 'orders/abandoned_orders',
      title: t('error.abandoned_orders'),
      icon: 'orders',
      form: ['order'],
      gate: 'order'
    },
    {
      path: 'rating-reviews',
      title: t('error.rr'),
      icon: 'rating-reviews',
      form: ['rating-reviews'],
      gate: 'rating_review'
    },

    {
      path: 'users',
      title: t('error.users'),
      icon: 'users',
      open: false,
      gate: 'user',
      children: [
        {
          path: 'registered-users',
          title: t('profile.registered'),
          icon: 'registered',
          gate: 'user'
        },
        {
          path: 'guest-users',
          title: t('profile.guest'),
          icon: 'guest',
          gate: 'user'
        }
      ]
    },
    /* {
       path: 'users',
       title: t('error.users'),
       icon: 'users',
       form: ['user'],
       gate: 'user'
     },*/
    {
      path: 'subscription',
      title: t('error.subs'),
      icon: 'subscription',
      open: false,
      children: [
        {
          path: 'subscribers',
          title: t('error.subCrbs'),
          icon: 'subscribers',
          form: ['subscriber'],
          gate: 'subscriber'
        },
        {
          path: 'subscription-email-formats',
          title: t('error.ef'),
          icon: 'subscription-email-formats',
          form: ['subscription-email-format'],
          gate: 'subscription_email_format'
        },
      ]
    },
    {
      path: 'bulk-upload',
      title: t('title.bu'),
      icon: 'bulk-upload',
      form: ['bulk-upload'],
      gate: 'bulk_upload'
    },
    {
      path: 'roles-permissions',
      title: t('error.rp'),
      icon: 'roles-permissions',
      form: ['roles-permissions'],
      gate: 'role'
    },
    {
      path: 'admins-vendors',
      title: t('error.av'),
      icon: 'admins-vendors',
      form: ['admins-vendors'],
      gate: 'admin'
    },
    {
      path: 'withdrawal',
      title: t('error.wth'),
      icon: 'withdrawal',
      open: false,
      children: [
        {
          path: 'withdrawal-requests',
          title: t('error.req'),
          icon: 'withdrawal-requests',
          gate: 'withdrawal_request'
        },
        {
          path: 'withdrawal-accounts',
          title: t('error.acc'),
          icon: 'withdrawal-accounts',
          gate: 'withdrawal_account'
        }
      ]
    },
    {
      path: 'ui',
      title: 'UI',
      icon: 'ui',
      open: false,
      children: [
        {
          path: 'pages',
          title: t('error.pages'),
          icon: 'pages',
          form: ['customPage'],
          gate: 'page'
        },
        {
          path: 'home-slider',
          title: t('profile.hSlid'),
          icon: 'home-slider',
          gate: 'home_slider'
        },
        {
          path: 'banners',
          title: t('admin.banners'),
          icon: 'banners',
          gate: 'banner'
        },
        {
          path: 'footer-links',
          title: t('error.fl'),
          icon: 'footer',
          gate: 'footer_link'
        },
        {
          path: 'header-links',
          title: t('dataPage.hl'),
          icon: 'header-links',
          gate: 'header_link'
        },
        {
          path: 'site-features',
          title: t('title.sf'),
          icon: 'site-features',
          gate: 'home_slider'
        },
        {
          path: 'site-setting',
          title: t('admin.site'),
          icon: 'site-setting',
          gate: 'site_setting'
        },
        {
          path: 'custom-scripts',
          title: t('title.cs'),
          icon: 'custom-scripts',
          gate: 'site_setting'
        },
      ]
    },
    {
      path: 'store',
      title: t('error.store'),
      icon: 'store',
      gate: 'store'
    },
    {
      path: 'setting/currency',
      active: 'setting',
      title: t('list.set'),
      icon: 'setting',
      gate: 'setting'
    }
  ]);

  const childrenOpened = ref(false);

  const sidebarCollapsable = computed(() => {
    const data = {}
    sidebarsArr.value.forEach(i => {
      if (!i.gate) {
        data[i.path] = i?.children?.map(j => {
          return j.gate
        })
      }
    })
    return data
  });


  const navigate = (event) => {
    event.preventDefault(); // Prevent default anchor behavior
    const shouldNavigate = false; // Replace with your logic
    if (shouldNavigate) {
      navigateTo('/target-route');
    }
  };

  const checkBadge = (item) => {
    return item?.badge || false
  };

  const hasParentPermission = (item) => {
    if (!item?.gate && !item.children) {
      return true
    }
    if (!item?.gate) {
      // Checking if any child has permission
      return !!sidebarCollapsable.value[item.path]?.find(i => {
        return hasPermission({gate: i})
      })
    }
    return hasPermission(item)
  };

  const hasPermission = (item) => {
    return (sidebarPermissions.value &&
            (sidebarPermissions.value[`${item?.gate}.create`] !== undefined ||
                    sidebarPermissions.value[`${item?.gate}.view`] !== undefined)
    )
  };

  const route = useRoute();

  const isCurrentRoute = (val) => {
    let check = val.path
    if (val?.active) {
      check = val?.active
    }

    const routePath = route.path.replace(/^\/|\/$/g, '');
    const checkPath = check.replace(/^\/|\/$/g, '');

    if (val.exact) {
      return routePath === checkPath;
    }

    return routePath === checkPath || routePath.startsWith(checkPath + '/');
  };

  const closeChild = () => {
    const findI = sidebarsArr.value.findIndex((o) => {
      return o.open === true
    })

    if (findI > -1) {
      sidebarsArr.value[findI].open = childrenOpened.value = false
    }
  };

  const getRoute = (value) => {
    if (value?.children) {
      return ''
    }
    return `/${value.path}`
  };

  const clicked = (event, value) => {
    if (value.children) {
      if (!sidebarOpen.value) {
        const findI = sidebarsArr.value.findIndex((o) => {
          return o.open === true
        })
        if (findI > -1 && sidebarsArr.value[findI].path !== value.path) {
          sidebarsArr.value[findI].open = childrenOpened.value = false
        }
      }
      value.open = !value.open
      childrenOpened.value = !sidebarOpen.value && value.open

    } else {
      if (!sidebarOpen.value) {
        closeChild();
      }

      reRenderActiveChild()
      if (window.innerWidth < 992) {
        hideSidebar()
      }
    }
  };

  const reRenderActiveChild = () => {
    const sidbearI = sidebarsArr.value.findIndex((o) => {
      return o.childActive === true
    })
    if (sidbearI > -1) {
      sidebarsArr.value.splice(sidbearI, 1, {...sidebarsArr.value[sidbearI], ...{childActive: false}})
    }
    sidebarsArr.value.forEach((value, index) => {
      if (isCurrentRoute(value)) {
        return false
      } else {
        return value?.children?.forEach((v, i) => {
          if (isCurrentRoute(v)) {
            sidebarsArr.value[index].childActive = true
            return false
          }
        })
      }
    })
  };


  watch(sidebarOpen, () => {
    closeChild();
  });


  onMounted(() => {
    if (window.innerWidth < 778) {
      hideSidebar()
    }


    if (sidebarOpen.value) {
      sidebarsArr.value.forEach((value, index) => {
        if (isCurrentRoute(value)) {
          return false
        } else {
          return value?.children?.forEach((v, i) => {
            if (isCurrentRoute(v)) {
              sidebarsArr.value[index].open = true
              sidebarsArr.value[index].childActive = true
              return false
            }
          })
        }
      })
    } else {
      sidebarsArr.value.forEach((value, index) => {
        if (isCurrentRoute(value)) {
          return false
        } else {
          return value?.children?.forEach((v, i) => {
            if (isCurrentRoute(v)) {
              sidebarsArr.value[index].childActive = true
              return false
            }
          })
        }
      })
    }
  });
</script>

