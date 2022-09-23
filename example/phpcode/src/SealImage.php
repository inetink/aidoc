<?php


namespace App\Libraries\DrawSeal\Seals;

/**
 * 印章的制作
 * 支持圆形章、椭圆形章、正方形方章、长方形方章
 * 思路原理：
 *      1.计算机中无论图形是什么样的，最终这张图像始终是2D矩形的图片，坐标系x、y则是整张图形的宽高。
 *      2.圆形章中将每个文字看作是一个小正方形，那么章子内则需要让小正方形旋转相应角度后，围绕圆心进行排列。
 *      3.由1、2得出要想围绕圆心排序需解决：a.每张文字小图旋转多少度？b.旋转后每张图布局（图层合并）到圆形底图时的坐标是多少？
 *      4.方章中 2-4 一般会制作正方形方章，而超过4个字时一般会制作长方形方章，这点可以根据需要进行操作
 *      5.关于生成图片的大小部分：a.此类的圆形章可以指定图片大小，字体排列时指定锁住
 * 1.无需指定图片宽高，会根据字体自动生成，暂不支持修改字体大小
 * 2.默认支持输入2-4个汉字字符，产出"方形个人章"
 * 3.如果超出4个汉字字符，则生成"矩形方章"，暂不支持换行
 */
class SealImage
{

//    支持的字体大小：（字体大小，bfSize, edgeSize, 产出的width、height）
//    25，
    private $sealString;    // 设置：印章字符（方形章或圆形章中的主要文字）
    private $font;          // 设置：印章字符的字体, 默认字体为"宋体"因为支持广泛（一般章子中使用的字体是相同的，所以如果要使用不同字体，可以看调用时的方法是否支持，或自己修改）
    private $edgeSize;      // 设置：边框的大小，单位同图片的宽高，像素
    private $bfSize;        // 设置：border与 字 间留一个小距离
    private $fontColor;     // 设置：字体即边框颜，数组格式[r,g,b,a]

    private $vix = 0;       // 可选设置：x轴的偏移量，默认为0，-1向左，+1向右（因字体基线原因，文字绘制在图上时存在偏差）
    private $viy = -4;      // 可选设置：y轴的偏移量，默认为-4，因默认字体（宋体）基线原因，文字绘制在图上时文字偏下，所以向上偏移4

    private $strLen;        // 程序：$sealString字符个数
    private $img;           // 程序：生成的图像
    private $width;         // 程序：生成的图像-宽
    private $height;        // 程序：生成的图像-高
    private $_fontColor;    // 程序：字体颜色同fontColor，转换得来，初始化图像后程序用值类型



    /**
     * @api /user/id 创建方形章图片
     * @apiPrivate
     * @apiBody string $sealString 文字内容，最大支持4个
     * @apiBody string $font 字体，默认宋体（支持汉字较为全面）
     * @apiBody int $fsize 图片文字大小，默认50px
     * @apiBody int $border 边框粗细，默认10px
     * @apiBody int $bz 边框与字的距离/或理解为字间距，默认10px
     * @apiBody int $vix 文字左右偏移量
     * @apiBody int $viy 文字上下偏移量
     *
     * @apiDescription 无需设置图片的宽高，宽高 = 文字大小 * 2 + 边框 * 2 + 间距 * 3 = 由默认值得图片宽高大小为150x150
     */
    public function __construct($sealString = "茕茕孑立", $font = "", $fsize = 50, $border = 10, $bz = 10, $vix = 0, $viy = -4)
    {
//        要求汉字字符utf8，不可以有空字符，如果没有可以编码处理
//        $charset = mb_detect_encoding($this->sealString);
//        if ($charset != 'UTF-8') {
//            $this->sealString = mb_convert_encoding($this->sealString, 'UTF-8', 'GBK');
//        }
        $this->sealString = $sealString;
        $this->font = empty($font) ? "./Songti.ttc" : $font;
        $this->fontSize = $fsize; // 像素值
        $this->edgeSize = 8;
        $this->bfSize = 12;
        $len = mb_strlen($this->sealString, 'utf8');
        $this->strLen = $len;
        $this->fontColor = [218, 27, 27, 127 * 0];
    }

