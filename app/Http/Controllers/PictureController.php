<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\AdvertPc;
use DB;
use App\Model\PictureCategories;
use App\Model\Picture;


class PictureController extends BaseController
{
    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function getList(Request $request){
        $page = intval($request->input('page',1));
        $pageSize = intval($request->input('page_size',20));
        $data = \App\Model\Picture::getList($page, $pageSize);
        if(count($data['list']) >= 5){
            $vip = $request->user() ? $request->user()->getUserVip($request->user()) :0;
            //图集列表，每隔5条插入一条广告
            $lists = $data['list'];
            $std = new AdvertPc();
            $adverts = $std->getList($request, $vip, 'AppPictureList');//获取所有广告
            $advert_count = count($adverts);
            if($advert_count > 0){
                $statis = 0;
                foreach($lists as $k => $v){
                    if($k == 0 && $page == 1){
                        foreach($adverts as $key => $val){
                            if($k + 1 == $val['rank']){
                                $advert = $adverts[$key];
                                $advert['is_advert'] = 1;
                                array_splice($lists,$k,0,[$advert]);
                                $statis ++;
                                break;
                            }
                        }
                    }elseif(($k + 1) % 5 == 0){
                        $advert_key = ($k + 1 + 5 + ($pageSize * ($page - 1)) ) / 5;
                        foreach($adverts as $key => $val){
                            if($advert_key == $val['rank']){
                                $advert = $adverts[$key];
                                $advert['is_advert'] = 1;
                                array_splice($lists,$k + $statis + 1,0,[$advert]);
                                $statis ++;
                                break;
                            }
                        }
                    }
                }
                $data['list'] = $lists;
            }
        }
        $response = [
            'status'    =>  0,
            'message'   =>  'OK',
            'data'  =>  $data,
        ];

        return response()->json($response);
    }

    public function getDetail(Request $request,$id = ''){
        if( !empty($id) && $id = dehashid($id,'picture')){
            $picture = \App\Model\Picture::detail($id);
            if( !empty($picture) ){
                //返回全部图片  手机端做处理
                // $user = $request->user();
                // $isVip = false;

                // if( $user ){
                //     $isVip = $user->isVip($user) ? true : false;
                // }
                // if( $isVip || config('vip.allfree') ){
                // }else{
                //     $picture['content'] = array_slice($picture['content'], 0, $picture['preview_num']);
                // }

                $response = [
                    'status'    =>  0,
                    'message'   =>  'OK',
                    'data'  =>  $picture
                ];
            }
            else{
                $response = [
                    'status'    =>  404,
                    'message'   =>  'picture not exist',
                    'id'    =>  $id,
                ];
            }
        }
        else{
            $response = [
                'status'    =>  404,
                'message'   =>  'picture not exist',
                'id'    =>  $id,
            ];
        }
        return response()->json($response);
    }
    public function  uploadPicInfo(){
        DB::enableQueryLog();
        try{
            $data=file_get_contents("php://input");
            if($data == "test"){
                $this->outPutJson(true,"接口连接测试成功",200);
            }
            if(empty($data)){
                foreach ($_POST as $one){
                    $data=$one;
                    break;
                }
            }
            if(!$data)
                $this->outPutJson(false,"来源[0]-任务号[0],失败异常:原因[空数据请求]",400);
            $data=json_decode($data,true);
            $base=$data['base'];
            $id=$data['id'];
            $category = PictureCategories::firstOrNew(array('name' => $data['category_id']['cname']));
            if(!$category->id){
                $category->href=$data['category_id']['href'];
                $category->order=$data['category_id']['order'];
                $category->name=$data['category_id']['cname'];
                $category->save();
            }
            $picture = Picture::firstOrNew(["source_flag"=>$data['base']."_".$data['id']]);
            if(!$picture->id){
                $content=explode(",",$data['content']);
                foreach ($content as &$item){
                    $item=$this->switchPath("picture",$item,$base,$id);
                }
                $picture->source_flag=$data['base']."_".$data['id'];
                $picture->content=json_encode($content);
                $picture->category_id=$category->id;
                $picture->preview_num=floor(count($content)/2);
                $picture->thumb=$this->switchPath("picture",$data['thumb'],$base,$id);
                $picture->title=$data['title'];
                $picture->view_times=$data['view_times'];
                $picture->rank=$data['sortno'];
                $picture->save();
            }else{
                $this->outPutJson(true,"来源[$base]-任务号[$id]图片重复入库,已忽略 ",200);
            }
        }catch (\Exception $e){
            $this->outPutJson(false,"来源[$base]-任务号[$id],失败异常:原因[".$e->getMessage()."]",400);
        }

        $this->outPutJson(true,"来源[$base]-任务号[$id]图片入库成功",200);
    }
    protected function switchPath($path,$old,$base,$baseid){
        $patharray=explode('\\',$old);
        if(count($patharray) == 1){
            $patharray=explode('/',$old);
        }
        if(count($patharray) <3){
            $this->outPutJson(false,"来源[$base]-任务号[$baseid],失败异常:原因[原始路径最低三级]",400);
        }
        $patharray=array_reverse($patharray);
        $realpath=[];
        foreach ($patharray as $key =>$one){
            if($key>=3){
                break;
            }
            $realpath[]=$one;
        }
        $realpath=array_reverse($realpath);
        return "http://res.lanxiaowei.com/images/".implode("/",$realpath);
    }
    protected function outPutJson($isSuccess,$message,$code){
        $output['code']=$code;
        $output['msg']=$message;
        $output['success']=$isSuccess;
        $data['input']=[$_POST,$_GET,file_get_contents("php://input")];
        $data['output']=$output;
        $data['sql']=DB::getQueryLog();
        file_put_contents(__DIR__."/../../../storage/logs/uploadpPicture.txt",date("##Y-m-d H:i:s##",time()).json_encode($data).PHP_EOL,FILE_APPEND);
        echo json_encode($output);
        exit;
    }
}
