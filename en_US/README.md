WeChatPHP-SDK
=============

![WeChatPHP-SDK](https://raw.github.com/zemzheng/WeChatPHP-SDK/master/banner.png)

SDK to admin.wechat.com for php


#### TODO
API update: <a href="http://mp.weixin.qq.com/wiki/index.php?title=%E9%AB%98%E7%BA%A7%E7%BE%A4%E5%8F%91%E6%8E%A5%E5%8F%A3#.E4.B8.8A.E4.BC.A0.E5.9B.BE.E6.96.87.E6.B6.88.E6.81.AF.E7.B4.A0.E6.9D.90" target="_blank">高级群发接口 </a>
Request:
 - [x] Update WeChatServer.php
 - [ ] Update WeChatClient.php
 - [ ] Update Test
 - [ ] Update Documents

#### Logs
* 2014-04-16 json_encode unicode escape fix 
* 2014-03-27 Fix WeChatServer Response media
* 2014-02-18 add bnner & zh_CN setting for WeChatClient
* 2014-01-23 Upload

#### links
* <a href="https://github.com/zemzheng/WeChatPHP-SDK" target="_blank">WeChatPHP-SDK@github</a>
* <a href="http://admin.wechat.com/wiki" target="_blank">WeChat OA Developer Wiki</a>
* <a href="http://mp.weixin.qq.com/wiki" target="_blank">微信公众平台开发者文档</a>
* <a href="http://hello.ziey.info/wechat-php-sdk/" target="_blank">Zem's Blog</a>

###WeChatServer.php

##### About
WeChatServer is used to start an api for admin.wechat.com to connect.

##### Getting start with Hook
Hook mark the position in process and you can handle data/process there.

###### Wiki
* <a href="http://admin.wechat.com/wiki/index.php?title=Common_Messages" target="_blank">Common Messages</a>
* <a href="http://admin.wechat.com/wiki/index.php?title=Event_Messages" target="_blank">Event Messages</a>

###### Hook List
    # ============================================================
    # Hook  Name                || Handle Function
    # ============================================================
    # All the event name you can use.
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

##### How to use hook?
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

##### Hooks detail:
* receiveAllStart [1st Hook before all]
<pre>
  $postData = array( 
    # Base Keys:
    'id'   => /* message id          */ ,
    'from' => /* follower open id    */ ,
    'to'   => /* admin OA account id */ ,
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
In hook you can send response by use 

    echo WeChatServer::getXml4* # ...

<a href="http://admin.wechat.com/wiki/index.php?title=Callback_Messages" target="_blank">Callback Message</a>
  
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

#### About
WeChatClient is used to set/get user-defined menu in chat, manage followers group, upload/download media file and send customer server messages.

#### getting start
<pre>&lt;?PHP
    include( 'WeChatClient.php' );
    # If you are the user of mp.weixin.qq.com, please include WeChatClient.zh_CN.php
    # include( 'WeChatClient.zh_CN.php' ); 
    $client = new WeChatClient( 'your-appid', 'your-appsecret' );
</pre>
#### Access Token 
* <a href="http://admin.wechat.com/wiki/index.php?title=Access_token" target="_blank">wiki</a>
<pre>&lt;?PHP
    # If you need access token, you can use following:
    $client->getAccessToken(); 

    # If you need access token with expire time, use:
    $tokenOnly = 0;
    $client->getAccessToken( $tokenOnly ); 
    # @return array(
    #             'token'  => /* access token */,
    #             'expire' => /* timestamp */
    #         )

    # access token info will be cached
    #   once $client->getAccessToken is called

    $client->setAccessToken( $tokenInfo );
    # Cached accesstoken, $tokenInfo = $client->getAccessToken( 0 );
</pre>

#### User-defined Menu
<a href="http://admin.wechat.com/wiki/index.php?title=Create" target="_blank">Menu Create Wiki</a>
<pre>&lt;?PHP
    # Get Menu Array or null for empty;
    $client->getMenu();

    # Delete Menu as you see
    $client->deleteMenu();

    #
    $client->setMenu( $menu )
    # @param $menu {Array|String}
    #   When use String: $menu should be Json String
    #   When use Array:  Make sure 
    #      1) Your PHP Version support json_encode JSON_UNESCAPED_UNICODE
    #   OR 2) Don't use Unicode Chars.
</pre>

#### Manage Followers & Group 
* <a href="http://admin.wechat.com/wiki/index.php?title=Group_Management_API" target="_blank">Group Management API</a>
* <a href="http://admin.wechat.com/wiki/index.php?title=User_Profile" target="_blank">User profile</a>
<pre>&lt;?PHP

    $client->getUserInfoById( $userid [, $lang='en' ] );
    # @return {Array} For detail 
    #       @see http://admin.wechat.com/wiki/index.php?title=User_Profile

    $client->getFollowersList( [ $next_id = '' ] );
    # @return {Array}   array(
    #                       'total'   => {int},
    #                       'list'    => array( userid1, userid2 ... )
    #                       'next_id' => {string}
    #                   )
    # if total length > list length, you can use
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

#### Media File
* <a href="http://admin.wechat.com/wiki/index.php?title=Transferring_Multimedia_Files" target="_blank">wiki</a>
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
* <a href="http://admin.wechat.com/wiki/index.php?title=Customer_Service_Messages" target="_blank">wiki</a>
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
* <a href="http://admin.wechat.com/wiki/index.php?title=Generating_Parametric_QR_Code" target="_blank">wiki</a>
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
