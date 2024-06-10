<template>
    <div class="flex w-100 space" style="margin : 0 10%">
        <div class="left-card">
            <div class="title">
            Document Overview
            </div>
            <div class="detailDiv">
                <div >
                    <div class="details">Processed by</div>
                    <div>{{ data?data.user_name:'' }}</div>
                </div>
                <div >
                    <div class="details">Organization</div>
                    <div>{{ data?data.org_name:'' }}</div>
                </div>
                <div >
                    <div class="details">Date/Time</div>
                    <div>{{ data?data.date:'' }}</div>
                </div>
                <div >
                    <div class="details">Case Number</div>
                    <div>{{ data?data.case_number:'' }}</div>
                </div>
                <div style="border-bottom: none">
                    <div class="details">Notes</div>
                    <div>{{ data?data.notes:'' }}</div>
                </div>
            </div>
            <div class="editButtons">
                <button data-toggle="modal" data-target=".bd-share-modal-lg">Edit <br> Notes</button>
                <button data-toggle="modal" data-target=".bd-share-modal-sm" style="cursor : pointer;">Sharing Options</button>
                <!-- <button>Tag as Compeleted</button> -->
            </div>
        </div>
        <div class="right-card">

            <div class="mt-5">
                <div class="inputTitle">
          Input: {{ data?data.input_name:'' }}
                </div>
                <div class="flex  space align-center">
                    <div>
                        <img src="assets/images/pdf.png" width="70" height="70" alt="">
                    </div>
                    <div>
                        <button class="inputDiv px-4 py-1">
                            <a :href="data?data.input_url:''" style="text-decoration: none;" target="_blank">
                            Open
                            </a>
                        </button>
                     </div>
                    <!-- <div>
                    <button class="inputDiv px-2">Open File <br> Location</button>
                    </div> -->
                    <div>
                        <button class="inputDiv px-2 py-1">
                            <a :href="data?data.input_url:''" target="_blank">
                                <svg xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 20 20" fill="#fff">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </button>
                    </div>
                </div>
            </div>
            <div>
                <svg class="mx-auto mt-4" xmlns="http://www.w3.org/2000/svg" height="40" width="45"  fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">
  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
