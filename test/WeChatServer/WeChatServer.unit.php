<?php
class WeChatServerEchostr extends PHPUnit_Framework_TestCase{

    protected function setUp(){
        $timestamp = time();
        $nonce = md5( time() );
        $token = md5( time() );

        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $signature = sha1( $tmpStr );

        $_GET["signature"] = $signature;
        $_GET["timestamp"] = $timestamp;
        $_GET["nonce"]     = $nonce;
        $_GET["echostr"]   = md5( time() );
        $_GET["TOKEN"]     = $token;
    }
    protected function tearDown(){
        unset( $_GET["signature"] );
        unset( $_GET["timestamp"] );
        unset( $_GET["nonce"]     );
        unset( $_GET["echostr"]   );
        unset( $_GET["TOKEN"]     );
    }

    public function testSignature(){
        $this->expectOutputString( $_GET["echostr"] );
        new WeChatServer( $_GET["TOKEN"] );
    }
}
