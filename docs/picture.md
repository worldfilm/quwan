#图片区
## 获取图集列表
> /api/picture/list

请求方式`GET`
参数
```
page int //页码（默认为1）
page_size int //每页个数
```
响应
```
{
    status: 0,
    message: "OK",
    data: {
        page: {
            total: 35,
            current: 1,
            size: 1
        },
        list: [
            {
                id: "7n9eDle1Az",
                title: "test",
                thumb: "http://images.17173.com/2012/dota2/2012/01/04/dota2_showgirl_120114001.jpg"
            }
        ]
    }
}
```

## 获取图集详情


> /api/picture/{picture_id}

请求方式`GET`
picture_id为图集列表返回的ID
详情
```
{
    status: 0,
    message: "OK",
    data: {
        id: "7n9eDle1Az",
        title: "test",
        thumb: "http://images.17173.com/2012/dota2/2012/01/04/dota2_showgirl_120114001.jpg",
        content: [
            "http://images.17173.com/2012/dota2/2012/01/04/dota2_showgirl_120114002.jpg"
        ],
        preview_num: 1, //免费会员允许预览的张数
        total_num: 2 //图集总张数
    }
}
```


