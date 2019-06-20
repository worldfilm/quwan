<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta name=viewport content="width=device-width,initial-scale=1,user-scalable=no">
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
  <link rel="stylesheet" href="../../css/iconfont3.css">
  <link rel="stylesheet" href="http://lib.baomitu.com/Swiper/4.0.2/css/swiper.min.css">
  <script src="http://lib.baomitu.com/Swiper/4.0.2/js/swiper.min.js" charset="utf-8"></script>
  <script src="https://cdn.bootcss.com/jquery/3.4.0/jquery.min.js"></script>
  <style>
      * {margin: 0;  padding: 0;  font-family: "微软雅黑";}
      html{background: #f7f7f7;}
      li {list-style: none;}
      a{text-decoration: none;color: #000;}
      .dsnone{display: none !important;}
      .dsblock{display: block !important;}
      #app{display: block;position: absolute;height: 100%;width: 100%;background: #f7f7f7;}
      .header-bar a{display: block;}
      .header-bar{position: fixed;top: 0;left: 0;width: 100%;height:11vw;line-height:11vw;text-align: center;font-size: 3vw;background: #000;z-index: 10;}
      .header-bar .title{color:#fff;}
      .header-bar  img{height: 7vw;padding-top: 2vw;padding-left: 2vw;}
      .iconfont .icon-jiantou {font-size: 0.3rem;transform: rotate(90deg);}
      .left-btn,.right-btn {position: absolute;top: 0;}
      .left-btn {left: 0;padding-left: 0.1rem;}
      .right-btn {right: 0;padding-right: 0.1rem;}
      .right-btn  img {height: 7vw;padding-top: 2vw;padding-right: 2vw;}
      /* 导航条 */
      .nav{height: 18vw; margin: 0 auto; width: 93%;background: #fff;border-radius: 2vw;position: relative;top: -2vw;text-align: center;margin-top: -1vw}
      .navlist{text-align: center; width:21vw;height: 18vw;display: inline-block;}
      .navlist .title{text-align: center; width:100%;height: 4vw;line-height: 4vw;font-size: 3.5vw;}
      .navlist i{text-align: center; width:10vw;height: 10vw;line-height: 10vw;display: block;margin: 0 auto;}
      .navlist a{display: block;}
      .navlist .nav_img{height: 10vw;padding-top: 2vw;}
      /* 导航条 */
      /* 游戏列表,滑动导航条样式 */
      .list_home{font-size: 0;background: #fff;margin-top: 0rem;}
      .list_home .titlelist .active p{color:#FF0556;}
      .list_home .titlelist .active{border-bottom: 0.5vw solid #ff0556;}
      .list_home .titlelist,.result .titlelist{height: 10vw;overflow: -webkit-paged-x;font-size: 0;width: 100%;}
      .list_home .titlelist ul,.result .titlelist ul{white-space: nowrap;overflow-x: auto;}
      .list_home .titlelist ul::-webkit-scrollbar ,.result .titlelist ul::-webkit-scrollbar{width:0; height:0;display: none;}
      .list_home .titlelist ul li,.result .titlelist ul li{display: inline-block;width: 22vw;text-align: center;height:8vw;padding-top: 0rem;font-size: 0.3rem;color: #000;}
      .list_home .titlelist ul li{border-bottom: 0.5vw solid #eee;margin: 0.1rem auto;line-height: 8vw;font-size: 4vw;}
      .list_home .gamelist li{width: 25vw;height: 25vw;display: inline-block;margin: 3vw 3vw;text-align: center;background: #F4F4F4;border-radius: 2vw;}
      .list_home .gamelist li img{width: 15vw;height: 15vw;margin-top: 3vw;}
      .list_home .gamelist li span{width: 25vw;height: 4vw;display: inline-block;color: #000;font-size: 3vw;padding-top: 1vw;}
      .list_home .gamelist{display: none;text-align: left;}
      /* 游戏列表,滑动导航条样式 */
      /* 结果 */
      .result .titlelist ul li{position: relative;width: 55vw;height: 23vw;display: inline-block;padding-top:0;margin:3vw;background: linear-gradient(to top, #6887F9 , #A9BCFA);border-radius: 2vw;}
      .result .titlelist ul li img{height:17vw;margin-top: 3vw;}
      .result .titlelist ul{height:100%;display: block;}
      .result .titlelist{height: 33vw;}
      /* .result .titlelist .title{} */
      .result .info{color: #fff;width: 30vw;display: inline-block;float: left;padding-left: 2vw;padding-top: 3vw;}
      .result .info .info_p{font-size: 3.5vw;white-space: normal;text-align: left;}
      .result .info .money_p{font-size: 4vw;color: #F3EB19;text-align: left;font-weight: bold;position: absolute;bottom: 3vw;}
      .result .info .img{display: inline-block;width: 23vw;height: 23vw;}
      .result .title{font-size: 4vw;margin-left: 5vw;border-left: 1vw solid red;padding-left: 2vw;}
      .result{margin-top: 1.5vw;background: #fff;padding-top: 1.5vw;}
      .result .winInfo{width:30vw;}
        /* 结果 */
      .Banner{margin-top: 11vw;}
      .ShowInfoBar{margin: 0 auto; width: 90%; height: 6vw; background: #fff; border-radius: 5vw; position: relative; top: -3vw;z-index: 1; }
      .Bar{position: relative;top: -3vw;z-index: 1;margin: 0 auto;width: 93%;background-color: #fff;color: #000;border-radius: 5vw;height: 7vw;margin-bottom: 2vw;}
      .icon{width: 7vw;height: 7vw;display: inline-block;float: left;line-height: 7vw;text-align: center;}
      .iconfont{display: block;font-size: 4vw;}
      marquee{height: 7vw;line-height: 7vw;width: 90%;float: right;font-size:3vw;margin-right: 2vw;color:#C36532;}
      marquee .red{color: #ff0000;}
      /* 轮播图尺寸 */
      .imgitem{height:40vw;display:block;width:100%;}
      .footer {display: flex;display: -webkit-flex;flex-wrap: nowrap;width: 100%;height: 11vw;background: #fff;box-shadow: 0 -1px 1px #ccc;position: fixed;bottom: 0;left: 0;z-index:10;}
      .footer  a {display: flex;flex-direction: column;flex-grow: 1;font-size: 0.2rem;justify-content: center;align-items: center;width: 33%;display: inline-block;text-align: center;}
      .icon-notice{width: 5vw;padding: 1vw 2vw;}
  </style>
</head>
<body>
  <span id="api_token" class="dsnone">{{$api_token ?? null}}</span>
  <!-- <span id="api_token" class="dsn">a85541a8c1bc4e2a5061bdab3ad6ac20</span> -->
<div id="app" >

  <div class="header-bar">
    <span class="left-btn iconfont icon-jiantou">
        <img src="http://play.zdzlw.com/img/v/vapp/quwan/gamelist/logo.png" alt="">
    </span>
    <!-- <div class="title">趣玩娱乐</div> -->
    <div class="right-btn">
        <img src="http://play.zdzlw.com/img/v/vapp/quwan/gamelist/serve.png" alt="">
    </div>
  </div>
<!-- banner轮播图部分  start -->
  <div class="Banner">
    <div class="swiper-container">
      <div class="swiper-wrapper">
        @foreach($banners as $v)
        <div class="swiper-slide">
          <a href="{{$v->url}}">
          <img src="{{$v->thumb_img_url}}" class="imgitem" alt="">
          </a>
        </div>
        @endforeach
      </div>
    </div>
  </div>
<!-- banner轮播图部分  end -->
<!-- 滚动条部分 start-->
  <div class="Bar">
    <div class='icon'>
      <img class="icon-notice" src="http://play.zdzlw.com/img/v/vapp/quwan/gamelist/icon_notice.png" alt="">
    <!-- <i class="iconfont icon-notice"></i> -->
    </div>
    <marquee  data-behavior="scroll" data-direction="left" data-width='100%' data-scrollamount='40'>{{$carousel_message_val}}</marquee>
  </div>
<!-- 滚动条部分 end-->

<!-- 游戏导航条部分 start -->
  <div class="nav">
    <ul >
      <li class="navlist recharge">
        <img src="http://play.zdzlw.com/img/v/vapp/quwan/gamelist/recharge.png" class="nav_img" alt="">
        <p class="title">充值</p>
      </li>
      <li class="navlist withdraw">
        <img src="http://play.zdzlw.com/img/v/vapp/quwan/gamelist/withdraw.png" class="nav_img" alt="">
        <p class="title">提现</p>
      </li>
      <li class="navlist wallet">
        <img src="http://play.zdzlw.com/img/v/vapp/quwan/gamelist/wallet.png" class="nav_img" alt="">
        <p class="title">转换</p>
      </li>
      <li class="navlist freegame">
        <img src="http://play.zdzlw.com/img/v/vapp/quwan/gamelist/freegame.png" class="nav_img" alt="">
        <p class="title">免费试玩</p>
      </li>
    </ul>
  </div>
<!-- 游戏导航条部分 end -->

<!-- 游戏列表内容部分  start -->
  <div class="list_home">
    <div class="titlelist">
      <ul>
        @foreach($cates as $k => $v)
        <li @if($k == 0) class="active" @else class="" @endif>
          <p>{{$v->name}}</p>
        </li>
        @endforeach
      </ul>
    </div>
    @foreach($cates as $k => $v)
      <ul @if($k == 0) class="gamelist dsblock" @else class="gamelist" @endif>
        @foreach($v->lists as $val)
        <li data-platform="{{$val->game_platform}}" data-kind_id="{{$val->kind_id}}" class="game_item">
          <img src="{{$val->icon_url}}" alt="">
          <span>{{$val->game_title}}</span>
        </li>
        @endforeach
      </ul>
    @endforeach

  </div>
  <!-- 游戏列表内容部分  end -->

<!-- 开奖结果部分 start -->
  <div class="result">
    <div class="winInfo">
      <div class="title">中奖信息</div>
    </div>
    <div class="titlelist">
      <ul id="result">

      </ul>
    </div>
  </div>
  <!-- 开奖结果部分 end -->

</div>
<script type="text/javascript">
    (function(){
      // 公用请求
    var api_token=document.getElementById("api_token").innerText
    var origin=window.location.origin

      function  network(api, data, fun) {
          var url=window.location.origin+api
          if (!data) {
            $.ajax({
              type: "GET",
              url:  url,
              headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'api_token':api_token
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
                'X-Requested-With': 'XMLHttpRequest',
                'api_token':api_token
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
      }
      // 游戏导航条点击
      $(".titlelist li").on("click",function(){
           $(".titlelist li").removeClass("active")
           $(".titlelist li").eq($(this).index()).addClass("active")
           $(".gamelist").removeClass("dsblock")
           $(".gamelist").eq($(this).index()).addClass("dsblock")
      })

      // banner轮播图部分
      var mySwiper = new Swiper('.swiper-container', {
        autoplay: {
          delay: 2000,
          stopOnLastSlide: false,
          disableOnInteraction: true,
          },
          loop : true,
      });
      $(".game_item").on("click",function(){
        var platform=$(this).attr("data-platform")
        var kind_id=$(this).attr("data-kind_id")
        gotogame(platform,kind_id)
      })
      function gotogame(gametype,id){
        window.nativeObj.nativeMethod();
        window.webkit.messageHandlers.jsToOcNoPrams.postMessage(api_token);
        network("/api/game/login/"+gametype+"?kind_id="+id, null, res=>{
          if(res.status==0){
            // gametype=="leg"?location.href=res.data
            location.href=res.url
          }else{
            // setTimeout(()=>{
            //   this.alertText=res.message
            //   this.copyAlert=true
            // },30000)
          }
        })
      }
     // 导航跳转
    $(".recharge").on("click",function(){//充值
       location.href=origin+"/api/pay/h5?api_token="+api_token
    })
    $(".withdraw").on("click",function(){//提现
       location.href=origin+"/api/withdraw/index?api_token="+api_token
    })
    $(".wallet").on("click",function(){//钱包
       location.href=origin+"/api/game/wallet?api_token="+api_token
    })
    $(".freegame").on("click",function(){//免费游戏
      window.webkit.messageHandlers.jsToOcNoPrams.postMessage(api_token);
      window.nativeObj.nativeMethod();
       location.href="http://demo.aiqp001.com/php/autologin.php?game=fgame"
    })
    $(".right-btn").on("click",function(){//客服
       location.href="http://f18.livechatvalue.com/chat/chatClient/chatbox.jsp?companyID=870787&configID=75764&jid=2552196099"
    })
    //开奖结果
    // /api/game/winnersRecords

    function result(){
      network("/api/game/winnersRecords", null, res=>{
        if(res.status==0){
            var list = res.data
            var str=''
            console.log(list)
             for(var i=0;i<list.length;i++){
               str+='<li><div class="info"><p class="info_p"><span class="span">玩家'+list[i].username+'在'+list[i].game_title+'</span></p><p class="money_p"><i class="money">喜中</i><i>'+list[i].profit+'</i><i>钻</i></p></div><div class="img"><img src="'+list[i].icon_url+'" alt=""></div></li>'
             }
            $("#result").html(str)
        }else{

        }
      })
    }
    result()
    })()
</script>
</body>
</html>
