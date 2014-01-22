WeChatPHP-SDK
=============
[github](https://github.com/zemzheng/WeChatPHP-SDK)

SDK to admin.wechat.com for php

###WeChatServer.php

#### Useage
WeChatServer is used to start an api for admin.wechat.com to connect.
##### Getting start with Hook
Hook mark the position in process and you can handle data/process there.
<pre>&lt;PHP $svr = new WeChatServer( 'token', 
    array( /* HOOKs list */ 
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
    # receiveMsg::link          => ||
    # receiveMsg::voice         => ||
    # receiveEvent::subscribe   => ||
    # receiveEvent::unsubscribe => ||
    # receiveEvent::scan        => ||
    # receiveEvent::location    => ||
    # receiveEvent::click       => ||
    # receiveAllEnd             => ||
    # accessCheckSuccess        => ||
    # 404                       => --
    # ============================================================
    ) 
);
</pre>
* receiveAllStart
<pre>$postData = array( 
    # Base Keys:
    'id'   => /* message id          */ ,
    'from' => /* follower open id    */ ,
    'to'   => /* admin OA account id */ ,
    'time' => /* msg timestamp       */ ,
    'type' => /* message type        */
  ) + receiveMsg OR receiveEvent
</pre>
* receiveMsg::text
<pre>$postData = array( /* ... base ... */ 'content' => )
</pre>
* receiveMsg::location
<pre>$postData = array( 
    /* ... base ... */
    'X' => /* latitude   */ ,
    'Y' => /* longitude  */ ,
    'S' => /* scale      */ ,
    'I' => /* label info */
  )
</pre>
* receiveMsg::image
<pre>$postData = array(
    /* ... base ... */
    'url' => /* image url                                      */ ,
    'mid' => /* image media id (can download or sent to other) */
  )
</pre>
* receiveMsg::video
<pre>$postData = array(
    /* ... base ... */
    'mid       => /* video media id       */ ,
    'thumbmid' => /* thumb image media id */
  )
</pre>
* receiveMsg::link
<pre>$postData = array(
    /* ... base ... */
    'title' => /* title       */ ,
    'desc'  => /* description */ ,
    'url'   => /* link url    */
  )
</pre>
* receiveMsg::voice
<pre>$postData = array(
    /* ... base ... */
    'mid'     => /* voice media id                        */ ,
    'format'  => /* voice format for exp : amr, speex ... */
    [ , 'txt' => /* voice recognition result              */ ]
  )
</pre>
* receiveEvent::subscribe
<pre>$postData = array(
    /* ... base ... */
    'event'      => /* event name          */ ,
    [ , 'key'    => /* qrcode option value */
      , 'ticket' => /* qrcode ticket       */
    ]
  )
</pre>
* receiveEvent::unsubscribe
<pre>$postData = array(
    /* ... base ... */
    'event'      => /* event name */
  )
</pre>
* receiveEvent::scan
<pre>$postData = array(
    /* ... base ... */
    'event'      => /* event name          */ ,
    [ , 'key'    => /* qrcode option value */
      , 'ticket' => /* qrcode ticket       */
    ]
  )
</pre>
* receiveEvent::location
<pre>$postData = array(
    /* ... base ... */
    'event' => /* event name */ ,
    'la'    => /* Latitude   */ ,
    'lo'    => /* Longitude  */ ,
    'p'     => /* Precision  */
)
</pre>
* receiveEvent::click
<pre>$postData = array(
    /* ... base ...*/
    'event' => /* event name */ ,
    'key'   => /* custom key */
)
</pre>
* receiveAllEnd
<pre>$postData = array(
    'id'   => /* message id          */ ,
    'from' => /* follower open id    */ ,
    'to'   => /* admin OA account id */ ,
    'time' => /* msg timestamp       */ ,
    'type' => /* message type        */
) + receiveMsg OR receiveEvent
</pre>
* accessCheckSuccess
* 404
##### get xml
In hook you can send response by use 
<pre> &lt;?PHP echo WeChatServer::getXml4* # ...  
</pre>
* WeChatServer::getXml4Txt( $text );
* WeChatServer::getXml4ImgByMid( $mediaid );
* WeChatServer::getXml4VoiceByMid( $mediaid );
* WeChatServer::getXml4VideoByMid( 
    $mediaid, $title 
    [, $description=$title as Default ] 
  );
* WeChatServer::getXml4MusicByUrl( 
    $music_url, $mediaid_thumb, $title 
    [, $description             = $title as Default
     , $high_quailty_music_url  = $music_url as Default
    ] 
  );
* WeChatServer::getXml4RichMsgByArray(
    array(
        array(
            'title' => # title
            'desc'  => # description
            'pic'   => # picture url
            'url'   => # article url
        )
        [, ... ]
    );
#### DEMO CODE
<pre>
&lt;?PHP  
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

#### Useage
WeChatClient is used to set/get user-defined menu in chat, manage followers group, upload/download media file and send customer server messages.

#### getting start
<pre>&lt;PHP $client = new WeChatClient( 'your-appid', 'your-appsecret' );
</pre>
#### Access Token
<pre>&lt;PHP
    # If you need access token, you can use following:
    $client->getAccessToken(); 

    # If you need access token with expire time, use:
    $tokenOnly = 0;
    $client->getAccessToken( $tokenOnly ); 
    # array(
    #     'token'  => /* access token */,
    #     'expire' => /* timestamp */
    # )

    # access token info will be cached
    #   once $client->getAccessToken is called

    $client->setAccessToken( $tokenInfo );
    # Cached accesstoken, $tokenInfo = $client->getAccessToken( 0 );
</pre>

#### User-defined Menu
<pre>&lt;PHP
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
<pre>&lt;PHP

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
<pre>&lt;PHP
    $client->upload( $type, $file_path [, $mediaidOnly = true ] );
    # @param $type {string} image | voice | video | thumb
    # @return {string} When $mediaidOnly = true, return media id
    # @return {Array} When $mediaidOnly = false, 
    #       return array( 'type' =>, 'media_id' =>, 'create_at' => /* timestamp */)

    $client->download( $media_id );
    # @return media file binary data
</pre>

#### Customer Server Message
<pre>&lt;PHP
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
<pre>&lt;PHP
    $client->getQrcodeTicket( [ $options ] );
    # @param $options {Array}
    #       array(
    #           'scene_id'   => /* default = 1*/
    #           'expire'     => /* default = 0 mean no limit */
    #           'ticketOnly' => /* default = true */
    #       )
    # @return {string|null} when ticketOnly = true, return ticket string
    # @return {array|null}  when ticketOnly = false, 
    #       return array( 'ticket' => /* ... */,  'expire' => /* ... */ )

    WeChatClient::getQrcodeImgUrlByTicket( $ticket )
    # @return {string} 

    WeChatClient::getQrcodeImgUrlByTicket( $ticket )
    # @return Image Binary
</pre>
