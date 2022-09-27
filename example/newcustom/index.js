

/**
 * @api {get} [path] 接口标题
 * @apiGroup 组昵称
 * @apiName 再加个Name
 * @apiDescription Compare version 0.3.0 with 0.2.0 and you will see the green markers with new items in version 0.3.0 and red markers with removed items since 0.2.0.
 * @apiParam {Number[2..5]} [id] Users unique ID.
 */
function abc(){}

/**
注释 - 参数解析data  -  在dumi中的映射
apiVersion 0.3.0   解析data中version， 如果没有则默认0.0.0 
apiName  解析data中name: 是“方法名-同时是左侧菜单的标题，如果没有则默认为 {type} + path 的大驼峰命名”

 */