    /**
     * @api {get} aa make() 方法
     * 根据初始化的字体大小，创建一个正方形图章
     * 
     */
    public function make()
    {
        $this->height = $this->width = 2 * $this->edgeSize + 2 * $this->fontSize + 3 * $this->bfSize;
        $this->img = imagecreatetruecolor($this->width, $this->height);   // 创建一个正方形的图像
        $bgcolor = imagecolorallocatealpha($this->img, 0, 255, 255, 127);
        imagefill($this->img, 0, 0, $bgcolor); // 填充背景色，
        $this->_fontColor = imagecolorallocatealpha($this->img, $this->fontColor[0], $this->fontColor[1], $this->fontColor[2], $this->fontColor[3]);;
        imagefilledrectangle($this->img, 0, 0, $this->width, $this->edgeSize, $this->_fontColor);
        imagefilledrectangle($this->img, 0, $this->height - $this->edgeSize, $this->width, $this->height, $this->_fontColor);
        imagefilledrectangle($this->img, 0, 0, $this->edgeSize, $this->height, $this->_fontColor);
        imagefilledrectangle($this->img, $this->width - $this->edgeSize, 0, $this->width, $this->height, $this->_fontColor);

        $radius = $this->edgeSize;
        $corner = $this->makeRadiusImg($radius);
        imagecopymerge($this->img, $corner, -1, -1, 0, 0, $radius, $radius, 100);
        imagecopymerge($this->img, imagerotate($corner, 270, $bgcolor), $this->width - $radius + 1, -1, 0, 0, $radius, $radius, 100);
        imagecopymerge($this->img, imagerotate($corner, 90, $bgcolor), -1, $this->height - $radius + 1, 0, 0, $radius, $radius, 100);
        imagecopymerge($this->img, imagerotate($corner, 180, $bgcolor), $this->width - $radius + 1, $this->height - $radius + 1, 0, 0, $radius, $radius, 100);

        imagecolortransparent($this->img, $bgcolor);
        $this->drawString();
    }


    /**
     * 圆角处理
     * 原理：以一个小正方形的一角画弧
     * @param $radius
     * @return false|resource
     */
    private function makeRadiusImg($radius)
    {
        $img = imagecreatetruecolor($radius, $radius);  // 创建一个小正方形的图像
        $fgcolor = $this->_fontColor; // 扇区颜色：蓝色
        $green = imagecolorallocate($img, 0, 255, 255);   // 白色背景色，与原图保持一致
        imagefill($img, 0, 0, $green);
        imagefilledarc($img, $radius, $radius, $radius * 2, $radius * 2, 180, 270, $fgcolor, IMG_ARC_PIE);
        return $img;
    }


    //画正方形的名字 - 当前方法只支持4个汉字
    private function drawString()
    {
        $pt = $this->px2fpt($this->fontSize);
        //  = x + 边框 + 边字距离
        // $viy轴偏移量，由于字体基线问题，一般字体绘到图像上时会偏下；所以这里给一个y轴反向（向上）的偏移量
        $x0 = 0 + $this->edgeSize + $this->bfSize + $this->vix;
        $y0 = 0 + $this->edgeSize + $this->bfSize + $this->fontSize + $this->viy;
        $x1 = 0 + $this->edgeSize + 2 * $this->bfSize + $this->fontSize + $this->vix;
        $y2 = 0 + $this->edgeSize + 2 * $this->bfSize + 2 * $this->fontSize + $this->viy;
        imagettftext($this->img, $pt, 0, $x0, $y0, $this->_fontColor, $this->font, mb_substr($this->sealString, 0, 1, 'utf8'));
        imagettftext($this->img, $pt, 0, $x1, $y0, $this->_fontColor, $this->font, mb_substr($this->sealString, 1, 1, 'utf8'));
        imagettftext($this->img, $pt, 0, $x0, $y2, $this->_fontColor, $this->font, mb_substr($this->sealString, 2, 1, 'utf8'));
        imagettftext($this->img, $pt, 0, $x1, $y2, $this->_fontColor, $this->font, mb_substr($this->sealString, 3, 1, 'utf8'));
    }


