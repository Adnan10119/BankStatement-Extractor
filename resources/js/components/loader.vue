<template>
    <div className='body-loader' id="body-loader">
        <img width="100" src="/assets/images/Spinner.gif" />
    </div>
</template> 
<script>
export default {
    
}
</script>
<style scoped>
.body-loader {
    display: none;
    width: 100vw;
    height: 100vh;
    background: #f9fafe;
    align-items: center;
    justify-content: center;
    position: fixed;
    z-index: 99999999;
    top: 0;
    left: 0;
}
</style>