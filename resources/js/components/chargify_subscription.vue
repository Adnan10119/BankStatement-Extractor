<template>
  <div class="container my-5">
    <loader v-if="loading"/>
    <div class="row justify-content-center">
            <div class="mainContainer">
                <div style="width : 100%;">
                    <div class="heading1">Chargify Subscription</div>
                    <div class="modal-body row mx-auto" style="width : 80%;">
                      <div class="col-md-6">
                        <div>
                            <label class="labels" for="">Name</label>
                            <p class="inputs" type="text" >{{userDetail.name}}</p>
                        </div>
                        <div>
                            <label class="labels" for="">Email</label>
                            <p class="inputs" type="text" >{{userDetail.email}}</p>
                        </div>
                        <div>
                            <label class="labels" for="">Address</label>
                            <p class="inputs" type="text" >{{userDetail.add_line_1}}</p>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div style="width:100%;">
                          <!-- <select v-on:change="changeItem">
                            <option value="card">Credit Card</option>
                            <option value="bank">Bank Account</option>
                          </select> -->
                          <label class="labels" for="">Select Plan</label>
                          <select class="dropDownStyle dropdown" name="plan_id" v-model="plan_id">
                              <option value=1 selected>Pay As You Go</option>
                              <option value=2 >Month to Month</option>
                              <option value=3 >Prepay Annual</option>
                          </select>
                          <br />
                          <br />
                          <label class="labels" for="">Credit Card</label>
                          <form>
                            <div id="chargify-form"></div>
                            <!-- <label>
                              Hidden Token: <input id="host-token" disabled v-model="token"/>
                            </label> -->
                            <p>
                              <button class="createBtn" v-on:click.prevent="handleClick">Subscribe</button>
                            </p>
                          </form>
                        </div>
                      </div>
                    </div>
                    <!-- <div class="heading2">Organization use</div> -->
                    <!-- <form class="formContainer" v-on:submit.prevent="singnupValidate" autocomplete="off" id="signup-form">
                      <div class="flex mt-3">
                          <div class="w-100 mr-3">
                              <label class="labels" for="">First Name</label>
                              <p class="inputs" type="text" >{{userDetail.first_name}}</p>
                          </div>
                          <div class="w-100 ml-2">
                              <label class="labels" for="">Last Name</label>
                              <p class="inputs" type="text" >{{userDetail.last_name}}</p>
                          </div>
                      </div>
                      <div class="flex mt-3">
                          <div class="w-100 mr-3">
                              <label class="labels" for="">Phone Number</label>
                              <p class="inputs" type="text" >{{userDetail.phone_number}}</p>
                          </div>
                          <div class="w-100 ml-2">
                              <label class="labels" for="">Email</label>
                              <p class="inputs" type="text" >{{userDetail.email}}</p>
                          </div>
                      </div>
                      <div class="flex mt-3">
                          <div class="w-100 mr-3">
                              <label class="labels" for="">Address</label>
                              <p class="inputs" type="text" >{{userDetail.add_line_1}}</p>
                          </div>
                          <div class="w-100 ml-2">
                              <label class="labels" for="">City</label>
                              <p class="inputs" type="text" >{{userDetail.city}}</p>
                          </div>
                      </div>
                      <div class="flex mt-3">
                          <div class="w-100 mr-3">
                              <label class="labels" for="">State</label>
                              <p class="inputs" type="text" >{{userDetail.state}}</p>
                          </div>
                          <div class="w-100 ml-2">
                              <label class="labels" for="">Zip Code</label>
                              <p class="inputs" type="text" >{{userDetail.zip_code}}</p>
                          </div>
                      </div>
                  </form> -->
                  <!-- <div style="text-align:center;width:100%;">
                  <select class="dropDownStyle" name="plan_id" v-model="plan_id">
                      <option value=1 selected>Pay As You Go</option>
                      <option value=2 >Month to Month</option>
                      <option value=3 >Prepay Annual</option>
                  </select>
                  <br />
                  <br />
                  <form>
                    <div id="chargify-form"></div>
                    <p>
                      <button class="createBtn" v-on:click.prevent="handleClick">Subscribe</button>
                    </p>
                  </form>
                </div> -->
                </div>
              </div>
    </div>
  </div>
  <!-- <div style="text-align:center;background-color: #1d3161;width:100%;">
    <select v-on:change="changeItem">
      <option value="card">Credit Card</option>
      <option value="bank">Bank Account</option>
    </select>
    <br />
    <br />
    <form>
      <div id="chargify-form"></div>
      <label>
        Hidden Token: <input id="host-token" disabled v-model="token"/>
      </label>
      <p>
        <button v-on:click.prevent="handleClick">Submit Host Form</button>
      </p>
    </form>
  </div> -->
</template>