    // 字体大小px支持4至93像素
    function px2fpt($px)
    {
        $out = 'a:2:{s:2:"a1";a:74:{i:0;i:4;i:1;i:5;i:2;i:7;i:3;i:8;i:4;i:9;i:5;i:10;i:6;i:11;i:7;i:12;i:8;i:14;i:9;i:15;i:10;i:16;i:11;i:17;i:12;i:18;i:13;i:19;i:14;i:21;i:15;i:22;i:16;i:23;i:17;i:25;i:18;i:26;i:19;i:27;i:20;i:28;i:21;i:29;i:22;i:30;i:23;i:32;i:24;i:33;i:25;i:34;i:26;i:35;i:27;i:36;i:28;i:38;i:29;i:39;i:30;i:40;i:31;i:41;i:32;i:43;i:33;i:44;i:34;i:46;i:35;i:47;i:36;i:48;i:37;i:48;i:38;i:50;i:39;i:51;i:40;i:52;i:41;i:53;i:42;i:55;i:43;i:56;i:44;i:57;i:45;i:58;i:46;i:60;i:47;i:62;i:48;i:63;i:49;i:63;i:50;i:64;i:51;i:67;i:52;i:68;i:53;i:69;i:54;i:70;i:55;i:71;i:56;i:72;i:57;i:74;i:58;i:75;i:59;i:76;i:60;i:77;i:61;i:78;i:62;i:79;i:63;i:81;i:64;i:83;i:65;i:84;i:66;i:85;i:67;i:86;i:68;i:86;i:69;i:88;i:70;i:90;i:71;i:91;i:72;i:92;i:73;i:93;}s:2:"a2";a:74:{i:0;a:2:{i:0;i:4;i:1;i:1;}i:1;a:2:{i:0;i:5;i:1;i:2;}i:2;a:2:{i:0;i:7;i:1;i:3;}i:3;a:2:{i:0;i:8;i:1;i:4;}i:4;a:2:{i:0;i:9;i:1;i:5;}i:5;a:2:{i:0;i:10;i:1;i:6;}i:6;a:2:{i:0;i:11;i:1;i:7;}i:7;a:2:{i:0;i:12;i:1;i:8;}i:8;a:2:{i:0;i:14;i:1;i:9;}i:9;a:2:{i:0;i:15;i:1;i:10;}i:10;a:2:{i:0;i:16;i:1;i:11;}i:11;a:2:{i:0;i:17;i:1;i:12;}i:12;a:2:{i:0;i:18;i:1;i:13;}i:13;a:2:{i:0;i:19;i:1;i:14;}i:14;a:2:{i:0;i:21;i:1;i:15;}i:15;a:2:{i:0;i:22;i:1;i:16;}i:16;a:2:{i:0;i:23;i:1;i:17;}i:17;a:2:{i:0;i:25;i:1;i:18;}i:18;a:2:{i:0;i:26;i:1;i:19;}i:19;a:2:{i:0;i:27;i:1;i:20;}i:20;a:2:{i:0;i:28;i:1;i:21;}i:21;a:2:{i:0;i:29;i:1;i:22;}i:22;a:2:{i:0;i:30;i:1;i:23;}i:23;a:2:{i:0;i:32;i:1;i:24;}i:24;a:2:{i:0;i:33;i:1;i:25;}i:25;a:2:{i:0;i:34;i:1;i:26;}i:26;a:2:{i:0;i:35;i:1;i:27;}i:27;a:2:{i:0;i:36;i:1;i:28;}i:28;a:2:{i:0;i:38;i:1;i:29;}i:29;a:2:{i:0;i:39;i:1;i:30;}i:30;a:2:{i:0;i:40;i:1;i:31;}i:31;a:2:{i:0;i:41;i:1;i:32;}i:32;a:2:{i:0;i:43;i:1;i:33;}i:33;a:2:{i:0;i:44;i:1;i:34;}i:34;a:2:{i:0;i:46;i:1;i:35;}i:35;a:2:{i:0;i:47;i:1;i:36;}i:36;a:2:{i:0;i:48;i:1;i:37;}i:37;a:2:{i:0;i:48;i:1;i:38;}i:38;a:2:{i:0;i:50;i:1;i:39;}i:39;a:2:{i:0;i:51;i:1;i:40;}i:40;a:2:{i:0;i:52;i:1;i:41;}i:41;a:2:{i:0;i:53;i:1;i:42;}i:42;a:2:{i:0;i:55;i:1;i:43;}i:43;a:2:{i:0;i:56;i:1;i:44;}i:44;a:2:{i:0;i:57;i:1;i:45;}i:45;a:2:{i:0;i:58;i:1;i:46;}i:46;a:2:{i:0;i:60;i:1;i:47;}i:47;a:2:{i:0;i:62;i:1;i:48;}i:48;a:2:{i:0;i:63;i:1;i:49;}i:49;a:2:{i:0;i:63;i:1;i:50;}i:50;a:2:{i:0;i:64;i:1;i:51;}i:51;a:2:{i:0;i:67;i:1;i:52;}i:52;a:2:{i:0;i:68;i:1;i:53;}i:53;a:2:{i:0;i:69;i:1;i:54;}i:54;a:2:{i:0;i:70;i:1;i:55;}i:55;a:2:{i:0;i:71;i:1;i:56;}i:56;a:2:{i:0;i:72;i:1;i:57;}i:57;a:2:{i:0;i:74;i:1;i:58;}i:58;a:2:{i:0;i:75;i:1;i:59;}i:59;a:2:{i:0;i:76;i:1;i:60;}i:60;a:2:{i:0;i:77;i:1;i:61;}i:61;a:2:{i:0;i:78;i:1;i:62;}i:62;a:2:{i:0;i:79;i:1;i:63;}i:63;a:2:{i:0;i:81;i:1;i:64;}i:64;a:2:{i:0;i:83;i:1;i:65;}i:65;a:2:{i:0;i:84;i:1;i:66;}i:66;a:2:{i:0;i:85;i:1;i:67;}i:67;a:2:{i:0;i:86;i:1;i:68;}i:68;a:2:{i:0;i:86;i:1;i:69;}i:69;a:2:{i:0;i:88;i:1;i:70;}i:70;a:2:{i:0;i:90;i:1;i:71;}i:71;a:2:{i:0;i:91;i:1;i:72;}i:72;a:2:{i:0;i:92;i:1;i:73;}i:73;a:2:{i:0;i:93;i:1;i:74;}}}';
        $out = unserialize($out);
        for ($i = 0; $i < count($out['a1']); $i++) {
            if ($px == $out['a1'][$i]) {
                return $out['a2'][$i][1]; // 存在
            } else if ($px < $out['a1'][$i]) {
                $prev = $out['a2'][$i - 1];
                $next = $out['a2'][$i];
                return (($prev[1] + $next[1]) / 2); // 不存在时，返回当前与前一个之间的均值
            }
        }
        return 1; // 支持4px至93px 不支持时直接返回1值
    }

