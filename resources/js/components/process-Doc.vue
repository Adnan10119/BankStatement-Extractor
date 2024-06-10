<template>
    <div v-if="!isShow" class="tabBody1">
        <div class="conversion">Output File</div>
        <div class="w-50 mx-auto">
            <div class="flex justify-center align-center mt-4">
                <div><img src="assets/images/Excel.png" alt=""></div>
                <div class="w-100 ml-5">
                    <div class="w-100">
                        <label class="labels" for=""
                        >File Name</label
                        >
                        <input class="inputs" v-model="data.fileName" type="text"/>
                    </div>
                    <div class="w-100 mt-3">
                        <label class="labels" for="">Description</label>
                        <textarea class="w-100" v-model="data.description" rows="5"></textarea>
                    </div>
                    <div class="w-100" style="position:relative;">
                        <label class="labels" for="">Case Number</label>
                        <input @click="OpenCase" class="inputs" v-model="data.case_number" type="text"/>
                        <div v-if="seen" class="orgDropdown">
                            <div @click="selectCaseNumber(case_number_list.case_number); seen = false" v-for="case_number_list in case_number_list">{{case_number_list.case_number}}</div>
                        </div>
                    </div>
                    <div class="w-100" style="position:relative;">
                        <div class="dropdown show mt-3">
                            <a class="btn btn-secondary dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: #00000000 !important;">
                                Select Country
                            </a>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                <a class="dropdown-item" href="#" v-on:click="onSelectCountry('USA')">Borderless</a>
                                <a class="dropdown-item" href="#" v-on:click="onSelectCountry('India')">Border</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex mt-3 spaceBT mx-auto" style="max-width : 60%;">
            <button class="deleteBtn" @click="terminateProcess">Close</button>
            <!-- <button class="saveBtn" style="font-size: 20px">Sharing <br> Options</button> -->
            <!-- <button class="saveBtn">Flag</button> -->
            <!-- <a class="saveBtn" :href="fileUrl">Open</a> -->
            <!-- <button class="saveBtn">Save As</button> -->
            <button id="saveBtnJob" class="saveBtn" @click="saveFile">Save</button>
        </div>
    </div>

    <!-- Upload image -->
    <div v-if="isShow" class="tabBody1">
        <Loader></Loader>
        <input @change="onUploadFile" data-toggle="modal" data-target="#percentage_modal" accept="application/pdf" hidden type="file" name="" id="fileUpload">
        <div
            style="width : 70%"
            class="d-flex justify-content-between align-items-center mx-auto"
        >
            <div class="dropDocument">
                <div>

                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="#0fb397"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"
                        />
                    </svg>
                    <div class="dropDocText">
                        Drop Document <br> Here
                    </div>
                </div>
            </div>
            <div class="mx-5 OR">OR</div>
            <div>
                <button @click="onFileSelect()" class="createBtn px-4 mt-0">
                    Select File
                </button>
            </div>
        </div>
    </div>
<!--  -->
    <div class="body-percentage" v-if="uploadPercentage > 1">
        <div class='percentage-loader'>
            <div class="percentage-modal-header mt-4">Please wait</div>
            <div class="percentage-modal-body">Analyzing your document with AI, ML, and OCR</div>
            <div>
                <progress class="progressbar-style" max="100" :value.prop="uploadPercentage"></progress>
            </div>
            <div class="percentage-modal-body">We’ve got you covered. No manual input is required.</div>
        </div>
    </div>

