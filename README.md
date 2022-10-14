# aidoc 
[TOC]

用于快速将注释直接转为文档站点，基于apidoc库，全部支持apidoc文档使用(但未完全展示全部参数结构)，专属官方文档后续建设。

目前正在快速完善中，如果 **遇到问**，可以issue反馈或回复邮件[inetink@126.com]。

感谢使用！

## 安装

全局安装后执行`aidoc`命令即可完成使用。

```
npm -g install aidoc
```

常用命令
```
aidoc -i ./src -w   	# 以监控模式运行，一般开发时使用

aidoc -i ./src 			# 编译输出文档
```


## 使用教程

使用简单，最多需要2步：

> 1.创建apidoc.json文件，指定项目及解析源码目录等被支持项（根据情况创建）；

> 2.为方法书写aidoc注释；

步骤2中给出了常用示例，详细支持文档后续给出，初步可参考原(apidocjs.com)

### 步骤2：（API常用注释段示例）
```
/**
 * @api {Any} /module/controller/action 小标题
 * @apiGroup 用户认证
 * @apiName 登录认证
 * @apiVersion 0.0.1
 * @apiDescription 接收用户账号和密码进行登录认证，登录成功并返回access-token
 *
 * @apiBody {String}  [username] 用户账号/用户名
 * @apiBody {String}  [password] 用户密码
 *
 * @apiSuccess {Number} code    状态码，0：请求成功
 * @apiSuccess {String} message   提示信息
 * @apiSuccess {Object} [data]    返回数据
 *
 * @apiSuccessExample {json} Success-Response:
 * {"code":0,"message":"","data":[]}
 *
 * @apiErrorExample {json} Error-Response:
 * {"code":5001,"message":"接口异常"}
 */
```
### 步骤1：（apidoc.json示例）

较apidocjs新增‘首页配置’，暂时移除对header和footer的支持，但新增支持index.md（来配置首页） 
```
{
  "name": "项目标题",
  "version": "0.3.0",
  "description": "文档描述",
  "title": "Custom AiDoc Title",
  "url" : "https://api.example.com",
  "sampleUrl": "https://apidoc.free.beeceptor.com",
  "output": "./apidoc-output",
  "input":["./"],
  "index": {
    "title": "首页内容-支持dumi的FrontMatter配置",
    "filename": "footer.md"
  },
  "order": [
    "User",
    "PostUser",
    "GetUser",
    "City",
    "Category (official)"
  ],
  "template": {
    "showRequiredLabels": false,
    "withCompare": true,
    "withGenerator": true,
    "aloneDisplay": false
  }
}

```

## 菜单排序专题



