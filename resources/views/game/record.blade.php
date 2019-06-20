<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="keywords" content="">
  <meta name="description" content="">
  <title></title>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">
  <meta name="format-detection" content="telephone=no">
  <meta name="screen-orientation" content="portrait">
  <meta name="x5-orientation" content="portrait">
  <meta name="full-screen" content="yes">
  <meta name="x5-fullscreen" content="true">
  <meta name="browsermode" content="application">
  <meta name="x5-page-mode" content="app">
  <meta name="msapplication-tap-highlight" content="no">
  <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css">
  <link rel="stylesheet" href="https://unpkg.com/mint-ui/lib/style.css">
  <link rel="stylesheet" href="../../css/iconfont3.css">
  <!-- <link rel="stylesheet" href="iconfont3.css"> -->
  <!-- <script src="https://cdn.bootcss.com/vue/2.6.10/vue.min.js" charset="utf-8"></script> -->
  <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
  <script src="https://unpkg.com/element-ui/lib/index.js"></script>
  <script src="https://unpkg.com/mint-ui/lib/index.js"></script>
  <script src="http://lib.baomitu.com/jquery/2.1.1/jquery.min.js"></script>
  <style>
    *{ margin:0px; padding:0px;}
    li{list-style:none;}
    html{overflow-y:scroll;font-size:0.4rem;}
    body{background: #f6f6f6;user-select: none;onselectstart:none;font-family: PingFangSC-Regular;font-weight: normal;}
    .fl{float:left}
    .fr{float:right}
    .dsn{display:none!important}
    .dsb{display:block!important}
    .black{color:#000;}
    .blue{color:#00b7ee;}
    #app{font-size: 0.4rem;}
    #app .gamelist ul li{display: inline-block;height: 2rem;width: 2rem;text-align: center;font-size: 0.4rem;margin: 0.1rem;}
    #app .gamelist ul li img{height: 2rem;}
    .main{margin-top: 2rem;font-size: 0.4rem;}
    .main h2{text-align: center;}
    .main p{text-align: left;height: 0.6rem;line-height: 0.6rem;margin: 0.15rem 0rem;}
    .header{background: #fff;position: fixed;top: 0;width: 100%;}
    .header .table li{display: inline-block;height:1.8rem;line-height: 1.8rem;padding:0rem 0.3rem;}
    .header .table .goback{background:url("http://play.zdzlw.com/img/v/vapp/gamelist/goback.png") no-repeat;display: inline-block;width: 1.5rem;height: 1.5rem;background-size: 100%;}
    .header .table .change{width: 0.8rem;height: 0.8rem;line-height: 0.8rem !important;font-size: 0.4rem;background: #5bbae8;color: #fff;background-size: 100%;}
    .header .table .recoad{width: 0.8rem;height: 0.8rem;line-height: 0.8rem !important;font-size: 0.5rem;background-size: 100%;color: #fff;background: #f3b495;}
    .header .table{width: 100%;}
    .priceList{margin:1vw 2vw;}
    .priceList li{display: inline-block;width: 18.5vw;height: 1.2rem;  line-height: 1.2rem;background: #fff;border: 1.2px solid #f6f6f6;text-align: center;}
    .priceList .active{background: #ee84ac;color:#fff;}
    .priceList li:last-child{width:99%;}
    .surebtn{font-size: 0.5rem;height: 1.3rem; padding: 0.01vw; text-align: center;color: #fff;background: #ee84ac;border: none;outline: none;border-radius: 0.1rem;width: 9.5rem;margin: 4vw auto;display: inherit;}
    .surebtn:active{background: #5bbae8}
    .list li,.main .li{height: 1.1rem;line-height: 1.1rem;background: #fff;padding:0rem 0.3rem;margin-top: 0.05rem;}
    .main .li{text-align: center;margin: 0;}
    .main .li .info{font-size: 0.3rem;color: #c2c0c0;}
    .priceNum{font-size:0.6rem;color:#ee84ac;font-weight: bold; padding-right: 0.2rem}
    .el-button{padding: 5px 9px;margin-top: 0.15rem;margin-left: 0.5rem;}
    .el-button--danger.is-plain{color: #ec87ab;background: #fff;border-color: #ec87ab;}
    .el-button--danger .is-plain:active,.el-button--danger.is-plain:focus, .el-button--danger.is-plain:hover{background: #fff;border-color: #fbc4c4;}
    .paink{color:#ee84ac; padding-right: 0.2rem}
    .title{font-size:0.4rem;font-weight: bold;color: #827c7c;}
    .header .table .boxx{position: relative;top: 0.3rem;font-size: 0.35rem;color: #c6c6c6;}
    .header .table .boxx span{display: block;line-height: normal;text-align: center;}
    .priceC{margin-left:0.35rem;color:#ee84ac;}
    .icon-shuaxin{padding-right:0.2rem; font-size: 0.5rem}
    .recoadMain{margin:0.2rem 0rem;background: #fff;}
    .watelicon{width: 0.8rem;height: 0.8rem;float: left;background: url("http://play.zdzlw.com/img/v/vapp/gamelist/walet.png")no-repeat;background-size: 100%;margin: 0rem 0.25rem;margin-top: 0.45rem;}
    .recoadMain li{height: 1.5rem;background: #fff;margin: 0rem 0.4rem;border-bottom: 0.02rem solid #ededed;}
    .rlist{margin-top: 3rem;}
    .headeinfo{height: 1.2rem;line-height: 1rem;background: #f6f6f6;padding: 0rem 0.4rem;position: fixed;top: 1.7rem;width: 100%;}
    .span span{display: block;height: 0.5rem;color: #827c7c;}
    .span{width: 5rem;line-height: 1.2rem;}
    .priceN{margin-top: 0.5rem;}
    .infotex{height: 1rem;margin: 0.2rem 0.8rem;}
    .infotex p{height: 0.45rem;line-height: 0.45rem;}
    .selectime{border-radius: 0.26rem;background: #fff;height: 0.7rem;line-height: 0.7rem;width: 1.6rem;margin-top: 0.3rem;}
    .select{width: 1rem;display: inline-block;text-align: center;}
    .dark{background: #ddd !important;}

    .inputText {border: 0.02rem solid #b5b5b5;width: 6rem;-webkit-appearance: none;height: 1rem;color: #000; border: none ; font-size: 0.8rem; border: none ;  font-size: 0.8rem; outline: none}
    .inputText::placeholder {color: #b5b5b5;font-size: 0.4rem;}
    .icon-jinbi{font-size: 0.6rem; color:gold;margin-left: 0.3rem;}
    .el-collapse-item__header{padding: 0rem 0.2rem;}
    @-webkit-keyframes goup {
    0% {-webkit-transform: translateY(100%);transform: translateY(100%);}
    50% {-webkit-transform: translateY(0%);transform: translateY(0%);}
    70% {-webkit-transform: translateY(0%);transform: translateY(0%);}
    80% {-webkit-transform: translateY(0%);transform: translateY(0%);}
    100% {-webkit-transform: translateY(100%);transform: translateY(100%);}
    }
    .pickheader{text-align: center;height: 1.3rem;line-height: 1.3rem;margin: 0rem 0.4rem;border-bottom: 0.02rem solid #ddd;}
    .pickt{text-align: center;height: 1.3rem;line-height: 1.3rem;margin: 0rem 0.4rem;}
    .pickheader span,.pickt span{color: #ee84ac;font-size: 0.4rem;}
    .pickheader .fontSize5,.pickt .fontSize5{font-size: 0.5rem;font-weight: bold;color: #6f6f6f;}
    .datepicker{margin: 0.5rem 0rem;}
    .btnc{border-radius: 0.26rem;background: #fff;height: 0.7rem;line-height: 0.7rem;width: 1.6rem;text-align: center;display: inline-block;margin: 0.3rem 0.5rem;}
    .fz2{font-size: 0.4rem;}
    .startime,.endtime{width: 3.5rem;border-bottom: 0.02rem solid #ee84ac;}
    .el-collapse-item__content{padding: 0.3rem 0.5rem;}
    .el-collapse-item__content button{margin: 0rem 0.5rem;width: 2rem;height: 0.85rem;display: inline-block;background: #f5f5f5;border-radius: 0.1rem;border: 0.02rem solid #ededed;}
    .moveup{-webkit-animation: timeselect 0.3s;  animation: timeselect 0.3s;}
    .pickuptime{position: fixed;bottom: 0rem;width: 100%;background: #f5f5f5;}
    .el-loading-mask{background-color: rgba(255, 255, 255, 0.1);z-index: 10;}
    .movedown{-webkit-animation: timeselectgodown 1s;  animation: timeselectgodown 1s;}
    @-webkit-keyframes timeselect {
    0% {-webkit-transform: translateY(100%);transform: translateY(100%);}
    100% {-webkit-transform: translateY(0%);transform: translateY(0%);}
    }
    @-webkit-keyframes timeselectgodown {
    0% {-webkit-transform: translateY(0%);transform: translateY(0%);}
    100% {-webkit-transform: translateY(100%);transform: translateY(100%);}
    }
    .copyAlert{text-align: center;font-size: 0.4rem;}
    .copyAlert .title{position: relative;top: -1.9rem;height: 3rem;}
    .copyAlert .title span{color: #fff;}
    .copyAlert .btnsure{background: #ee84ac;color: #fff;height: 0.8rem;width: 2.5rem;border-radius: 0.1rem;border: 0.1rem solid #ee84ac;}
    .copyAlert .btn{background: url("http://play.zdzlw.com/img/v/vapp/gamelist/btn.png");border: none;}
    .cover{height: 100%;  width: 100%;background: rgba(0, 0, 0, 0.5);position: fixed;top: 0; z-index: 20}
    .copyAlert .centen{background: #fff;position: relative;top: -1rem;height: 4.5rem;width: 6.5rem;border-radius: 0.1rem; z-index: 15}
    .copyAlert .centen .ShowBtnGoGame{position: absolute;bottom: 0;width: 100%;font-size: 0;}
    .copyAlert .centen .ShowBtnGoGame button{width: 50%;border-radius: 0;height: 1rem;}
    .container{width: 6.5rem;margin: 65% auto;}
    .titleText{color:#000;height: 2rem;line-height: 2rem;position: relative;top: -2rem;width: 5rem;margin: 0 auto;}
    .ShowBtn{position: absolute;bottom: 0.3rem;width: 100%;font-size: 0;}
    .el-button--danger .is-plain:active, .el-button--danger.is-plain:focus, .el-button--danger.is-plain:hover{color: #ec87ab;background: #fff;border-color: #ec87ab;}
    /* 弹出遮罩 */
    .mesk{position: fixed;top: 0;right: 0;bottom: 0;left: 0;background-color: rgba(0,0,0,.6);z-index: 20;}
    /* 输入转换金额 */
    .inputmoney{width:4rem; margin-left: 0.2rem;position: relative;font-size: 0.38rem;left: 0.3rem;}
    .icon-renmingbi{font-size: 0.55rem;color: #b5b5b5;position: relative;top: -0.05rem;}
    /* 点击转入上浮弹框 */
    .moveup{-webkit-animation: timeselect 0.5s;  animation: timeselect 0.5s;}
    .pickupwallet{position: fixed;bottom: 0rem;width: 100%;background: #f5f5f5; z-index: 50}
    .icon-jiantou-right:before{ color: #6f6f6f }
    .el-dropdown-link{position: relative; left: 0.4rem; width:0.2rem; color: #000; top: 0.15rem}
    /*  下拉框样式  */
    .el-input--suffix .el-input__inner{padding-right:0px;height: 0.7rem;  margin-top: 0.3rem; margin-left: 0.3rem; border-radius: 0.5rem;font-weight: 500;line-height: 0.7rem;}
    .el-select .el-input .el-select__caret {    line-height: 0.7rem; margin-top: 0.05rem;}
    .el-input__inner{border: none}
    .el-select>.el-input{width: 2.7rem;line-height: 0.7rem}
    .el-input__suffix-inner{  position: relative; left: 0.2rem;   top: 0.08rem;}
    .el-icon-arrow-up:before{ font-weight: 900;  color: #000; }
    .el-select-dropdown{margin-left: 0.3rem}
    .title_c{color: #ff0556;}
  </style>
  <script>
      !function(N,M){function L(){var a=I.getBoundingClientRect().width;a/F>1024&&(a=1024*F);var d=a/10;I.style.fontSize=d+"px",D.rem=N.rem=d}var K,J=N.document,I=J.documentElement,H=J.querySelector('meta[name="viewport"]'),G=J.querySelector('meta[name="flexible"]'),F=0,E=0,D=M.flexible||(M.flexible={});if(H){/*console.warn("将根据已有的meta标签来设置缩放比例");*/var C=H.getAttribute("content").match(/initial\-scale=([\d\.]+)/);C&&(E=parseFloat(C[1]),F=parseInt(1/E))}else{if(G){var B=G.getAttribute("content");if(B){var A=B.match(/initial\-dpr=([\d\.]+)/),z=B.match(/maximum\-dpr=([\d\.]+)/);A&&(F=parseFloat(A[1]),E=parseFloat((1/F).toFixed(2))),z&&(F=parseFloat(z[1]),E=parseFloat((1/F).toFixed(2)))}}}if(!F&&!E){var y=N.navigator.userAgent,x=(!!y.match(/android/gi),!!y.match(/iphone/gi)),w=x&&!!y.match(/OS 9_3/),v=N.devicePixelRatio;F=x&&!w?v>=3&&(!F||F>=3)?3:v>=2&&(!F||F>=2)?2:1:1,E=1/F}if(I.setAttribute("data-dpr",F),!H){if(H=J.createElement("meta"),H.setAttribute("name","viewport"),H.setAttribute("content","initial-scale="+E+", maximum-scale="+E+", minimum-scale="+E+", user-scalable=no"),I.firstElementChild){I.firstElementChild.appendChild(H)}else{var u=J.createElement("div");u.appendChild(H),J.write(u.innerHTML)}}N.addEventListener("resize",function(){clearTimeout(K),K=setTimeout(L,300)},!1),N.addEventListener("pageshow",function(b){b.persisted&&(clearTimeout(K),K=setTimeout(L,300))},!1),"complete"===J.readyState?J.body.style.fontSize=12*F+"px":J.addEventListener("DOMContentLoaded",function(){J.body.style.fontSize=12*F+"px"},!1),L(),D.dpr=N.dpr=F,D.refreshRem=L,D.rem2px=function(d){var c=parseFloat(d)*this.rem;return"string"==typeof d&&d.match(/rem$/)&&(c+="px"),c},D.px2rem=function(d){var c=parseFloat(d)/this.rem;return"string"==typeof d&&d.match(/px$/)&&(c+="rem"),c}}(window,window.lib||(window.lib={}));
  </script>
</head>
<body>
  <div id="app" v-loading="loading" >
    <div :class="switchmesk"></div>
    <div class="header">
       <ul class="table">
         <li class="fl" style="width:1.9rem;"><span class="goback" @click="goback" v-show="showgoback"></span></li>
         <li style="margin-left:0.7rem">
           <div class="boxx">
               <span :class="['change iconfont icon-shuangjiantou', backgroundchange]" @click="change"></span><span>转换</span>
            </div>
         </li>
         <li style="padding:0">
            <div>
                <span  style="font-stretch:condensed;font-size:1rem ; font-weight: 100; display: inline-block; color:#ededed;" >|</span>
             </div>
          </li>
         <li>
           <div class="boxx">
               <span :class="['recoad iconfont icon-shouzhizhangben',backgroundrecoad]" @click="recoad"></span><span class="title_c">记录</span>
            </div>
         </li>
       </ul>
    </div>

    <!-- 快速转账 -->
    <!-- 记录 -->
    <div :class="['recoadMain',showrecoad]">
      <div class="headeinfo">
        <div class="fl selectime" @click="selectime">
          <span class="select">本月</span><span class="iconfont icon-jiantouxia"></span>
        </div>

        <!-- 选择器 -->
      <el-select v-model="recordVal" @change="getRecord(recordVal,null,null)" >
          <el-option
              v-for="item in options"
              :key="item.recordKey"
              :value="item.recordVal">
          </el-option>
      </el-select>


        <div class="fr infotex">
        </div>
      </div>
      <ul class="rlist">
        <li v-for="item in list">
          <div class="watelicon"></div>
          <div class="fl span">
            <span  v-if="item.props == 'chess'  "   style="font-weight: normal;color: #252525;" v-text="item.type=='in'? '钻石钱包→开元钱包':'开元钱包→钻石钱包'"></span>
            <span v-if="item.props == 'ticket'  " style="font-weight: normal;color: #252525;" v-text="item.type=='in'? '钻石钱包→福彩钱包':'福彩钱包→钻石钱包'"></span>
            <!-- <span v-if="item.props == 'ig'  " style="font-weight: normal;color: #252525;" v-text="item.type=='in'? '钻石钱包→爱棋牌钱包':'爱棋牌钱包→钻石钱包'"></span> -->
            <span v-if="item.props == 'leg'  " style="font-weight: normal;color: #252525;" v-text="item.type=='in'? '钻石钱包→乐游钱包':'乐游钱包→钻石钱包'"></span>
            <span v-if="item.props == 'withdraw'  " style="font-weight: normal;color: #252525;" v-text=" '提现状态：' + item.status"></span>
            <!-- <span v-if="list.props == withdraw "></span> -->
            <span  v-text="item.props == 'withdraw' ? item.created_at :  item.time "></span>
          </div>
           <!-- <span  v-if="list.props == null" class="fr" v-text="item.amount" :class="['priceN',pricecolor,item.color]"></span> -->
           <span v-if="item.props !== 'withdraw' " class="fr"  v-text="item.amount" :class="['priceN',pricecolor,item.color]"></span>
           <span  v-else class="fr" v-text="item.amount" :class="['priceN',pricecolor,item.color]"></span>

          </li>
      </ul>
    </div>
    <!-- 记录 -->
    <!-- 时间选择 -->
    <div :class="['pickuptime',showpickuptime]">
       <div class="pickheader">
         <span class="fl" @click="cancle">取消</span>
         <span class="fontSize5">选择时间</span>
         <span  class="fr" @click="getRecord(recordVal,start_time,end_time)">完成</span>
       </div>
       <div class="pickt">
         <span class="fl startime" @click="Pickerstartime" v-text="start_time"></span>
         <span class="fontSize5">至</span>
         <span class="fr endtime" @click="Pickerendtime" v-text="end_time"></span>
       </div>
       <div class="datepicker">
         <mt-datetime-picker
            ref="picker"
            type="date"
            year-format="{value}"
            month-format="{value}"
            date-format="{value}"
            v-model="pickerValue"
            @confirm="handleConfirm">
          </mt-datetime-picker>
       </div>
    </div>
    <!-- 时间选择 -->

  </div>
</body>
<!-- <span id="api_token" class="dsn">af343e2197bbfc802e741cc26e67d021</span> -->
</html>
<script  charset="utf-8">
    var vm=new Vue({
        data() {
            return {
              loading:false,
              // 棋牌金额
              options:[
                {recordKey: "chess", recordVal: '开元记录'},
                {recordKey: "ticket" ,recordVal: '福彩记录'},
                // {recordKey: "ig" ,recordVal: '爱棋牌记录'},
                {recordKey: "leg" ,recordVal: '乐游记录'},
                {recordKey: "withdraw" ,recordVal: '提现记录'},
                ],
              recordVal:'开元记录',
              recordKey:'',
              showCopyAlert:'dsn',
              api_token:'',
              ip:window.location.origin,
              // ip:'http://testgame.vbo0.com',
              activeNames: '',
              transtxet:"转入",
              trans:false,
              thistime:'',
              pickerValue:'',
              pricecolor:'',
              showchange:'dsn',
              showrecoad:'dsb',
              showpickuptime:'dsn',
              backgroundchange:'dark',
              backgroundrecoad:'',
              select:0,
              copyAlert:false,
              list:[],//转账列表
              page:1,
              page_size:100,
              start_time:'',
              end_time:'',
              num:0,
              alertText:'',
              // 钱包页，钱包金额转换
              transmoney:"",
              showWallet:"dsn",

              isActive:0,
              switchmesk:"dsn",
              chooseIndex:0,
              showgoback:true,
            }
        },
        el: '#app',
        components: {},
        methods: {

            // 公用请求
          network(url, data, fun) {
            if (!data) {
              $.ajax({
                type: "GET",
                url:  url,
                headers: {
                  'X-Requested-With': 'XMLHttpRequest'
                },
                ContentType: "application/x-www-form-urlencoded",
                dataType: "json",
                success: function(data) {
                  fun(data)
                },
                error: function(data) {
                  fun(data)
                }
              });
            } else {
              $.ajax({
                type: "POST",
                url:  url,
                data: data,
                headers: {
                  'X-Requested-With': 'XMLHttpRequest'
                },
                ContentType: "application/x-www-form-urlencoded",
                dataType: "json",
                success: function(data) {
                  fun(data)
                },
                error: function(data) {
                  // fun(data)
                }
              });
            }
          },
          // 页面跳转获取token
          getApiToken(){
            let ip = window.location.href
            this.api_token = ip.split("=")[1]
            let type=window.location.href
            type.split("&")[1]?this.showgoback=false:this.showgoback=true
          },
          getRecord(command,startTime,endTime){

            let chess;
            let ticket;
            let recordAip;

            if(command === "提现记录"){
              command = "withdraw"
              recordAip = "record"
            }else{
              if(command === "开元记录"){
                recordAip = "transferRecord/chess"
              }
              if(command === "福彩记录"){
                recordAip = "transferRecord/ticket"

              }
              if(command === "爱棋牌记录"){
                recordAip = "transferRecord/ig"

              }
              if(command === "乐游记录"){
                recordAip = "transferRecord/leg"

              }
                command = "game"
            }
            this.loading=true
            let ip=this.ip
            let api_token=this.api_token
            let page=this.page
            let page_size=this.page_size
            let start_time = this.start_time
            let end_time = this.end_time
            if(startTime == null ||  endTime == null)
            {
                this.network(ip+"/api/"+ command +"/" + recordAip  + "?api_token="+api_token+'&page='+page+'&page_size='+page_size, null, res=>{
                  if(res.status==0){
                    this.loading=false
                    this.list=res.data.list
                    let list=res.data.list
                    for(let i=0;i<list.length;i++){
                      if(recordAip == "record"){
                        list[i].props = command
                        if(list[i].status=="待处理"){
                          list[i].color='black'
                        }
                        else if(list[i].status=="已拒绝"){
                          list[i].amount='-'+list[i].amount
                          list[i].color='blue'

                        }
                        else{
                          list[i].amount='+'+list[i].amount
                          list[i].color='blue'
                        }
                      }
                      else{
                        list[i].props = recordAip.split("/")[1]
                        if(list[i].type=="in"){
                          list[i].amount='-'+list[i].amount
                          list[i].color='black'
                        }
                        else{
                          list[i].amount='+'+list[i].amount
                          list[i].color='blue'
                        }

                      }
                    }

                  this.list=list
                  }else{
                      this.loading=false
                      this.alertText=res.message
                      this.imgsrc='http://play.zdzlw.com/img/v/vapp/gamelist/faild.png'
                      this.showCopyAlert="dsb"
                  }
              })
            }
            else {
              this.network(ip+"/api/"+ command +"/" + recordAip  + "?api_token="+api_token+'&page='+page+'&page_size='+page_size + '&start_time='+start_time+'&end_time='+end_time, null, res=>{
                  if(res.status==0){
                    this.loading=false
                    this.list=res.data.list
                    let list=res.data.list
                    for(let i=0;i<list.length;i++){
                      if(recordAip == "record"){
                        list[i].props = command
                        if(list[i].status=="待处理"){
                          list[i].color='black'
                        }
                        else if(list[i].status=="已拒绝"){
                          list[i].amount='-'+list[i].amount
                          list[i].color='blue'

                        }
                        else{
                          list[i].amount='+'+list[i].amount
                          list[i].color='blue'
                        }
                      }
                      else{
                        list[i].props = recordAip.split("/")[1]
                        if(list[i].type=="in"){
                          list[i].amount='-'+list[i].amount
                          list[i].color='black'
                        }
                        else{
                          list[i].amount='+'+list[i].amount
                          list[i].color='blue'
                        }

                      }
                    }

                  this.list=list
                  this.cancle()
                  }
                  else{
                      this.loading=false
                      this.alertText=res.message
                      this.imgsrc='http://play.zdzlw.com/img/v/vapp/gamelist/faild.png'
                      this.showCopyAlert="dsb"
                  }
              })

            }

          },

          change(){
            this.backgroundchange=""
            this.backgroundrecoad="dark"
            this.showchange="dsb";
            this.showrecoad="dsn"
            this.showpickuptime="dsn"
            let ip =this.ip
            let api_token = this.api_token
            window.location.href = ip + "/api/game/wallet?api_token=" + api_token
          },
          recoad(){//记录
            this.backgroundchange="dark"
            this.backgroundrecoad=""
            this.showchange="dsn";
            this.showrecoad="dsb"
            this.showpickuptime="dsn"
          },
          selectime(){//选择时间按钮
            this.showpickuptime="moveup"
          },
          Pickerstartime() {//起止时间
            this.$refs.picker.open();
            this.thistime="start_time"
          },
          Pickerendtime(){//结束时间
            this.$refs.picker.open();
            this.thistime="end_time"
          },
          handleConfirm(data){//选取时间
            var arr = [data.getFullYear(), data.getMonth() + 1, data.getDate()];
            arr=arr.join('-');
            let time=this.thistime
            if(time=="start_time"){
              this.start_time=arr
            }
            if(time=="end_time"){
              this.end_time=arr
            }
          },
          cancle(){//取消按钮
            this.showpickuptime="movedown"
            setTimeout(()=>{
              this.showpickuptime="dsn"
            },500)
          },
          goback(){//返回按钮
            let origin=window.location.origin
            let api_token=this.api_token
            window.location.href =origin+"/api/game/index?api_token="+api_token;
          },

        },
        updated() {

        },
        created() {
          this.getApiToken()
          this.getRecord(this.options[0].recordVal,null,null)

            // this.gameRecord()//游戏记录
            //取当前时间
            var data = new Date();
            var today = data.toLocaleString()
            var before = data-1000*60*60*24
            var beforeDate = new Date(before)
            before = beforeDate.toLocaleString()
            today =  today.split("\ ")[0]
            before =  before.split("\ ")[0]
            today = today.replace(/\//g,"-")
            before = before.replace(/\//g,"-")
            this.end_time=today
            this.start_time=before

        },
    })
</script>
