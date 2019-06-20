<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>个人中心</title>
	<!-- <link rel="stylesheet" href="{{ asset('css/main.css') }}"> -->
	<link rel="stylesheet" href="{{ asset('css/iconfont1.css') }}">
	<link href="{{ asset('css/layer.css')}}?v=2"  type="text/css" />
	<style media="screen">
	*{ margin:0; padding:0; box-sizing: border-box;}
	li{list-style: none;}
	.actAmount .red{color:#fb0000;}
	.actAmount  i {position: relative; left: 0.2rem; top: 0.2rem;display: inline-block; width: 0.6rem; height: 0.6rem; background: url("http://play.zdzlw.com/img/v/vapp/rechargeH5/diamond.png") 0 0 no-repeat; background-size: cover; }
	.actAmount{position: relative;height: 1.3rem;line-height: 1.3rem;background: #fff;font-size: 0.4rem;margin-top: 0.1rem;padding: 0rem 0.2rem;}
	.diaM{ margin-top:0.1rem;padding: 0rem 0.22rem;margin: 0 auto; }
	.line{ display: block;margin: 0.1rem auto;height:0.2rem; background: #e4dee0;border-radius:0.07rem;}
	.diaM .getDm{margin:0 auto; background: #eee; }
	.diaM .getDm .title{ position: relative; width:100%; height:0.76rem; line-height: 0.76rem; text-indent: 0.3rem; font-size: 0.28rem; background: #fff; }
	.diaM .getDm .title > i{ position: absolute; left:0.02rem; top:0.12rem; display: block; width:0.06rem; height:0.5rem; background: #fe0000; }
	.dimBox {background: #fff;font-size: 0;margin: 0 0.3rem;}
	.dimBox li.discount.active{ border:0.02rem solid red; background: #ffdede;}
	.dimBox li{display: inline-block;position: relative; width:25%; height:2.7rem; border:0.01rem dashed #ccc;font-size: 0.3rem;}
	.dimBox li.discount p{ text-align: center; }
	.dimBox li.discount p.f-p{ padding-top:0.6rem; }
	.dimBox li.discount p.s-p{ margin-top:0.2rem; font-weight: bold; color:red; }
	.dimBox li.discount p.f-p i{ position: relative; top:0.1rem; display: inline-block; width:0.5rem; height:0.5rem; background: url("http://play.zdzlw.com/img/v/vapp/rechargeH5/diamond.png") 0 0 no-repeat; background-size:cover; margin-left:0.1rem;}
	.dimBox li.discount .t-tip{ position: absolute; left: 0rem; bottom:0rem; width:100%; font-size: 0.3rem; background: yellow; }
	.dimBox li.discount .t-tip p{ width:100%; padding:0.05rem 0rem;}
	.dimBox li.discount .t-tip span{color:red;}
	.tip-t{ color:#2f2f2f; font-size: 0.35rem;  font-weight: bold; }
	.tip-u{ padding-left:0.12rem;height: 1.5rem;display: inline-block;}
	.tip-u a.go{ color:red; }
	.tip-u a.go > img{ position: relative; top:0.12rem; }
	  .layui-m-layercont{line-height: 0.4rem !important;}
		button{outline: none;text-align: center;border-radius: 0.1rem;}
		.btn-group .displaynone{display:none}
		.btn-group {position: relative;font-size: 0;margin-bottom: 0.15rem;bottom: 0.15rem;}
		.payType{display: inline-block;width: 48%;margin-left: 0.1rem;margin-top:0.2rem;border-radius: 0.2rem;margin-left: 0.05rem;height: 1rem;}
		.alipay{ background: url("http://play.zdzlw.com/img/v/vapp/rechargeH5/aliPay.png") 0 0 no-repeat; background-size:cover;}
		.native{ background: url("http://play.zdzlw.com/img/v/vapp/rechargeH5/aliPay.png") 0 0 no-repeat; background-size:cover;}
		.wechat{ background: url("http://play.zdzlw.com/img/v/vapp/rechargeH5/weChat.png") 0 0 no-repeat; background-size:cover;}
		.qq{ background: url("http://play.zdzlw.com/img/v/vapp/rechargeH5/qqPay.png") 0 0 no-repeat; background-size:cover;}
		.jd{ background: url("http://play.zdzlw.com/img/v/vapp/rechargeH5/jdPay.png") 0 0 no-repeat; background-size:cover;}
		.payType.bank{ background: url("http://play.zdzlw.com/img/v/vapp/rechargeH5/bank_1.png") 0 0 no-repeat; background-size:cover;}
		.payType.manual{ background: url("http://play.zdzlw.com/img/v/vapp/rechargeH5/manual.png") 0 0 no-repeat; background-size:cover;}
		.actAmount .Charge,.actAmount .Charge{width:50%;display:inline-block;text-align: center;}
		.actAmount  .activee{background: #03A9F4;color: #fff;}
		#cAlert{display:none}
		#autoCharge{display:block;}
		#ManualCharge{display:block;padding: 0.1rem 0rem;font-size: 0.35rem;}
		#infoo{height:1rem;line-height: 1rem;padding-left: 0.1rem;font-size: 0.35rem;}
		#ManualCharge .myAccount{text-align: center;background: #fff;height:1rem;line-height: 1rem;}
		#ManualCharge .myAccount button{height: 0.7rem;line-height: 0.7rem;font-size: 0.35rem;padding: 0 0.2rem;background:#f3f3f3;box-shadow: 2px 3px 5px #dcd6d8;border: 0.01rem solid #ededed;}
		.clist{background: #fff;}
		.clist ul li{width: 33.28%;text-align: center;display: inline-block;border: 1px dashed #ccc;padding: 0.2rem 0.1rem;}
		.clist ul li p{height: 0.7rem;overflow: hidden;text-overflow: ellipsis;line-height: 0.7rem;}
		.Charge .hot{position: relative;right: -0.1rem;color: #f50909;font-size: 0.23rem;top: -0.18rem;font-weight: 600;}
		.clist ul li  i{height: 0.4rem;line-height: 0.4rem;font-size: 0.45rem;width: 0.7rem;color: #fff;display: inline-block;margin-top: 0.15rem;}
		.clist ul li  button,.tip-u .wechatbtn{font-size: 0.35rem;background: #00cc00;padding: 0.1rem 0.3rem;color: #fff;box-shadow: 0 3px 5px #b3b7b2;border: none;margin-top: 0.1rem;}
		.tip-u .wechatbtn{margin-left:0.2rem;}
		.my_account{padding-right:10px}
		#cAlert{width: 100%;height: 100%;background-color: rgba(0,0,0,.5);position: fixed;top: 0;left: 0;z-index: 15;overflow: auto;}
		#cAlert .cAlert{width:70%;margin: 5rem auto;background-color: #fff;position: relative;overflow: hidden;}
		#cAlert .cAlert p{font-size: 0.4rem;padding: 0.2rem;}
		.cAlert i{height: 1rem;width: 1rem;display: inline-block;color: #000;font-size: 0.5rem;float: right;}
		.cAlert .wechatbtn{margin: 0 auto;height:0.8rem;line-height:0.8rem;font-size: 0.35rem;width: 4rem;background: #00cc00;color:#fff;display: block;box-shadow: 2px 3px 5px #b3b7b2;border: none;}
		.cAlert .wechatbtn a{color:#fff;}
		#cAlert .cAlert .title{background: #00cc00;height: 1rem;line-height: 1rem;padding: 0;text-align: center;color: #fff;}
		#cAlert .cAlert .infobtn{height: 1.1rem;line-height: 1.1rem;}
		#cAlert .cAlert .info{margin: 0.2rem;border: 0.01rem solid #333;border-radius: 0.1rem;}
		#cAlert .cAlert .act{text-align: center;}
		.title{position: relative;font-size: 0.4rem;font-weight: bold;padding-left: 0;height: 0.7rem;line-height: 0.7rem;}
		.greenc{background: #00cc00 !important;color: #fff;border: 0.01rem solid #00cc00!important;}
		.redc{background: #fb0000 !important;color: #fff;border: 0.01rem solid #fb0000!important;}
		.myAccount .switch{float:right;margin-top: 0.15rem;margin-right: 0.9rem;}
		.head{height: 0.5rem;}
		#app .title{color:#f90707;font-size:0.5rem;text-align: center;}
		#app .body{padding: 0.8rem;}
		#qrcode{text-align: center;padding: 1rem 0rem;}
    #qrcode img{display: inline-block !important;width: 5rem;}
		.goback{background:url("http://play.zdzlw.com/img/v/vapp/gamelist/goback.png") no-repeat;display: inline-block;width: 1.5rem;height: 1.5rem;background-size: 100%;position: relative;top: -0.15rem;}
	</style>
	<script>
			!function(N,M){function L(){var a=I.getBoundingClientRect().width;a/F>1024&&(a=1024*F);var d=a/10;I.style.fontSize=d+"px",D.rem=N.rem=d}var K,J=N.document,I=J.documentElement,H=J.querySelector('meta[name="viewport"]'),G=J.querySelector('meta[name="flexible"]'),F=0,E=0,D=M.flexible||(M.flexible={});if(H){/*console.warn("将根据已有的meta标签来设置缩放比例");*/var C=H.getAttribute("content").match(/initial\-scale=([\d\.]+)/);C&&(E=parseFloat(C[1]),F=parseInt(1/E))}else{if(G){var B=G.getAttribute("content");if(B){var A=B.match(/initial\-dpr=([\d\.]+)/),z=B.match(/maximum\-dpr=([\d\.]+)/);A&&(F=parseFloat(A[1]),E=parseFloat((1/F).toFixed(2))),z&&(F=parseFloat(z[1]),E=parseFloat((1/F).toFixed(2)))}}}if(!F&&!E){var y=N.navigator.userAgent,x=(!!y.match(/android/gi),!!y.match(/iphone/gi)),w=x&&!!y.match(/OS 9_3/),v=N.devicePixelRatio;F=x&&!w?v>=3&&(!F||F>=3)?3:v>=2&&(!F||F>=2)?2:1:1,E=1/F}if(I.setAttribute("data-dpr",F),!H){if(H=J.createElement("meta"),H.setAttribute("name","viewport"),H.setAttribute("content","initial-scale="+E+", maximum-scale="+E+", minimum-scale="+E+", user-scalable=no"),I.firstElementChild){I.firstElementChild.appendChild(H)}else{var u=J.createElement("div");u.appendChild(H),J.write(u.innerHTML)}}N.addEventListener("resize",function(){clearTimeout(K),K=setTimeout(L,300)},!1),N.addEventListener("pageshow",function(b){b.persisted&&(clearTimeout(K),K=setTimeout(L,300))},!1),"complete"===J.readyState?J.body.style.fontSize=12*F+"px":J.addEventListener("DOMContentLoaded",function(){J.body.style.fontSize=12*F+"px"},!1),L(),D.dpr=N.dpr=F,D.refreshRem=L,D.rem2px=function(d){var c=parseFloat(d)*this.rem;return"string"==typeof d&&d.match(/rem$/)&&(c+="px"),c},D.px2rem=function(d){var c=parseFloat(d)/this.rem;return"string"==typeof d&&d.match(/px$/)&&(c+="rem"),c}}(window,window.lib||(window.lib={}));
	</script>
	<script src="{{ asset('js/jquery.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('js/qrcode.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('js/layer.js')}}" type="text/javascript" ></script>
	<script src="https://cdn.jsdelivr.net/clipboard.js/1.5.12/clipboard.min.js"></script>
	<script type="text/javascript">
			(function(){
				$(document).ready(function(){
					var type=window.location.href
					type.split("&")[1]?$(".goback").css("display","none"):$(".goback").css("display","block")
          $(".goback").on("click",function(){
						var origin=window.location.origin
            var api_token  = $("#token").data('val');
            window.location.href =origin+"/api/game/index?api_token="+api_token;
					})
	        var clipboard = new Clipboard('#copy_btn');
					var uid=$('#uid').attr('data-val')
	        var origin=window.location.origin
	        clipboard.on('success', function(e) {
	            // alert("微信号复制成功",1500);
	            window.location.href='weixin://';
	            e.clearSelection();
	        });
					var description,infoo,page_top,my_account
					var datanum=$('.dimBox li')
					var btngroup=$('.payMethods li').attr('data-channel')
					$('.payMethods li').addClass('displaynone')
					// 默认展示第一项
					datanum.eq(0).addClass('active')
					var defaultChannel=datanum.eq(0).attr('data-channel').split(',')
					for(var i=0;i<defaultChannel.length;i++){
						$('.payMethods li').eq(i).removeClass('displaynone alipay wechat qq jd bank manual')
						$('.payMethods li').eq(i).addClass(defaultChannel[i])
						$('.payMethods li').eq(i).attr('data-method',defaultChannel[i])
					}
					// 点击列表
					datanum.on('click',function(){
						 datanum.removeClass('active')
						 $('.payMethods li').addClass('displaynone')
						 datanum.eq($(this).index()).addClass('active')
							var channel=datanum.eq($(this).index()).attr('data-channel')
							channel=channel.split(',')
							for(var i=0;i<channel.length;i++){
								$('.payMethods li').eq(i).removeClass('displaynone alipay wechat qq jd bank manual')
								$('.payMethods li').eq(i).addClass(channel[i])
								$('.payMethods li').eq(i).attr('data-method',channel[i])
							}
					})
					if(uid==0||uid==undefined||uid==null){
						layer.open({
								content: '获取用户信息失败,请重新登录!',
								area: ['200px','100px'],
								shadeClose:true,
								btn:['确定'],
								yes:function(index,layero){
										layer.closeAll();
								}
						});
					}else{
						getList()
					}
					// 点击代充上下线
					var k=false
					$(".switch").on("click",function(){
						$(".switch").removeClass("greenc redc")
						if(k==false){
							changeStatus(1)
							$("#list").html('')
							k=true
						}else{
							changeStatus(0)
							$("#list").html('')
							k=false
						}
					})
					// 点击支付类型列表
					$(".payType").click(function(){
									var method = $(this).data('method');
									console.log(method)
									var pay    = $(".dimBox li.active").data('pay');
									var os     = $("#os").data('val');
									var money     = $(".dimBox li.active").data('num');
									var channel = $("#channel").data('val');
									// var uid  = $("#uid").data('val');
									var token  = $("#token").data('val');
									var url = window.location.href
									var id,payurl;
									var testId =url.split("&")[url.split("&").length-1].split("=")[0]
									var wechatQr=$("#wechatQr").data("val");
									var alipayQr=$("#alipayQr").data("val");
									var qqQr=$("#qqQr").data("val");
									var jdQr=$("#jdQr").data("val");
									var bankQr=$("#bankQr").data("val");
									var orderUrl  = $("#orderUrl").data('val');
									//判断room_id是否存在,再提交
											if(testId=="room_id"){
													id =url.split("=")[url.split("=").length-1]
													payurl = "project_id=1&pay_method="+method+"&money="+money+"&uid="+uid+"&id="+pay+"&os="+os+"&ch="+channel+"&room_id="+id;
											}else{
													id =0
													payurl = "project_id=1&pay_method="+method+"&money="+money+"&uid="+uid+"&id="+pay+"&os="+os+"&ch="+channel+"&room_id="+id;
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
	                $.ajax({
	                    type:"GET",
	                    // url:"pay?"+payurl,
	                    url:orderUrl+payurl,
	                    cache:false,
	                    headers:{'api_token':token},
	                    "beforeSend" : function(){
	                        layer.open({
	                            type: 2,
	                            content: '请求中',
	                            shadeClose:false
	                        });
	                    },
	                    success : function(data){
	                        if(data.status == 0){
														  if(data.data.type === "qr" && data.data.pay_channel === "BeePay"&&method=="wechat"){
																window.location.href =data.data.pay_html_url;//跳转链接
															}
															if(data.data.type === "qr" && data.data.pay_channel === "BeePay"&&method=="alipay"){
																if(isiOS){//如果是IOS
																	// 将链接:data.data.pay_html_url 生成二维码
																	var str='<div class="body"><p class="title">请截图保存二维码,用支付宝扫码支付</p><div id="qrcode" ></div></div>'
																	document.getElementById("app").innerHTML=str
																	new QRCode(document.getElementById("qrcode"), {
																			text: data.data.pay_html_url,
																			colorDark : "#000000",
																			colorLight : "#ffffff",
																	});
																}else{
																		window.location.href =data.data.pay_html_url;//跳转链接
																}
															}
	                            if(data.data.type === "wap" && data.data.pay_channel === "Tjfpay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "YSpay"){
																window.location.href =data.data.pay_html_url;
	                            }else if(data.data.type === "wap" &&data.data.pay_channel === "BeePay"&&method=="wechat"){
                                window.location.href =data.data.pay_html_url;
	                            }else if(data.data.type === "wap" &&data.data.pay_channel === "BeePay"&&method=="alipay"){
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
	                            }else if(data.data.pay_channel === "YYpay"){
	                                $('html').html(data.data.pay_html_url);
	                            }else if(data.data.pay_channel === "KKpay"&&method=="alipay"){
																if(isiOS){//如果是IOS
																	// 将链接:data.data.pay_html_url 生成二维码
																	var str='<div class="body"><p class="title">请截图保存二维码,用支付宝扫码支付</p><div id="qrcode" ></div></div>'
																	document.getElementById("app").innerHTML=str
																	new QRCode(document.getElementById("qrcode"), {
																			text: data.data.pay_html_url,
																			colorDark : "#000000",
																			colorLight : "#ffffff",
																	});
																}else{
																		window.location.href =data.data.pay_html_url;//跳转链接
																}
	                            } else if(data.data.pay_channel === "WDpay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "AYpay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "YQpay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "AngelPay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "Ymfpay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "WFBPay"){
                                    $('html').html(data.data.pay_html_url);
                                } else if(data.data.pay_channel === "TSPay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "NewHFPay"){
                                    $('html').html(data.data.pay_html_url);
                                } else if(data.data.pay_channel === "HFPay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "Happypay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "Slpay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "SRBpay"){
	                                $('html').html(data.data.pay_html_url);
	                            } else if(data.data.pay_channel === "JCpay"){
	                                location.href = data.data.pay_html_url;
	                            } else if(data.data.type == 'native' && data.data.os == 'android') {
	                                window.javaObject.invokeNativeAlipay(data.data.alipayStr);
	                            } else if(data.data.type == 'native' && data.data.os == 'ios'){
	                                window.location.href = 'nativeAction://alipay?title='+data.data.alipayStr;
	                            }else if(data.data.pay_channel === "XFTpay"){
	                                $('html').html(data.data.pay_html_url);
	                            }  else{
	                                location.href = data.data.pay_html_url;
	                            }
	                        }else{
	                            layer.open({
	                                content: data.message,
	                                area: ['200px','100px'],
	                                shadeClose:true,
	                                btn:['确定'],
	                                yes:function(index,layero){
	                                    layer.closeAll();
	                                }
	                            });
	                        }
	                    }
	                });
							});
					// 自动,手动充值
					$('.Charge').on('click',function(){
							$('.Charge').removeClass('activee')
							$('.Charge').eq($(this).index()).addClass('activee')
							if($(this).index()==0){
									$('#autoCharge').css('display','block')
									$('#ManualCharge').css('display','none')
							}else{
									$('#autoCharge').css('display','none')
									$('#ManualCharge').css('display','block')
							}
					})
					function changeStatus(status){
						$.ajax({
							type: "GET",
							url:origin+'/api/pay/lockManualRecharge?uid='+uid+'&status='+status,//正式
							// url:'http://zb-api-ceshi.mekxfj.com/api/pay/lockManualRecharge?uid='+uid+'&status='+status,//测试
							headers: {
								'X-Requested-With': 'XMLHttpRequest'
							},
							ContentType: "application/x-www-form-urlencoded",
							dataType: "json",
							success: function(res) {
								getList()
							},
							error: function(res) {
							}
						})
					}

	        function getList(){
						$.ajax({
							type: "GET",
							url: origin+'/api/pay/manualRecharge?uid='+uid,//正式
							// url:'http://zb-api-ceshi.mekxfj.com/api/pay/manualRecharge?uid='+uid,//测试
							headers: {
								'X-Requested-With': 'XMLHttpRequest'
							},
							ContentType: "application/x-www-form-urlencoded",
							dataType: "json",
							success: function(res) {
								if(res.status==0){
										$(".switch").css("display",'none')
									infoo=res.data.notice
									$('#infoo').text(infoo)
								var list=res.data.list
								var html=''
								for(var i=0;i<list.length;i++){
									if(uid==list[i].user_id){
										if(list[i].status==0){
											$(".switch").removeClass("greenc redc")
											$(".switch").text('代充上线')
											$(".switch").addClass('greenc')
										}else{
											$(".switch").removeClass("greenc redc")
											$(".switch").addClass('redc')
											$(".switch").text('代充下线')
										}
									}
									if(uid==list[i].user_id){
										$(".switch").css("display",'block')
									}
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
	    });
			window.addEventListener("resize", this.setSize, false);
			window.addEventListener("orientationchange", this.setSize, false);
			$('input, textarea, button, a, select').off('touchstart mousedown').on('touchstart mousedown', function(e) {
						e.stopPropagation();
				});
			})()
	</script>
</head>
<body style="background: #eee;" id='app'>
	<section>

		<div class="actAmount">
			<div style="width: 1.5rem;float: left;height: 1.2rem;">
	       <span class="goback"></span>
			</div>
			<span>账户余额：</span><span class="red">{{ $user->deposit or 0}}</span><i>&nbsp;</i>
		</div>
	</section>
	<section>
	</section>
	<section id='autoCharge'>
		<div class="diaM">
			<div class="getDm">
				<span class="line"></span>
				@if($user)
				<ul class="dimBox">
					@foreach($recharge as $key=>$tip)
					@continue($user->level < $tip->level_limit)
					<li class="discount @if(!$tip->i)  @endif" data-num = "{{ $tip->dis_price }}" data-pay="{{ $tip->id }}" data-channel="{{$tip->channel}}">
						<p class="f-p"><span class="cnt">{{ $tip->dis_price*10 }}</span><i></i></p>
						<p class="s-p">￥<span class="price">{{ $tip->dis_price }}</span></p>
						@if($tip->present != 0)
						<div class="t-tip">
							<p>额外赠送<span>{{ $tip->present*100 }}%</span></p>
						</div>
						@endif
					</li>
					@endforeach
				</ul>
				@endif
			</div>
			<img src="http://play.zdzlw.com/img/v/vapp/rechargeH5/btmBg.png" width="100%" alt="">
			<section class="btn-group">
				<ul class="payMethods">
					<li class="payType wechat" data-method="wechat"></li>
					<li class="payType alipay" data-method="alipay"></li>
					<li class="payType qq" data-method="qq"></li>
					<li class="payType jd" data-method="jd"></li>
					<li class="payType bank" data-method="bank"></li>
				</ul>
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
			</section>
			<p class="actAmount" ><span class="title">官方代充:</span><span id='infoo'> </span></p>
			<section id='ManualCharge'>
				<div class="clist">
					<p class="myAccount">
						<span>我的账号：</span><span class="my_account">{{$uid}}</span>
						<button class='mybtn'>复制</button>
						<button class="switch greenc"></button>
					</p>
					 <ul id='list'></ul>
				</div>
				<img src="{{ asset('images/btmBg.png') }}" width="100%" alt="">
			</section>
			<p class="tip-t">充值提示：</p>
			<ul class="tip-u">
				<li><a class="go" href="http://f18.livechatvalue.com/chat/chatClient/chatbox.jsp?companyID=870787&configID=75764&jid=2552196099">1.若遇到问题，请联系客服，24小时在线。<button class="wechatbtn">联系客服</button></a></li>
			</ul>
			<input type="hidden" id="os" data-val="{{ $os }}" name="">
			<input type="hidden" id="token" data-val="{{ $apiToken }}" name="">
			<input type="hidden" id="channel" data-val="{{ $channel }}" name="">
		</div>
	</section>
	<section id='cAlert' v-show='cAlert'>
		<div class="cAlert">
			<p class="title"><span>联系代理</span><i class="icon iconfont icon-closedx"></i></p>
			<p><span id='page_top'></span></p>
			<p class='act'><span class="account" id="target"></span></p>
			<p class="infobtn"><button class="wechatbtn btn"  data-clipboard-action="copy" data-clipboard-target="#target" id="copy_btn">复制微信号并打开微信</button></p>
			<p class="info"><span id='description'></span></p>
		</div>
	</section>

</body>
</html>
