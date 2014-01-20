<?PHP
class WeChatServer{
    private $_token;
    /**
     * ****** hooks list ******
     * receiveAllStart
     * receiveMsg::text
     * receiveMsg::location
     * receiveMsg::image
     * receiveMsg::video
     * receiveMsg::link
     * receiveMsg::voice
     * receiveEvent::subscribe
     * receiveEvent::unsubscribe
     * receiveEvent::scan
     * receiveEvent::location
     * receiveEvent::click
     * receiveAllEnd
     * accessCheckSuccess
     * 404
     */
    private $_hooks;


    public function __construct( $token, $hooks  = array() ){
        $this->_token = $token;
        $this->_hooks = $hooks;
        $this->accessDataPush();
    }

    private function _activeHook( $type ){
        if( 
            !isset( $this->_hooks[$type] )
            || !is_callable( $this->_hooks[$type] )
        ) return null;
        $argvs = func_get_args();
        array_shift( $argvs );
        return call_user_func_array(
            $this->_hooks[ $type ], $argvs
        );
    }
    private function _checkSignature(){
        return true;
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];	

        $token = $this->_token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    private function _handlePostObj( $postObj ){
        $MsgType = strtolower( (string)$postObj->MsgType );
        $result = array(
            'from'  => self::$_from_id = (string) htmlspecialchars( $postObj->FromUserName ),
            'to'    => self::$_my_id   = (string) htmlspecialchars( $postObj->ToUserName ),
            'time'  => (int)    $postObj->CreateTime,
            'type'  => (string) $MsgType
        );

        if( property_exists($postObj, 'MsgId') ){
            $result['id'] = $postObj->MsgId;
        }

        switch( $result['type'] ){
            case 'text':
                $result['content'] = (string) $postObj->Content; // Content 消息内容
                break;

            case 'location':
                $result['X'] = (float) $postObj->Location_X; // Location_X 地理位置纬度
                $result['Y'] = (float) $postObj->Location_Y; // Location_Y 地理位置经度
                $result['S'] = (float) $postObj->Scale;      // Scale 地图缩放大小
                $result['I'] = (string) $postObj->Label;     // Label 地理位置信息
                break;

            case 'image':
                $result['url'] = (string) $postObj->PicUrl;  // PicUrl 图片链接，开发者可以用HTTP GET获取
                $result['mid'] = (string) $postObj->MediaId; // MediaId 图片消息媒体id，可以调用多媒体文件下载接口拉取数据。
                break;

            case 'video':
                $result['mid']      = (string) $postObj->MediaId;      // MediaId 图片消息媒体id，可以调用多媒体文件下载接口拉取数据。
                $result['thumbmid'] = (string) $postObj->ThumbMediaId; // ThumbMediaId 视频消息缩略图的媒体id，可以调用多媒体文件下载接口拉取数据。
                break;

            case 'link':
                $result['title'] = (string) $postObj->Title;       // 消息标题
                $result['desc']  = (string) $postObj->Description; // 消息描述
                $result['url']   = (string) $postObj->Url;         // 消息链接
                break;

            case 'voice':
                $result['mid']    = (string) $postObj->MediaID;     // 语音消息媒体id，可以调用多媒体文件下载接口拉取该媒体
                $result['format'] = (string) $postObj->Format;      // 语音格式：amr
                if( property_exists( $postObj, Recognition ) ){
                    $result['txt']    = (string) $postObj->Recognition; // 语音识别结果，UTF8编码
                }
                break;

            case 'event':
                $result['event'] = strtolower((string) $postObj->Event);    // 事件类型，subscribe(订阅)、unsubscribe(取消订阅)、CLICK(自定义菜单点击事???
                switch( $result['event'] ){

                    // case 'unsubscribe': // 取消订阅
                    case 'subscribe': // 订阅 
                    case 'scan': // 扫描二维码
                        if( property_exists( $postObj, EventKey ) ){
                            // 扫描带参数二维码事件
                            $result['key'] = str_replace(
                                'qrscene_', '', (string) $postObj->EventKey 
                            ); // 事件KEY值，qrscene_为前缀，后面为二维码的参数值
                            $result['ticket'] = (string) $postObj->Ticket;
                        }
                        break;

                    case 'location': // 上报地理位置事件
                        $result['la'] = (string) $postObj->Latitude;  // 地理位置纬度
                        $result['lo'] = (string) $postObj->Longitude; // 地理位置经度
                        $result['p']  = (string) $postObj->Precision; // 地理位置精度
                        break;

                    case 'click': // 自定义菜单事件
                        $result['key']   = (string) $postObj->EventKey; // 事件KEY值，与自定义菜单接口中KEY值对???
                        break;
                }
        }

        return $result;

    }

