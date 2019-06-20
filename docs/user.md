#创建用户
首次启动APP时，调用此接口，创建用户

请求方式 `POST`
> /api/user/{identifier}

identifier为客户端根据手机生成的唯一标识
参数
```
nickname string //客户端为用户生成昵称
channel string //客户端渠道号
```
响应数据
http code :200
注册/登录成功
```
{
  "id": "Kd7V19",
  "nickname": "nima",
  "identifier": "816de09025e613c129780e6f6759acb2",
  "api_token": "296c4446d71b21e180a7fb6e44117785",
  "vip_type": 1,
  "created_at": "2017-03-31 16:37:24",
  "updated_at": "2017-04-02 10:44:07",
  "channel": "1001",
  "vip": {
    "title": "免费会员"
  }
}
```
http code != 200 注册/登录失败

登录成功后，保留此api_token，在访问其他接口时，http header信息中添加api_token字段用于标识登录