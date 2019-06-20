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
  <script src="https://cdn.bootcss.com/vue/2.6.10/vue.min.js" charset="utf-8"></script>
  <!-- <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script> -->
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
    .main{margin-top: 0.1rem;font-size: 0.4rem;}
    .main h2{text-align: center;}
    .main p{text-align: left;height: 0.6rem;line-height: 0.6rem;margin: 0.15rem 0rem;}
    .header{background: #fff;width: 100%;}
    .header .table li{display: inline-block;height:1.8rem;line-height: 1.8rem;padding:0rem 0.3rem;}
    .header .table .goback{background:url("http://play.zdzlw.com/img/v/vapp/gamelist/goback.png") no-repeat;display: inline-block;width: 1.5rem;height: 1.5rem;background-size: 100%;}
    .header .table .change{width: 0.8rem;height: 0.8rem;line-height: 0.8rem !important;font-size: 0.4rem;background: #5bbae8;color: #fff;background-size: 100%;}
    .header .table .recoad{width: 0.8rem;height: 0.8rem;line-height: 0.8rem !important;font-size: 0.5rem;background-size: 100%;color: #fff;background: #f3b495;}
    .header .table{width: 100%;}
    .priceList{margin:1vw 2vw;}
    .priceList li{display: inline-block;width: 18.5vw;height: 1.2rem;  line-height: 1.2rem;background: #fff;border: 1.2px solid #f6f6f6;text-align: center;}
    .priceList .active{background: #ff0556;color:#fff;}
    .priceList li:last-child{width:99%;}
    .surebtn{font-size: 0.5rem;height: 1.3rem; padding: 0.01vw; text-align: center;color: #fff;background: linear-gradient(to right, #fe548b , #ff0758);border: none;outline: none;border-radius: 0.1rem;width: 9.5rem;margin: 4vw auto;display: inherit;}
    .surebtn:active{background: #5bbae8}
    .list li,.main .li{height: 1.1rem;line-height: 1.1rem;background: #fff;padding:0rem 0.3rem;margin-top: 0.05rem;}
    .main .li{text-align: center;margin: 0;color: #827c7c;}
    .main .li button{width: 2.5rem;height: 0.8rem;}
    .main .li .title{padding-left:0.35rem;color:#827c7c;font-size: 0.36rem;}
    .main .li .info{font-size: 0.3rem;color: #c2c0c0;}
    .main .priceNum{font-size:0.6rem;color:#ff0556;font-weight: bold; padding-right: 0.2rem}
    .el-button{padding: 5px 9px;margin-top: 0.15rem;margin-left: 0.5rem;}
    .el-button--danger.is-plain{color: #ff0556;background: #fff;border-color: #ff0556;}
    .el-button--danger .is-plain:active,.el-button--danger.is-plain:focus, .el-button--danger.is-plain:hover{background: #fff;border-color: #fbc4c4;}
    .paink{color:#ff0556; padding-right: 0.2rem}
    .title{font-size:0.35rem;color: #827c7c;padding-left: 0.35rem;}
    .header .table .boxx{position: relative;top: 0.3rem;font-size: 0.35rem;color: #c6c6c6;}
    .header .table .boxx span{display: block;line-height: normal;text-align: center;}
    .icon-shuaxin{padding-right:0.2rem; font-size: 0.5rem}
    .watelicon{width: 0.8rem;height: 0.8rem;float: left;background: url("http://play.zdzlw.com/img/v/vapp/gamelist/walet.png")no-repeat;background-size: 100%;margin: 0rem 0.25rem;margin-top: 0.45rem;}
    .headeinfo{height: 1.2rem;line-height: 1rem;background: #f6f6f6;padding: 0rem 0.4rem;position: fixed;top: 1.7rem;width: 100%;}
    .span span{display: block;height: 0.5rem;color: #827c7c;}
    .span{width: 5rem;line-height: 1.2rem;}
    .priceN{margin-top: 0.5rem;}
    .infotex{height: 1rem;margin: 0.2rem 0.8rem;}
    .infotex p{height: 0.45rem;line-height: 0.45rem;}
    .dark{background: #ddd !important;}
    .inputText {border: 0.02rem solid #b5b5b5;width: 6rem;-webkit-appearance: none;height: 1rem;color: #000; border: none ; font-size: 0.8rem; border: none ;  font-size: 0.8rem; outline: none;}
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
    .copyAlert .btnsure{background: linear-gradient(to right, #fe598e , #ff0858);color: #fff;height: 0.8rem;width: 2.5rem;border-radius: 0.1rem;border: 0.1rem solid #ff0556;}
    .copyAlert .btn{background: linear-gradient(to right, #fe598e , #ff0858);border: none;}
    .cover{height: 100%;  width: 100%;background: rgba(0, 0, 0, 0.5);position: fixed;top: 0; z-index: 20}
    .copyAlert .centen{background: #fff;position: relative;top: -1rem;height: 4.5rem;width: 6.5rem;border-radius: 0.1rem; z-index: 15}
    .copyAlert .centen .ShowBtnGoGame{position: absolute;bottom: 0;width: 100%;font-size: 0;}
    .copyAlert .centen .ShowBtnGoGame button{width: 50%;border-radius: 0;height: 1rem;}
    .container{width: 6.5rem;margin: 65% auto;}
    .titleText{color:#000;height: 2rem;line-height: 2rem;position: relative;top: -2rem;width: 5rem;margin: 0 auto;}
    .ShowBtn{position: absolute;bottom: 0.3rem;width: 100%;font-size: 0;}
    .el-button--danger .is-plain:active, .el-button--danger.is-plain:focus, .el-button--danger.is-plain:hover{color: #ff0556;background: #fff;border-color: #ff0556;}
    /* 钱包页新样式 */
    .moneyTrans{height: 4.2rem;background: #fff;padding: 0.4rem 0.3rem;}
    .moneyTrans  .transIcon{width: 0.7rem;height: 0.7rem;display: inline-block;margin-left: 0.2rem;}
    .transfer div{margin-left: 1.3rem;position: relative;top:0.2rem;}
    .transfer:active  {background: #5bbae8;color: #fff;}
    .transfer{height: 1.5rem;width:3.5rem;border:0.02rem solid #b5b5b5;border-radius: 8px;float: left; font-size: 0.36rem}
    .transfer p{text-align: center;}
    .rt{float: right;}
    .transfont{height: 1.5rem;float: left;margin-left: 0.8rem;}
    .transfont p {text-align: center;border-radius: 5px;}
    .transfont .icon-shuangjiantou{width: 0.8rem;height: 0.8rem;font-size: 0.4rem;background: #5bbae8;color: #fff;background-size: 100%;position: relative;top: 0.1rem;margin: 0 auto;}
    .transfont .icon-shuangjiantou:before{position: relative;top: 0.1rem;}
    .pickWalletHead{text-align: center;height: 1.3rem;line-height: 1.3rem;  border-bottom: 0.02rem solid #ddd;}
    .cancleWallet{margin-left: 0.4rem;color: #6f6f6f;font-size: 0.6rem;}
    .walletContent{text-align: left;height: 1.3rem;line-height: 1.3rem;padding-left: 0.2rem}
    .walletContent:active{background: #5bbae8;color: #fff;}
    .walletContent img{padding-left: 0.5rem;margin-top: 0.2rem;width: 0.7rem;position: relative;top: 0.2rem;}
    .walletIcon{margin-left: 0.2rem;margin-top: 0.2rem;width: 0.9rem;}
    .liContent{margin-left: 0.8rem;line-height :0.5rem;position: relative;}
    .wltbottom{border-bottom: 0.03rem solid #ddd;position: relative;left: 2.3rem;}
    .inputframe{margin-top: 0.3rem;border: 0.02rem solid #ddd;border-radius: 8px;width: 100%;height: 2rem;}
    .icon-zhengque{font-size: 0.7rem;color: #ff0556;margin-right: 0.5rem;float: right;}/* 选中钱包对勾 */
    .mesk{position: fixed;top: 0;right: 0;bottom: 0;left: 0;background-color: rgba(0,0,0,.6);z-index: 20;}/* 弹出遮罩 */
    .inputmoney{width:4rem; margin-left: 0.2rem;position: relative;font-size: 0.38rem;left: 0.3rem;}/* 输入转换金额 */
    .icon-renmingbi{font-size: 0.55rem;color: #b5b5b5;position: relative;top: -0.05rem;margin-left: 0.2rem;}
    .moveup{-webkit-animation: timeselect 0.5s;  animation: timeselect 0.5s;}/* 点击转入上浮弹框 */
    .pickupwallet{position: fixed;bottom: 0rem;width: 100%;background: #f5f5f5; z-index: 50}
    .icon-jiantou-right:before{ color: #6f6f6f }
    .el-dropdown-link{position: relative; left: 0.4rem; width:0.2rem; color: #000; top: 0.15rem}
    .main .li .withDraw {width: 1.4rem;margin-left: 0;}/*提现按钮*/
    .title_c{color: #ff0556;}
  </style>
  <script>
      !function(N,M){function L(){var a=I.getBoundingClientRect().width;a/F>1024&&(a=1024*F);var d=a/10;I.style.fontSize=d+"px",D.rem=N.rem=d}var K,J=N.document,I=J.documentElement,H=J.querySelector('meta[name="viewport"]'),G=J.querySelector('meta[name="flexible"]'),F=0,E=0,D=M.flexible||(M.flexible={});if(H){/*console.warn("将根据已有的meta标签来设置缩放比例");*/var C=H.getAttribute("content").match(/initial\-scale=([\d\.]+)/);C&&(E=parseFloat(C[1]),F=parseInt(1/E))}else{if(G){var B=G.getAttribute("content");if(B){var A=B.match(/initial\-dpr=([\d\.]+)/),z=B.match(/maximum\-dpr=([\d\.]+)/);A&&(F=parseFloat(A[1]),E=parseFloat((1/F).toFixed(2))),z&&(F=parseFloat(z[1]),E=parseFloat((1/F).toFixed(2)))}}}if(!F&&!E){var y=N.navigator.userAgent,x=(!!y.match(/android/gi),!!y.match(/iphone/gi)),w=x&&!!y.match(/OS 9_3/),v=N.devicePixelRatio;F=x&&!w?v>=3&&(!F||F>=3)?3:v>=2&&(!F||F>=2)?2:1:1,E=1/F}if(I.setAttribute("data-dpr",F),!H){if(H=J.createElement("meta"),H.setAttribute("name","viewport"),H.setAttribute("content","initial-scale="+E+", maximum-scale="+E+", minimum-scale="+E+", user-scalable=no"),I.firstElementChild){I.firstElementChild.appendChild(H)}else{var u=J.createElement("div");u.appendChild(H),J.write(u.innerHTML)}}N.addEventListener("resize",function(){clearTimeout(K),K=setTimeout(L,300)},!1),N.addEventListener("pageshow",function(b){b.persisted&&(clearTimeout(K),K=setTimeout(L,300))},!1),"complete"===J.readyState?J.body.style.fontSize=12*F+"px":J.addEventListener("DOMContentLoaded",function(){J.body.style.fontSize=12*F+"px"},!1),L(),D.dpr=N.dpr=F,D.refreshRem=L,D.rem2px=function(d){var c=parseFloat(d)*this.rem;return"string"==typeof d&&d.match(/rem$/)&&(c+="px"),c},D.px2rem=function(d){var c=parseFloat(d)/this.rem;return"string"==typeof d&&d.match(/px$/)&&(c+="rem"),c}}(window,window.lib||(window.lib={}));
  </script>
</head>
<body>
  <span id="api_token" class="dsn">{{$api_token}}</span>
  <!-- <span id="api_token" class="dsn">af343e2197bbfc802e741cc26e67d021</span> -->
  <div id="app" v-loading="loading" >
    <div :class="switchmesk"></div>
    <div class="header">
       <ul class="table">
         <li class="fl" style="width:1.9rem;"><span class="goback" @click="goback" v-show="showgoback"></span></li>
         <li style="margin-left:0.7rem">
           <div class="boxx">
               <span class="change iconfont icon-shuangjiantou"></span><span class="title_c">转换</span>
            </div>
         </li>
         <li style="padding:0">
            <div>
                <span  style="font-stretch:condensed;font-size:1rem ; font-weight: 100; display: inline-block; color:#ededed;" >|</span>
             </div>
          </li>
         <li>
           <div class="boxx">
               <span class="recoad iconfont icon-shouzhizhangben dark" @click="recoad"></span><span>记录</span>
            </div>
         </li>
       </ul>
    </div>
    <!-- 快速转账 -->
    <div class="main">
       <p class="li ">
         <span class="title">钻石钱包</span>
         <span class="info">可投注彩票类游戏</span>
         <span class="iconfont icon-shuaxin paink fr" @click="getData(null)"></span>
       </p>
       <p class="li" style="border-bottom: 0.05rem dashed #ededed;">
           <span class="fl"></span>
           <el-button type="danger" plain class="fr box" @click="walletToDiamond('钱包=>钻石')">一键收回</el-button>
           <!-- <el-button type="danger" plain class="fr box withDraw" @click="WithDrawMoney">提现</el-button> -->
           <span  class="priceNum fr" v-text="diamond"></span>
       </p>

       <p class="li" v-for="item in walteList">
          <span class="fl" class="itemclass" v-text="item.title"></span>
          <el-button type="danger" plain class="fr box" @click="DiamondToWallet(item)">一键转入</el-button>
          <span class="paink fr" v-text="item.game_money"></span>
       </p>
       <p>
         <span class="title">请选择额度转换平台</span>
         <span style="font-size: 0.36rem;">(10钻石=1钱包金币)</span>
       </p>
      <div class="moneyTrans">
          <div>
            <div class="transfer" @click="transferOut" >
              <div>转出</div>
              <p>
                <span v-text="walletOut"></span>
                <span class="iconfont icon-jiantou-right"></span>
              </p>
            </div>
            <div class="transfont">
              <p class="iconfont icon-shuangjiantou"></p>
              <p>转换</p>
            </div>
            <div class="transfer rt" @click="transferIn">
              <div>转入</div>
              <p>
                <span v-text="walletIn"></span>
                <span class="iconfont icon-jiantou-right"></span>
              </p>
            </div>
          </div>
          <div class="fl inputframe">
            <p class="inputmoney">输入转换金额</p>
            <p >
              <span :class="['icon', 'iconfont','transIcon',rmbicon]" ></span>
            <span>
                <input  class="inputText" type="number" name="" v-model="JinE"   placeholder="请输入转账金额"  @focus="removeIcon"  @blur="addIcon">
            </span>
            </p>
          </div>
       </div>
      <!-- 金额转出弹框  -->
      <div :class="['pickupwallet',showWallet]">
        <div class="pickWalletHead">
          <span class="fl cancleWallet icon iconfont icon-closedx" @click="cancleWallet"></span>
          <span>选择转换钱包</span>
        </div>
        <ul>
          <li v-for="(item,index) in ListWallet"  class="walletContent" @click="walletChoose(item,index)">
            <img :src="item.img_url" alt="">
            <span class="liContent" v-text="item.title" ></span>
            <span :class="['icon','iconfont',{'icon-zhengque':index==isActive}]" ></span>
            <div class="wltbottom"></div>
          </li>
        </ul>
        <div class="datepicker"></div>
      </div>
      <!-- 金额转出弹框  -->
       <p>
         <button type="button" name="button" class="surebtn"  @click="Casher">确认</button>
       </p>
    </div>
    <!-- 快速转账 -->
    <!-- 所有提示弹出框 -->
    <section  :class="['cover copyAlert',showCopyAlert]">
        <div class="container">
          <div class="centen">
            <img :src="imgsrc" alt="" class="title">
            <p class="titleText"><span v-text="alertText"></span></p>
            <p v-if="ShowBtnGoGame" class="ShowBtnGoGame">
              <button type="button" name="button" class="btnsure btn" @click="btnsure">确定</button>
              <button type="button" name="button" class="btnsure btn" @click="goGame">去游戏</button>
            </p>
            <p v-if="ShowBtnSuer" class="ShowBtn">
              <button type="button" name="button" class="btnsure btn" @click="btnsure">确定</button>
            </p>
          </div>
        </div>
      </section>
    <!-- 所有提示弹出框 -->
  </div>
</body>
</html>
<script  charset="utf-8">
    var vm=new Vue({
        data() {
            return {
              imgsrc:'',
              ShowBtnGoGame:false,
              ShowBtnSuer:false,
              JinE:null,
              loading:false,
              diamond:'',
              showCopyAlert:'dsn',
              api_token:document.getElementById("api_token").innerText,
              ip:window.location.origin,
              // ip:'http://testgame.vbo0.com',
              copyAlert:false,
              num:0,
              alertText:'',
              showWallet:"dsn",
              walletOut:"",
              walletIn:"",
              isActive:0,
              switchmesk:"dsn",
              chooseIndex:0,
              rmbicon:"icon-renmingbi",//人民币字体图标
              walteList:[],//钱包列表
              ListWallet:[],//上划弹框钱包列表
              method:"in",
              type:'',
              showgoback:true,
            }
        },
        el: '#app',
        components: {},
        methods: {
          transferOut(){//转出------------------------------------------------------------------------------------------------------------------------------------------
            this.method="out"
            this.showWallet="moveup"
            this.switchmesk = "mesk"
            document.getElementsByClassName("walletContent")[0].style="opacity: 1; pointer-events: auto;";
          },
          transferIn(){//转入------------------------------------------------------------------------------------------------------------------------------------------
            this.method="in"
            this.showWallet="moveup"
            this.switchmesk = "mesk"
            document.getElementsByClassName("walletContent")[0].style="opacity: 0.5; pointer-events: auto;";
            if(this.walletIn==this.ListWallet[0].title){
              this.isActive = 1
            }
          },
          walletChoose(item,index){//上划弹框互斥逻辑-------------------------------------------------------------------------------------------------------
             if(this.method=="out"){
               this.isActive = index//选中效果
               this.walletOut =item.title
               if(this.walletOut==this.ListWallet[0].title){
                 this.walletIn=this.ListWallet[1].title
                 this.type=this.ListWallet[1].type
               }else{
                 this.walletIn=this.ListWallet[0].title
                 this.type=this.ListWallet[index].type
               }
               this.cancleWallet()
             }
             if(this.method=="in"){
               this.walletIn =item.title
               if(this.walletIn==this.ListWallet[0].title){
                 this.isActive = 1
                 this.walletOut=this.ListWallet[1].title
                 this.type=this.ListWallet[1].type
               }else{
                 this.isActive = index
                 this.walletOut=this.ListWallet[0].title
                 this.type=this.ListWallet[index].type
               }
               this.cancleWallet()
             }
          },
          network(api, data, fun) { // 公用请求---------------------------------------------------------------------
            let ip = this.ip
            let url = ip + api
            let api_token = this.api_token
            if (!data) {
              var xhr = new XMLHttpRequest();
              xhr.open("GET", url);
              xhr.responseType = 'json';
              xhr.setRequestHeader('api_token', api_token);
              xhr.onload = function() {fun(xhr.response)};
              xhr.onerror = function() {console.log("error");};
              xhr.send();
            } else {
              var xhr = new XMLHttpRequest();
              xhr.open("POST", url);
              xhr.responseType = 'json';
              xhr.setRequestHeader('api_token', api_token);
              xhr.onload = function() {fun(xhr.response)};
              xhr.onerror = function() {console.log("error")};
              xhr.send(JSON.stringify(data)); //需要先转成字符串再发送
            }
          },
          getData(gamename){//获取参数------------------------------------------------------------------------------
            let type=window.location.href
            type.split("&")[1]?this.showgoback=false:this.showgoback=true
            this.loading=true
            let gametype
            if(gamename){
              gametype="/"+gamename
            }else{
              gametype=""
            }
            this.network("/api/game/balance"+gametype, null, res=>{
                if(res.status==0){
                  this.loading=false
                  let walteList=res.data
                  let arr=[]//展示余额列表
                  let ary=[]//展示弹框钱包列表
                  for(let i in walteList){
                    i=="deposit"?(walteList[i]={title:"钻石钱包",img_url:"http://play.zdzlw.com/img/v/vapp/gamelist/damomoney.png",game_money:res.data.deposit,type:"deposit"}):null
                    i=="avia"?(walteList[i].title="泛亚电竞",walteList[i].img_url="http://play.zdzlw.com/img/v/vapp/gamelist/qipai_icon.png",walteList[i].type="avia"):null
                    i=="leg"?(walteList[i].title="乐游钱包",walteList[i].img_url="http://play.zdzlw.com/img/v/vapp/gamelist/leggame/lyqp.png",walteList[i].type="leg"):null
                    i=="ticket"?(walteList[i].title="福彩钱包",walteList[i].img_url="http://play.zdzlw.com/img/v/vapp/gamelist/lottery_icon.png",walteList[i].type="ticket"):null
                    i=="chess"?(walteList[i].title="开元钱包",walteList[i].img_url="http://play.zdzlw.com/img/v/vapp/gamelist/qipai_icon.png",walteList[i].type="chess"):null
                    i=="ig"?(walteList[i].title="爱棋牌钱包",walteList[i].img_url="http://play.zdzlw.com/img/v/vapp/gamelist/iggame/ig_logo_2.png",walteList[i].type="ig"):null
                    i!="deposit"&&i!="avia"&&i!="ig"?arr.push(walteList[i]):null//去除钻石和泛亚爱棋牌
                    i!="deposit"&&i!="avia"&&i!="ig"?ary.push(walteList[i]):null//去除钻石和泛亚爱棋牌
                    this.diamond=res.data.deposit.game_money//钻石数
                  }
                  this.walteList=arr//展示余额列表
                  this.type=arr[0].type
                  let obj={title:"钻石钱包",img_url:"http://play.zdzlw.com/img/v/vapp/gamelist/damomoney.png",game_money:res.data.deposit.game_money}
                  this.ListWallet=ary
                  this.ListWallet.unshift(obj)
                  this.walletOut=this.ListWallet[0].title
                  this.walletIn=this.ListWallet[1].title
                  this.JinE=null
                }else{
                  this.JinE=null
                  setTimeout(()=>{
                    this.alertText=res.message
                    this.imgsrc='http://play.zdzlw.com/img/v/vapp/gamelist/faild.png'
                    this.showCopyAlert="dsb"
                  },30000)
               }
            })
          },
          DiamondToWallet(item){//一键转入----------------------------------------------------------------------------
            // console.log(item.type)
            this.method="in"
            this.JinE=this.diamond
            this.type=item.type
            this.Casher()
          },
          removeIcon(){//输入框失去焦点---------------------------------------------------------------------------------
            this.rmbicon=""
          },
          addIcon(){//输入框获取焦点---------------------------------------------------------------------------------
            this.rmbicon="icon-renmingbi"
          },
          Casher(){//确认转账按钮--------------------------------------------------------------------------------------------------------------------------
            let method=this.method
            let JinE=this.JinE
            let type=this.type
            method=="out"?JinE=JinE*10:JinE=JinE
            JinE==null||JinE==""?JinE=0:JinE=parseInt(JinE)
            if(JinE<10||typeof(JinE)!="number"||JinE==null||JinE==""){
              this.ShowBtnSuer=true
              this.ShowBtnGoGame=false
              this.imgsrc='http://play.zdzlw.com/img/v/vapp/gamelist/faild.png'
              this.showCopyAlert="dsb"
              this.fazhi="dsn"
              this.alertText="输入金额不得小于10"
              this.loading=false
            }else{
              this.loading=true
              this.network("/api/game/transfer/"+type+"?method="+method+"&money="+JinE, null, res=>{// 判断当前由钻石向哪种钱包转账
                if(res.status==0){
                   this.loading=false
                   this.imgsrc='http://play.zdzlw.com/img/v/vapp/gamelist/coins.png'
                   this.showCopyAlert="dsb"
                   this.alertText=res.message
                   this.num=0
                   this.ShowBtnGoGame=true
                   this.ShowBtnSuer=false
                   this.getData()
                }else{
                  this.loading=false
                  this.getData()
                  setTimeout(()=>{
                    this.alertText=res.message
                    this.ShowBtnGoGame=false
                    this.ShowBtnSuer=true
                    this.imgsrc='http://play.zdzlw.com/img/v/vapp/gamelist/faild.png'
                    this.showCopyAlert="dsb"
                  },1000)
                }
              })
            }
          },
          goGame(){//直接进入游戏--------------------------------------------------------------------------------------------------------------------------
            let type=this.type
            this.network("/api/game/login/"+type, null, res=>{
              if(res.status==0){
                window.location.href=res.url
              }else{
                setTimeout(()=>{
                  this.ShowBtnSuer=true
                  this.ShowBtnGoGame=false
                  this.alertText=res.message
                  this.imgsrc='http://play.zdzlw.com/img/v/vapp/gamelist/faild.png'
                  this.copyAlert=true
                },30000)
              }
            })
          },
          walletToDiamond(method){//一键回收 钱包转钻石---------------------------------------------------------------------------------
            this.loading=true
            this.network("/api/game/oneKeyTransfer", null, res=>{
              if(res.status==0){
                 this.loading=false
                 this.imgsrc='http://play.zdzlw.com/img/v/vapp/gamelist/coins.png'
                 this.showCopyAlert="dsb"
                 this.alertText=res.message
                 this.num=0
                 this.isFinish=true
                 this.ShowBtnGoGame=false
                 this.ShowBtnSuer=true
                 this.getData()
              }else{
                this.loading=false
                setTimeout(()=>{
                  this.alertText=res.message
                  this.ShowBtnGoGame=false
                  this.ShowBtnSuer=true
                  this.imgsrc='http://play.zdzlw.com/img/v/vapp/gamelist/faild.png'
                  this.showCopyAlert="dsb"
                  this.isFinish=true
                },10000)
              }
            })
          },
          cancle(){//取消按钮
            this.showpickuptime="movedown"
            setTimeout(()=>{
              this.showpickuptime="dsn"
            },1000)
          },
          recoad(){//跳转去记录页面
            let ip =this.ip
            let api_token = this.api_token
            let type=window.location.href
            if(type.split("&")[1]){
                window.location.href = ip + "/api/game/recordH5?api_token=" + api_token+"&"+type.split("&")[1]
            }else{
                window.location.href = ip + "/api/game/recordH5?api_token=" + api_token
            }
          },
          WithDrawMoney(){//去提现页面-----------------------------------------------------------------------------------------
            let ip =this.ip
            let api_token = this.api_token
            window.location.href = ip + "/api/withdraw/index?api_token=" + api_token
          },
          goback(){//返回按钮
            let origin=window.location.origin
            let api_token=this.api_token
            window.location.href =origin+"/api/game/index?api_token="+api_token;
          },
          btnsure(){//关闭弹框
             this.copyAlert=false
             this.showCopyAlert="dsn"
          },
          cancleWallet(){//上划弹框取消按钮
            this.showWallet="movedown"
            setTimeout(()=>{
              this.showWallet="dsn"
             this.switchmesk="dsn"
            },300)
          },
        },
        created() {
            this.getData()//初始调用一次
        },
    })
</script>
