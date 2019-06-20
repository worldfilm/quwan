<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>会员充值</title>
	<link href="{{ asset('css/layer.css')}}"  type="text/css" />
	<style>
		*{ margin:0rem; padding:0rem; font-size: 0.24rem; color:#fffdfe; }

		a { text-decoration: none; }

		body{ background: #1d191a; }

		.inputCard{ width:6.72rem; height:0.8rem; border:none; border-radius:0.05rem; text-indent:1em; color:#1d191a;  margin:0.2rem 0rem 0.5rem 0.4rem;}

		a.text{ margin-left:0.4rem; }

		a.btn{ display: block; width:5.5rem; height:0.8rem; line-height: 0.8rem; margin:0 auto; text-align:center; background: #e20056; color:#fffdfe; border-radius: 0.1rem; font-size:0.26rem; letter-spacing: 0.05rem; margin-top:3rem; }

		.customerService{ margin-left:0.4rem; }

		.customerService i { position: relative; left: 0.1rem; top: 0.18rem; display: inline-block; width: 0.4rem; height: 0.45rem; background: url({{asset('images/customerService.png')}}) no-repeat 0 0; background-size: 100%; }
	</style>

</head>
<body>
	<input class="inputCard" type="text" placeholder="请输入购买卡密">


	<p class="customerService"><a href="{{ $customerService }}" target="_self">* 如支付遇到问题，请联系人工客服</a> <i></i></p>
	<a href="javascript:void(0)" class="btn cardPayBtn">确认</a>
	<input type="hidden" id="token" data-val="{{ $apiToken }}" name="">

	<script type="text/javascript" src="{{ asset('js/jquery.min.js')}}?v=1"></script>
	<script type="text/javascript" src="{{ asset('js/layer.js')}}"></script>
	<script type="text/javascript">
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

			$(".payCont .listItem").click(function(){
				$(this).addClass("active").siblings(".listItem").removeClass("active");
			})

			$(".cardPayBtn").click(function(){
				var cardId    = $(".inputCard").val();
				var payurl = "secret="+cardId;
				var token  = $("#token").data('val');
				layer.open({
				    type: 2,
				    content: '请求中'
				});
				$.ajax({
					type:"GET",
					url:"{{ url('api/pay/payCard') }}?"+payurl,
					cache:false,
					headers:{'api-token':token},
					success:function(data){

						if(data.status === 0){
						    layer.open({
							    content: data.message,
							    skin: 'msg',
							    
							    shadeClose:true,
							    btn:['确定'],
							    yes:function(index,layero){
							    	layer.closeAll();
							    }
							});
						}else{
							layer.open({
							    content: data.message,
							    skin: 'msg',
							    
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
		})
	</script>
</body>
</html>