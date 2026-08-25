<template>
  <div class="order-status-wrapper">

    <!-- Order ID -->
<!--    <p class="order-id" v-if="isCancelled">ORDER ID: <strong>{{ orderID }}</strong></p>-->

    <!-- Status Steps -->
    <div v-if="!isCancelled" class="status-steps">
      <div
          v-for="(value, index) in filteredOrderStatus"
          :key="index"
          class="status-item"
          :class="[{done: index < statusOfOrder}, {active: parseInt(index) === statusOfOrder}]"
      >
        <div class="step-number">{{ index  }}</div>
        <div class="status-title">{{ value.title }}</div>
      </div>
    </div>

    <!-- Cancelled Card -->
    <div v-if="isCancelled" class="cancelled-card">
      <div class="cancelled-header">
        <svg xmlns="http://www.w3.org/2000/svg" class="cancel-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
        <h2>ORDER CANCELLED</h2>
      </div>
      <p class="cancel-reason"><b>REASON:</b> {{ cancelReason || 'No reason provided' }}</p>
    </div>

  </div>
</template>

<script>
import util from '~/mixin/util'

export default {
  name: 'OrderedStatus',
  mixins: [util],
  props: {
    statusOfOrder: {
      type: Number,
      required: true
    },
    cancelReason: {
      type: String,
      default: ''
    },
    orderID: {
      type: String,
      default: ''
    }
  },
  computed: {
    isCancelled() {
      return this.statusOfOrder === this.orderStatusIn.CANCELLED
    },
    filteredOrderStatus() {
      // If cancelled → only show cancelled step
      if (this.isCancelled) {
        return {
          [this.orderStatusIn.CANCELLED]: this.orderStatus[this.orderStatusIn.CANCELLED]
        }
      }

      // Otherwise, exclude the Cancelled step
      const steps = { ...this.orderStatus }
      delete steps[this.orderStatusIn.CANCELLED]
      return steps
    }
  }
}
</script>

<style scoped>
.order-status-wrapper {
  max-width: 700px;
  margin: 30px auto;
  font-family: 'Arial', sans-serif;
}

/* Order ID */
.order-id {
  text-align: center;
  font-size: 18px;
  margin-bottom: 25px;
  font-weight: bold;
}

/* Status Steps */
.status-steps {
  display: flex;
  justify-content: space-between;
  margin-bottom: 30px;
}

.status-item {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  text-align: center;
  padding: 10px;
}

.status-item::after {
  content: '';
  position: absolute;
  top: 15px;
  right: -50%;
  height: 4px;
  width: 100%;
  background: #ccc;
  z-index: -1;
}

.status-item:last-child::after {
  display: none;
}

.step-number {
  width: 35px;
  height: 35px;
  line-height: 35px;
  border-radius: 50%;
  background-color: #ccc;
  color: white;
  font-weight: bold;
  margin-bottom: 5px;
}

.status-item.done .step-number {
  background-color: #c8a330;
}

.status-item.active .step-number {
  background-color: #947822;
}

.status-title {
  font-size: 14px;
  font-weight: bold;
}

/* Cancelled Card */
.cancelled-card {
  background: #ffe6e6;
  border: 2px solid #ff4d4f;
  padding: 25px;
  border-radius: 10px;
  text-align: center;
  box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
}

.cancelled-header {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 15px;
}

.cancelled-header h2 {
  color: #ff4d4f;
  margin-left: 10px;
  font-size: 24px;
  font-weight: bold;
}

.cancel-icon {
  width: 28px;
  height: 28px;
  stroke: #ff4d4f;
}

.cancel-reason {
  font-size: 18px;
  color: #a8071a;
  font-weight: 600;
}
</style>
