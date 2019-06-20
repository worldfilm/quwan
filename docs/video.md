#视频

##免费专区
> /api/video/list/free

请求方式  `GET`
参数
```
page int //用于翻页
page_size int //指定每页个数
```
详情
http code == 200
```
{
    "page": {
        "total": 29,
        "current": 1,
        "size": 1
    },
    "category": {
        "id": "6V4K",
        "name": "分类1"
    },
    "list": [
        {
            "id": "qARdM5d9xn",
            "title": "免费-test-1",
            "thumb_img_url": "https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png"
        }
    ]
}
```


##黄金会员
> /api/video/list/gold

参数
```
page int //用于翻页
page_size int //指定每页个数
```
请求方式  `GET`

响应
http code  == 200

```
{
    "page": {
        "total": 75,
        "current": 1,
        "size": 1
    },
    "category": {
        "id": "MrgK",
        "name": "分类2"
    },
    "list": [
        {
            "id": "MboKbXdkwA",
            "title": "黄金-test-30",
            "thumb_img_url": "https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png"
        }
    ]
}
```
http code == 403 
```
{
  "msg": "需要黄金vip"
}
```

##根据分类ID获取视频列表（白金VIP）
> /api/video/list/{category_id}

category_id 为分类列表中的id
请求方式`GET`
响应
http code == 200
```
{
    "page": {
        "total": 1,
        "current": 1,
        "size": 2
    },
    "category": {
        "id": "KnAM",
        "name": "分类3"
    },
    "list": [
        {
            "id": "ROlZqYBZ8b",
            "title": "白金-test-1",
            "thumb_img_url": "https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png"
        }
    ]
}
```

http code == 403
```
{
  "msg": "需要白金vip"
}
```

## 获取视频详情（播放用）
> /api/video/detail/{video_id}

请求方式 `GET`

响应
```
{
	status: 0,
	message: "OK",
	data: {
		id: "Nbkg9OZq0n",
		title: "免费-test-6",
		video_url: "http://www.mvmyusa.com/2016/10//25070619163-21273/index.m3u8",
		thumb_img_url: "https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_120x44dp.png",
		created_at: "2017-03-31 16:25:46",
		category: {
			id: "6V4K",
			name: "分类1"
		}
	}
}
```
