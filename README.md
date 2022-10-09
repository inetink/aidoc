# aidoc 
[TOC]

用于快速将注释直接转为文档站点，基于apidoc库，全部支持apidoc文档使用(但未完全展示全部参数结构)，专属官方文档后续建设。

目前正在快速完善中，如果 **遇到问**，可以issue反馈或回复邮件[inetink@126.com]。

感谢使用！


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

在实现过程中，代码/类会越来越多，实现一个很好的目录（菜单）就非常重要，而由于是自动生成的文档，所以要实现排序并能定义排序变得困难，本次升级过程就是要很好的解决排序问题，让排序变得规范化、简单化。

### 期望
```
# 关于目录及排序方案的实现，我们首先讨论并确定一下期望，制定好目标，再进行实现。

## 场景一：

1.一篇接口文档，一般包含以下内容：
	接口标题  --->  这个标题一般也会作为左侧最小子菜单
	简单说明
	请求路径
	参数说明
	返回值
	请求示例
	返回示例

2.当实现的接口变多后，我们就希望将接口按功能模块进行分组，比如：
	一个文章模块，实现了“增删改查”4个接口，所以我们将其定义为同一个组“文章组”。

	|-- 文章组
	|------- 新增文章
	|------- 删除文章
	|------- 修改文章
	|------- 查询文章
	|-- 认证组
	|------ 登陆
	|------ 修改密码
	...

3.而当模块也变的更多后，同理，您可能也希望将其进行分组。
	
	|-- 网站接口
	|------ 文章组
    |------ ...
	|-- 商城接口

## 场景二：
1.一篇接口文档，同场景一是一样的；
2.当接口变多，如果按场景一2的菜单形式，我们可能觉的菜单又变得太多太长了。所以我们可能将“左侧菜单”直接按组展示，而在右侧做小菜单：
	
	｜-- 文章模块			
	｜-- 认证模块
								- 登陆
								- 修改密码
	...

3.同场景一3的菜单，但减少了最小子菜单一层（具体接口是数量最多的一层）


## 场景三：
当接口数量增多，或模块增多时，前两个场景都是在解决左侧菜单数量的问题，这里再给个一个方案，可以通过解决 navs （导航栏）来增加模块，从而更友好的展示文档。

```
看完👆三个场景，具体操作请看接下来的`原理分析`

### 原理分析

如上，了解我们的期望值后，我们可以看到场景一在接口数量少时，或者说项目初期时可以很好的应用，而当系统庞大接口变得更多时场景一就变得不再友好，我们考虑以下实现方式。
```
# 开发中我们会创建很多 controller 文件，每个控制器文件存在很多接口方法，同一个模块下（如：文章模块）很可能有多个controlle文件，所以我们为了方便分组和排序采取以下措施：

1.首次构建文档，自动程序仅实现具体接口的文档构建，并以此生成“菜单文件”；
2.用户可通过直接修改“菜单文件”为期建立"分组" 和实现"排序"操作；

apidoc中，解析 apiGoup 为左侧菜单标题，默认值：文件路
apidoc中，解析 apiName 为接口名称，默认值：@api {get} path 是get和path的大坨峰值

# 在dumi中，默认情况是一个md文件一个菜单

```

### 最终实现
```
	｜-- 文章模块	(apiGroup)		
	｜-- 认证模块
								- 登陆 (apiName)
								- 修改密码
	...

# 如果想要将多个 apiGroup 包含到同一个“父级菜单”

|-- 父级菜单
	｜-- 文章模块	(apiGroup)		
	｜-- 认证模块
								- 登陆 (apiName)
								- 修改密码
	...
# 需要设置menus：
[
  {
    title: '父级菜单名',
    children: [				// 多个apiGroup名称
      "apiGroup 名称",
      "apiGroup",
      "apiGroup",
    ],
  },
],


```

