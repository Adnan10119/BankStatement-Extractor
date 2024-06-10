<template>
  <navbar></navbar>
  <router-view />
</template> 
<script>
import navbar from './navbar.vue'
export default {
    components : {
        navbar
    }
}
</script>