</svg>
            </div>
             <div class="mt-4">
                <div class="inputTitle">
          Output: {{ data?data.output_name:'' }}
                </div>
                <div class="flex  space align-center">
                    <div><img src="assets/images/Excel.png" alt=""></div>
                    <div>
                        <button class="inputDiv px-4 py-1">
                            <a :href="data?data.output_url:''" style="text-decoration: none;" target="_blank">
                            Open
                            </a>
                        </button>
                    </div>
                    <!-- <div>
                    <button class="inputDiv px-2">Open File <br> Location</button>
                    </div> -->
                    <div>
                    <button class="inputDiv px-2 py-1">
                        <a :href="data?data.output_url:''" target="_blank">
                            <svg xmlns="http://www.w3.org/2000/svg" height="30" width="30" viewBox="0 0 20 20" fill="#fff">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-share-modal-sm"
            tabindex="-1"
            role="dialog"
            aria-labelledby="mySmallModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="pt-4 pb-5 createdText">
                        <span id="popup-title">Sharing Options</span>
                        <div id="popup-message" style="text-align: justify;padding: 0px 30px;font-size: 14px;font-weight: 500;">
                            <div class="radio-1 mt-2">
                                Share with everyone
                                <input class="ml-auto"  @change="shareOption('all')" type="radio" :checked="audience=='all'" name="sharing" id="inlineRadio1" value="everyone">
                            </div>
                            <div class="radio-2">
                                Only me
                                <input class="ml-auto" @change="shareOption('me')" :checked="audience=='me'" type="radio" name="sharing" id="inlineRadio1" value="me">
                            </div>

                            <div class="radio-3 pr-1" @click="shareOption('only')" data-dismiss="modal" data-toggle="modal" data-target=".bd-share-with-modal-sm">
                                Share only with...
                                <svg xmlns="http://www.w3.org/2000/svg" style="width: 25px;margin-left: auto;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                            <div class="actionsBtn">
                                <button class="cancelBtn px-3" data-dismiss="modal">Cancel</button>
                                <button class="saveBtn px-4 py-1" data-dismiss="modal" @click="shareDocument">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade bd-share-with-modal-sm"
            tabindex="-1"
            role="dialog"
            aria-labelledby="mySmallModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="pt-4 pb-5 createdText">
                        <span id="popup-title">Sharing Options</span>
                        <div id="popup-message" style="text-align: justify;padding: 0px 30px;font-size: 14px;font-weight: 500;">
                            <div class="mt-3 mb-3">
                                <input class="w-100 filterInput" type="search" v-model="audience_search" @keyup="searchUser" placeholder="Search name..."/>
                            </div>
                            <div class="form-group mt-2">
                                <div v-if="audience_list.length > 0" style="max-height:270px;overflow-y:auto;">
                                    <div v-for="audience_list in audience_list" class="d-flex">
                                        <div>{{audience_list.name}}</div>
                                        <div class="ml-auto">
                                            <input type="radio" :checked="selected_audience == audience_list.id" name="audience" id="" :value="audience_list.id" @change="selectUser(audience_list.id)">
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    No user found
                                </div>
                                <!-- <textarea class="form-control w-100" style="border:1px solid #1D3161;" type="search" name="search-person" rows="3"></textarea> -->
                            </div>
                            <div class="actionsBtn">
                                <button class="cancelBtn px-3" data-dismiss="modal">Cancel</button>
                                <button class="saveBtn px-4 py-1" data-dismiss="modal" @click="shareDocument">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
         <div class="modal fade bd-share-modal-lg"
            tabindex="-1"
            role="dialog"
            aria-labelledby="mySmallModal"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="pt-4 pb-5 createdText">
                        <span id="popup-title">Edit Notes</span>
                        <div id="popup-message" style="text-align: justify;padding: 0px 30px;font-size: 14px;font-weight: 500;">
                           <label style="margin-bottom: 0px; font-weight: 700;" for="">Notes</label>
                           <textarea class="popinput" type="text" placeholder="Notes" v-model="notes"></textarea>
                            <div class="actionsBtn">
                                <button class="cancelBtn px-3" data-dismiss="modal">Cancel</button>
                                <button class="saveBtn px-4 py-1" data-dismiss="modal" @click="updateNotes">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import $ from "jquery";
export default {
    name : 'overview',
    data(){
        return{
            data: null,
            recordId: localStorage.getItem('recordOverviewId'),
            apiCreate: axios.create({
                baseURL: '',
                timeout: 90000,
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                    'X-Requested-With': 'XMLHttpRequest',
                }
            }),
            audience: "me",
            audience_id: null,
            selected_audience: null,
            audience_list: [],
            audience_search: "",
            notes: ""
        };
    },
    mounted(){
        this.getData();
        this.getUsers();
    },
    methods:{
        getData(){
            this.apiCreate.post('/api/file/detail',{id:this.recordId})
            .then(response => {
                this.data = response.data.data;
                this.recordId = this.data.id;
                this.notes = this.data.notes;
                this.audience = this.data.share_type;
                this.selected_audience = this.data.share_with;
            })
        },

        updateNotes(){
            this.apiCreate.post('/api/editHistoryNotes',{id:this.recordId,notes:this.notes})
            .then(response => {
                if(response.data.success == true){
                    this.getData();
                    alert(response.data.message);
                }
                else{
                    alert(response.data.message);
                }
            })
        },

        shareOption(audience){
            this.audience = audience;
        },

        shareDocument(){
            this.apiCreate.post('/api/shareHistoryWith',{recordId:this.recordId,share_with:this.audience,userId:this.audience_id})
            .then(response => {
                if(response.data.success == true){
                    this.getData();
                    alert(response.data.message);
                }
                else{
                    alert(response.data.message);
                }
            })
        },

        getUsers(){
            this.apiCreate.get('/api/getUsersWithSameOrg',{id:this.recordId})
            .then(response => {
                this.audience_list = response.data.data;
            })
        },

        selectUser(id){
            this.audience_id = id;
        },

        searchUser(){
            this.apiCreate.post('/api/search/user/share',{name:this.audience_search})
            .then(response => {
                this.audience_list = response.data.data;
            })
        }
    }
}
</script>
<style scoped>
input[type='radio'] {
  -webkit-appearance:none;
  width:15px;
  height:15px;
  border:2px solid rgb(15, 3, 3);
  border-radius:50%;
  outline:none;
  padding: 2px;
}

