#分类
## 获取分类列表
> /api/category/list

请求方式:`GET`
参数(可选，默认返回第一页数据)
```
page int //页码
page_size int //每页个数
```

响应数据
http code = 200
```
{
    page: {
        total: 8,   //总页数
        current: 1, //当前页数
        size: 1 //每页个数
    },
    list: [
        {
            id: "5JlM",
            name: "分类8"
        }
    ]
}
```
http code = 403
```
{
  "msg": "需要白金vip"
}
```