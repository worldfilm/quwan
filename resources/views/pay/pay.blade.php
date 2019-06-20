<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>会员充值</title>
	<link href="{{ asset('css/layer.css')}}"  type="text/css" />
	<link rel="stylesheet" href="{{ asset('css/iconfont1.css') }}">
	<style>
		* { margin: 0; padding: 0; box-sizing: border-box;   max-height:10000px;}
		input { border: none;}
		ul { list-style: none;}
		img { border: none; vertical-align: top;}
		a { -webkit-tap-highlight-color: transparent; text-decoration: none;}
		input { -webkit-appearance: none;}
		.clearfix:after { content: ""; display: block; clear: both;}
		.pull-left { float: left;}
		.pull-right { float: right;}
		button {outline: none;text-align: center;border-radius: 0.1rem;}
		html, body {font-family: Microsoft YaHei,Arial, Helvetica, STHeiTi, sans-serif; font-size: 0.22rem; background: #31313b;}
		.rechargeBox{ width:100%; }
		.rechargeBox .txt{ width: 100%; height: 0.6rem; line-height: 0.6rem; font-size: 0.28rem;  background: #ff9900; color:#fff; text-align:center; }
		.rechargeBox .payBox{ width: 100%; padding: 0.24rem; background: #31313b; border-radius: 0 0 8px 8px; }
		.rechargeBox .payCont{ margin: 0 auto; }
		.rechargeBox .payCont .listItem{ width: 100%; padding: 0.23rem 0.13rem; margin-bottom:0.2rem; background: #fff; border:1px solid #f95e98; }
		.rechargeBox .payCont .listItem .top{ margin-bottom: 0.1rem; font-size: 0.26rem; color:#da0b59;}
		.rechargeBox .payCont .listItem .top span:nth-of-type(1){ font-size: 0.28rem; letter-spacing: 0.02rem; font-weight: bold; color:#da0b59; }
		.rechargeBox .payCont .listItem .top span:nth-of-type(2){ font-size: 0.28rem; letter-spacing: 0.02rem; font-weight: bold; color:#da0b59; float: right; }
		.rechargeBox .payCont .listItem .bottom span{ font-size: 0.24rem; color:#000; }
		.rechargeBox .payCont .listItem .bottom i{ font-style:normal; color:#da0b59; margin-right:0.2rem;}
		.rechargeBox .payCont .listItem .bottom i:nth-of-type(1){ font-size:0.24rem; font-weight: bold;  }
		.rechargeBox .payCont .listItem .bottom i:nth-of-type(2){ text-decoration: line-through; }
		.rechargeBox .payCont .listItem .bottom span:nth-of-type(2){ float: right; font-size: 0.24rem; }
		.rechargeBox .payCont .listItem .bottom span:nth-of-type(2) i{ margin-right: 0; font-size: 0.24rem;}
		.rechargeBox .payCont .listItem .oneMonth { position: relative; top: 0.2rem; }
		.rechargeBox .payCont .listItem.active{ background: #e20054; }
		.rechargeBox .payCont .listItem.active .top{ font-size: 0.26rem; }
		.rechargeBox .payCont .listItem.active span{ color:#282828; font-size: 0.24rem; }
		.rechargeBox .payCont .listItem.active .top span:nth-of-type(1){ color:#ffe500; font-size: 0.26rem;}
		.rechargeBox .payCont .listItem.active .top span:nth-of-type(2){ color:#ffe500; font-size: 0.26rem;}
		.rechargeBox .payCont .listItem.active .bottom i{ color:#ffe500; }
		.rechargeBox .payCont .listItem.active .bottom span{ color:#fff; }
		.rechargeBox .payBtn .jd{ background: url({{asset('images/jdPay.png')}}) no-repeat 0 0; background-size:100%; }
		.rechargeBox .payBtn .qq{ background: url({{asset('images/qqPay.png')}}) no-repeat 0 0; background-size:100%; }
		.rechargeBox .payBtn .bank{ background: url({{asset('images/bank_1.png')}}) no-repeat 0 0; background-size:100%; }
		.rechargeBox .payBtn .wechat{ background: url({{asset('images/wechatPay.png')}}) no-repeat 0 0; background-size:100%; }
		.rechargeBox .payBtn .alipay{ background: url({{asset('images/aliPay.png')}}) no-repeat 0 0; background-size:100%; }
		.rechargeBox .payBtn .native{ background: url({{asset('images/aliPay.png')}}) no-repeat 0 0; background-size:100%; }
		.rechargeBox .payBtn .cardPay{ background: url({{asset('images/cardPay.png')}}) no-repeat 0 0; background-size:100%; }
		.rechargeBox .payTip{ margin-top: 0.1rem; text-align: center; font-size: 0.24rem; color:#fde9f4; }
		.rechargeBox .payTip i { position: relative; left: -0.1rem; top: 0.05rem; display: inline-block; width: 0.32rem; height: 0.28rem; background: url({{asset('images/warn.png')}}) no-repeat 0 0; background-size: 100%; }
		.rechargeBox .customerService{ margin-top: 0.1rem; text-align: center; font-size: 0.24rem; color:#fde9f4; }
		.rechargeBox .customerService i { position: relative; left: -0.1rem; top: 0.05rem; display: inline-block; width: 0.32rem; height: 0.28rem; background: url({{asset('images/customerService.png')}}) no-repeat 0 0; background-size: 100%; }
		.rechargeBox .close { position: absolute; right: -0.3rem; top: -0.3rem; display: block; width: 0.72rem; height: 0.72rem; background: url({{'images/collectClose.png'}}) no-repeat 0 0; background-size: cover;}
		.h5Tip{ font-size: 0.26rem; margin-top:0.4rem; margin-bottom:0.2rem;color: #fefdf9;}
		.btm-p{ line-height: 1.8em; font-size: 0.24rem;color: #fefdf9;}
		.btm-p > a { text-decoration: underline; color:#ffe500; }
		.btm-p > i { position: relative; left:0.1rem; top:0.15rem; display: inline-block; width: 0.5rem; height:0.5rem; background: url({{asset('images/beauty.png')}}) no-repeat 0 0; background-size:100%;}
		.layui-m-layer-msg .layui-m-layercont { padding: 0.2rem 1rem !important; font-size: 0.24rem !important;}
		.layui-m-layerbtn { padding: 0.2rem 0rem !important; }
		.layui-m-layerbtn span{ font-size: 0.24rem !important; }
		.rechargeBox .payBtn a{ display: inline-block; width: 46%; height: 0.9rem; margin: 0.15rem 0.1rem; }
		.layui-m-layer2 .layui-m-layercont p { margin-top: 0.4rem !important; font-size: 0.24rem !important;}
		.payBtn .pop{position: relative;left:2.3rem;top:0.2rem;display: inline-block;}
		.payBtn .tag{display: inline-block;padding: 5px;border:5px solid #FFEB3B;position:relative;background-color:#FFEB3B;color:#f80808;animation: elemScale 2s infinite;}
		.payBtn .tag:before,.btn-group .tag:after{content:"";display:block;border-width:20px;position:absolute; bottom:-40px;left:9px;border-style:solid dashed dashed;border-color:#FFEB3B transparent transparent;font-size:0;line-height:0;}
		.payBtn .tag:after{bottom:-33px;border-color:#FFF transparent transparent;}
		.choose_warning{color:#f80808;font-size: 0.3rem;font-weight: bold;}
		@keyframes elemScale{
		    0% { transform: scale(0.9); }
		  50% { transform: scale(1.0); }
		  100%{ transform: scale(0.9);}
		}
		/* 手动充值 */
		.layui-m-layercont {font-size: 0.3rem;}
		.layui-m-layerbtn span {font-size: 0.3rem !important;}
		.actAmount {position: relative;height: 0.76rem;line-height: 0.76rem;background: #fff;font-size: 0.3rem;margin-top: 0.1rem;}
		.actAmount .Charge, .actAmount .Charge {width: 50%;display: inline-block;text-align: center;color: #333;}
		.actAmount .activee {background: #03A9F4;color: #fff;}
		#cAlert{display:none}
		#autoCharge{display:block;}
		#ManualCharge{padding: 0.1rem 0rem;}
		#infoo{height:1rem;line-height: 1rem;height: 0.6rem;line-height: 0.6rem;padding-left: 0.1rem;}
		#ManualCharge .myAccount{text-align: center;background: #fff;height:1rem;line-height: 1rem;padding-right: 0.1rem;}
		#ManualCharge .myAccount button{height: 0.5rem;line-height: 0.5rem;font-size: 0.25rem;width: 1.3rem;background:#f3f3f3;box-shadow: 2px 8px 20px #6b595d;}
		.clist{background: #fff;}
		.my_account{padding-right:10px}
		.clist ul li{width: 33.28%;text-align: center;display: inline-block;border: 1px dashed #ccc;padding: 0.2rem 0.1rem;}
		.clist ul li p{height: 0.7rem;overflow: hidden;text-overflow: ellipsis;}
		.Charge .hot{position: relative;right: -0.1rem;color: #f50909;font-size: 0.23rem;top: -0.18rem;font-weight: 600;}
		.clist ul li  i{height: 0.4rem;line-height: 0.4rem;font-size: 0.45rem;width: 0.7rem;color: #fff;display: inline-block;margin-top: 0.15rem;}
		.clist ul li  button,.btm-p .wechatbtn{font-size: 0.25rem;background: #00cc00;padding: 0.1rem 0.3rem;color: #fff;box-shadow: 0 8px 20px #3f7b38;border: none;}
		.btm-p .wechatbtn{margin-left: 0.3rem;box-shadow:none;}
		#cAlert{width: 100%;height: 100%;background-color: rgba(0,0,0,.5);position: fixed;top: 0;left: 0;z-index: 15;overflow: auto;}
		#cAlert .cAlert{width: 5.5rem;margin: 3rem auto;background-color: #fff;position: relative;overflow: hidden;}
		#cAlert .cAlert p{font-size: 0.3rem;padding: 0.2rem;}
		.cAlert i{height: 0.7rem;width: 0.7rem;display: inline-block;color: #000;font-size: 0.45rem;float: right;}
		.cAlert .wechatbtn{height: 0.7rem;line-height: 0.7rem;font-size: 0.25rem;width: 4rem;background: #00cc00;color:#fff;display: block;margin-left: 0.6rem;box-shadow: 2px 8px 20px #3f7b38;border: none;}
		.cAlert .wechatbtn a{color:#fff;}
		#cAlert .cAlert .title{background: #00cc00;height: 0.7rem;line-height: 0.7rem;padding: 0;text-align: center;color: #fff;}
		#cAlert .cAlert .infobtn{height: 1.1rem;line-height: 1.1rem;}
		#cAlert .cAlert .info{margin: 0.2rem;border: 0.01rem solid #333;border-radius: 0.1rem;}
		#cAlert .cAlert .act{text-align: center;}
		.choose_p {height: 0.8rem;line-height: 0.8rem;background: #fff;padding-left: 0.12rem;}
		.payBtn{font-size: 0;margin-bottom: 0.15rem;}
		.payBtn p{text-align: left;}
		.title{position: relative;font-size: 0.4rem;font-weight: bold;padding-left: 0;height: 0.7rem;line-height: 0.7rem;}
		.greenc{background: #00cc00 !important;color: #fff;border: 0.01rem solid #00cc00!important;}
		.redc{background: #fb0000 !important;color: #fff;border: 0.01rem solid #fb0000!important;}
		.layui-m-layer0 .layui-m-layerchild{width: 65%;}
		#app .title{color:#f90707;font-size:0.5rem;text-align: center;}
		#app .body{padding: 0.8rem;background: #fff;position: absolute;height: 100%;width: 100%;}
		#qrcode{text-align: center;padding: 1rem 0rem;}
    #qrcode img{display: inline-block !important;width: 5rem;}
	</style>
</head>
<body id='app'>
	<!-- 遮罩区域-begin -->
		<div class="rechargeBox">
			@if($payBanner)
			<img src="{{ $payBanner }}" width="100%">
			@else
			<img src="{{ asset('images/payTop.jpg')}}" width="100%">
			@endif
			<div class="payBox" id="autoCharge">
				<ul class="payCont">
					@foreach($vips as $key=>$vip)
					<li class="listItem @if($key==0) active @endif" data-pay="{{ $vip->id }}" data-money="{{ $vip->dis_price }}">
						<p class="top">
							<span>{{ $vip->title }}</span>
							<span>￥{{ $vip->dis_price }}元</span>
						</p>
						<p class="bottom">
							@if($vip->period>=30)
								<span>全站畅享<i>{{ round($vip->period/30) }}个月</i>共计<i>{{ $vip->price }}元</i></span>
							@else
								<span>全站畅享<i>{{ $vip->period }}天</i>共计<i>{{ $vip->price }}元</i></span>
							@endif
							<span>已优惠:<i>{{ $vip->price - $vip->dis_price }}元</i></span>
						</p>
					</li>
					@endforeach
				</ul>
				<div class="payBtn">
					<p>
					@foreach($payList as $pay)
					<a href="###" data-method="{{ $pay['method'] }}" class="{{ $pay['method'] }}  rechargeBtn"></a>
					@endforeach
          </p>
				</div>
				<p class="choose_p" ><span class="title">官方代充:</span><span id='infoo'> </span></p>
				<section id='ManualCharge'>
					<div class="clist">
						<p class="myAccount">
							<span>我的账号：</span><span class="my_account">{{$uid}}</span>
							<button class='mybtn'>复制</button>
						</p>
						 <ul id='list'></ul>
					</div>
					<img src="{{ asset('images/btmBg.png') }}" width="100%" alt="">
				</section>
				<p class="btm-p">
					<a href="{{ $customerService }}" target="_self">1.若遇到问题，请联系客服，24小时在线。
						<button class="wechatbtn">联系客服</button>
					</a>
				</p>
				<input type="hidden" id="os" data-val="{{ $os }}" name="">
				<input type="hidden" id="token" data-val="{{ $apiToken }}" name="">
				<input type="hidden" id="channel" data-val="{{ $channel }}" name="">
				<input type="hidden" id="orderUrl" data-val="{{ $orderUrl }}" name="">
				<input type="hidden" id="uid" data-val="{{ $uid }}" name="">
				<input type="hidden" id="wechatQr" data-val="{{ $wechatQr }}" name="">
				<input type="hidden" id="alipayQr" data-val="{{ $alipayQr }}" name="">
				<input type="hidden" id="qqQr" data-val="{{ $qqQr }}" name="">
				<input type="hidden" id="jdQr" data-val="{{ $jdQr }}" name="">
				<input type="hidden" id="bankQr" data-val="{{ $bankQr }}" name="">
			</div>

			<section id='cAlert' v-show='cAlert'>
				<div class="cAlert">
					<p class="title"><span>联系代理</span><i class="icon iconfont icon-closedx"></i></p>
					<p><span id='page_top'></span></p>
					<p class='act'><span class="account" id="target"></span></p>
					<p class="infobtn"><button class="wechatbtn btn"  data-clipboard-action="copy" data-clipboard-target="#target" id="copy_btn">复制微信号并打开微信</button></p>
					<p class="info"><span id='description'></span></p>
				</div>
			</section>
		</div>
	<!-- 遮罩区域-end -->
	<script type="text/javascript" src="{{ asset('js/jquery.min.js')}}?v=1"></script>
	<script src="{{ asset('js/qrcode.min.js') }}" type="text/javascript"></script>
	<script type="text/javascript" src="{{ asset('js/layer.js')}}"></script>
	<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
	<script type="text/javascript">
	var origin=window.location.origin
	// origin='http://rk-api.nmgflower.com'
	var uid=$('#uid').attr('data-val')
		var _cig = {
			setSize : function() {
				var html = document.getElementsByTagName('html')[0];
				var width = html.getBoundingClientRect().width;
				html.style.fontSize = width / 7.5 + "px";
			},
			writeMeta : function(){
				var pixelRatio = 1 / window.devicePixelRatio;
				document.write('<meta name="viewport" content="width=device-width,initial-scale='+pixelRatio+',minimum-scale='+pixelRatio+',maximum-scale='+pixelRatio+'" />');
			},
			windowEvent : function(){
				window.addEventListener("resize", this.setSize, false);
				window.addEventListener("orientationchange", this.setSize, false);
			},
		};
		_cig.writeMeta();
		_cig.setSize();
		_cig.windowEvent();


		$(function(){
			var pay    = $(".active").data('pay');
			var os     = $("#os").data('val');
			var channel = $("#channel").data('val');
			var money = $(".active").data('money');
			var i=0
			$(".payCont .listItem").click(function(){
				pay =$(this).data('pay');
        money =$(this).data('money');
				$(this).addClass("active").siblings(".listItem").removeClass("active");
			})
      // 点击支付类型列表
			$(".rechargeBtn").click(function(){
				var token  = $("#token").data('val');
				var method = $(this).data('method');
				var payurl = "project_id=2&pay_method="+method+"&money="+money+"&uid="+uid+"&id="+pay+"&os="+os+"&ch="+channel;
        var orderUrl  = $("#orderUrl").data('val');
				var wechatQr=$("#wechatQr").data("val");
				var alipayQr=$("#alipayQr").data("val");
				var qqQr=$("#qqQr").data("val");
				var jdQr=$("#jdQr").data("val");
				var bankQr=$("#bankQr").data("val");
				// console.log(method)
				if(uid==0||uid==undefined||uid==null){
					layer.open({
							content: '获取用户信息失败,请重新登录',
							shadeClose:true,
							btn:['确定'],
							yes:function(index,layero){
									layer.closeAll();
							}
					});
				}else{
					ajaxStar()
				}
				//微信二维码支付
				if(method=="wechat"){
					if(wechatQr=="open"){
						console.log('wechatQr=open')
						var link=window.location.origin
						window.location.href=link+'/api/pay/qr?item_id='+pay+'&price='+money+'&uid='+uid+'&type='+method
						return false
					}
					if(wechatQr=="close"){
						console.log('wechatQr=close')
					}
				}
				if(method=="alipay"){
					if(alipayQr=="open"){
						console.log('alipayQr=open')
						var link=window.location.origin
						window.location.href=link+'/api/pay/qr?item_id='+pay+'&price='+money+'&uid='+uid+'&type='+method
						return false
					}
					if(alipayQr=="close"){
						console.log('alipayQr=close')
					}
				}
				if(method=="qq"){
					if(qqQr=="open"){
						console.log('qqQr=open')
						var link=window.location.origin
						window.location.href=link+'/api/pay/qr?item_id='+pay+'&price='+money+'&uid='+uid+'&type='+method
						return false
					}
					if(qqQr=="close"){
						console.log('qqQr=close')
					}
				}
				if(method=="jd"){
					if(jdQr=="open"){
						console.log('jdQr=open')
						var link=window.location.origin
						window.location.href=link+'/api/pay/qr?item_id='+pay+'&price='+money+'&uid='+uid+'&type='+method
						return false
					}
					if(jdQr=="close"){
						console.log('jdQr=close')
					}
				}
				if(method=="bank"){
					if(bankQr=="open"){
						console.log('bankQr=open')
						var link=window.location.origin
						window.location.href=link+'/api/pay/qr?item_id='+pay+'&price='+money+'&uid='+uid+'&type='+method
						return false
					}
					if(bankQr=="close"){
						console.log('bankQr=close')
					}
				}

				var isiOS = !!navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);
				function ajaxStar(){
					$.ajax({
						type:"GET",
						{{--url:"{{ url('api/vip/purchase') }}?"+payurl,--}}
            url:orderUrl+payurl,
						cache:false,
						headers:{'api-token':token},
						beforeSend : function(){
							layer.open({
									type: 2,
									content: '请求中'
							});
						},
						success:function(data){
							if(data.status === 0){
								if(data.data.type === "wap" && data.data.pay_channel === "Tjfpay"){
											  $('html').html(data.data.pay_html_url);
										} else if(data.data.pay_channel === "AYpay"){
		                    $('html').html(data.data.pay_html_url);
		                } else if(data.data.pay_channel === "YYpay"){
		                    $('html').html(data.data.pay_html_url);
		                } else if(data.data.pay_channel === "YSpay"){
											window.location.href =data.data.pay_html_url;
		                } else if(data.data.pay_channel === "BeePay"&&method=="wechat"){
											window.location.href =data.data.pay_html_url;
												// 将链接:data.data.pay_html_url 生成二维码
												// var str='<div class="body"><p class="title">请截图保存二维码,用微信扫码支付</p><div id="qrcode" ></div></div>'
												// document.getElementById("app").innerHTML=str
												// 		new QRCode(document.getElementById("qrcode"), {
												// 				text: data.data.pay_html_url,
												// 				colorDark : "#000000",
												// 				colorLight : "#ffffff",
												// 		});
										}else if(data.data.type === "wap" &&data.data.pay_channel === "BeePay"&&method=="alipay"){
											// window.location.href =data.data.pay_html_url;
											if(isiOS){//如果是IOS
												// 将链接:data.data.pay_html_url 生成二维码
												var str='<div class="body" ><p class="title">请截图保存二维码,用支付宝扫码支付</p><div id="qrcode" ></div></div>'
												document.getElementById("app").innerHTML=str
												new QRCode(document.getElementById("qrcode"), {
														text: data.data.pay_html_url,
														colorDark : "#000000",
														colorLight : "#ffffff",
												});
											}else{
													window.location.href =data.data.pay_html_url;//跳转链接
											}
										}else if(data.data.type === "qr" &&data.data.pay_channel === "BeePay"&&method=="alipay"){
											if(isiOS){//如果是IOS
												// 将链接:data.data.pay_html_url 生成二维码
												var str='<div class="body" ><p class="title">请截图保存二维码,用支付宝扫码支付</p><div id="qrcode" ></div></div>'
												document.getElementById("app").innerHTML=str
												new QRCode(document.getElementById("qrcode"), {
														text: data.data.pay_html_url,
														colorDark : "#000000",
														colorLight : "#ffffff",
												});
											}else{
													window.location.href =data.data.pay_html_url;//跳转链接
											}
										}else if(data.data.pay_channel === "KKpay"&&method=="alipay"){
											if(isiOS){//如果是IOS
												// 将链接:data.data.pay_html_url 生成二维码
												var str='<div class="body" ><p class="title">请截图保存二维码,用支付宝扫码支付</p><div id="qrcode" ></div></div>'
												document.getElementById("app").innerHTML=str
												new QRCode(document.getElementById("qrcode"), {
														text: data.data.pay_html_url,
														colorDark : "#000000",
														colorLight : "#ffffff",
												});
											}else{
													window.location.href =data.data.pay_html_url;//跳转链接
											}
		                }else if(data.data.pay_channel === "WDpay"){
		                    $('html').html(data.data.pay_html_url);
		                } else if(data.data.pay_channel === "YQpay"){
		                    $('html').html(data.data.pay_html_url);
		                } else if(data.data.pay_channel === "NewHFPay"){
		                    $('html').html(data.data.pay_html_url);
		                } else if(data.data.pay_channel === "HFPay"){
		                    $('html').html(data.data.pay_html_url);
		                } else if(data.data.pay_channel === "AngelPay"){
		                    $('html').html(data.data.pay_html_url);
										} else if(data.data.pay_channel === "Ymfpay"){
												$('html').html(data.data.pay_html_url);
										} else if(data.data.pay_channel === "WFBPay"){
											$('html').html(data.data.pay_html_url);
										} else if(data.data.pay_channel === "TSPay"){
												$('html').html(data.data.pay_html_url);
										} else if(data.data.pay_channel === "Slpay"){
												$('html').html(data.data.pay_html_url);
										}else if(data.data.pay_channel === "XFTpay"){
				                $('html').html(data.data.pay_html_url);
				            }else if(data.data.type == 'native' && data.data.os == 'android') {
												window.javaObject.invokeNativeAlipay(data.data.alipayStr);
										}else if(data.data.type == 'native' && data.data.os == 'IOS'){
											window.location.href = 'nativeAction://alipay?title='+data.data.alipayStr;
										}else{
											location.href = data.data.pay_html_url;
										}
										}else{
											layer.open({
													content: '支付异常',
													skin: 'msg',
													time: 8, //2秒后自动关闭
													shadeClose:true,
													btn:['确定'],
													yes:function(index,layero){
														layer.closeAll();
													}
											});
										}
										setTimeout(function(){
											console.log('setTimeout')
											layer.open({
												 content: '请求超时',
												 skin: 'msg',
												 time: 8, //2秒后自动关闭
												 shadeClose:true,
												 btn:['确定'],
												 yes:function(index,layero){
													 layer.closeAll();
													 i++
													 if(i==1){
														 ajaxStar()
													 }
												 }
										 });
									 }, 30000);
								},
								error:function(){
									console.log('error')
									layer.open({
											content: '支付异常',
											skin: 'msg',
											time: 8, //2秒后自动关闭
											shadeClose:true,
											btn:['确定'],
											yes:function(index,layero){
												layer.closeAll();
											}
									});
								}
							});
						}
					});
				})
    getList()
		function getList(){
			$.ajax({
				type: "GET",
				url: origin+'/api/pay/manualRecharge?uid='+uid,//正式
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				},
				ContentType: "application/x-www-form-urlencoded",
				dataType: "json",
				success: function(res) {
					if(res.status==0){
						infoo=res.data.notice
						$('#infoo').text(infoo)
					var list=res.data.list
					var html=''
					for(var i=0;i<list.length;i++){
						if(list[i].status!=0){
							$("#list").html('')
							html+="<li>\
								<p><span class='page_top'>"+list[i].page_top+"</span></p><button >充值</button>\
							<span class='btn' style='display:none'>"+list[i].account+"</span>\
							<span class='description' style='display:none'>"+list[i].description+"</span>\
							</li>"
							description=list[i].description
						}
					}
					page_top=list[0].page_top

					$('#list').append(html)
					$('#description').text(description)
					$('#page_top').text(page_top)
					$('.clist li').on('click',function(){
						$("#cAlert").css('display','block')
						$(".account").text($('.btn').eq($(this).index()).text())
						$("#page_top").text($('.page_top').eq($(this).index()).text())
						$("#description").text($('.description').eq($(this).index()).text())
						copy($(this).index())
					})
					$('.icon-closedx').on('click',function(){
						$('#cAlert').css('display','none')
					})
					// 复制到剪切板
					function copy(idx) {
						 var clipboard = new Clipboard('.btn', {
								 target: function(e) {
									 e=e.toString(e)
										 return document.getElementsByClassName('btn')[idx];
								 }
						 });
						 clipboard.on('success', function(e) {
								 clipboard.destroy();
						 });
						 clipboard.on('error', function(e) {
								 clipboard.destroy();
						 });
						}
					}
				},
				error: function(data) {
				}
			});
		}
		// 复制我的账号
		$('.mybtn').on('click',function(){
			var clipboard2 = new Clipboard('.mybtn', {
					target: function(e) {
						e=e.toString(e)
							return document.getElementsByClassName('my_account')[0];
					}
			});
			clipboard2.on('success', function(e) {
					clipboard2.destroy();
			});
			clipboard2.on('error', function(e) {
					clipboard2.destroy();
			});
			layer.open({
					content: '复制成功!',
					area: ['200px','100px'],
					shadeClose:true,
					btn:['确定'],
					yes:function(index,layero){
							layer.closeAll();
					}
			});
		})

	$(document).ready(function(){
		var clipboard = new Clipboard('#copy_btn');
		clipboard.on('success', function(e) {
				// alert("微信号复制成功",1500);
				window.location.href='weixin://';
				e.clearSelection();
		});
	});
	</script>
</body>
</html>
