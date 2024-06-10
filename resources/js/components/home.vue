<template>
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="mainDiv">
        <div class="flex">
          <div class="FIT">eFraud Converter</div>
          <div class="tabs">
            <router-link class-active="active" style="text-align: center;" to="/home">
              <li >Process Documents</li>
            </router-link>
            <router-link class-active="active" :class="{'active':this.$route.path == '/overview'}" style="text-align: center;" to="/history">
              <li>History</li>
            </router-link>
            <router-link class-active="active" style="text-align: center;" to="/">
              <li>Settings</li>
            </router-link>
            <router-link class-active="active" style="text-align: center;" to="/">
              <li>Profile</li>
            </router-link>
            <li data-toggle="modal"  style="text-align: center;" data-target=".bd-example-modal-sm">
              Log Out
            </li>
          </div>
        </div>
        <!-- Logout Popup -->
        <div
          class="modal fade bd-example-modal-sm"
          tabindex="-1"
          role="dialog"
          aria-labelledby="mySmallModalLabel"
          aria-hidden="true"
        >
          <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
              <div class="py-5 confirmText">
                Are you sure you <br />
                want to log out?
                <div class="logoutBtn">
                  <button class="cancelBtn" data-dismiss="modal">Cancel</button>
                  <button class="logout" data-dismiss="modal" v-on:click="logout">Log Out</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="tab-body">
          <router-view></router-view>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import Navbar from "./navbar";
export default {
  name: "home",
  components: {Navbar},
  data () {
      return{
          isShow:true,
          token: localStorage.getItem('api_token'),
          is_subscribed: parseInt(localStorage.getItem('is_subscribed')),
      };
  },
  mounted(){
    if ( this.token == null || this.token == ''){
        this.$router.push('/');
        return;
    }
    if(!this.is_subscribed){
      this.$router.push('/subscription');
    }
  },
  methods: {
    onFileUplaod(){
        this.isShow = false;
      document.getElementById("fileUpload").click();
    },
    logout(){
      localStorage.setItem('api_token','');
      localStorage.setItem('is_subscribed','');
      this.$router.push('/');
    },
  },
};
</script>

<style scoped>
  .FIT {
    min-width: 350px;
    font-style: normal;
    font-weight: 700;
    font-size: 20px;
    line-height: 23px;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .tabs {
    height: 65px;
    display: flex;
    justify-items: center;
    justify-content: space-between;
    width: 100%;
    background-color: #fff;
  }
  .tabs > a {
    color: #fff;
    list-style-type: none;
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #1d3161;
    margin-left: 3px;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
  }
  .tab-body {
    background-color: #1d3161;
    height: 500px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding-top: 25px;
    padding-bottom: 5px;
    border-top: 3px solid #fff;
  }

  .mainDiv {
    width: 100%;
    background-color: #1d3161;
  }
  
  .flex {
    display: flex;
  }
    
  .confirmText {
    font-style: normal;
    font-weight: 700;
    font-size: 30px;
    line-height: 42px;
    text-align: center;
    color: #1d3161;
  }
  .logoutBtn {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin-top: 75px;
  }
  .logout {
    font-weight: 700;
    font-size: 22.625px;
    padding: 0 25px;
    display: flex;
    align-items: center;
    text-align: center;
    color: #fff;
    background-color: #0fb397;
    border: none;
    outline: none;
    letter-spacing: -1px;
  }
  .cancelBtn {
    font-weight: 700;
    font-size: 22.625px;
    padding: 0 25px;
    display: flex;
    align-items: center;
    text-align: center;
    color: #1d3161;
    border: 3px solid #1d3161;
    outline: none;
    max-height: 42px;
    letter-spacing: -1px;
  }
  .modal-sm {
    max-width: 350px !important;
  }
</style>
