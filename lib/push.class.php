<?php
class push{
	public $config=array(),$template;
	public function __construct($config=array()){
		header("Content-Type: text/html; charset=utf-8");
		require_once(dirname(__FILE__) . '/getui_sdk/' . 'IGt.Push.php');
		require_once(dirname(__FILE__) . '/getui_sdk/' . 'igetui/IGt.AppMessage.php');
		require_once(dirname(__FILE__) . '/getui_sdk/' . 'igetui/IGt.APNPayload.php');
		require_once(dirname(__FILE__) . '/getui_sdk/' . 'igetui/template/IGt.BaseTemplate.php');
		require_once(dirname(__FILE__) . '/getui_sdk/' . 'IGt.Batch.php');
		require_once(dirname(__FILE__) . '/getui_sdk/' . 'igetui/utils/AppConditions.php');
		if($config){
			$this->config=$config;
		}else{
			$this->config=array(
				'HOST'=>'http://sdk.open.api.igexin.com/apiex.htm',
				'APPKEY'=>'nvp5seRqBL8oJuwBEwRPo5',
				'APPID'=>'RxDrjbWDtU7cFQ8IYkdeb2',
				'MASTERSECRET'=>'PoVcBvrCXM9PVYBsMubUl5',	
			);
		}
	}
	//指定APP推送
	public function pushApp(){
	    $config=$this->config;
	    $igt = new IGeTui($config['HOST'],$config['APPKEY'],$config['MASTERSECRET']);
	    //个推信息体
	    //基于应用消息体
	    $message = new IGtAppMessage();
	    $message->set_isOffline(true);
	    $message->set_offlineExpireTime(3600*12*1000);//离线时间单位为毫秒，例，两个小时离线为3600*1000*2
	    $message->set_data($this->template);
	    $message->set_PushNetWorkType(1);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
	    $message->set_speed(100);// 设置群推接口的推送速度，单位为条/秒，例如填写100，则为100条/秒。仅对指定应用群推接口有效。
	    $message->set_appIdList(array($config['APPID']));
	    $message->set_phoneTypeList(array('ANDROID'));
	//  $message->set_provinceList(array('浙江','北京','河南'));
	//  $message->set_tagList(array('开心'));
	    $rep = $igt->pushMessageToApp($message);
	    var_dump($rep);
	    echo ("<br><br>");
	}	
	//指定用户组推送
	public function pushList($cid=array()){
	    putenv("needDetails=true");
	    $config=$this->config;
	    $igt = new IGeTui($config['HOST'],$config['APPKEY'],$config['MASTERSECRET']);
	    //个推信息体
	    $message = new IGtListMessage();
	    $message->set_isOffline(true);//是否离线
	    $message->set_offlineExpireTime(3600*12*1000);//离线时间
	    $message->set_data($this->template);//设置推送消息类型[模版]
	    $message->set_PushNetWorkType(1);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
	    $contentId = $igt->getContentId($message);
	    //接收方  
	    if($cid){
	    	foreach ($cid as $v) {
			    $target = new IGtTarget();
			    $target->set_appId($config['APPID']);
			    $target->set_clientId($v);
			    $targetList[] = $target;
	    	}
	    }
	    $rep = $igt->pushMessageToList($contentId, $targetList);
	    var_dump($rep);
	    echo ("<br><br>");
	}	
	//指定用户推送
	public function pushSingle($cid){
		$config=$this->config;
	    $igt = new IGeTui($config['HOST'],$config['APPKEY'],$config['MASTERSECRET']);
	    //个推信息体
	    $message = new IGtSingleMessage();
	    
	    $message->set_isOffline(true);//是否离线
	    $message->set_offlineExpireTime(3600*12*1000);//离线时间
	    $message->set_data($this->template);//设置推送消息类型[模版]
	    //$message->set_PushNetWorkType(0);//设置是否根据WIFI推送消息，1为wifi推送，0为不限制推送
	    //接收方
	    $target = new IGtTarget();
	    $target->set_appId($config['APPID']);
	    $target->set_clientId($cid);
	    try {
	        $rep = $igt->pushMessageToSingle($message, $target);
	        var_dump($rep);
	        echo ("<br><br>");
	 
	    }catch(RequestException $e){
	        $requstId =e.getRequestId();
	        $rep = $igt->pushMessageToSingle($message, $target,$requstId);
	        var_dump($rep);
	        echo ("<br><br>");
	    }
	 
	}


