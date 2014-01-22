<?PHP
/**
 * @author zemzheng@gmail.clom
 * @description 
 * @see https://github.com/zemzheng/WeChatPHP-SDK
 * @see http://admin.wechat.com/wiki
 * @see http://mp.weixin.qq.com/wiki
 */
class WeChatClient{
    // SETTING 
    // Tail this file N U will see.
    public static $_URL_API_ROOT;
    public static $_URL_FILE_API_ROOT;
    public static $_QRCODE_TICKET_DEFAULT_ID = 1;
    public static $ERRCODE_MAP;

    // DATA
    private $_appid;
    private $_appsecret;
    private static $_accessTokenCache = array();    
    private static $ERROR_LOGS = array();

    public function __construct( $appid, $appsecret ){
        $this->_appid     = $appid;
        $this->_appsecret = $appsecret;
    }


    public static function checkIsSuc( $res ){
        $result = true;
        if( is_string( $res ) ){
            $res = json_decode( $res, true );
        }
        if( isset($res['errcode']) && ( 0 !== (int)$res['errcode']) ){
            array_push( self::$ERROR_LOGS, $res );            
            $result = false;
        }
        return $result; 
    }

    /**
     * @method get
     * @static
     * @param  {string}        
     * @return {string|boolen}
     */
    public static function get( $url ){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        # curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        if(!curl_exec($ch)){
            error_log( curl_error ( $ch ));
            $data = ''; 
        } else {
            $data = curl_multi_getcontent($ch);

        }
        curl_close($ch);
        return $data;
    }

    /**
     * @method post
     * @static
     * @param  {string}        $url URL to post data to
     * @param  {string|array}  $data Data to be post
     * @return {string|boolen} Response string or false for failure.
     */
    private static function post( $url, $data ){
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        # curl_setopt( $ch, CURLOPT_HEADER, 1);

        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, FALSE );

        curl_setopt( $ch, CURLOPT_POST, 1 );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
        $data = curl_exec($ch);
        if(!$data) error_log( curl_error ( $ch ) );
        curl_close( $ch );
        return $data;
    }

    public function getAccessToken( $tokenOnly = 1 ){
        $myToeknInfo = null;
        $appid       = $this->_appid;
        $appsecret   = $this->_appsecret;

        // check cache
        if( isset( self::$_accessTokenCache[ $appid ] ) ){
            $myToeknInfo = self::$_accessTokenCache[ $appid ];

            if( time() < $myToeknInfo[ 'expire' ] ){
                return $tokenOnly ? $myToeknInfo['token'] : $myToeknInfo;
            }
        }

        // get new token 
        $url = self::$_URL_API_ROOT;
        $url  = "$url/cgi-bin/token?grant_type=client_credential&appid=$appid&secret=$appsecret";

        $json = self::get( $url );
        $res = json_decode( $json, true );

        if ( self::checkIsSuc($res) ){
            // update cache
            self::$_accessTokenCache[ $appid ] = $myToeknInfo = array(
                'token'  => $res['access_token'],
                'expire' => time() + (int) $res['expires_in']
            );
        }
        return $tokenOnly ? $myToeknInfo['token'] : $myToeknInfo;
    }
    public function setAccessToken( $tokenInfo ){
        if( $tokenInfo ){
            $appid       = $this->_appid;
            self::$_accessTokenCache[ $appid ] = array(
                'token'  => $tokenInfo['token'],
                'expire' => $tokenInfo['expire']
            );
        }
    }

    // *************** media file upload/download ************
    public function upload( $type, $file_path, $mediaidOnly = 1 ){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_FILE_API_ROOT . "/cgi-bin/media/upload?access_token=$access_token&type=$type";

        $res = self::post( $url, array( 'media' => "@$file_path" ) );
        $res = json_decode( $res, true );

        if( self::checkIsSuc( $res ) ){
            return $mediaidOnly ? $res['media_id'] : $res;
        }
        return null;
    }
    public function download( $mid ){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_FILE_API_ROOT . "/cgi-bin/media/get?access_token=$access_token&media_id=$mid";
        
        return self::get( $url );
    }

    // *************** MENU ******************
    public function getMenu(){

        $access_token = $this->getAccessToken();
        $url = self::$_URL_API_ROOT . "/cgi-bin/menu/get?access_token=$access_token";

        $json = self::get($url);
        $res = json_decode($json, true);
        if( self::checkIsSuc( $res ) ){
            return $res;
        }
        return null;
    }
    public function deleteMenu(){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_API_ROOT . "/cgi-bin/menu/delete?access_token=$access_token";

        $res = self::get($url);
        return self::checkIsSuc( $res );
    }
    public function setMenu( $myMenu ){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_API_ROOT . "/cgi-bin/menu/create?access_token=$access_token";

        if( defined( 'JSON_UNESCAPED_UNICODE' ) ){
            $json = is_string( $myMenu ) ? $myMenu : json_encode( $myMenu, JSON_UNESCAPED_UNICODE );
        } else{
            $json = is_string( $myMenu ) ? $myMenu : json_encode( $myMenu );
        }

        $res = self::post($url, $json);

        return self::checkIsSuc( $res );
    }

    // *************** send msg ******************
    private  function _send( $to, $type, $data ){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_API_ROOT . "/cgi-bin/message/custom/send?access_token=$access_token";

        $json = json_encode(
            array(
                'touser'  => $to,
                'msgtype' => $type,
                $type     => $data
            )
        );

        $res = self::post($url, $json);

        return self::checkIsSuc( $res );
    }

    public function sendTextMsg( $to, $msg ){
        return $this->_send( $to, 'text', array( 'content' => $msg ) );
    }
    public function sendImgMsg( $to, $mid ){
        return $this->_send( $to, 'image', array( 'media_id' => $mid ) );
    }
    public function sendVoice( $to, $mid ){
        return $this->_send( $to, 'voice', array( 'media_id' => $mid ) );
    }
    public function sendVideo( $to, $mid, $title, $desc ){
        return $this->_send( $to, 'video', array(
            'media_id'    => $mid,
            'title'       => $title,
            'description' => $desc
        ) );
    }
    public function sendMusic( $to, $url, $thumb_mid, $title, $desc = '', $hq_url = '' ){
        return $this->_send( $to, 'music', array(
            'media_id'       => $mid,
            'title'          => $title,
            'description'    => $desc || $title,
            'musicurl'       => $url,
            'thumb_media_id' => $thumb_mid,
            'hqmusicurl'     => $hq_url || $url
        ) );
    }
    static private function _filterForRichMsg( $articles ){
        $i = 0;
        $ii = len( $articles );
        $list = array( 'title', 'desc', 'url', 'thumb_url' );
        $result = array();
        while( $i < $ii ){
            $currentArticle = $articles[ $i++ ];
            try{
                array_push( $result, array(
                    'title'       => $currentArticle['title'],
                    'description' => $currentArticle['desc'],
                    'url'         => $currentArticle['url'],
                    'picurl'      => $currentArticle['thumb_url']
                ) );
            } catch( Exception $e ){}
        }
        return $result;
    }
    public function sendRichMsg( $to, $articles ){

        return $this->_send( $to, 'news', array(
            'articles' => self::_filterForRichMsg( $articles )
        ) );
    }

    // *************** followers admin ******************
    // follower group
    public function createGroup( $name ){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_API_ROOT . "/cgi-bin/groups/create?access_token=$access_token";

        $res = self::post( $url, json_encode( array(
            'group' => array( 'name' => $name )
        ) ) );

        $res = json_decode( $res, true );
        return self::checkIsSuc( $res ) ? $res['group']['id'] : null;
    }
    public function renameGroup( $gid, $name ){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_API_ROOT . "/cgi-bin/groups/update?access_token=$access_token";

        $res = self::post( $url, json_encode( array(
            'group' => array(
                'id'   => $gid,
                'name' => $name
            )
        ) ) );

        $res = json_decode( $res, true );
        return self::checkIsSuc( $res );
    }
    public function moveUserById( $uid, $gid ){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_API_ROOT . "/cgi-bin/groups/members/update?access_token=$access_token";
        
        $res = self::post(
            $url, 
            json_encode( 
                array(
                    'openid'     => $mid,
                    'to_groupid' => $gid
                )
            )
        );

        $res = json_decode( $res, true );
        return self::checkIsSuc( $res );
    }

    public function getAllGroups(){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_API_ROOT . "/cgi-bin/groups/get?access_token=$access_token";

        $res = self::get( $url );
        echo $res;
        $res = json_decode( $res, true ); 
        
        return self::checkIsSuc( $res ) ? $res['groups'] : null;
    }

    public function getGroupidByUserid( $uid ){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_API_ROOT . "/cgi-bin/groups/getid?access_token=$access_token";

        $res = self::post( $url, json_encode( array(
            'openid' => $mid
        ) ) );

        $res = json_decode( $res, true );
        return self::checkIsSuc( $res ) ? $res['groupid'] : null;
    }

    // *************** Followers info ******************
    public function getUserInfoById( $uid, $lang='en' ){
        $access_token = $this->getAccessToken();
        $url = self::$_URL_API_ROOT . "/cgi-bin/user/info?access_token=$access_token&openid=$uid&lang=$lang";

        $res = json_decode( self::get( $url ), true );

         return self::checkIsSuc( $res ) ? $res : null;
    }

    public function getFollowersList( $next_id = '' ){
        $access_token = $this->getAccessToken();
        $extend = '';
        if( $next_id ){
            $extend = "&next_openid=$next_id";
        }
        $url = self::$_URL_API_ROOT . "/cgi-bin/user/get?access_token=${access_token}$extend";

        $res = json_decode( 
            self::get( $url ),
            true
        );

        return self::checkIsSuc( $res ) 
            ? array(
                'total'   => $res['total'],
                'list'    => $res['data']['openid'],
                'next_id' => isset( $res['next_openid'] ) ? $res['next_openid'] : null
            ) 
            : null;
    }

    // ************************** qr code *****************
    public static function getQrcodeImgByTicket( $ticket ){
        return self::get( $this->getQrcodeImgUrlByTicket( $ticket ) );
    }
    public static function getQrcodeImgUrlByTicket( $ticket ){
        $ticket = urlencode( $ticket );
        return self::$_URL_API_ROOT . "/cgi-bin/showqrcode?ticket=$ticket";
    }
    public function getQrcodeTicket( $options = array() ){
        $access_token = $this->getAccessToken();

        $scene_id   = isset( $options[ 'scene_id' ] )   ? (int)$options[ 'scene_id' ] : 0;
        $expire     = isset( $options[ 'expire' ] )     ? (int)$options[ 'expire' ]   : 0;
        $ticketOnly = isset( $options[ 'ticketOnly' ] ) ? $options[ 'ticketOnly' ]    : 1;

        if( $scene_id < 1 || $scene_id > 100000 ){
            $scene_id = self::$_QRCODE_TICKET_DEFAULT_ID;
        }

        $url = "$_URL_API_ROOT/cgi-bin/qrcode/create?access_token=$access_token";
        $data = array(
            'action_name' => 'QR_LIMIT_SCENE',
            'action_info' => array(
                'scene' => array( 
                    'scene_id' => $scene_id
                )
            )
        );
        if( $expire ){
            $data['expire_seconds'] = $expire;
            $data['action_name']    = 'QR_SCENE';
        }
        $data = json_encode( $data );

        $res = self::post( $url, $data );
        $res = json_decode( $res, true );

        if( self::checkIsSuc( $res ) ){
            return $ticketOnly ? $res['ticket'] : array(
                'ticket' => $res['ticket'],
                'expire' => $res['expire_seconds']
            );
        }
        return null;
    }
}
# ######################################################################
# mp.weixin.qq.com                                                     
# ######################################################################
# WeChatClient::$_URL_API_ROOT      = 'https://api.weixin.qq.com';     
# WeChatClient::$_URL_FILE_API_ROOT = 'http://file.api.weixin.qq.com'; 
#
# @see http://mp.weixin.qq.com/wiki/index.php?title=%E5%85%A8%E5%B1%80%E8%BF%94%E5%9B%9E%E7%A0%81%E8%AF%B4%E6%98%8E
# WeChatClient::$ERRCODE_MAP = array(
#         '-1' => '系统繁忙', '0' => '请求成功', '40001' => '获取access_token时AppSecret错误，或者access_token无效', '40002' => '不合法的凭证类型', '40003' => '不合法的OpenID', '40004' => '不合法的媒体文件类型', '40005' => '不合法的文件类型', '40006' => '不合法的文件大小', '40007' => '不合法的媒体文件id', '40008' => '不合法的消息类型', '40009' => '不合法的图片文件大小', '40010' => '不合法的语音文件大小', '40011' => '不合法的视频文件大小', '40012' => '不合法的缩略图文件大小', '40013' => '不合法的APPID', '40014' => '不合法的access_token', '40015' => '不合法的菜单类型', '40016' => '不合法的按钮个数', '40017' => '不合法的按钮个数', '40018' => '不合法的按钮名字长度', '40019' => '不合法的按钮KEY长度', '40020' => '不合法的按钮URL长度', '40021' => '不合法的菜单版本号', '40022' => '不合法的子菜单级数', '40023' => '不合法的子菜单按钮个数', '40024' => '不合法的子菜单按钮类型', '40025' => '不合法的子菜单按钮名字长度', '40026' => '不合法的子菜单按钮KEY长度', '40027' => '不合法的子菜单按钮URL长度', '40028' => '不合法的自定义菜单使用用户', '40029' => '不合法的oauth_code', '40030' => '不合法的refresh_token', '40031' => '不合法的openid列表', '40032' => '不合法的openid列表长度', '40033' => '不合法的请求字符，不能包含\uxxxx格式的字符', '40035' => '不合法的参数', '40038' => '不合法的请求格式', '40039' => '不合法的URL长度', '40050' => '不合法的分组id', '40051' => '分组名字不合法', '41001' => '缺少access_token参数', '41002' => '缺少appid参数', '41003' => '缺少refresh_token参数', '41004' => '缺少secret参数', '41005' => '缺少多媒体文件数据', '41006' => '缺少media_id参数', '41007' => '缺少子菜单数据', '41008' => '缺少oauth code', '41009' => '缺少openid', '42001' => 'access_token超时', '42002' => 'refresh_token超时', '42003' => 'oauth_code超时', '43001' => '需要GET请求', '43002' => '需要POST请求', '43003' => '需要HTTPS请求', '43004' => '需要接收者关注', '43005' => '需要好友关系', '44001' => '多媒体文件为空', '44002' => 'POST的数据包为空', '44003' => '图文消息内容为空', '44004' => '文本消息内容为空', '45001' => '多媒体文件大小超过限制', '45002' => '消息内容超过限制', '45003' => '标题字段超过限制', '45004' => '描述字段超过限制', '45005' => '链接字段超过限制', '45006' => '图片链接字段超过限制', '45007' => '语音播放时间超过限制', '45008' => '图文消息超过限制', '45009' => '接口调用超过限制', '45010' => '创建菜单个数超过限制', '45015' => '回复时间超过限制', '45016' => '系统分组，不允许修改', 
#         '45017' => '分组名字过长', '45018' => '分组数量超过上限', '46001' => '不存在媒体数据', '46002' => '不存在的菜单版本', '46003' => '不存在的菜单数据', '46004' => '不存在的用户', '47001' => '解析JSON/XML内容错误', '48001' => 'api功能未授权', '50001' => '用户未授权该api'
#     );
# ######################################################################

