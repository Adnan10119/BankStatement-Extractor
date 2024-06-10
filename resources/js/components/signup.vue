<template>
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="mainContainer">
                <div style="width : 100%;">
                    <div class="heading1">Create an account</div>
                    <div class="heading2">Organization use</div>
                    <form class="formContainer" v-on:submit.prevent="singnupValidate" autocomplete="off" id="signup-form">
                        <div class="oraganizationName" id="oraganizationName">
                            <label class="labels" for=""
                                >Organization Name</label
                            >
                            <input id="orgInput" @focus="seen = true" :class="{'invalid':validation.invalid.org_name}" v-on:focus="clearValidation('org_name')" @blur="closeDropdown()" v-model="signup.org_name" class="inputs" type="text" />
                            <div v-if="seen" class="orgDropdown">
                                <div @click="SelectOrg(org_list.org_name); seen = false" v-for="org_list in org_list">{{org_list.org_name}}</div>
                            </div>
                        </div>
                        <div class="flex mt-3">
                            <div class="w-100 mr-3">
                                <label class="labels" for="">First Name</label>
                                <input class="inputs" :class="{'invalid':validation.invalid.first_name}" v-on:focus="clearValidation('first_name')" v-model="signup.first_name" type="text" />
                            </div>
                            <div class="w-100 ml-2">
                                <label class="labels" for="">Last Name</label>
                                <input class="inputs" :class="{'invalid':validation.invalid.last_name}" v-on:focus="clearValidation('last_name')" v-model="signup.last_name" type="text" />
                            </div>
                        </div>
                        <div class="flex mt-3">
                            <div class="w-100 mr-3">
                                <label class="labels" for=""
                                    >Phone Number</label
                                >
                                <input class="inputs" :class="{'invalid':validation.invalid.phone_number}" v-on:focus="clearValidation('phone_number')" v-model="signup.phone_number" type="number" />
                            </div>
                            <div class="w-100 ml-2">
                                <label class="labels" for="">Email</label>
                                <input class="inputs" :class="{'invalid':validation.invalid.email}" v-on:focus="clearValidation('email')" v-model="signup.email" type="text" />
                            </div>
                        </div>
                        <div class="flex mt-3">
                            <div class="w-100 mr-3">
                                <label class="labels" for=""
                                    >Address Line 1</label
                                >
                                <input class="inputs" :class="{'invalid':validation.invalid.add_line_1}" v-on:focus="clearValidation('add_line_1')" v-model="signup.add_line_1" type="text" />
                            </div>
                            <div class="w-100 ml-2">
                                <label class="labels" for=""
                                    >Address Line 2</label
                                >
                                <input class="inputs" :class="{'invalid':validation.invalid.add_line_2}" v-on:focus="clearValidation('add_line_2')" v-model="signup.add_line_2" type="text" />
                            </div>
                        </div>
                        <div class="flex mt-3">
                            <div class="w-100 mr-3">
                                <label class="labels" for=""
                                    >City</label
                                >
                                <input class="inputs" :class="{'invalid':validation.invalid.city}" v-on:focus="clearValidation('city')" v-model="signup.city" type="text" />
                            </div>
                            <div class="w-40 ml-2 mr-3">
                                <label class="labels" for="">State</label>
                                <select style="height: 24px;" name="state" :class="{'invalid':validation.invalid.state}" v-on:focus="clearValidation('state')" v-model="signup.state" id="stateDropdown">
                                    <option value="Alabama" selected>Alabama</option>
                                    <option value="Alaska">Alaska</option>
                                    <option value="Arizona">Arizona</option>
                                    <option value="Arkansas">Arkansas</option>
                                    <option value="California">California</option>
                                    <option value="Colorado">Colorado</option>
                                    <option value="Connecticut">Connecticut</option>
                                    <option value="Delaware">Delaware</option>
                                    <option value="District of Columbia">District of Columbia</option>
                                    <option value="Florida">Florida</option>
                                    <option value="Georgia">Georgia</option>
                                    <option value="Hawaii">Hawaii</option>
                                    <option value="Idaho">Idaho</option>
                                    <option value="Illinois">Illinois</option>
                                    <option value="Indiana">Indiana</option>
                                    <option value="Iowa">Iowa</option>
                                    <option value="Kansas">Kansas</option>
                                    <option value="Kentucky">Kentucky</option>
                                    <option value="Louisiana">Louisiana</option>
                                    <option value="Maine">Maine</option>
                                    <option value="Maryland">Maryland</option>
                                    <option value="Massachusetts">Massachusetts</option>
                                    <option value="Michigan">Michigan</option>
                                    <option value="Minnesota">Minnesota</option>
                                    <option value="Mississippi">Mississippi</option>
                                    <option value="Missouri">Missouri</option>
                                    <option value="Montana">Montana</option>
                                    <option value="Nebraska">Nebraska</option>
                                    <option value="Nevada">Nevada</option>
                                    <option value="New Hampshire">New Hampshire</option>
                                    <option value="New Jersey">New Jersey</option>
                                    <option value="New Mexico">New Mexico</option>
                                    <option value="New York">New York</option>
                                    <option value="North Carolina">North Carolina</option>
                                    <option value="North Dakota">North Dakota</option>
                                    <option value="Ohio">Ohio</option>
                                    <option value="Oklahoma">Oklahoma</option>
                                    <option value="Oregon">Oregon</option>
                                    <option value="Pennsylvania">Pennsylvania</option>
                                    <option value="Rhode Island">Rhode Island</option>
                                    <option value="South Carolina">South Carolina</option>
                                    <option value="South Dakota">South Dakota</option>
                                    <option value="Tennessee">Tennessee</option>
                                    <option value="Texas">Texas</option>
                                    <option value="Utah">Utah</option>
                                    <option value="Vermont">Vermont</option>
                                    <option value="Virginia">Virginia</option>
                                    <option value="Washington">Washington</option>
                                    <option value="West Virginia">West Virginia</option>
                                    <option value="Wisconsin">Wisconsin</option>
                                    <option value="Wyoming">Wyoming</option>
                                </select>
                            </div>
                            <div class="w-40 ml-2">
                                <label class="labels" for="">Zip Cide</label>
                                <input class="inputs" :class="{'invalid':validation.invalid.zip_code}" v-on:focus="clearValidation('zip_code')" v-model="signup.zip_code" type="text" />
                            </div>
                        </div>
                        <div class="flex mt-3">
                            <div class="w-100 mr-3">
                                <div class="d-flex">
                                    <label class="labels" for="">Password</label>
                                    <div class="tooltip-custom ml-auto text-white" for="">Requirements
                                        <div class="tooltiptext-custom">
                                           <div style="margin-left:-15px;">Password Must Contain:</div>
                                           <ul class="text-left mb-0" style="padding-left: 1.2rem!important;">
                                                <li>At least 8 characters</li>
                                                <li>At least one uppercase</li>
                                                <li>At least one lowercase</li>
                                                <li>At least one number</li>
                                           </ul>
                                        </div>
                                    </div>
                                </div>
                                <input class="inputs" :class="{'invalid':validation.invalid.password}" v-on:focus="clearValidation('password')" v-model="signup.password" type="text" />
                            </div>
                            <div class="w-100 ml-2">
                                <label class="labels" for=""
                                    >Re-Enter Password</label
                                >
                                <input class="inputs" :class="{'invalid':validation.invalid.re_password}" v-on:focus="clearValidation('re_password')" v-model="signup.re_password" type="text" />
                            </div>
                        </div>
                        <button
                            class="createBtn"
                            type="submit"
                            data-toggle="modal"
                            data-target=".bd-example-modal-sm"
                        >
                            Create Account
                        </button>
                        <button
                            id="hidepopup"
                            style="display:none"
                            data-toggle="modal"
                            data-target=".bd-example-modal-sm"
                        >
                        </button>

                        <div
                            class="modal fade bd-example-modal-sm"
                            tabindex="-1"
                            role="dialog"
                            aria-labelledby="mySmallModalLabel"
                            aria-hidden="true"
                        >
                            <div
                                class="modal-dialog modal-sm modal-dialog-centered"
                            >
                                <div class="modal-content">
                                    <div class="pt-2 pr-2">
                                        <svg
                                            data-dismiss="modal"
                                            xmlns="http://www.w3.org/2000/svg"
                                            style="float: right;"
                                            height="20"
                                            width="20"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                        >
                                            <path
                                                fill-rule="evenodd"
                                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                clip-rule="evenodd"
                                            />
                                        </svg>
                                    </div>
                                    <div class="pt-4 pb-5 createdText">
                                        <span id="popup-title">Account Created</span>
                                        <div id="popup-message" style="text-align: justify;text-align: center;padding: 0px 30px;font-size: 14px;font-weight: 500;">
                                            Please log into your account
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import axios from 'axios';