	/*****************推送模版***************/
	//打开应用
	public function NotificationTemplate(){
		$config=$this->config;
	    $template =  new IGtNotificationTemplate();
	    $template->set_appId($config['APPID']);            //应用appid
	    $template->set_appkey($config['APPKEY']);         //应用appkey
	    $template->set_transmissionType(1);               //透传消息类型
	    $template->set_transmissionContent("测试离线");   //透传内容
	    $template->set_title("个推");                     //通知栏标题
	    $template->set_text("个推最新版点击下载");        //通知栏内容
	    $template->set_logo("logo.png");                  //通知栏logo
	    $template->set_logoURL("http://wwww.igetui.com/logo.png"); //通知栏logo链接
	    $template->set_isRing(true);                      //是否响铃
	    $template->set_isVibrate(true);                   //是否震动
	    $template->set_isClearable(true);                 //通知栏是否可清除
	    $this->template=$template;
	    return $this;
	}
	//打开网页
	public function LinkTemplate(){
		$config=$this->config;
	    $template =  new IGtLinkTemplate();
	    $template ->set_appId($config['APPID']);                  //应用appid
	    $template ->set_appkey($config['APPKEY']);                //应用appkey
	    $template ->set_title("请输入通知标题");       //通知栏标题
	    $template ->set_text("请输入通知内容");        //通知栏内容
	    $template->set_logo("");                       //通知栏logo
	    $template->set_logoURL("");                    //通知栏logo链接
	    $template ->set_isRing(true);                  //是否响铃
	    $template ->set_isVibrate(true);               //是否震动
	    $template ->set_isClearable(true);             //通知栏是否可清除
	    $template ->set_url("http://www.baidu.com/"); //打开连接地址
	    $this->template=$template;
	    return $this;
	}
	//下载文件
	public function NotyPopLoadTemplate(){
		$config=$this->config;
	    $template =  new IGtNotyPopLoadTemplate();
	    $template ->set_appId($config['APPID']);   //应用appid
	    $template ->set_appkey($config['APPKEY']); //应用appkey
	    //通知栏
	    $template ->set_notyTitle("个推");                 //通知栏标题
	    $template ->set_notyContent("个推最新版点击下载"); //通知栏内容
	    $template ->set_notyIcon("");                      //通知栏logo
	    $template ->set_isBelled(true);                    //是否响铃
	    $template ->set_isVibrationed(true);               //是否震动
	    $template ->set_isCleared(true);                   //通知栏是否可清除
	    //弹框
	    $template ->set_popTitle("弹框标题");   //弹框标题
	    $template ->set_popContent("弹框内容"); //弹框内容
	    $template ->set_popImage("");           //弹框图片
	    $template ->set_popButton1("下载");     //左键
	    $template ->set_popButton2("取消");     //右键
	    //下载
	    $template ->set_loadIcon("");           //弹框图片
	    $template ->set_loadTitle("地震速报下载");
	    $template ->set_loadUrl("http://dizhensubao.igexin.com/dl/com.ceic.apk");
	    $template ->set_isAutoInstall(false);
	    $template ->set_isActived(true);
	    $this->template=$template;
	    return $this;
	}
	//透传消息模版
	public function TransmissionTemplate(){
		$config=$this->config;
	    $template =  new IGtTransmissionTemplate();
	    //应用appid
	    $template->set_appId($config['APPID']);
	    //应用appkey
	    $template->set_appkey($config['APPKEY']);
	    //透传消息类型
	    $template->set_transmissionType(1);
	    //透传内容
	    $template->set_transmissionContent("测试离线");
	    $template->set_pushInfo("actionLocKey","badge","message",
	    "sound","payload","locKey","locArgs","launchImage");
	    /*iOS 推送需要对该字段进行设置具体参数详见iOS模板说明(PHP)*/
	    $this->template=$template;
	    return $this;
	}	
}
$push=new push();
$push->LinkTemplate()->pushApp();