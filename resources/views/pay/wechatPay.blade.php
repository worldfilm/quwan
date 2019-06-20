<!DOCTYPE html>
<html>
<head>
  <meta charset=utf-8>
  <meta name=viewport content="width=device-width,initial-scale=1">
  <!-- <link rel="shortcut icon" href=favicon.ico type=image/x-icon> -->
  <!-- <link rel="stylesheet" href="https://unpkg.com/element-ui/lib/theme-chalk/index.css"> -->
  <link rel="stylesheet" href="../../css/iconfont.css">
  <style media="screen">
  body {background:#fff;z-index:1;}
  * {margin: 0;padding: 0;font-family: 微软雅黑;}
  a {text-decoration: none;}
  input {outline: none}
  li {list-style: none;}
  #app{background-size: 100%;z-index:10;}
  .blod{font-weight: bold;}
  .pay{width: 100%;text-align: center;position: relative;padding-top: 1rem;}
  .pay img{width: 4rem;height: 4rem;padding:1rem 2rem;margin: 0 auto;}
  .pay p{height: 1rem;font-size:0.4rem;margin: 0 auto;line-height: 1rem;font-weight: bold;}
  .btn a{padding:0.3rem 0.6rem;border-radius: 0.15rem;color: #3a2f2f;background: #fff;outline: none;display: inline-block;border: 0.03rem solid #808080;}
  .red{color:#f11717;}
  .price{font-size:0.7rem;padding: 0rem 0.1rem;}
  .copybtn,.openWechat,.openAlipay,.surebtn,.custom{margin-left: 0.4rem;border: 0.02rem solid #00cc00;padding: 0.05rem 0.1rem;border-radius: 0.1rem;background: #00cc00;color: #fff;box-shadow: 0 3px 5px #3f7b38;}
  .copyAlert{width: 100%;height: 1.3rem;line-height: 1.3rem;color: #fff;background-color: rgba(0,0,0,.5);position: fixed;top: -1.3rem;left: 0;z-index: 15;overflow: auto;text-align: center;font-size: 0.4rem;-webkit-animation: hjTranslate 3s infinite;  animation: hjTranslate 3s infinite;}
  @-webkit-keyframes hjTranslate {
  0% {-webkit-transform: translateY(0%);transform: translateY(0%);}
  50% {-webkit-transform: translateY(100%);transform: translateY(100%);}
  70% {-webkit-transform: translateY(100%);transform: translateY(100%);}
  100% {-webkit-transform: translateY(0%);transform: translateY(0%);}
  }
  .openWechat,.openAlipay,.surebtn{padding: 0.1rem 0.2rem;}
  .openWechat{}
  .openAlipay{}
  .addwidth{width: 2.5rem;}
  .addpadding{padding: 0.1rem 0.2rem;}
  .addpaddingtop{padding-top: 1rem;}
  .addfontsize{font-size:0.5rem;}
  .surebtn{padding:0.15rem 0.8rem;}
  .addweight{font-weight: 400;}
  .Alert{height: 100%;width: 100%;position: fixed;top: 0;left: 0;background: rgba(35, 34, 34, 0.4);}
  .Alert .contain{position: relative;width: 95%;padding-bottom: 0.5rem;background: #eee;top: 56%;font-size: 0.4rem;font-weight: bold;margin: auto;-webkit-animation: Alerts 0.5s;  animation: Alerts 0.5s;}
  .Alert .contain .title{height: 0.1rem;line-height: 1rem;text-align: center;}
  .Alert .contain .title .closed{float: right;padding-right: 0.2rem;}
  .Alert .contain .content{text-align: center;text-align: center;padding: 0rem 0.2rem;line-height: 0.7rem;}
  .Alert .contain .content p{padding: 0.1rem 0rem;/*line-height: 0.6rem;*/text-align: left;}
  .Alert .contain .content ul{text-align: left;padding-left: 0.3rem;}
.Alert .contain .content .btn{width: 4rem;height: 1rem;font-size: 0.5rem;font-weight: bold;/*margin-top:0.15rem;*/}
  @-webkit-keyframes Alerts {
  0% {-webkit-transform: translateY(100%);transform: translateY(100%);}
  100% {-webkit-transform: translateY(0%);transform: translateY(0%);}
  }
  </style>
  <link href="{{ asset('css/layer.css')}}?v=2"  type="text/css" />
  <title></title>
  <script>
      !function(N,M){function L(){var a=I.getBoundingClientRect().width;a/F>1024&&(a=1024*F);var d=a/10;I.style.fontSize=d+"px",D.rem=N.rem=d}var K,J=N.document,I=J.documentElement,H=J.querySelector('meta[name="viewport"]'),G=J.querySelector('meta[name="flexible"]'),F=0,E=0,D=M.flexible||(M.flexible={});if(H){/*console.warn("将根据已有的meta标签来设置缩放比例");*/var C=H.getAttribute("content").match(/initial\-scale=([\d\.]+)/);C&&(E=parseFloat(C[1]),F=parseInt(1/E))}else{if(G){var B=G.getAttribute("content");if(B){var A=B.match(/initial\-dpr=([\d\.]+)/),z=B.match(/maximum\-dpr=([\d\.]+)/);A&&(F=parseFloat(A[1]),E=parseFloat((1/F).toFixed(2))),z&&(F=parseFloat(z[1]),E=parseFloat((1/F).toFixed(2)))}}}if(!F&&!E){var y=N.navigator.userAgent,x=(!!y.match(/android/gi),!!y.match(/iphone/gi)),w=x&&!!y.match(/OS 9_3/),v=N.devicePixelRatio;F=x&&!w?v>=3&&(!F||F>=3)?3:v>=2&&(!F||F>=2)?2:1:1,E=1/F}if(I.setAttribute("data-dpr",F),!H){if(H=J.createElement("meta"),H.setAttribute("name","viewport"),H.setAttribute("content","initial-scale="+E+", maximum-scale="+E+", minimum-scale="+E+", user-scalable=no"),I.firstElementChild){I.firstElementChild.appendChild(H)}else{var u=J.createElement("div");u.appendChild(H),J.write(u.innerHTML)}}N.addEventListener("resize",function(){clearTimeout(K),K=setTimeout(L,300)},!1),N.addEventListener("pageshow",function(b){b.persisted&&(clearTimeout(K),K=setTimeout(L,300))},!1),"complete"===J.readyState?J.body.style.fontSize=12*F+"px":J.addEventListener("DOMContentLoaded",function(){J.body.style.fontSize=12*F+"px"},!1),L(),D.dpr=N.dpr=F,D.refreshRem=L,D.rem2px=function(d){var c=parseFloat(d)*this.rem;return"string"==typeof d&&d.match(/rem$/)&&(c+="px"),c},D.px2rem=function(d){var c=parseFloat(d)/this.rem;return"string"==typeof d&&d.match(/px$/)&&(c+="rem"),c}}(window,window.lib||(window.lib={}));
  </script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.5.16/vue.js" charset="utf-8"></script>
  <script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
  <script src="{{ asset('js/layer.js')}}" type="text/javascript" ></script>
</head>
<body>
  <div id=app  :style="'height:'+ num+'px'">
    <div class="pay">
      <p>
        <span>请<span class="red" style="font-size:0.6rem">务必</span>支付：</span>
        <span class="red price" id="price">{{ $price }}</span>
        <span>元</span>
        <button type="button" name="button" class="copybtn" @click='copyMoney'>复制金额</button>
      </p>
      <p><span>否则无法自动到帐</span></p>
      <img src="{{ $url }}" alt="" class="QRcode">
      <p><span>常按二维码保存图片或截屏后，用<span class="red" v-text="methodsTxt"></span>支付</span></p>
      <p>
        <button type="button" name="button" class="openWechat addwidth" @click="goApp('wechat')">打开微信</button>
        <button type="button" name="button" class="openAlipay addwidth" @click="goApp('alipay')">打开支付宝</button>
      </p>
      <p class="addpaddingtop"><span>提示:充值不到帐,请</span><a class="copybtn addpadding  addweight" class="openAlipay" href="http://f18.livechatvalue.com/chat/chatClient/chatbox.jsp?companyID=870787&configID=75764&jid=2552196099">联系客服</a></p>
      <section class="Alert" v-show='Alert'>
        <div class="contain">
          <p class="title"> <span class="closed  icon iconfont icon-closedx" @click="closed"></span></p>
          <div class="content">
            <p class="addfontsize red"><span>警告：请务必支付 </span><span class="red price">{{ $price }}</span><span>元</span></p>
               <ul>
                 <li> 1、请截屏保存二维码</li>
                 <li>2、打开<span v-text="paywayname"></span>，选择扫一扫</li>
                 <li>3、选择右上角相册</li>
                 <li>4、选择保存的二维码图片</li>
               </ul>
            <!-- <p><span class="red blod addpadding addfontsize">{{ $price }}</span><span>元</span></p> -->
            <p class="red blod"><span>注意：请一定选择最新保存的二维码图片！</span> </p>
            <button type="button" name="button" class="surebtn btn" @click="surebtn">确定</button>
          </div>
        </div>
    	</section>
    </div>
    <section class="copyAlert" v-show='copyAlert'>
  			<p class="title"><span>复制成功,请支付</span><span class="red blod addpadding">{{ $price }}</span><span>元</span></p>
  	</section>

  </div>
</body>
</html>
<script  charset="utf-8">
var vm=new Vue({
  data() {
    return {
      num:'800',
      methodsTxt:'',
      copyAlert:false,
      Alert:false,
      payway:'',
      paywayname:'',
    }
  },
  el: '#app',
  components: {},
  methods: {
     copyMoney(){
       this.copyAlert=true
      var clipboard2 = new Clipboard('.copybtn', {
          target: function(e) {
            e=e.toString(e)
              return document.getElementsByClassName('price')[0];
          }
      });
      clipboard2.on('success', function(e) {
          clipboard2.destroy();
      });
      clipboard2.on('error', function(e) {
          clipboard2.destroy();
      });
    setTimeout(()=>{
        this.copyAlert=false
      },3000)
    },
    auto(){
      var price=document.getElementById("price").innerText;
      var oInput = document.createElement('input');
      oInput.value = price;
      console.log(price)
      document.body.appendChild(oInput);
      oInput.select(); // 选择对象
      document.execCommand("Copy"); // 执行浏览器复制命令
      oInput.className = 'oInput';
      oInput.style.display='none';
    },
    goApp(way){
      console.log(way)
      this.Alert=true
      if(way=="wechat"){
        this.paywayname="微信"
        this.payway="wechat"
      }
      if(way=="alipay"){
        this.paywayname="支付宝"
        this.payway="alipay"
      }
    },
    surebtn(){
      let way=this.payway
      if(way=="wechat"){
          window.location.href='weixin://';
      }
      if(way=="alipay"){
          window.location.href='//alipay.com';
      }
    },
    closed(){
      this.Alert=false
    }
 },
 mounted(){

 },
 updated() {

  },
  created() {
    let clientHeight=document.documentElement.clientHeight//浏览器高度
    this.num=clientHeight
    let methods=window.location.href.split("&")[window.location.href.split("&").length-1].split("=")[1]
    if(methods=='wechat'){methods='微信扫码'}
    if(methods=='jd'){methods='京东扫码'}
    if(methods=='alipay'){methods='支付宝扫码'}
    if(methods=='qq'){methods='qq扫码'}
    if(methods=='bank'){methods='银联扫码'}
    this.methodsTxt=methods
 },
})
</script>
