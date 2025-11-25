<template>
    <div 
      v-if="isLoading" 
      class="fixed top-0 left-0 w-full h-1 bg-blue-500 z-50 animate-pulse"
    ></div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        isLoading: false
      }
    },
    mounted() {
      document.addEventListener('loading:start', this.startLoading)
      document.addEventListener('loading:end', this.endLoading)
    },
    beforeUnmount() {
      document.removeEventListener('loading:start', this.startLoading)
      document.removeEventListener('loading:end', this.endLoading)
    },
    methods: {
      startLoading() {
        this.isLoading = true
      },
      endLoading() {
        this.isLoading = false
      }
    }
  }
  </script>