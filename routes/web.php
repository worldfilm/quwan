<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/home', 'HomeController@index');

Route::group(['prefix'	=>	'/api'],function(){
	//用户
	Route::group(['prefix'	=>	'/user'], function(){
		Route::get('/', 'UserController@getUser');

		//Route::post('/{id}', 'UserController@postUser');

        Route::post('/register/{id}', 'UserController@register');
        Route::post('/login/{id}', 'UserController@login');
        Route::get('/loginout','UserController@loginout');
        Route::get('/check/{id}', 'UserController@checkUser');
        Route::get('/list/collect','UserController@getUserCollect');
        Route::get('/verifyCode/{type}/{phone}','UserController@getVerifyCode');
        Route::post('/bind','UserController@bindPhone');
        Route::post('/edit_bind','UserController@unbindPhone');
        Route::post('/reset','UserController@resetPassword');
        Route::any('/captcha','UserController@getCaptcha');
        Route::get('/promote','UserController@markPromote');
        Route::get('/exchange/{porint}','UserController@doExchange');
	});

	//分类
	Route::group(['prefix'	=>	'/category'], function(){
		Route::get('/list', 'CategoryController@getList');
	});

    //new提现
    Route::group(['prefix' => '/withdraw'],function (){
        Route::get('/bankcard','WithdrawController@bankcard');
        Route::post('/bind','WithdrawController@bind');
        Route::post('/commit','WithdrawController@commit');
        Route::get('/index','WithdrawController@index'); //提现入口
        Route::get('/record','WithdrawController@record');
    });

    //new轮播图和公告
    Route::group(['prefix'	=>	'/banner'], function(){
        Route::get('/list', 'VideoController@bannerList');
    });

    //new游戏界面入口/额度转化
    Route::group(['prefix'	=>	'/game'], function(){
        Route::get('/index', 'GameController@index');
        Route::get('/transfer', 'GameController@transferH5');
        //获取中奖信息
        Route::get('/winnersRecords','GameController@getWinnersRecords');
        //钱包路由
        Route::get('/wallet','GameController@wallet');
        //从game项目拷过来
        Route::get('/login/{platform}','GameController@login');
        Route::get('/transfer/{platform}','GameController@transfer');
        Route::get('/oneKeyTransfer','GameController@oneKeyTransfer');
        Route::get('/transferRecord/{platform}','GameController@getTransferRecord');
        Route::get('/balance','GameController@balance');
        Route::get('/balance/{platform}','GameController@balance');
        Route::get('/gameUrl/{platform}','GameController@getGameUrl');
        Route::get('/transferRecord','GameController@getTransferRecord');
        Route::get('list','GameController@getList');
        Route::get('/recordH5','GameController@recordH5');
    });

    //new轮播图和公告
    Route::group(['prefix'	=>	'/account'], function(){
        Route::get('/record', 'GameController@record');
    });

	//视频
	Route::group(['prefix'	=>	'/video'], function(){
		Route::get('/list/free','VideoController@getFreeList');
		Route::get('/list/vip','VideoController@getVipList');
		Route::get('/recommend','VideoController@recommend');
		Route::get('/favorite','VideoController@favorite');
		Route::get('/detail/{video_id?}', 'VideoController@getDetail');
		Route::get('/play/{video_id?}', 'VideoController@getPlay');
		Route::get('/like/{video_id?}', 'VideoController@addLike');
		Route::get('/dislike/{video_id?}', 'VideoController@disLike');
		Route::get('/collect/{video_id?}', 'VideoController@createCollect');
		Route::get('/cancelCollect/{video_id?}', 'VideoController@cancelCollect');
		Route::get('/review/{video_id}','VideoController@getReviews');
		Route::get('/recommends/{video_id}','VideoController@getRecommends');
		//Route::post('/publishReview/{video_id}','VideoController@postReviews');
        Route::post('/uploadVideoInfo', 'VideoController@uploadVideoInfo');
        Route::get('/list/search','VideoController@getSearchList');
        Route::get('/list/hotSearch','VideoController@getHotSearchList');//获取热门搜索视频

        Route::post( '/upload', 'VideoController@upload' );//批量上传视频
        Route::post( '/addMyVideo', 'VideoController@addMyVideo' );//提交用户上传的视频信息
        Route::post( '/buyVideo', 'VideoController@buyVideo' );//购买视频
        Route::get( '/isBought', 'VideoController@isBought' );//判断视频是否已购买
        Route::get( '/getMyVideo', 'VideoController@getMyVideo' );//我的视频,上传列表
        Route::get( '/getPurchase', 'VideoController@getPurchase' );//我的视频,购买列表
        Route::get( '/videoBill', 'VideoController@getVideoBill' );//账单
        Route::get( '/collectCount', 'VideoController@getCollectCount' );//收藏视频总数
	});

	//新闻管理
	Route::group(['prefix'	=>	'/news'], function(){
		Route::get('/', 'NewsController@newsList');
	});

	//图片管理
	Route::group(['prefix'	=>	'/picture'], function(){
		Route::get('/list', 'PictureController@getList');
		Route::get('/detail/{id}', 'PictureController@getDetail');
        Route::post('/uploadPicInfo', 'PictureController@uploadPicInfo');
	});

	//会员管理
	Route::group(['prefix'	=>	'/vip'], function(){
		Route::get('/','VipController@getList');
		Route::get('/settype','VipController@setType');
		Route::get('/addvip','VipController@addVip');
		Route::get('/purchase','VipController@getPurchase');
		Route::get('/purchase/result','VipController@getPurchaseResult');
	});

	//版本信息
	Route::group(['prefix'	=>	'/version'], function(){
		Route::get('/update', 'VersionController@getUpdate');
	});

	//基础配置
	Route::group(['prefix'	=>	'/config'], function(){
		Route::get('/list/{alias_name?}', 'ConfigController@getConfig');
	});

	//系统配置
	Route::group(['prefix'	=>	'/sys'],function(){
		Route::get('/notice', 'SysController@getNotice');
		Route::get('/pay/list','SysController@getPayList');
	});

	//广告
	Route::group(['prefix' => '/advert'],function(){
			Route::get('/list','AdvertController@getAdverts');
            Route::get('/new_list','AdvertController@getNewAdverts');
	});

	//支付回调
	Route::group(['prefix'	=>	'/pay'], function(){
		Route::get('/qr',['as'=>'pay.qr', 'uses'=>'PayController@getQR']);
		Route::get('/','PayController@pay');
		Route::get('/h5','PayController@payHtml'); //充值入口
		Route::get('/card','PayController@cardSecretView');
		Route::get('/payCard','PayController@cardSecretPay');
        Route::get( '/manualRecharge', ['as'=>'pay.manualRecharge', 'uses'=>'PayController@manualRecharge']);
		Route::get( '/checkOrder', [ 'as'=>'pay.check', 'uses'=>'PayController@checkOrder']);
		Route::post('/notify/goldenpay', 'PayController@postNotifyGlodenpay');
		Route::get('/return/baotee', 'PayController@getReturnBaoteepay');
		Route::post('/notify/baotee', 'PayController@postNotifyBaoteepay');
		Route::post('/notify/baoteewx', 'PayController@postNotifyBaoteewxpay');
		Route::post('/notify/lepay','PayController@postNotifyLePay');
		Route::any('/notify/juxinpay','PayController@postNotifyJuXinpay');
		Route::any('/notify/ztbaopay','PayController@postNotifyZtbaoPay');
		Route::any('/notify/startpay','PayController@postNotifyStartPay');
		Route::any('/notify/htpay','PayController@postNotifyHTPay');
		Route::any('/notify/jinyangpay','PayController@postNotifyJinYangPay');
		Route::any('/notify/liyingpay','PayController@postNotifyLiYingPay');
		Route::any('/notify/hhpay','PayController@postNotifyHHPay');
		Route::any('/notify/avpay','PayController@postNotifyAvPay');
		Route::any('/notify/forpay','PayController@postNotifyForPay');
		Route::any('/notify/doctorpay','PayController@postNotifyDoctorPay');
		Route::any('/notify/bosspay','PayController@postNotifyBossPay');
		Route::any('/notify/ytpay','PayController@postNotifyYtPay');
		Route::any('/notify/rypay','PayController@postNotifyRyPay');
		Route::any('/notify/stpay','PayController@postNotifyStPay');
		Route::any('/notify/zxtpay','PayController@postNotifyZxtPay');
		Route::any('/notify/yfpay','PayController@postNotifyYfPay');
		Route::any('/notify/tjfpay','PayController@postNotifyTjfPay');
		Route::any('/notify/ymfpay','PayController@postNotifyYmfPay');
		Route::post('/notify/alipay','PayController@postNativeAlipayNotify');
		Route::get('/notify/sxpay','PayController@postNotifySXPay');
		Route::post('/notify/hypay','PayController@postNotifyHYPay');
		Route::post('/notify/bxfpay','PayController@postNotifyBXFPay');
		Route::post('/notify/jcpay','PayController@postNotifyJCPay');
		Route::get('/notify/sbpay','PayController@postNotifySBPay');
		Route::post('/notify/kjpay','PayController@postNotifyKJPay');
		Route::post('/notify/kkpay','PayController@postNotifyKKPay');
		Route::post('/notify/ttpay','PayController@postNotifyTTPay');
		Route::any('/notify/qjpay','PayController@postNotifyQJPay');
		Route::any('/notify/slpay','PayController@postNotifySlPay');
        Route::any('/notify/mtlpay','PayController@postNotifyMtlPay');
		Route::get('/notify/yunpay','PayController@postNotifyYunPay');
		Route::get('/notify/tspay','PayController@postNotifyTSPay');
		Route::post('/notify/hfpay','PayController@postNotifyHFPay');
        Route::get('/achieve','PayController@notifySuccess');
		Route::get('/success', 'PayController@success');
	});
});