export default {
    data(){
        return {
            seen : false,
            signup : {
                org_name : '',
                first_name : '',
                last_name : '',
                phone_number : '',
                email : '',
                add_line_1 : '',
                add_line_2 : '',
                city : '',
                state : 'Alabama',
                zip_code : '',
                password : '',
                re_password : '',
                error: true,
                message: [],
            },
            validation: { invalid:{} },
            apiCreate: axios.create({
                baseURL: '',
                timeout: 90000,
            }),
            org_list: [],
        }
    },
    mounted() {
        this.getOrgList();
    },
    methods : {
        SelectOrg(e){
            this.signup.org_name = e;
        },
        closeDropdown(){
            setTimeout(() => {
                this.seen = false
            }, 150);
        },
        singnupValidate(){
            //organization name
            this.signup.message = [];
            if (!this.signup.org_name) {
                this.validation.invalid.org_name = true;
                this.signup.error = true;
                this.signup.message.push('Please enter organization name!');
            }

            //first name
            if (!this.signup.first_name) {
                this.validation.invalid.first_name = true;
                this.signup.error = true;
                this.signup.message.push('Please enter first name!');
            }

            //last name
            if (!this.signup.last_name) {
                this.validation.invalid.last_name = true;
                this.signup.error = true;
                this.signup.message.push('Please enter last name!');
            }

            //Phone number
            if (!this.signup.phone_number) {
                this.validation.invalid.phone_number = true;
                this.signup.error = true;
                this.signup.message.push('Please enter phone number!');
            }

            //Email
            var validator = require("email-validator");
            if (!this.signup.email) {
                this.validation.invalid.email = true;
                this.signup.error = true;
                this.signup.message.push('Please enter email!');
            }
            else if(!validator.validate(this.signup.email)){
                this.validation.invalid.email = true;
                this.signup.error = true;
                this.signup.message.push('Please enter valid email!');
            }

            //address line 1
            if (!this.signup.add_line_1) {
                this.validation.invalid.add_line_1 = true;
                this.signup.error = true;
                this.signup.message.push('Please enter address line 1!');
            }

            //address line 2
            // if (!this.signup.add_line_2) {
            //     this.validation.invalid.add_line_2 = true;
            //     this.signup.error = true;
            //     this.signup.message.push('Please enter address line 2!');
            // }

            //city
            if (!this.signup.city) {
                this.validation.invalid.city = true;
                this.signup.error = true;
                this.signup.message.push('Please enter city name!');
            }

            //state
            if (!this.signup.state) {
                this.validation.invalid.state = true;
                this.signup.error = true;
                this.signup.message.push('Please enter state name!');
            }

            //zipcode
            if (!this.signup.zip_code) {
                this.validation.invalid.zip_code = true;
                this.signup.error = true;
                this.signup.message.push('Please enter zipcode!');
            }

            //password
            let strongPassword = new RegExp('(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})');
            if (!this.signup.password) {
                this.validation.invalid.password = true;
                this.signup.error = true;
                this.signup.message.push('Please enter password!');
            }
            else if(!strongPassword.test(this.signup.password)) {
                this.validation.invalid.password = true;
                this.signup.error = true;
                this.signup.message.push('Password should be at least 8 (1 lower, 1 upper, 1 numeric and 1 special chars) long');
            }

            //re-enter password
            if (!this.signup.re_password) {
                this.validation.invalid.re_password = true;
                this.signup.error = true;
                this.signup.message.push("Please enter re-enter password!");
            }
            else if(this.signup.password != this.signup.re_password){
                this.validation.invalid.password = true;
                this.validation.invalid.re_password = true;
                this.signup.error = true;
                this.signup.message.push("Password don't message");
            }

            if(!this.signup.error){
                this.navigateToLogin();
                document.getElementById("popup-title").innerHTML = 'Account';
                document.getElementById("popup-message").style.textAlign = 'center';
                document.getElementById("popup-message").style.fontSize = '15px';
                document.getElementById("popup-message").style.fontWeight = '700';
                document.getElementById("popup-message").innerHTML = 'Process initiated please wait for response!';
            }
            else{
                document.getElementById("popup-message").style.textAlign = 'justify';
                document.getElementById("popup-message").style.fontSize = '14px';
                document.getElementById("popup-message").style.fontWeight = '500';
                document.getElementById("popup-title").innerHTML = 'Validation Error';
                document.getElementById("popup-message").innerHTML = '';
                this.signup.message.forEach(element => {
                    document.getElementById("popup-message").innerHTML += '<img src="assets/images/danger-info.png" style="margin-right: 5px;margin-bottom: 3px;" width="14">' + element + '<br>';
                });

            }
        },
        clearValidation(attr){
            this.validation.invalid[attr] = false;
            this.signup.error = false;
        },
        navigateToLogin(){
            this.apiCreate.post('/api/signup',this.signup)
            .then(response => {
                console.log(response.data);
                if(response.data.success == true){
                    sessionStorage.setItem('user_detail', JSON.stringify(this.signup));
                    document.getElementById("popup-title").innerHTML = 'Account';
                    document.getElementById("popup-message").style.textAlign = 'center';
                    document.getElementById("popup-message").style.fontSize = '15px';
                    document.getElementById("popup-message").style.fontWeight = '700';
                    document.getElementById("popup-message").innerHTML = 'Saving records moving to subscription';
                    setTimeout(() => {
                        document.getElementById("hidepopup").click();
                        this.$router.push('/subscription');
                    }, 2000);
                }
                else{
                    document.getElementById("popup-message").style.textAlign = 'center';
                    document.getElementById("popup-message").style.fontSize = '14px';
                    document.getElementById("popup-message").style.fontWeight = '500';
                    document.getElementById("popup-title").innerHTML = 'Validation Error';
                    document.getElementById("popup-message").innerHTML = '';
                    document.getElementById("popup-message").innerHTML += '<img src="assets/images/danger-info.png" style="margin-right: 5px;margin-bottom: 3px;" width="14">' + response.data.message + '<br>';
                }
            });

        },

        getOrgList(){
            this.apiCreate.post('/api/get/organization',this.signup)
            .then(response => {
                if(response.data.success == true){
                    this.org_list = response.data.data;
                }
            });
        }
    }
  }
</script>

<style scoped>
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
.inputs > input {
    border: none;
    height: 24px;
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
    height: 24px;
    width: 100%;
    outline: none;
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
    width: 100%;
    padding: 20px 0;
    font-style: normal;
    font-weight: 700;
    font-size: 25.625px;
    line-height: 30px;
    color: #fff;
    border: none;
    outline: none;
    background-color: #0fb397;
    margin-top: 40px;
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