</template>
<script>
import axios from 'axios';
import Loader from './loader';
export default {
    name: 'process-document',
    components: {Loader},
    mounted(){
        var recordId = localStorage.getItem('recordId');
        if(recordId != '' && recordId != null){
            this.data.fileName = localStorage.getItem('fileName');
            this.data.recordId = recordId;
            this.isShow = false;
        }
        else{
            this.isShow = true;
        }
        this.getCaseNumber();
    },
    data() {
        return {
            isShow: true,
            data : {
                fileName : '',
                recordId : '',
                description: '',
                case_number: '',
                country: 'USA',
                error: true,
                message: [],
            },
            case_number_list: [],
            apiCreate: axios.create({
                baseURL: '',
                timeout: 90000,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'multipart/form-data',
                }
            }),
            apiCreate123: axios.create({
                baseURL: '',
                timeout: 10000,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            }),
            uploadPercentage: 1,
            seen: false,
        };
    },
    methods: {
        onFileSelect() {
            document.getElementById("fileUpload").click();
        },

        onSelectCountry(country){
            this.data.country = country;
        },

        async onUploadFile(e) {
            this.uploadPercentage = 10;
            let file = e.target.files[0];
            const formData = new FormData();
            formData.append("file", file);
            // document.getElementById("body-loader").style.display = 'flex';
            document.getElementById("fileUpload").value = '';
            var response = await this.apiCreate.post('/api/convert_pdf_to_csv', formData,
            {
                onUploadProgress: function( progressEvent ) {
                    let percentage = parseInt( Math.round( ( progressEvent.loaded / progressEvent.total ) * 100 ) );
                    console.log(percentage);
                    if(percentage < 85){
                        this.uploadPercentage = percentage;
                    }
                }.bind(this)
            }
            ).then(response => {
                this.uploadPercentage = 100;
                setTimeout(res=>{
                    document.getElementById("body-loader").style.display = 'none';
                    if(response.data.success == true){
                        this.isShow = false;
                        this.data.fileName = response.data.data;
                        this.data.recordId = response.data.recordId;
                        localStorage.setItem('recordId',this.data.recordId);
                        localStorage.setItem('fileName',this.data.fileName);
                        this.uploadPercentage = 0;
                    }
                    else{
                        this.uploadPercentage = 0;
                        alert(response.data.message);
                    }
                },500)
            })
            .catch(error=>{
                if (error.hasOwnProperty('response'))
                {
                    if (error.response.status == 401) {
                        this.uploadPercentage = 0;
                        localStorage.setItem('api_token', '');
                        this.$router.push('/');
                    }
                }
                else{
                    this.uploadPercentage = 0;
                    document.getElementById("body-loader").style.display = 'none';
                    alert("Internet connectivity issue please try again!");
                }
            });
        },

        saveFile(){
            $("#saveBtnJob").attr("disabled", true);
            this.apiCreate123.post('/api/update/file', this.data
            ).then(response => {
                if(response.data.success == true){
                    localStorage.setItem('recordId','');
                    localStorage.setItem('fileName','');
                    this.$router.push('history');
                }
                else{
                    alert(response.data.message);
                }
            });
        },

        terminateProcess(){
            this.apiCreate123.post('/api/delete/file', {id:this.data.recordId}
            ).then(response => {
                if(response.data.success == true){
                    localStorage.setItem('recordId','');
                    localStorage.setItem('fileName','');
                    this.isShow = true;
                }
                else{
                    alert(response.data.message);
                }
            });
        },

        selectCaseNumber(case_number){
            this.data.case_number = case_number;
        },

        getCaseNumber(){
            this.apiCreate.get('/api/getUserCaseNumbers')
            .then(response => {
                if(response.data.success == true){
                    this.case_number_list = response.data.data;
                }
            })
        },

        OpenCase(){
            if(this.seen == true){
                this.seen = false;
            }
            else{
                this.seen = true;
            }
        }
    },
}
</script>

<style scoped>

progress::-moz-progress-bar { background: #0FB397; }
progress::-webkit-progress-value { background: #0FB397; }
progress { color: #0FB397; }

.progressbar-style{
    margin-bottom: -3px;
    width: 90%;
    border: 2px solid #1D3161;
    border-radius: 5px;
    height: 20px;
}
.percentage-modal-header{
    font-style: normal;
    font-weight: 700;
    font-size: 36px;
    line-height: 42px;
    text-align: center;
    color: #1D3161;
}
.percentage-modal-body{
    font-style: normal;
    font-weight: 700;
    font-size: 14px;
    line-height: 28px;
    text-align: center;
    color: #1D3161;
}
.body-percentage {
    display: flex;
    width: 100vw;
    height: 100vh;
    align-items: center;
    justify-content: center;
    position: fixed;
    z-index: 99;
    top: 0;
    left: 0;
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(10px);
}
.tabBody1 {
    width: 100%;
    margin: 0 auto;
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
.percentage-loader {
    background: white;
    width: 440px;
    height: 170px;
    border-radius: 5px;
    text-align: center;
        /* display: flex;
    align-items: center;
    justify-content: center; */
}
.conversion {
    font-style: normal;
    font-weight: 700;
    font-size: 40px;
    line-height: 47px;
    color: #ffffff;
    text-align: center;
}

.deleteBtn {
    cursor: pointer;
    height: 50px;
    border: 3px solid #fff;
    background: transparent;
    color: #fff;
    font-weight: 700;
    font-size: 25.625px;
    line-height: 30px;
    padding: 0 18px;
}

.saveBtn {
    cursor: pointer;
    height: 50px;
    background: #0fb397;
    color: #fff;
    font-weight: 700;
    font-size: 25.625px;
    line-height: 20px;
    padding: 0 16px;
    border: none;
    outline: none;
    text-decoration: none;
    display: flex;
    align-items: center;
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

.align-center {
    align-items: center;
}

.spaceBT {
    justify-content: space-between;
}

.justify-center {
    display: flex;
    justify-content: center;
    align-items: center;
}

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
    cursor: pointer;
}

.dropDocument {
    width: 250px;
    height: 250px;
    border-radius: 50%;
    border: 5px solid #0fb397;
    border-style: dashed;
    display: flex;
    justify-content: center;
    align-items: center;
}

.dropDocument > svg {
    height: 100px;
    width: 100px;
}

.dropDocText {
    font-weight: 700;
    font-size: 20px;
    line-height: 23px;
    text-align: center;
    color: #ffffff;
}

.OR {
    font-style: normal;
    font-weight: 700;
    font-size: 29px;
    line-height: 34px;
    text-align: center;
    color: #ffffff;
}
</style>
