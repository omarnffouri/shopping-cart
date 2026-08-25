export function useConstants() {

  const currencyPositionsIn = {
    PRE: 1,
    POST: 2
  };

  const {t} = useI18n();

  const featuredObj = {
    1: {title: t('prod.yes')},
    2: {title: t('prod.no')},
  };

  const statusObj = {
    1: {title: t('util.public')},
    2: {title: t('util.private')}
  };

  const priceTFormat = {
    FLAT: 1,
    PERCENT: 2
  };

  const priceTypeObj = {
    1: {title: t('util.flat')},
    2: {title: t('util.perc')}
  };


  const dpConfig = {
    enableTime: true,
    altFormat: "F j, Y, H:i",
    altInput: true
  };


  const wysiwygType = {
    PAGE: 1,
    PRODUCT_OVERVIEW: 2,
    PRODUCT_DESCRIPTION: 3
  };

  const mediaStorageData = {
    LOCAL: 'LOCAL',
    GCS: 'GCS',
    URL: 'URL'
  };

  const orderStatus = {
    1: {title: t('util.pend')},
    2: {title: t('util.con')},
    3: {title: t('util.pu')},
    4: {title: t('util.otw')},
    5: {title: t('index.delivered')},
    6: {title: t('title.cancel')}
  };


  const paymentTypes = {
    1: t('title.rp'),
    2: t('fSale.cod'),
    3: t('title.sp'),
    4: t('title.pay'),
    5: t('title.fw'),
    6: t('title.ip'),
    7: t('title.bt'),
    8: t('ship.payfast'),
    9: t('ship.telr'),
  };

  const paymentTypesDrop = {
    1: {title: t('title.rp')},
    2: {title: t('fSale.cod')},
    3: {title: t('title.sp')},
    4: {title: t('title.pay')},
    5: {title: t('title.fw')},
    6: {title: t('title.ip')},
    7: {title: t('title.bt')},
    8: {title: t('ship.payfast')},
    9: {title: t('ship.telr')},
  };

  const status = {
    PUBLIC: 1,
    PRIVATE: 2,
  };


  const orderTypes = {
    all: {title: t('index.all')},
    website: {title: t('ship.ofw')},
    pos: {title: t('ship.po')},
  };

  const orderStatusIn = {
    PENDING: 1,
    CONFIRMED: 2,
    PICKED_UP: 3,
    ON_THE_WAY: 4,
    DELIVERED: 5,
    CANCELED: 6
  };

  const orderMethodsIn = {
    CASH_ON_DELIVERY: 2,
    BANK: 7
  };

  const paymentStatus = {
    1: {title: t('fSale.paid')},
    '0': {title: t('fSale.unpaid')}
  };

  const verifiedObj = {
    1: {title: t('user.verified')},
    '0': {title: t('user.uv')}
  };

  const booleanObj = {
    1: {title: t('prod.yes')},
    '0': {title: t('prod.no')},
  };

  const withdrawalStatusIn = {
    APPROVED: 1,
    PENDING: 2,
    CANCELLED: 3
  };

  const withdrawalStatus = {
    1: t('util.appr'),
    2: t('util.pend'),
    3: t('dataPage.can')
  };

  const pageComponent = {
    Contact: {title: 'Contact'},
    Sitemap: {title: 'Sitemap'}
  };


  const productSourceObj = {
    1: {title: t('error.cat')},
    3: {title: t('admin.tags')},
    4: {title: t('error.brands')},
    5: {title: t('error.prod')},
    6: {title: 'URL'},
  };

  const productSources = {
    category: 1,
    sub_category: 2,
    tag: 3,
    brand: 4,
    product: 5,
    url: 6,
  };

  const bannerUsed = {
    1: t('util.bfb'),
    2: t('util.bofs'),
    3: t('util.bofs'),
    4: t('util.bofs'),
    5: t('util.tod'),
    6: t('util.bod'),
    7: t('util.dp'),
    8: t('util.tb'),
    9: t('util.pb')
  };


  const currencyPositions = {
    1: {title: t('util.left')},
    2: {title: t('util.right')}
  };

  const  messageReply= {
    1: { title: t('util.replied')},
    2: { title: t('util.nyr')}
  };

  return {
    currencyPositionsIn, featuredObj, statusObj, priceTFormat, paymentTypes, orderTypes,
    dpConfig, priceTypeObj, wysiwygType, mediaStorageData, orderStatus, status, orderStatusIn,
    orderMethodsIn, paymentStatus, paymentTypesDrop, verifiedObj, booleanObj, withdrawalStatusIn,
    withdrawalStatus, pageComponent, productSourceObj, productSources, bannerUsed, currencyPositions,
    messageReply
  }
}


