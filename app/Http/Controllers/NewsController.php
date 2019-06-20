<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use App\Model\News;

class NewsController extends BaseController {

    /**
     * [__construct description]
     */
    public function __construct()
    {
        $this->response = ['status' => 0, 'message' => 'OK', 'data' => []];
    }

    /**
     * 获取新闻广播
     * @return [type] [description]
     */
    public function newsList()
    {
        $this->response['data'] = News::getNews();

        return response()->json($this->response);
    }

}