# ######################################################################
# admin.wechat.com                                                     
# ######################################################################
WeChatClient::$_URL_API_ROOT      = 'https://api.wechat.com';          
WeChatClient::$_URL_FILE_API_ROOT = 'http://file.api.weixin.qq.com';   

# @see http://admin.wechat.com/wiki/index.php?title=Return_Codes
WeChatClient::$ERRCODE_MAP = array(
        '-1' => 'System busy', '0' => 'Request succeeded', '40001' => 'Verification failed', '40002' => 'Invalid certificate type', '40003' => 'Invalid Open ID', '40004' => 'Invalid media file type', '40005' => 'Invalid file type', '40006' => 'Invalid file size', '40007' => 'Invalid media file ID', '40008' => 'Invalid message type', '40009' => 'Invalid image file size', '40010' => 'Invalid audio file size', '40011' => 'Invalid video file size', '40012' => 'Invalid thumbnail file size', '40013' => 'Invalid App ID', '40014' => 'Invalid access token', '40015' => 'Invalid menu type', '40016' => 'Invalid button quantity', '40017' => 'Invalid button quantity', '40018' => 'Invalid button name length', '40019' => 'Invalid button KEY length', '40020' => 'Invalid button URL length', '40021' => 'Invalid menu version', '40022' => 'Invalid sub-menu levels', '40023' => 'Invalid sub-menu button quantity', '40024' => 'Invalid sub-menu button type',    '40025' => 'Invalid sub-menu button name length',    '40026' => 'Invalid sub-menu button KEY length',    '40027' => 'Invalid sub-menu button URL length',    '40028' => 'Invalid custom menu user',    '40029' => 'Invalid oauth code',    '40030' => 'Invalid refresh token',    '40031' => 'Invalid openid list',    '40032' => 'Invalid openid list length',    '40033' => 'Invalid request characters: The character "\uxxxx" cannot be included.',    '40035' => 'Invalid parameters',    '40038' => 'Invalid request format',    '40039' => 'Invalid URL length',    '40050' => 'Invalid group ID',    '40051' => 'Invalid group name',    '41001' => 'Parameter missing: access token',    '41002' => 'Parameter missing: appid',    '41003' => 'Parameter missing: refresh token',    '41004' => 'Parameter missing: secret',    '41005' => 'Multimedia file data missing',    '41006' => 'Parameter missing: media id',    '41007' => 'Sub-menu data missing',    '41008' => 'Parameter missing: oauth code',    '41009' => 'Parameter missing: openid',    '42001' => 'access token timed out',    '42002' => 'refresh token timed out',    '42003' => 'oauth code timed out',    '43001' => 'GET request required',    '43002' => 'POST request required',    '43003' => 'HTTPS request required',    '43004' => 'The other user is not yet a follower',    '43005' => 'The other user is not yet a follower',    '44001' => 'Multimedia file is empty',    '44002' => 'POST package is empty',    '44003' => 'Rich media message is empty',    '44004' => 'Text message is empty',    '45001' => 'Error source: multimedia file size',    '45002' => 'Message contents too long',    '45003' => 'Title too long',    '45004' => 'Description too long',    '45005' => 'URL too long',    '45006' => 'Image URL too long',    '45007' => 'Audio play time over limit',    '45008' => 'Rich media messages over limit',    '45009' => 'Error source: interface call',    '45010' => 'Message quantity over limit',    '45015' => 'Response too late',    '45016' => 'System group cannot be changed.',   
        '45017' => 'System name too long',    '45018' => 'Too many groups',    '46001' => 'Media data missing',    '46002' => 'This menu version doesn\'t exist.',    '46003' => 'This menu data doesn\'t exist.',    '46004' => 'This user doesn\'t exist.',    '47001' => 'Error while extracting JSON/XML contents',    '48001' => 'Unauthorized API function',    '50001' => 'The user is not authorized for this API'
    );
# ######################################################################
