<?php
if (! function_exists('hashid')) {
    /**
     * 加密数字
     *
     */
    function hashid($id,$type = 'video'){
        $config = config('hashid.'.$type);
        $hashids = new \Hashids\Hashids($config['salt'],$config['length'],$config['alphabet']);
        return $hashids->encode($id);
    }
}
if (! function_exists('dehashid')) {
    /**
     * 加密数字
     *
     */
    function dehashid($id,$type = 'video'){
        $config = config('hashid.'.$type);
        $hashids = new \Hashids\Hashids($config['salt'],$config['length'],$config['alphabet']);
        $data = $hashids->decode($id);

        return isset($data[0]) ?  $data[0] : false;
    }
}

if(!function_exists('get_param') ){
    function get_param($request, $key){
        $jsonData = $request->toArray();
        $data = $request->input($key) ? $request->input($key) : (isset($jsonData[$key]) ? $jsonData[$key] : '');
        return $data;
    }
}

if(!function_exists('randFloat') ){
    function randFloat($min=0, $max=2){
        return $min + mt_rand()/mt_getrandmax() * ($max-$min);
    }
}

if (! function_exists('generateWithdrawNo')) {
    /**
     * @return string
     */
    function generateWithdrawNo(){
        $uuid = uniqid('',true);
        $suffix = substr($uuid, strpos($uuid, ".") + 1);
        return 'WD'.date('YmdHis').$suffix;
    }
}

if ( ! function_exists('goods_name'))
{
    /**
     * Get the configuration path.
     *
     * @param  string $path
     * @return string
     */
    function goods_name($price )
    {
        $goodsArray1 = [
            ['name'=>'晨光中性笔12支'],
            ['name'=>'益而高海绵头液体胶水50ml*40'],
            ['name'=>'益而高加厚型订书针*30'],
            ['name'=>'晨光方/圆创意可爱桌面笔筒*4'],
            ['name'=>'广博(GuangBo)20只装加厚透明文件袋'],
            ['name'=>'广博(GuangBo)60只装15mm彩色长尾夹'],
            ['name'=>'广博(GuangBo)五层桌面文件柜/档案柜/资料柜'],
            ['name'=>'正彩（ZNCI）亚克力证件卡套竖式胸卡挂绳学生证工作厂牌证办公用品6个/盒'],
            ['name'=>'正彩（ZNCI）幸运草彩色资料册A4文件夹文件册学生试卷夹60页办公用品文具'],    
            ['name'=>'正彩（ZNCI）多层拉链手提文件袋/公文会议包/档案袋/商务办公用品多功能事务'],    
        ];
        $goodsArray2 = [
            ['name'=>'广博(GuangBo)6卷装60mm*150y透明封箱宽胶带胶布'],
            ['name'=>'广博(GuangBo)25cm旋转地球仪'],
            ['name'=>'广博(GuangBo)9位数自动号码机'],
            ['name'=>'晨光9位自动号码机金属数字打码机日期打码器AYZ97532'],
            ['name'=>'康巴丝挂钟 创意时尚时钟 静音石英客厅卧'],
            ['name'=>'广博(GuangBo)四层报刊架'],
            ['name'=>'三木(SUNWOOD)80页标准型资料册 蓝色 6册装 '],
            ['name'=>'信发（TRNFA）TN-M680 笔筒实木收纳座 双格万年历笔筒'],
            ['name'=>'西玛黄 SKPJ101用友凭证纸A4金额记账凭证打印纸 财务办公'],    
            ['name'=>'英雄（HERO）英雄100钢笔男士金笔14K金礼品钢笔'],    
        ];
        $goodsArray3 = [
            ['name'=>'斐利比（PHILIPPI）创意商务办公用品 旋转决策笔 金属圆珠笔'],
            ['name'=>'广博(GuangBo)80*60cm电子荧光板/带架子LED广告牌办公用品'],
            ['name'=>'益而高加厚型订书针*30'],
            ['name'=>'正彩（ZNCI）创意多功能电源记事本/笔记本文具办公用品 4323黑色 '],
            ['name'=>'施德楼（STAEDTLER） 920 固体胶 粘帖胶棒 学生 办公用品'],
            ['name'=>'广博(GuangBo)60只装15mm彩色长尾夹*12'],
            ['name'=>'广博(GuangBo)五层桌面文件柜/档案柜/资料柜'],
            ['name'=>'正彩（ZNCI）亚克力证件卡套竖式胸卡挂绳学生证工作厂牌证办公用品6个/盒*20'],
            ['name'=>'正彩（ZNCI）幸运草彩色资料册A4文件夹文件册学生试卷夹60页办公用品文具*50'],    
            ['name'=>'正彩（ZNCI）多层拉链手提文件袋/公文会议包/档案袋/商务办公用品多功能事务*30'],    
        ];
        $pointer = rand(0,9);
        if( $price < 100  ){

            $goods_name = $goodsArray1[$pointer];
        }elseif( $price < 300 && $price >=100 ){

            $goods_name = $goodsArray2[$pointer];
        }elseif( $price >= 300  && $price < 600){

            $goods_name = $goodsArray3[$pointer];
        }
        return $goods_name['name'];
    }
}