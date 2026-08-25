<template>
  <div class="custom-dropdown-column" ref="dropdownRef">
    <div class="dropdown-selected" @click="toggleDropdown">
      {{ selectedLabel }}
      <span class="arrow" :class="{open: open}">▾</span>
    </div>

    <div v-if="open" class="dropdown-list-wrapper">
      <ul class="dropdown-list">
        <li
            v-for="(item, key) in filteredOptions"
            :key="key"
            :class="{active: isSelected(key)}"
            @click="selectOption(key, item)"
        >
          <input
              v-if="multiSelect"
              type="checkbox"
              :checked="isSelected(key)"
              @click.stop
              class="checkbox-input"
          />
          {{ item[keyName] }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
export default {
  name: 'CustomDropdownColumn',
  props: {
    options: {
      type: Object,
      required: true
    },
    selectedKey: {
      type: [String, Array],
      default: null
    },
    keyName: {
      type: String,
      default: 'name'
    },
    multiSelect: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      open: false,
      internalKey: this.initializeInternalKey()
    }
  },
  mounted() {
    // Add click outside listener
    document.addEventListener('click', this.handleClickOutside)
  },
  beforeUnmount() {
    // Remove click outside listener
    document.removeEventListener('click', this.handleClickOutside)
  },
  computed: {
    selectedLabel() {
      if (this.multiSelect) {
        if (!this.internalKey || this.internalKey.length === 0) {
          return 'Select states'
        }
        const selectedCount = this.internalKey.length
        if (selectedCount === 1) {
          return this.options[this.internalKey[0]]?.[this.keyName] || 'Select states'
        }
        return `${selectedCount} states selected`
      }
      return this.options[this.internalKey]?.[this.keyName] || 'Select'
    },
    filteredOptions() {
      // In multi-select mode, exclude the 'default' option
      if (this.multiSelect) {
        return Object.fromEntries(
            Object.entries(this.options).filter(([key]) => key !== 'default')
        )
      }
      return this.options
    }
  },
  watch: {
    selectedKey(newVal) {
      this.internalKey = this.normalizeSelectedKey(newVal)
    },
    options() {
      if (this.multiSelect) {
        // Filter out any keys that no longer exist in options
        this.internalKey = this.internalKey.filter(k => this.options[k])
      } else {
        if (!this.options[this.internalKey]) {
          this.internalKey = Object.keys(this.options)[0] || ''
        }
      }
    }
  },
  methods: {
    initializeInternalKey() {
      return this.normalizeSelectedKey(this.selectedKey)
    },
    normalizeSelectedKey(key) {
      if (this.multiSelect) {
        if (Array.isArray(key)) {
          return key.filter(k => k !== '' && this.options[k])
        }
        if (key && key !== '') {
          return [key]
        }
        return []
      }
      return key || Object.keys(this.options)[0] || ''
    },
    toggleDropdown() {
      this.open = !this.open
    },
    handleClickOutside(event) {
      // Check if click is outside the dropdown component
      if (this.$refs.dropdownRef && !this.$refs.dropdownRef.contains(event.target)) {
        this.open = false
      }
    },
    isSelected(key) {
      if (this.multiSelect) {
        return this.internalKey.includes(key)
      }
      return this.internalKey === key
    },
    selectOption(key, item) {
      // Skip the 'default' placeholder option entirely
      if (key === 'default') {
        return;
      }

      if (this.multiSelect) {
        const index = this.internalKey.indexOf(key)
        if (index > -1) {
          // Remove from selection
          this.internalKey = this.internalKey.filter(k => k !== key)
        } else {
          // Add to selection
          this.internalKey = [...this.internalKey, key]
        }
        // Don't close dropdown in multi-select mode automatically
        this.$emit('clicked', {
          key: this.internalKey,
          value: this.internalKey.map(k => this.options[k])
        })
      } else {
        this.internalKey = key
        this.open = false
        this.$emit('clicked', { key, value: item })
      }
    }
  }
}
</script>

<style scoped>
.custom-dropdown-column {
  position: relative;
  width: 100%;
  max-width: 250px;
  font-family: Arial, sans-serif;
}

.dropdown-selected {
  border: 1px solid #d1d5db;
  padding: 10px 14px;
  border-radius: 8px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 14px;
  background-color: #ffffff;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.dropdown-selected:hover {
  border-color: #2563eb;
  box-shadow: 0 0 5px rgba(37, 99, 235, 0.2);
}

.arrow {
  transition: transform 0.3s ease;
  font-size: 12px;
  color: #6b7280;
}

.arrow.open {
  transform: rotate(180deg);
}

.dropdown-list-wrapper {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  width: 100%;
  max-height: 200px;
  overflow-y: auto;
  border: 1px solid #d1d5db;
  border-radius: 8px;
  background-color: #ffffff;
  z-index: 1000;
  box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.dropdown-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.dropdown-list li {
  padding: 10px 14px;
  cursor: pointer;
  font-size: 14px;
  transition: background 0.2s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.dropdown-list li:hover {
  background-color: #f3f4f6;
}

.dropdown-list li.active {
  background-color: #2563eb;
  color: #ffffff;
}

.checkbox-input {
  cursor: pointer;
  width: 16px;
  height: 16px;
}

@media (max-width: 768px) {
  .custom-dropdown-column {
    max-width: 100%;
  }
}
</style>