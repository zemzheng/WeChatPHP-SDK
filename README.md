WeChatPHP-SDK
=============

![WeChatPHP-SDK](https://raw.github.com/zemzheng/WeChatPHP-SDK/master/banner.png)

微信公众平台 PHP SDK

#### TODO
API update: <a href="http://mp.weixin.qq.com/wiki/index.php?title=%E9%AB%98%E7%BA%A7%E7%BE%A4%E5%8F%91%E6%8E%A5%E5%8F%A3#.E4.B8.8A.E4.BC.A0.E5.9B.BE.E6.96.87.E6.B6.88.E6.81.AF.E7.B4.A0.E6.9D.90" target="_blank">高级群发接口 </a>
Request:
 - [x] 默认使用中文
 - [ ] 传输消息加密
 - [ ] 新增接口 Server 调整
 - [ ] 新增接口 Client 调整

#### Logs
* 2014-04-16 json_encode unicode escape fix 
* 2014-03-27 Fix WeChatServer Response media
* 2014-02-18 add bnner & zh_CN setting for WeChatClient
* 2014-01-23 Upload

#### 链接
* <a href="https://github.com/zemzheng/WeChatPHP-SDK" target="_blank">WeChatPHP-SDK@github</a>
* <a href="http://admin.wechat.com/wiki" target="_blank">WeChat OA Developer Wiki</a>
* <a href="http://mp.weixin.qq.com/wiki" target="_blank">微信公众平台开发者文档</a>
* <a href="http://hello.ziey.info/wechat-php-sdk/" target="_blank">Zem's Blog</a>

###WeChatServer.php

##### 关于
WeChatServer is used to start an api for admin.wechat.com to connect.

##### 从钩子(hook) 开始
钩子搭在请求过程中，你可以选择合适的勾搭位置，获取数据/掌控过程

###### Wiki
* <a href="http://mp.weixin.qq.com/wiki/index.php?title=%E6%8E%A5%E6%94%B6%E6%99%AE%E9%80%9A%E6%B6%88%E6%81%AF" target="_blank">接收普通消息</a>
* <a href="http://mp.weixin.qq.com/wiki/index.php?title=%E6%8E%A5%E6%94%B6%E4%BA%8B%E4%BB%B6%E6%8E%A8%E9%80%81" target="_blank">接收事件推送</a>

###### Hook List
    # ============================================================
    # 钩子名称                     || 嵌入函数
    # ============================================================
    # WeChatServer 模块中的钩子.
    # 
    # ============================================================
    # receiveAllStart           => function( $postObj ){ ... }
    # receiveMsg::text          => /\
    # receiveMsg::location      => ||
    # receiveMsg::image         => ||
    # receiveMsg::video         => ||
    # receiveMsg::link          => /\
    # receiveMsg::voice         => ||
    # receiveEvent::subscribe   => ||
    # receiveEvent::unsubscribe => ||
    # receiveEvent::scan        => ||
    # receiveEvent::location    => /\
    # receiveEvent::click       => ||
    # receiveAllEnd             => ||
    # accessCheckSuccess        => ||
    # 404                       => /\
    # ============================================================

##### 如何使用Hook
<pre>&lt;?PHP 
  include('WeChatServer.php');
  function handle( $postData ){
    // your code here...
  }
  $svr = new WeChatServer( 
    'your-token', 
    array( /* Hook here */
        'HookName' => 'Handle Function Name OR Function'
        /* demo */
        'receiveMsg::text'     => 'handle',
        'receiveMsg::location' => handle,
        'receiveMsg::image'    => function( $postData ){ /* your code here */ }
      ) 
  );
</pre>

##### 钩子细节
* receiveAllStart [第一个钩子，请求刚开始的位置]
<pre>
  $postData = array( 
    # Base Keys:
    'id'   => /* message id          */ ,
    'from' => /* 听众的 open id    */ ,
    'to'   => /* 公众帐号 account id */ ,
    'time' => /* msg timestamp       */ ,
    'type' => /* message type        */
  ) + receiveMsg OR receiveEvent
</pre>

* receiveMsg::text [type = text]
<pre>
  $postData = array( 
    /* ... base ... */ 
    'content' => /* text content */
  )
</pre>

* receiveMsg::location [type = location] 
<pre>$postData = array( 
    /* ... base ... */
    'X' => /* latitude   */ ,
    'Y' => /* longitude  */ ,
    'S' => /* scale      */ ,
    'I' => /* label info */
  )
</pre>

* receiveMsg::image [type = image]
<pre>$postData = array(
    /* ... base ... */
    'url' => /* image url                                      */ ,
    'mid' => /* image media id (can download or sent to other) */
  )
</pre>

* receiveMsg::video [type = video]
<pre>$postData = array(
    /* ... base ... */
    'mid       => /* video media id       */ ,
    'thumbmid' => /* thumb image media id */
  )
</pre>

* receiveMsg::link [type = link]
<pre>$postData = array(
    /* ... base ... */
    'title' => /* title       */ ,
    'desc'  => /* description */ ,
    'url'   => /* link url    */
  )
</pre>
* receiveMsg::voice [type = voice]
<pre>$postData = array(
    /* ... base ... */
    'mid'     => /* voice media id                        */ ,
    'format'  => /* voice format for exp : amr, speex ... */
    [ , 'txt' => /* voice recognition result              */ ]
  )
</pre>
* receiveEvent::subscribe [type = event & event = subscribe]
<pre>$postData = array(
    /* ... base ... */
    'event'      => /* event name          */ ,
    [ , 'key'    => /* qrcode option value */
      , 'ticket' => /* qrcode ticket       */
    ]
  )
</pre>
* receiveEvent::unsubscribe [type = event & event = unsubscribe]
<pre>$postData = array(
    /* ... base ... */
    'event'      => /* event name */
  )
</pre>
* receiveEvent::scan [type = event & event = scan]
<pre>$postData = array(
    /* ... base ... */
    'event'      => /* event name          */ ,
    [ , 'key'    => /* qrcode option value */
      , 'ticket' => /* qrcode ticket       */
    ]
  )
</pre>
* receiveEvent::location [type = event & event = location]
<pre>$postData = array(
    /* ... base ... */
    'event' => /* event name */ ,
    'la'    => /* Latitude   */ ,
    'lo'    => /* Longitude  */ ,
    'p'     => /* Precision  */
)
</pre>
* receiveEvent::click [type = event & event = click]
<pre>$postData = array(
    /* ... base ...*/
    'event' => /* event name */ ,
    'key'   => /* custom key */
)
</pre>
* receiveAllEnd [Last Hook]
<pre>$postData = array(
    'id'   => /* message id          */ ,
    'from' => /* follower open id    */ ,
    'to'   => /* admin OA account id */ ,
    'time' => /* msg timestamp       */ ,
    'type' => /* message type        */
) + receiveMsg OR receiveEvent
</pre>
* accessCheckSuccess (without params)
* 404 (without params)

#####get xml 
在钩子中，你可以通过下面的方法来发送信息给关注人
    echo WeChatServer::getXml4* # ...

<a href="http://mp.weixin.qq.com/wiki/index.php?title=%E5%8F%91%E9%80%81%E8%A2%AB%E5%8A%A8%E5%93%8D%E5%BA%94%E6%B6%88%E6%81%AF" target="_blank">发送被动响应消息</a>
  
* Text

        {String} WeChatServer::getXml4Txt( $text );

* Image

        {String} WeChatServer::getXml4ImgByMid( $mediaid );

* Voice

        {String} WeChatServer::getXml4VoiceByMid( $mediaid );

* Video

        {String} WeChatServer::getXml4VideoByMid( 
                    $mediaid, $title 
                    [, $description=$title as Default ] 
                 );

* Music

        {String} WeChatServer::getXml4MusicByUrl( 
                     $music_url, $mediaid_thumb, $title 
                     [, $description             = $title as Default
                      , $high_quailty_music_url  = $music_url as Default
                     ] 
                 );

* Rich Message

        {String} WeChatServer::getXml4RichMsgByArray(
                    array(
                        array(
                            'title' => # title
                            'desc'  => # description
                            'pic'   => # picture url
                            'url'   => # article url
                        )
                        [, ... ]
                    )
                 );

#### DEMO CODE
<pre>&lt;?PHP
    include('WeChatServer.php');
    function responseTxt( $postObj ){
        $content = $postObj['content'];
        echo WeChatServer::getXml4Txt( "You say : [$content]." );
    }

    $svr = new WeChatServer( 
      'token_in_admin.wechat.com', 
      array(
        'receiveAllStart'  => function( $postObj ){ 
            log( $postObj['from'] );
            // log who sent this msg
            // if u want to send response here,
            //      please exit
            // echo WeChatServer::getXml4Txt( 'Hey' );
            // exit();
        },
        'receiveMsg::text' => 'responseTxt'
      )
    );

</pre>


###WeChatClient.php

#### 关于
WeChatClient 用于设置自定义菜单/管理关注人分组/上传下载媒体文件/发送客服消息/发送群发消息

#### 如何使用
<pre>&lt;?PHP
    include( 'WeChatClient.php' );
    # If you are the user of mp.weixin.qq.com, please include WeChatClient.zh_CN.php
    # include( 'WeChatClient.zh_CN.php' ); 
    $client = new WeChatClient( 'your-appid', 'your-appsecret' );
</pre>
#### Access Token 
* <a href="http://admin.wechat.com/wiki/index.php?title=Access_token" target="_blank">wiki</a>
<pre>&lt;?PHP
    # 你可以通过下面的方法获取到 access_token
    $client->getAccessToken(); 

    # 你还需要知道的 access_token 的失效时间，请使用下面的方式
    $tokenOnly = 0;
    $client->getAccessToken( $tokenOnly ); 
    # @return array(
    #             'token'  => /* access token */,
    #             'expire' => /* timestamp */
    #         )

    # 一个PHP请求中，执行过$client->getAccessToken() 方法
    # access_token 将会被缓存

    # 当然也可以通过缓存 access_token 来实现多请求共用
    # 下面放方法中的 $tokenInfo = $client->getAccessToken( 0 );
    $client->setAccessToken( $tokenInfo );
</pre>

#### 自定义菜单
<a href="http://mp.weixin.qq.com/wiki/index.php?title=%E8%87%AA%E5%AE%9A%E4%B9%89%E8%8F%9C%E5%8D%95%E5%88%9B%E5%BB%BA%E6%8E%A5%E5%8F%A3" target="_blank">自定义菜单创建接口</a>
<pre>&lt;?PHP
    # 下面的方法将返回 Array or null 
    # Array 为自定义菜单的内容
    # null 表示自定义菜单为空
    $client->getMenu();

    # 删除自定义菜单
    $client->deleteMenu();

    $client->setMenu( $menu )
    # @param $menu {Array|String} $menu 可以为数组或者 json 字符串
    #   When use Array:  Make sure 
    #      1) Your PHP Version support json_encode JSON_UNESCAPED_UNICODE
    #   OR 2) Don't use Unicode Chars.
    # 目前这个地方的中文 Array 转码问题已经处理，其他语言的未经过确认
</pre>

#### 用户管理

* <a href="http://mp.weixin.qq.com/wiki/index.php?title=%E5%88%86%E7%BB%84%E7%AE%A1%E7%90%86%E6%8E%A5%E5%8F%A3" target="_blank">分组管理接口</a>
* <a href="http://mp.weixin.qq.com/wiki/index.php?title=%E8%8E%B7%E5%8F%96%E7%94%A8%E6%88%B7%E5%9F%BA%E6%9C%AC%E4%BF%A1%E6%81%AF" target="_blank">获取用户基本信息</a>
<pre>&lt;?PHP

    $client->getUserInfoById( $userid [, $lang='en' ] );
    # @return {Array} 

    $client->getFollowersList( [ $next_id = '' ] );
    # @return {Array}   array(
    #                       'total'   => {int},
    #                       'list'    => array( userid1, userid2 ... )
    #                       'next_id' => {string}
    #                   )
    $client->getFollowersList( $next_id );
    
    $client->createGroup( $name );
    # @return {int|null} group id OR null for failure

    $client->renameGroup( $groupid, $name);
    # @return {boolen}

    $client->moveUserById( $userid, $groupid )
    # @return {boolen}

    $client->getAllGroups();
    # @return {Array|Null}

    $client->getGroupidByUserid( $userid );
    # @return {int}
</pre>

#### 多媒体文件
* <a href="http://mp.weixin.qq.com/wiki/index.php?title=%E4%B8%8A%E4%BC%A0%E4%B8%8B%E8%BD%BD%E5%A4%9A%E5%AA%92%E4%BD%93%E6%96%87%E4%BB%B6" target="_blank">上传下载多媒体文件</a>
<pre>&lt;?PHP
    $client->upload( $type, $file_path [, $mediaidOnly = true ] );
    # @param $type {string} image | voice | video | thumb
    # @return {string} When $mediaidOnly = true, return media id
    # @return {Array} When $mediaidOnly = false, 
    #       return array( 'type' =>, 'media_id' =>, 'create_at' => /* timestamp */)

    $client->download( $media_id );
    # @return media file binary data
</pre>

#### Customer Server Message
* <a href="http://mp.weixin.qq.com/wiki/index.php?title=%E5%8F%91%E9%80%81%E5%AE%A2%E6%9C%8D%E6%B6%88%E6%81%AF" target="_blank">发送客服消息</a>
<pre>&lt;?PHP
    # all the following will return {boolen}
    $client->sendTextMsg( $user_id, $txt );
    $client->sendImgMsg( $user_id, $media_id );
    $client->sendVoice( $user_id, $media_id );
    $client->sendVideo( $user_id, $media_id, $title, $description );
    $client->sendMusic( 
        $user_id, $music_url, $thumb_media_id, $title 
        [, $description = $title, $high_quality_music_url = $music_url ] 
    );
    $client->sendRichMsg( 
        $user_id, 
        array(
            array(
                'title'     => /* article title */,
                'desc'      => /* description */,
                'url'       => /* article url */,
                'thumb_url' => /* thumb url */,
            )
            [ , ... ]
        ) 
    );
</pre>

#### Qrcode
* <a href="http://mp.weixin.qq.com/wiki/index.php?title=%E7%94%9F%E6%88%90%E5%B8%A6%E5%8F%82%E6%95%B0%E7%9A%84%E4%BA%8C%E7%BB%B4%E7%A0%81" target="_blank">生成带参数的二维码</a>
<pre>&lt;?PHP
    $client->getQrcodeTicket( [ $options ] );
    # @param $options {Array}
    #       array(
    #           'scene_id'   => /* default = 1*/
    #           'expire'     => /* default = 0 mean no limit */
    #           'ticketOnly' => /* default = true */
    #       )
    # @return {string|null} when ticketOnly = true, return ticket string
    # @return {array|null}  when ticketOnly = false, 
    #       return array( 
    #                   'ticket' => /* ... */,  
    #                   'expire' => /* ... */ 
    #              )

    WeChatClient::getQrcodeImgUrlByTicket( $ticket )
    # @return {string} 

    WeChatClient::getQrcodeImgByTicket( $ticket )
    # @return Image Binary
</pre>