<script>
import loader from './loader2.vue'
export default {
  components: {loader},
  name: 'ChargifyForm',
  data: function() {
    return {
      chargify: new window.Chargify(),
      loading: false,
      paymentType: 'card',
      token: '',
      plan_id: 1,
      userDetail:{},
      apiCreate: axios.create({
                baseURL: '',
                timeout: 90000,
            }),
    }
  },
  mounted: function () {
    this.loadChargifyJs();
    this.userDetail = JSON.parse(sessionStorage.getItem('user_detail'));
  },
  methods: {
    changeItem: function changeItem(event) {
      this.paymentType = event.target.value;
      this.loadChargifyJs();
    },
    handleClick: function () {
      this.loading = true;
      this.chargify.token(
        document.querySelector('#chargify-form'),
        (token) => {
          // console.log('{host} token SUCCESS - token: ', token);
          this.token = token;
          const postData = {
            'plan_id':this.plan_id,
            'token':this.token,
            'email':this.userDetail.email,
          };
          this.apiCreate.post('/api/addSubscriber',postData)
            .then(response => {
                this.loading = false;
                // console.log(response.data);
                if(response.data.success == true){
                    setTimeout(() => {
                        this.$router.push('/');
                    }, 2000);
                }
                else{
                    if(response.data.message == 'Email: This email address is already in use'){
                      localStorage.setItem('is_subscribed', 1);
                      alert('Subscription already exists with this email please login to continue.');
                      this.$router.push('/');
                    }else{
                      alert(response.data.message);
                    }
                }
            });
        },
        (error) => {
          this.loading = false;
          console.log('{host} token ERROR - err: ', error);
        }
      );
    },
    loadChargifyJs: function() {
      this.chargify.load({
        // selector where the iframe will be included in the host's HTML (i.e. '#chargify-form')
        // optional if you have a `selector` on each and every field
        selector: '#chargify-form',
        // (i.e. '1a2cdsdn3lkn54lnlkn')
        publicKey: 'chjs_dbbgkg5zs5f2fc2d4396kqrf',
        // form type (possible values: 'card' or 'bank')
        type: this.paymentType || 'card',
        // points to your Chargify site
        serverHost: 'https://efraud-services.chargify.com'
      });
    },
  }
}
</script>
<style scoped>
.dropdown{
  width:326px;
  padding: 4px 10px;
  background: white;
  border-radius: 5px;
}
.tooltip-custom {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black;
    font-size: 12px;
    font-weight: 700;
    line-height: 14px;
    margin-bottom: 6px;
}

.tooltip-custom .tooltiptext-custom {
  visibility: hidden;
  width: 175px;
  background-color: #38b397;
  color: #fff;
  font-style: normal;
  font-weight: 700;
  font-size: 11px;
  line-height: 13px;
  text-align: center;
  border: 3px solid white;
  /* border-radius: 6px; */
  padding: 15px 15px;
  position: absolute;
  z-index: 1;
  bottom: 130%;
  left: 17%;
  margin-left: -60px;
}

.tooltip-custom .tooltiptext-custom::after {
  content: "";
  position: absolute;
  top: 100%;
  left: 50%;
  margin-left: -5px;
  border-width: 8px;
  border-style: solid;
  border-color: #38b397 transparent transparent transparent;
}

.tooltip-custom:hover .tooltiptext-custom {
  visibility: visible;
}

.mainContainer {
    width: 100%;
    padding: 30px 0;
    display: flex;
    justify-content: center;
    background-color: #1d3161;
}
.formContainer {
    width: 53%;
    margin: 0 auto;
    margin-top: 15px;
}
.heading1 {
    font-style: normal;
    font-weight: 700;
    font-size: 30px;
    line-height: 49px;
    color: #ffffff;
    text-align: center;
}
.heading2 {
    font-style: normal;
    font-weight: 600;
    font-size: 20px;
    color: #ffffff;
    text-align: center;
}
.labels {
    display: block;
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    line-height: 14px;
    margin-bottom: 6px;
}
.inputs {
    border: none;
    height: 34px;
    border-radius: 5px;
    width: 326px;
    outline: none;
    background-color: #ffffff;
    padding-left: 5px;
    padding-top: 5px;
    overflow: hidden;
}
.flex {
    display: flex;
}
/* .justify-center {
    display: flex;
    justify-content: center;
    align-items: center;
} */
.createBtn {
    width: 50%;
    padding: 10px 0;
    font-style: normal;
    font-weight: 700;
    font-size: 25.625px;
    line-height: 30px;
    color: #fff;
    border: none;
    outline: none;
    background-color: #0fb397;
    /* margin-top: 40px; */
}
.createdText {
    font-style: normal;
    font-weight: 700;
    font-size: 28px;
    line-height: 42px;
    text-align: center;
    color: #1d3161;
}
.createdText > div {
    font-style: normal;
    font-weight: 700;
    font-size: 15px;
    line-height: 24px;
    text-align: center;
    color: #1d3161;
}
.oraganizationName{
    position: relative;
}
.orgDropdown{
    position: absolute;
    top: 46px;
    left: 0;
    width: 100%;
    background-color: #fff;
    z-index: 99;
}
.orgDropdown > div {
    padding: 3px 10px;
    background-color: #fff;
}
.orgDropdown > div:hover {
    background-color: rgb(187, 187, 187);
}
.invalid{
    border: 2px solid red !important;
}
</style>