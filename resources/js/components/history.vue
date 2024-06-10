<template>
<div class="tabBody1">
  <div class="px-4">
    <div class="flex">
      <input class="w-100 filterInput" type="search" @keyup="validateFilter($event)" v-model="search.text" placeholder="Search documents..."/>
      <div class="filterBtn tooltip-custom px-4">
        <span>
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
          </svg>
        </span>
        Filter
        <div class="tooltiptext-custom" style="width:102px;top: 22px;left: 60px;border: 4px solid #38b397;">
            <div class="tooltip-items-filter" @click="recordFilter('date')">Date</div>
            <div class="tooltip-items-filter" @click="recordFilter('flag')">Flagged</div>
            <div class="tooltip-items-filter" @click="recordFilter('type')">Document Type</div>
        </div>
      </div>
    </div>

    <div class="d-flex">
        <div>
            <div class="filter-design" id="filter-design">
                <span class="tag">
                    <span id="filter_text" style="line-height: 23px;">

                    </span>
                    <img width="10" class="ml-2 mr-1" style="cursor:pointer;" @click="cleartext" src="assets/images/modal_cross.svg">
                </span>
            </div>
        </div>
        <div class="ml-auto">
            <div class="filter-design-flag ml-2" id="filter-design-flag">
                <span class="tag">
                    <span id="filter_text" style="line-height: 23px;color: #0fb397;">
                        Flagged
                    </span>
                    <img width="10" class="ml-2 mr-1" style="cursor:pointer;" @click="clearFlag" src="assets/images/modal_cross.svg">
                </span>
            </div>
            <div class="filter-design-date ml-2" id="filter-design-date">
                <span class="tag">
                    <span id="filter_text_date" style="line-height: 23px;">
                        <input type="date" style="background:#c6c8c9;" @change="getDate" v-model="search.date">
                    </span>
                    <img width="10" class="ml-2 mr-1" style="cursor:pointer;" @click="clearDate" src="assets/images/modal_cross.svg">
                </span>
            </div>
            <div class="filter-design-document ml-2" id="filter-design-document">
                <span class="tag">
                    <span id="filter_text" style="line-height: 23px;">
                        <span class="ml-1" style="display : inline-flex; align-items : center;">
                            <input class="myCheckBox" type="checkbox" style="height: 15px;width: 15px;" @change="document_type('png')" value="png">
                            <span class="ml-1" style="font-size:12px; color: #0fb397;">PNG</span>
                        </span>
                        <span class="ml-3" style="display : inline-flex; align-items : center;">
                            <input class="myCheckBox" type="checkbox" style="height: 15px;width: 15px;" @change="document_type('tiff')" value="tiff">
                            <span class="ml-1" style="font-size:12px; color: #0fb397;">TIFF</span>
                        </span>
                        <span class="ml-3" style="display : inline-flex; align-items : center;">
                            <input class="myCheckBox" type="checkbox" style="height: 15px;width: 15px;" @change="document_type('pdf')" value="pdf">
                            <span class="ml-1" style="font-size:12px; color: #0fb397;">PDF</span>
                        </span>
                        <span class="ml-3" style="display : inline-flex; align-items : center;">
                            <input class="myCheckBox" type="checkbox" style="height: 15px;width: 15px;" @change="document_type('jpeg')" value="jpeg">
                            <span class="ml-1" style="font-size:12px; color: #0fb397;">JPEG</span>
                        </span>
                    </span>
                    <img width="10" class="ml-2 mr-1 mb-1" style="cursor:pointer;" @click="clearType" src="assets/images/modal_cross.svg">
                </span>
            </div>

        </div>
    </div>






    <div class="mainFilters" id="mainFilters">
      <div class="tableHeader">
        <div style="width: 15%">Input Name</div>
        <div style="width: 15%">Output Name</div>
        <div style="width: 10%">Time Period</div>
        <div style="width: 10%">User's Name</div>
        <div style="width: 10%">
            Date
            <img :src="arrow_img" width="10" style="cursor:pointer;" @click="dateOrder">
        </div>

        <div style="width: 10%; text-align : center;">Case Number</div>
        <div style="width: 15%">Notes</div>
        <div style="width: 5%">Status</div>
        <div style="width: 15%; text-align: end">
          Operation Selected
        </div>
      </div>
      <div class="">
        <div v-for="data in data" class="tableBody">
          <div style="width: 15%;">
            <div :title="data.input_name" style="white-space: nowrap;text-overflow: ellipsis;overflow: hidden;width:130px;">
                {{ data.input_name }}
            </div>
          </div>
          <div style="width: 15%">
            <div :title="data.output_name" style="white-space: nowrap;text-overflow: ellipsis;overflow: hidden;width:130px;">
                {{ data.output_name }}
            </div>
            </div>
          <div style="width: 10%">{{ data.time_period }}</div>
          <div style="width: 10%">{{ data.user_name }}</div>
          <div style="width: 10%">{{ data.date }}</div>
          <div style="width: 10%; justify-content : center;">{{ data.case_number }}</div>
          <div style="width: 15%">
            {{ data.notes }}
          </div>
          <div style="width: 5%">
            <!-- {{ data.status }} -->
            <div v-if="data.status > 0 && data.status == 100">Completed</div>
            <div v-else-if="data.status == 102">Failed</div>
            <progress v-else class="progressbar-style" max="100" :value.prop="data.status"></progress>
          </div>
          <div style="width: 15%; justify-content: end">
            <div class="operationCol">
              <div @click="setFlag(data.id,data.flag)">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="#1d3161"
                  stroke-width="2"
                  :class="{ 'flag-on' : data.flag == 1, 'flag-off' : data.flag == 0 }"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"
                  />
                </svg>
              </div>
              <div @click="deleteRecord(data.id)">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="#1d3161"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                  />
                </svg>
              </div>
               <!-- data-toggle="modal" data-target=".bd-share-modal-lg" -->
              <div @click="getRecord(data.id)">
                <svg

                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="#1d3161"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"
                  />
                </svg>
              </div>
              <div class="tooltip-custom">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="#1d3161"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"
                  />
                </svg>
                    <div class="tooltiptext-custom">
                        <div class="tooltip-items" @click="overview(data.id)">Overview</div>
                        <div class="tooltip-items"><a :href="data.input_url" target="_blank">Download Input</a></div>
                        <div class="tooltip-items"><a :href="data.status != 100? chawal() : data.output_url" target="_blank">Download Output</a></div>
                    </div>
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
        aria-hidden="true">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="pt-4 pb-5 createdText">
                    <p style="text-align :center; font-size: 26px; font-weight: bolder;color: #1D3161;">Edit</p>
                    <div id="popup-message" style="text-align: justify;padding: 0px 30px;font-size: 14px;font-weight: 500;">
                        <label style="margin-bottom: 0px; font-weight: 700;" for="">Input Name</label>
                        <input class="popinput" type="text" placeholder="Input Name" v-model="file_detail.input_name">
                        <label style="margin-bottom: 0px; font-weight: 700;" for="">Output Name</label>
                        <input class="popinput" type="text" placeholder="Output Name" v-model="file_detail.output_name">

                        <label style="margin-bottom: 0px; font-weight: 700;" for="">Time Period From</label>
                        <input  class="popinput" type="date" placeholder="Transaction start date" v-model="file_detail.t_start_date">
                        <label style="margin-bottom: 0px; font-weight: 700;" for="">Time Period To</label>
                        <input  class="popinput" type="date" placeholder="Transaction end date" v-model="file_detail.t_end_date">

                        <label style="margin-bottom: 0px; font-weight: 700;" for="">User's Name</label>
                        <input class="popinput" type="text" placeholder="User's Name" readonly v-model="file_detail.user_name">
                        <label style="margin-bottom: 0px; font-weight: 700;" for="">Date</label>
                        <input  class="popinput" type="date" placeholder="Process" v-model="file_detail.date">
                        <label style="margin-bottom: 0px; font-weight: 700;" for="">Case Number</label>
                        <input class="popinput" type="text" placeholder="Case Number" v-model="file_detail.case_number">
                        <label style="margin-bottom: 0px; font-weight: 700;" for="">Notes</label>
                        <textarea class="popinput" type="text" placeholder="Notes" v-model="file_detail.notes"></textarea>
                        <div class="actionsBtn" style="    display: flex;justify-content: space-between;">
                            <button class="cancelBtn px-3" @click="closeUpdateModel">Cancel</button>
                            <button class="saveBtn px-4 py-1" @click="updateRecord">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mx-auto flex justify-content-center bg-color-green align-center paginationDiv" style="width: 10%;">
      <div @click="goback" style="cursor:pointer;">
        <svg xmlns="http://www.w3.org/2000/svg" height="18" width="18" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
      </div>
      <div class="paginationText">{{ current_page }} of {{ last_page }}</div>
      <div @click="goforward" style="cursor:pointer;">
        <svg xmlns="http://www.w3.org/2000/svg" height="18" width="18" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
        </svg>
      </div>
    </div>
  </div>
