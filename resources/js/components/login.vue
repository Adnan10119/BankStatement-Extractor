<template>
    <div class="container my-5">
        <loader v-if="loading"/>
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div
                        class="row bg-color-green px-5 pt-3 pb-5 justify-content-center home-title"
                    >
                        eFraud Converter
                    </div>
                    <div class="row">
                        <div
                            class="col-md-12 bg-color"
                            style="min-height: 440px"
                        >
                            <div
                                class="d-flex spaceBT mx-auto"
                                style="width: 65%; margin-top: 65px"
                            >
                                <div style="width: 40%">
                                    <h3 class="login-heading">
                                        Existing Users
                                    </h3>

                                    <div class="existUser mt-4">
                                        <label for="input1"
                                            >User Name or Email</label
                                        >
                                        <input :class="{'invalid':validation.invalid.email}" v-on:focus="clearValidation('email')" v-model="login.email"
                                            style="height: 24px; width: 100%"
                                            type="text"
                                        />
                                    </div>
                                    <div class="existUser mt-3">
                                        <label for="input1">Password</label>
                                        <input :class="{'invalid':validation.invalid.password}" v-on:focus="clearValidation('password')" v-model="login.password"
                                            type="password"
                                            style="height: 24px; width: 100%"
                                        />
                                    </div>
                                    <button
                                        class="w-100 bg-color-green loginBtn mt-4 py-1" style="cursor:pointer" @click="singnInValidate"
                                    >
                                        Log In
                                    </button>
                                    <div class="forgot-text my-4">
                                        Forgot User Name / Password
                                    </div>
                                </div>
                                <div style="width: 40%;z-index: 99999;">
                                    <h3 class="login-heading">New Users</h3>
                                    <router-link to="/signup">
                                    <button
                                        class="w-100 bg-color-green loginBtn mt-2 py-1" style="cursor:pointer;z-index: 999;"
                                    >
                                        Create Account
                                    </button>
                                    </router-link>
                                </div>
                                <div class="temp" style="background-image:url(https://efraud.aidevlab.com/public/assets/images/logo-watermark.png)">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import loader from './loader2.vue'
export default {
    components: {loader},
    data () {
      return{
          
      };
    },
    mounted(){

        var token = localStorage.getItem('api_token');
        if ( token != null && token != ''){
            this.$router.push('/home');
        }
    },
    data(){
        return {
            loading: false,
            seen : false,
            login : {
                email : '',
                password : '',
                error: true,
                message: [],
            },
            validation: { invalid:{} }, 
            apiCreate: axios.create({
                baseURL: '',
                timeout: 90000,
            }),
        };
    },
    methods:{
        singnInValidate(){
        //organization name
            this.login.message = [];
            
            //Email
            var validator = require("email-validator");
            if (!this.login.email) {
                this.validation.invalid.email = true;
                this.login.error = true;
                this.login.message.push('Please enter email!');
            }
            else if(!validator.validate(this.login.email)){
                this.validation.invalid.email = true;
                this.login.error = true;
                this.login.message.push('Please enter valid email!');
            }
            
            //password
            if (!this.login.password) {
                this.validation.invalid.password = true;
                this.login.error = true;
                this.login.message.push('Please enter password!');
            }
            
            if(!this.login.error){
                this.navigateToLogin();
            }
            else{
                alert(this.login.message[0]);
                console.log(this.login.message);
            }
        },
        clearValidation(attr){
            this.validation.invalid[attr] = false;
            this.login.error = false;
        },
        navigateToLogin(){
            this.loading = true;
             this.apiCreate.post('/api/login',this.login)
        .then(response => {
            // console.log(response.data);
            if(response.data.success == true){
                localStorage.setItem('api_token', response.data.data.access_token);
                localStorage.setItem('is_subscribed', response.data.data.is_subscribed);
                if(response.data.data.is_subscribed){
                    this.loading = false;
                    this.$router.push('/home');
                }else{
                    const postData = {
                        'email':this.login.email,
                    };
                    sessionStorage.setItem('user_detail', JSON.stringify(response.data.data));
                    this.apiCreate.post('/api/is-subscribed',postData)
                    .then(response2 => {
                        if(response2.data.success == true){
                            this.loading = false;
                            if(response2.data.message == "Found"){
                                console.log("Found");
                                localStorage.setItem('is_subscribed', 1);
                                this.$router.push('/home');
                            }else{
                                this.$router.push('/subscription');
                            }
                        }
                        else{
                            this.loading = false;
                            alert(response2.data.message);
                        }
                    });
                }
            }
            else{
                this.loading = false;
                alert(response.data.message);
            }
        });
        },
    },

};
</script>

<style scoped>
.temp{
    position: absolute;
    background: transparent;
    z-index: 9999;
    width: 420px;
    right: -100px;
    top: -25px;
    height: 440px;
}
.loginBtn {
    font-style: normal;
    font-weight: 700;
    font-size: 27px;
    /* line-height: 31px; */
    /* display: flex; */
    align-items: center;
    text-align: center;
    color: #ffffff;
}
.existUser > label {
    font-weight: 700;
    font-size: 12px;
    line-height: 14px;
    color: #ffffff;
    display: block;
}
.home-title {
    display: flex;
    align-items: center;
    text-align: center;

    font-style: normal;
    font-weight: 700;
    font-size: 40px;
    line-height: 49px;
    color: #ffffff;
    border-bottom: 3px solid white;
}

.login-heading {
    font-style: normal;
    font-weight: 700;
    font-size: 27px;
    line-height: 31px;
    color: #ffffff;
}
.spaceBT{
    justify-content: space-between;
}
.forgot-text {
    font-style: normal;
    font-weight: 700;
    font-size: 12px;
    line-height: 16px;
    text-decoration-line: underline;
    color: #ffffff;
}
.invalid{
    border: 2px solid red !important;
}

</style>
