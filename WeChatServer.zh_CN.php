<?PHP
    // @see http://mp.weixin.qq.com/wiki/index.php?title=%E9%AB%98%E7%BA%A7%E7%BE%A4%E5%8F%91%E6%8E%A5%E5%8F%A3#.E4.B8.8A.E4.BC.A0.E5.9B.BE.E6.96.87.E6.B6.88.E6.81.AF.E7.B4.A0.E6.9D.90
    WeChatServer::$ERRCODE_MAP = array(
        'send success' => '发送成功',
        'send fail'    => '发送失败',
        'err(10001)'   => '涉嫌广告',
        'err(20001)'   => '涉嫌政治',
        'err(20004)'   => '涉嫌社会',
        'err(20002)'   => '涉嫌色情',
        'err(20006)'   => '涉嫌违法犯罪',
        'err(20008)'   => '涉嫌欺诈',
        'err(20013)'   => '涉嫌版权',
        'err(22000)'   => '涉嫌互推(互相宣传)',
        'err(21000)'   => '涉嫌其他'
    );