    //输出
    /**
     * 文字生生世世
     * @param int $px 
     * @return string $aaa
     */
    public function outHtmlPng()
    {
        header('Content-type:image/png');
        ob_clean();
        imagepng($this->img);
        imagepng($this->img, "./out.png");
        imagedestroy($this->img);
    }

/**
 * <api method="GET" summary="获取所有的用户信息">
 *     <path path="/users">
 *         <query name="page" type="number" default="0">显示第几页的内容</query>
 *         <query name="size" type="number" default="20">每页显示的数量</query>
 *     </path>
 *     <tag>user</tag>
 *     <server>users</server>
 * </api>
 */
    
   /**
 * @api {get} /user/:region/:id/:opt Read data of a User
 * @apiVersion 0.3.0
 * @apiName GetUser
 * @apiGroup User
 * @apiPermission admin:computer
 *
 * @apiDescription Compare version 0.3.0 with 0.2.0 and you will see the green markers with new items in version 0.3.0 and red markers with removed items since 0.2.0.
 *
 * @apiHeader {String} Authorization The token can be generated from your user profile.
 * @apiHeader {String} X-Apidoc-Cool-Factor=big Some other header with a default value.
 * @apiHeaderExample {Header} Header-Example
 *     "Authorization: token 5f048fe"
 * @apiParam {Number} id User unique ID
 * @apiParam {String} region=fr-par User region
 * @apiParam {String} [opt] An optional param
 *
 * @apiExample {bash} Curl example
 * curl -H "Authorization: token 5f048fe" -i https://api.example.com/user/fr-par/4711
 * curl -H "Authorization: token 5f048fe" -H "X-Apidoc-Cool-Factor: superbig" -i https://api.example.com/user/de-ber/1337/yep
 * @apiExample {js} Javascript example
 * const client = AcmeCorpApi('5f048fe');
 * const user = client.getUser(42);
 * @apiExample {python} Python example
 * client = AcmeCorpApi.Client(token="5f048fe")
 * user = client.get_user(42)
 *
 * @apiSuccess {Number}   id            The Users-ID.
 * @apiSuccess {Date}     registered    Registration Date.
 * @apiSuccess {String}   name          Fullname of the User.
 * @apiSuccess {String[]} nicknames     List of Users nicknames (Array of Strings).
 * @apiSuccess {Object}   profile       Profile data (example for an Object)
 * @apiSuccess {Number}   profile.age   Users age.
 * @apiSuccess {String}   profile.image Avatar-Image.
 * @apiSuccess {Object[]} options       List of Users options (Array of Objects).
 * @apiSuccess {String}   options.name  Option Name.
 * @apiSuccess {String}   options.value Option Value.
 *
 * @apiError NoAccessRight Only authenticated Admins can access the data.
 * @apiError UserNotFound   The <code>id</code> of the User was not found.
 * @apiError (500 Internal Server Error) InternalServerError The server encountered an internal error
 *
 * @apiErrorExample Response (example):
 *     HTTP/1.1 401 Not Authenticated
 *     {
 *       "error": "NoAccessRight"
 *     }
 */
    public function test(){}

}
