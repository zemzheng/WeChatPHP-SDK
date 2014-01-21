WeChatPHP-SDK
=============

SDK to admin.wechat.com for php

###WeChatServer.php

#### Useage
WeChatServer is used to start an api for admin.wechat.com to connect.
##### Hook
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
TODO

