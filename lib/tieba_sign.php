<?php
	date_default_timezone_set('PRC');
	set_time_limit(0);
	echo "=========【".date('Y-m-d H:i:s')."】========="."\r\n";
	//身份标识
	$bduss="";

    /*定义自定义函数*/
    function xCurl($url,$cookie=null,$postdata=null,$header=array()){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        if (!is_null($postdata)) curl_setopt($ch, CURLOPT_POSTFIELDS,$postdata);
        if (!is_null($cookie)) curl_setopt($ch, CURLOPT_COOKIE,$cookie);
        if (!empty($header)) curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        $re = curl_exec($ch);
        curl_close($ch);
        return $re;
    };
    /*贴吧客户端请求头*/
    $tieba_header = array(
        'Content-Type: application/x-www-form-urlencoded',
        'Charset: UTF-8',
        'net: 3',
        'User-Agent: bdtb for Android 8.4.0.1',
        'Connection: Keep-Alive',
        'Accept-Encoding: gzip',
        'Host: c.tieba.baidu.com',
    );
    /*浏览器请求头*/
    $firefox_header = array(
        'Host: tieba.baidu.com',
        'User-Agent: Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:50.0) Gecko/20100101 Firefox/50.0',
        'Accept: */*',
        'Accept-Language: zh-CN,zh;q=0.8,en-US;q=0.5,en;q=0.3',
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'Referer: http://tieba.baidu.com/',
        'Connection: keep-alive',
    );    

    //获取tbs
    $re=json_decode(xCurl('http://tieba.baidu.com/dc/common/tbs','BDUSS=' . $bduss,null,$firefox_header),true);

    if (! $re['is_login']) {
    	echo "bduss 失效！";
    	exit;
    }
    //组织提交参数
    $tbs = $re['tbs'];
    $postdata = array ('BDUSS='.$bduss,'tbs=' . $tbs);
    $postdata = implode('&', $postdata).'&sign='.md5(implode('', $postdata).'tiebaclient!!!');
    //执行签到操作
    function dosign($bduss,$tbs,$postdata,$tieba_header){
	    //获取关注贴吧列表
	    for ($pageno = 1; 1 ; $pageno ++){
	    	$postdata='BDUSS='.urlencode($bduss).'&_client_version=8.1.0.4'.'&page_no=' . $pageno.'&page_size=100'.'&sign='.md5('BDUSS='.$bduss.'_client_version=8.1.0.4'.'page_no='.$pageno.'page_size=100'.'tiebaclient!!!');
	    	$res = json_decode(gzdecode(xCurl('http://c.tieba.baidu.com/c/f/forum/like','ca=open',$postdata,$tieba_header)),true);
	    	//循环执行签到
	    	foreach ($res['forum_list']['non-gconforum'] as $list) {
	    		echo '尝试签到[' . $list['name'].']吧:';
	    		$ret = json_decode(gzdecode(xCurl('http://c.tieba.baidu.com/c/c/forum/sign','ca=open','BDUSS='.urlencode($bduss).'&fid='.$list['id'].'&kw='.urlencode($list['name']).'&sign='.md5('BDUSS='.$bduss.'fid='.$list['id'].'kw='.$list['name'].'tbs='.$tbs.'tiebaclient!!!').'&tbs='.$tbs,$tieba_header)),true);
	    		if ($ret['error_code'] == '0'){
	    			echo '签到完成，经验值加' . $ret['user_info']['sign_bonus_point'] . '，你是今天第' . $ret['user_info']['user_sign_rank'] . '个签到的。'."\r\n";
	    		}else{
	    			echo $ret['error_msg'] . '。'."\r\n";
	    		}
	    	}

	    	if ($res['has_more'] == '0'){
	    		break;
	    	}
	    }
    }
    dosign($bduss,$tbs,$postdata,$tieba_header);

    //执行超级签到，并获取签到结果
    //$res = json_decode(xCurl('http://tieba.baidu.com/tbmall/onekeySignin1','BDUSS='.$bduss,'ie=utf-8&tbs='.$tbs,$firefox_header),true);
    // echo '签到完成！已签' . @$res['data']['signedForumAmount'] . '个吧，' . @$res['data']['unsignedForumAmount'] . '个吧未签。'."\r\n\r\n";
    // //检测是否全部签到成功
    // if(!empty($res['data'])&&!empty($res['data']['unsignedForumAmount'])){
    // 	echo $res['data']['unsignedForumAmount']."个贴吧签到失败，执行重签。"."\r\n";
    // 	dosign($bduss,$tbs,$postdata,$tieba_header);
    // }