input[type='radio']:before {
  content:'';
  display:block;
  width:100%;
  height:100%;
  margin: auto;
  border-radius:50%;
}
input[type='radio']:checked:before {
  background:green;
}
.filterInput {
  height: 26px;
  border-radius: 5px;
  background-color: #c4c4c4;
  font-size: 14px;
  font-weight: 500;
  color: #485167;
  margin-right: 20px;
  padding: 0 10px;
}
.radio-1{
    background: rgba(15, 179, 151, 0.2);
    font-style: normal;
    padding: 5px 10px;
    font-weight: 700;
    font-size: 15px;
    line-height: 24px;
    display: flex;
    align-items: center;
    color: #1D3161;
}
.radio-2{
    background: transparent;
    font-style: normal;
    padding: 5px 10px;
    font-weight: 700;
    font-size: 15px;
    line-height: 24px;
    display: flex;
    align-items: center;
    color: #1D3161;
}
.radio-3{
    background: #D2D6DF;
    font-style: normal;
    padding: 5px 10px;
    font-weight: 700;
    font-size: 15px;
    line-height: 24px;
    display: flex;
    align-items: center;
    color: #1D3161;
}
.actionsBtn {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin-top: 20px;
    }
.saveBtn {
    font-style: normal;
    font-weight: 700;
    font-size: 18px;
    line-height: 30px;
    display: flex;
    align-items: center;
    text-align: center;
    color: #FFFFFF;
    background: #38b397;
}
.cancelBtn {
    font-style: normal;
    font-weight: 700;
    font-size: 18px;
    line-height: 30px;
    display: flex;
    align-items: center;
    text-align: center;
    color: #1D3161;
    background: transparent;
    border: 3px solid #1D3161;
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
    font-size: 36px;
    line-height: 24px;
    text-align: center;
    color: #1d3161;
}
.inputDiv {
    background-color: #0fb397;
    line-height: 22px;
    color: #fff;
    font-size: 18px;
    font-weight: 700;
    max-width: 100px;
    height: 46px;
}
.editButtons{
    display: flex;
    justify-content: space-between;
    margin-top: 20px;
}
.editButtons > button{
    background-color: #0fb397;
    color: #fff;
    font-size: 15px;
    font-weight: 500;
    width: 92px;
    line-height: 19px;
    padding: 6px 0;
}
.inputTitle{
    font-style: normal;
font-weight: 700;
font-size: 18px;
line-height: 21px;
color: #FFFFFF;
margin-bottom: 15px;
}
.details{
    font-size: 15px;
    margin-bottom: 5px;
}
.detailDiv{
    border: 1px solid #fff;
    font-weight: 700;
    font-size: 12px;
    line-height: 16px;
    color: #FFFFFF;
}
.detailDiv > div{
    padding: 8px 15px;
    border-bottom: 1px solid #fff;
}
.left-card{
    width: 40%;
    margin-bottom: 35px;
}
.right-card{
    width: 50%;
}
.title{
font-weight: 700;
font-size: 24px;
line-height: 28px;
color: #FFFFFF;
text-align: start;
margin-bottom: 20px;
}
.popinput{
        border: 1px solid #1d3161;
    border-radius: 4px;
    outline: none;
    padding: 5px;
    width: 100%;
    color: #1d3161;
    margin-bottom: 15px;
}
/* width */
::-webkit-scrollbar {
  width: 0px;
}

/* Track */
::-webkit-scrollbar-track {
  background: #f1f1f1;
}

/* Handle */
::-webkit-scrollbar-thumb {
  background: #888;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: #555;
}
</style>