</div>
</template>
<script>
import $ from 'jquery';
export default {
  name: "history",
  data() {
    return {
        data: [],
        last_page: 0,
        current_page: 0,
        apiCreate: axios.create({
            baseURL: '',
            timeout: 90000,
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('api_token'),
                'X-Requested-With': 'XMLHttpRequest',
            }
        }),
        search: {
            text: "",
            flag: "",
            type: "",
            date: "",
        },
        recordId: null,
        file_detail: {
            id: "",
            input_name: "",
            output_name: "",
            t_start_date: "",
            t_end_date: "",
            user_name: "",
            date: "",
            case_number: "",
            notes: "",
        },
        arrow_img: "assets/images/up.svg",
        date_type: "DESC",
    };
  },
  mounted(){
    this.getData();
    setInterval( res =>{
      this.getData();
    },21000)
  },
  methods: {
    dateOrder(){
        if(this.arrow_img == "assets/images/up.svg"){
            this.arrow_img = "assets/images/down.svg";
            this.date_type = "ASC";
        }
        else{
            this.arrow_img = "assets/images/up.svg";
            this.date_type = "DESC";
        }
        this.current_page = 1;
        this.getData();
    },

    onFileUplaod: () => {
      document.getElementById("fileUpload").click();
    },

    goback(){
        if(this.current_page > 1){
          this.current_page = this.current_page - 1;
          this.getData();
        }
    },

    goforward(){
        if(this.current_page < this.last_page){
          this.current_page = this.current_page + 1;
          this.getData();
        }
    },

    getData(){
      this.apiCreate.post('/api/get/history?page='+this.current_page,
      {
        search:this.search.text,
        date:this.search.date,
        type:this.search.type,
        flag:this.search.flag,
        date_type: this.date_type,
      })
      .then(response => {
        if(response.data.success == true){
          this.data = response.data.data.data;
          this.current_page = response.data.data.current_page;
          this.last_page = response.data.data.last_page;
          console.log(this.current_page,this.last_page);
        }
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

    deleteRecord(id){
      this.apiCreate.post('/api/delete/history/record',{id:id})
      .then(response => {
        if(response.data.success == true){
          this.getData();
          alert(response.data.message);
        }
      });
    },

    setFlag(id,type){
      this.apiCreate.post('/api/flag/history/record',{id:id,type:type})
      .then(response => {
        if(response.data.success == true){
          this.getData();
          alert(response.data.message);
        }
        else{
          alert(response.data.message);
        }
      });
    },

    overview(id){
      localStorage.setItem('recordOverviewId',id)
      // this.$router.push({name: 'overview', params: {id:id}});
      this.$router.push('overview');
    },

    recordFilter(type){

        if(type == 'flag'){
            this.current_page = 1;
            // document.getElementById("mainFilters").style.marginTop = "58px";
            document.getElementById("filter-design").style.float = "right";

            // document.getElementById("filter-design-document").style.display = "none";
            // document.getElementById("filter-design").style.display = "none";
            // document.getElementById("filter-design-date").style.display = "none";
            document.getElementById("filter-design-flag").style.display = "block";
            // this.search.date = "";
            // this.search.type = "";
            this.search.flag = true;
            this.getData();
        }
        if(type == 'type'){
            // document.getElementById("mainFilters").style.marginTop = "58px";
            document.getElementById("filter-design").style.float = "right";
            // document.getElementById("filter-design-flag").style.display = "none";
            // document.getElementById("filter-design").style.display = "none";
            document.getElementById("filter-design-document").style.display = "block";
            // document.getElementById("filter-design-date").style.display = "none";
        }
        if(type == 'date'){
            // document.getElementById("mainFilters").style.marginTop = "58px";
            document.getElementById("filter-design").style.float = "right";
            // document.getElementById("filter-design-flag").style.display = "none";
            // document.getElementById("filter-design").style.display = "none";
            // document.getElementById("filter-design-document").style.display = "none";
            document.getElementById("filter-design-date").style.display = "block";
        }
    },

    validateFilter(event){
        if (event.shiftKey && event.keyCode == 13) {
        }
        else if (event.keyCode == 13) {
            // document.getElementById("mainFilters").style.marginTop = "58px";
            document.getElementById("filter_text").style.color = "#1D3161";
            document.getElementById("filter_text").innerText = this.search.text;
            // document.getElementById("filter-design-flag").style.display = "none";
            // document.getElementById("filter-design-date").style.display = "none";
            // document.getElementById("filter-design-document").style.display = "none";
            document.getElementById("filter-design").style.display = "block";
            document.getElementById("filter-design").style.float = "left";
            this.current_page = 1;
            this.getData();
        }
    },

    document_type(type){
        $(".myCheckBox").prop("checked", false);
        $(":checkbox[value="+type+"]").prop("checked","true");
        this.search.type = type;
        this.current_page = 1;
        this.getData();
    },

    getDate(){
        this.current_page = 1;
        this.getData();
    },

    cleartext(){
        this.current_page = 1;
        this.search.text = "";
        // document.getElementById("mainFilters").style.marginTop = "25px";
        document.getElementById("filter_text").innerText = "";
        document.getElementById("filter-design").style.display = "none";
        this.getData();
    },

    clearFlag(){
        this.current_page = 1;
        this.search.flag = "";
        document.getElementById("filter-design-flag").style.display = "none";
        this.getData();
    },

    clearDate(){
        this.current_page = 1;
        this.search.date = "";
        document.getElementById("filter-design-date").style.display = "none";
        this.getData();
    },

    clearType(){
        this.current_page = 1;
        this.search.type = "";
        document.getElementById("filter-design-document").style.display = "none";
        this.getData();
    },

    chawal(){
      return;
    },

    getRecord(id){
        this.apiCreate.post('/api/file/detail',{id:id})
        .then(response => {
            if(response.data.success == true){
                console.log(new Date(response.data.data.date).toISOString().substr(0, 10));
                console.log(response.data.data.date);
                this.file_detail.recordId = response.data.data.id;
                this.file_detail.input_name = response.data.data.input_name;
                this.file_detail.output_name = response.data.data.output_name;

                if(response.data.data.time_period != null){
                    this.file_detail.t_start_date = new Date(response.data.data.transcation_start).toISOString().substr(0, 10);
                    this.file_detail.t_end_date = new Date(response.data.data.transcation_end).toISOString().substr(0, 10);
                }

                this.file_detail.user_name = response.data.data.user_name;
                this.file_detail.date = new Date(response.data.data.date).toISOString().substr(0, 10);
                this.file_detail.case_number = response.data.data.case_number;
                this.file_detail.notes = response.data.data.notes;
                window.$('.bd-share-modal-lg').modal('toggle');
            }
            else{
                alert(response.data.message)
            }
        });
    },

    updateRecord(){
        this.apiCreate.post('/api/editHistoryRecord',{id:this.file_detail.recordId,data:this.file_detail})
            .then(response => {
                if(response.data.success == true){
                    this.getData();
                    window.$('.bd-share-modal-lg').modal('hide');
                    alert(response.data.message);
                }
                else{
                    window.$('.bd-share-modal-lg').modal('hide');
                    alert(response.data.message);
                }
            })
    },

    closeUpdateModel(){
        window.$('.bd-share-modal-lg').modal('hide');
    }
  },
}
</script>
<style scoped>
.filter-design {
    display: none;
    position: relative;
    top: 12px;
    float: left;
}
.filter-design-flag{
    display: none;
    position: relative;
    top: 12px;
    float: right;
}
.filter-design-document{
    display: none;
    position: relative;
    top: 12px;
    float: right;
}
.filter-design-date{
    display: none;
    position: relative;
    top: 12px;
    float: right;
}
.tag {
    display: flex;
    align-items: center;
    padding-top: 5px;
    padding-bottom: 2px;
    padding-left: 10px;
    padding-right: 10px;
    font-size: 15px;
    font-weight: 700;
    color: #1D3161;
    text-align: center;
    border-radius: 0.25em;
    background-color: #c4c4c4;
}
.tooltip-custom {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black;
    font-size: 12px;
    font-weight: 700;
    line-height: 14px;
}
.tooltip-items{
  font-style: normal;
  font-weight: 700;
  font-size: 12px;
  line-height: 11px;
  text-align: left;
  padding: 5px;
}
.tooltip-items a{
  text-decoration: none;
}
.tooltip-items:hover{
  background: rgba(15, 179, 151, 0.2);
}
.tooltip-custom .tooltiptext-custom {
  /* padding-bottom: 7px; */
  visibility: hidden;
  width: 110px;
  background-color: #fff;
  border: 3px solid #38b397;
  position: absolute;
  z-index: 1;
  top: 130%;
  left: 17%;
  margin-left: -60px;
  color: #1D3161;
}

.tooltip-custom .tooltiptext-custom::after {
  content: "";
  position: absolute;
  bottom: 100%;
  left: 60%;
  margin-left: -5px;
  border-width: 8px;
  border-style: solid;
  border-color: transparent transparent #38b397 transparent;
}

.tooltip-custom:hover .tooltiptext-custom {
  visibility: visible;
}
.tooltip-items-filter{
    font-style: normal;
    font-weight: 700;
    font-size: 10px;
    line-height: 11px;
    text-align: left;
    padding: 5px 10px;
    cursor: pointer;
}
.tooltip-items-filter:hover{
  background: rgba(15, 179, 151, 0.2);
}
.paginationDiv{
    border-radius: 3px;
}
.paginationText{
    font-size: 8px;
    font-weight: 700;
    line-height: 10px;
    color: #fff;
    padding:6px 8px;
    border-left: 1px solid #fff;
    border-right: 1px solid #fff;
}
.mainFilters{
    margin-top: 25px;
    min-height: 368px;
}
.operationCol {
  display: flex;
  justify-content: space-between;
  min-width: 126px;
}
.operationCol > div {
  padding: 5px;
  background-color: #fff;
  border-radius: 3px;
  cursor: pointer;
}
.operationCol > div > svg {
  height: 15px;
  width: 15px;
}
.tableHeader {
  background-color: #fff;
  height: 36px;
  font-size: 12px;
  font-weight: 600;
  color: #1d3161;
  display: flex;
  padding: 0 8px;
  align-items: center;
}
.tableBody > div {
  display: flex;
  align-items: center;
  padding: 5px;
}
.tableHeader > div {
  padding: 5px;
}
.tableBody {
  background-color: transparent;
  height: 36px;
  font-size: 10px;
  font-weight: 600;
  color: #fff;
  display: flex;
  padding: 0 8px;
  border-bottom: 1px solid #fff;
  line-height: 12px;
}
.tabBody1 {
  width: 100%;
  margin: 0 auto;
}
.flex {
  display: flex;
}
.align-center {
  align-items: center;
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
.filterInput:focus {
  background-color: #fff;
}
.filterBtn {
  background-color: #0fb397;
  color: #fff;
  font-size: 14px;
  font-weight: 500;
  padding-top: 2px;
  border-radius: 5px;
  display: flex;
  align-items: center;
}
.filterBtn > span {
  height: 16px;
  width: 16px;
  margin-right: 5px;
}
.flag-on{
  fill: red;
}
.flag-off{
  fill: white;
}
.new-center{
    display: inline-flex;
    align-items: center;
    justify-content: center;
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
</style>
