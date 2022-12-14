

/**
 * @api {get} [path] 接口标题
 * @apiGroup apiGroup组名2
 * @apiName 排序测试a1
 * @apiDescription Compare version 0.3.0 with 0.2.0 and you will see the green markers with new items in version 0.3.0 and red markers with removed items since 0.2.0.
 * @apiParam {Number[2..5]} [id] Users unique ID.
 */
function abc(){}

/**
注释 - 参数解析data  -  在dumi中的映射
apiVersion 0.3.0   解析data中version， 如果没有则默认0.0.0 
apiName  解析data中name: 是“方法名-同时是左侧菜单的标题，如果没有则默认为 {type} + path 的大驼峰命名”

 */



 /**
 * @api {get} [path] 接口标题2
 * @apiGroup apiGroup组名
 * @apiName 排序测试a2
 * @apiDescription Compare version 0.3.0 with 0.2.0 and you will see the green markers with new items in version 0.3.0 and red markers with removed items since 0.2.0.
 * @apiParam {Number[2..5]} [id] Users unique ID.
 */
function abc2(){}

 /**
 * @api {get} [path] 接口标题3
 * @apiGroup apiGroup组名
 * @apiName 排序测试a3
 * @apiDescription Compare version 0.3.0 with 0.2.0 and you will see the green markers with new items in version 0.3.0 and red markers with removed items since 0.2.0.
 * @apiParam {Number[2..5]} [id] Users unique ID.
 */
function abc2(){}


/**
 * @api {Any} /module/controller/action 北包包
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
function abc3(){}

// a