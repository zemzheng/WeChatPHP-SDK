WeChatPHP-SDK
=============

SDK to admin.wechat.com for php

WeChatPHP-SDK
=============

SDK to admin.wechat.com for php

##WeChatServer.php
<pre>
&lt;?PHP  
include('WeChatServer.php');
function responseTxt( $postObj ){
  /* ... */
    echo 
        getXml4Txt('I got your msg');
    # getXml4Txt( $text );
    # getXml4ImgByMid( $mediaid );
    # getXml4VoiceByMid( $mediaid );
    # getXml4VideoByMid( $mediaid, $title, [ $description ] );
    # getXml4MusicByUrl( $music_url, $mediaid_thumb, $title, [ $description, $high_quailty_music_url ]);
    # getXml4RichMsgByArray( array(
    #     array(
    #         'title' => # title
    #         'desc'  => # description
    #         'pic'   => # picture url
    #         'url'   => # article url
    #     )
    # ) );
}
$svr = new WeChatServer( 
  'token_in_admin.wechat.com', 
  array(
    'receiveAllStart'  => function( $postObj ){ 
      /* ... */ 
    },
    'receiveMsg::text' => 'responseTxt'
    # ============================================================
    # Event Name                || Handle Function
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
##WeChatClient.php
TODO