    private function accessDataPush(){
        if( !$this->_checkSignature() ){
            if( !headers_sent() ){
                header('HTTP/1.1 404 Not Found');
                header('Status: 404 Not Found');
            }
            $this->_activeHook('404');
            return;
        }
        
        if(isset($GLOBALS["HTTP_RAW_POST_DATA"])){
            if( !$this->_checkSignature() ) return;

            $postObj = simplexml_load_string(
                $GLOBALS["HTTP_RAW_POST_DATA"],
                'SimpleXMLElement', 
                LIBXML_NOCDATA
            );
            $postObj = $this->_handlePostObj($postObj);

            $this->_activeHook('receiveAllStart', $postObj);

            // Call Special Request Handle Function 
            if( isset( $postObj['event'] ) ){
                $hookName = 'receiveEvent::' . $postObj['event'];
            } else {
                $hookName = 'receiveMsg::' . $postObj['type'];
            }
            $this->_activeHook( $hookName, $postObj );
            
            $this->_activeHook('receiveAllEnd', $postObj);

        } else if( isset($_GET['echostr']) ){
            
            $this->_activeHook('accessCheckSuccess');
            // avoid of xss
            if( !headers_sent() ) header('Content-Type: text/plain');
            echo preg_replace('/[^a-z0-9]/i', '', $_GET['echostr']);
        }
    }

    private static $_from_id;
    private static $_my_id;
    private static function _format2xml( $nodes ){
        $xml = '<xml>'
            .     '<ToUserName><![CDATA[%s]]></ToUserName>'
            .     '<FromUserName><![CDATA[%s]]></FromUserName>'
            .     '<CreateTime>%s</CreateTime>'
            .     '%s'
            . '</xml>';
        return sprintf(
            $xml,
            self::$_from_id,
            self::$_my_id,
            time(),
            $nodes
        );
    }
    public static function getXml4Txt( $txt ){
        $xml = '<MsgType><![CDATA[text]]></MsgType>'
                . '<Content><![CDATA[%s]]></Content>';
        return self::_format2xml(
            sprintf(
                $xml,
                $txt
            )
        );
    }
    public static function getXml4ImgByMid( $mid ){
        $xml = '<MsgType><![CDATA[image]]></MsgType>'
                . '<Image>'
                .     '<MediaId><![CDATA[%s]]></MediaId>'
                . '</Image>';
        return self::_format2xml(
            $xml,
            $mid
        );
    }
    public static function getXml4VoiceByMid( $mid ){
        $xml = '<MsgType><![CDATA[voice]]></MsgType>'
                . '<Voice>'
                .     '<MediaId><![CDATA[%s]]></MediaId>'
                . '</Voice>';
        return self::_format2xml(
            $xml,
            $mid
        );
    }
    public static function getXml4VideoByMid( $mid, $title, $desc = '' ){
        $desc = '' !== $desc ? $desc : $title;
        $xml = '<MsgType><![CDATA[video]]></MsgType>'
                . '<Video>'
                .     '<MediaId><![CDATA[%s]]></MediaId>'
                .     '<Title><![CDATA[%s]]></Title>'
                .     '<Description><![CDATA[%s]]></Description>'
                . '</Video>';

        return self::_format2xml(
            $xml,
            $mid,
            $title,
            $desc
        );
    }
    public static function getXml4MusicByUrl( $url, $thumbmid, $title, $desc = '', $hqurl = '' ){
        $xml = '<MsgType><![CDATA[music]]></MsgType>'
                . '<Music>'
                .     '<Title><![CDATA[%s]]></Title>'
                .     '<Description><![CDATA[%s]]></Description>'
                .     '<MusicUrl><![CDATA[%s]]></MusicUrl>'
                .     '<HQMusicUrl><![CDATA[%s]]></HQMusicUrl>'
                .     '<ThumbMediaId><![CDATA[%s]]></ThumbMediaId>'
                . '</Music>';

        return self::_format2xml(
            $xml,
            $title,
            '' === $desc ? $title : $desc,
            $url,
            $hqurl ? $hqurl : $url,
            $thumbmid
        );
    }

    public static function getXml4RichMsgByArray( $list ){
        $max = 10;
        $i = 0;
        $ii = count( $list );
        $list_xml = '';
        while( $i < $ii && $i < $max ){
            $item = $list[ $i++ ];
            $list_xml .=
                sprintf(
                    '<item>'
                    .     '<Title><![CDATA[%s]]></Title> '
                    .     '<Description><![CDATA[%s]]></Description>'
                    .     '<PicUrl><![CDATA[%s]]></PicUrl>'
                    .     '<Url><![CDATA[%s]]></Url>'
                    . '</item>',
                    $item['title'],
                    $item['desc'],
                    $item['pic'],
                    $item['url']
                );
        }

        $xml = '<MsgType><![CDATA[news]]></MsgType>'
               . '<ArticleCount>%s</ArticleCount>'
               . '<Articles>%s</Articles>';

        return self::_format2xml( $xml, $i, $list_xml );
            
    }
}
