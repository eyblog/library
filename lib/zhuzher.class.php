<?php
	/**
	*住哲接口
	*2016.1.5
	**/

	//des加密类
	class DES{
	    private $key;
	    private $iv; //偏移量
	    public  function __construct($key, $iv=0){
	        $this->key = $key;
	        if($iv == 0){
	            $this->iv = $key;
	        }
	        else{
	            $this->iv = $iv;
	        }    	
	    }
	    //加密
	    public function encrypt($str){
	        $size = mcrypt_get_block_size ( MCRYPT_DES, MCRYPT_MODE_CBC );
	        $str = $this->pkcs5Pad ( $str, $size );
	        $data=mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv);
	        //$data=strtoupper(bin2hex($data)); //返回大写十六进制字符串
	        return base64_encode($data);
	    }
	  
	    //解密
	    public function decrypt($str){
	        $str = base64_decode ($str);
	        //$strBin = $this->hex2bin( strtolower($str));
	        $str = mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_DECRYPT, $this->iv );
	        $str = $this->pkcs5Unpad( $str );
	        return $str;
	    }
	  
	    public function hex2bin($hexData){
	        $binData = "";
	        for($i = 0; $i < strlen ( $hexData ); $i += 2)
	        {
	            $binData .= chr(hexdec(substr($hexData, $i, 2)));
	        }
	        return $binData;
	    }
	  
	    public function pkcs5Pad($text, $blocksize){
	        $pad = $blocksize - (strlen ( $text ) % $blocksize);
	        return $text . str_repeat ( chr ( $pad ), $pad );
	    }
	  
	    public function pkcs5Unpad($text){
	        $pad = ord ( $text {strlen ( $text ) - 1} );
	        if ($pad > strlen ( $text ))
	            return false;
	        if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
	            return false;
	        return substr ( $text, 0, - 1 * $pad );
	    }
	}	
	class zhuzher{
		//配置
		private $url="http://open.zhuzher.com/api/request.action";//请求地址
		private $cId="S9800001";    //有住哲分配给客户的调用ID
		private $key="123456";    //密钥，由住哲公司分配的密钥
		private $dataKey="12345678";//数据加密密钥，由住哲公司分配的密钥
		private $time=0;			//时间戳
		//构造
	    public  function __construct($config=""){
    		if(!empty($config)&&is_array($config)){
    			$this->url=$config['url'];
    			$this->cId=$config['cId'];
    			$this->key=$config['key'];
    			$this->dataKey=$config['dataKey'];
    		}
	    }
		//请求
		public function query($method,$data=""){
			header("Content-type: text/html; charset=utf-8");
			$request['head']="";
			$request['body']=$data;
			//print_r($this->arrtoxml($request));
			$str=$this->getQueryString($method,$this->arrtoxml($request));
			$rs=$this->curl_post($this->url,$str);
			print_r($rs);
			$rs=$this->xmltoarr($rs);
			return $rs['body'];
		}	    		
		//获取授权码
		private function getAuthCode($method){
			$this->time=time();
			return md5($this->cId.$method.$this->time.$this->key);
		}
		//拼接请求字符串
		private function getQueryString($method,$data=""){
			//参数加密
			$crypt = new DES($this->dataKey);
			$data = urlencode($crypt->encrypt($data));			
			$str='cId='.$this->cId.'&authCode='.$this->getAuthCode($method).'&method='.$method.'&time='.$this->time.'&data='.$data;
			return $str;
		}
		//xml转数组
		public function xmltoarr($xml){
			$ob=simplexml_load_string($xml);
			$json=json_encode($ob);
			$configData=json_decode($json, true);
			return 	$configData;
		}
		//数组转xml
	    public function arrtoxml($data, $rootNodeName = 'request', $xml=null){
	        if (ini_get('zend.ze1_compatibility_mode') == 1){
	            ini_set ('zend.ze1_compatibility_mode', 0);
	        }
	        if ($xml == null){
	            $xml = simplexml_load_string("<$rootNodeName />");
	        }
	        foreach($data as $key => $value){
	            if (is_numeric($key)){
	                $key = "unknownNode_". (string) $key;
	            }
	            $key = preg_replace('/[^a-z]/i', '', $key);
	            if (is_array($value)){
	                $node = $xml->addChild($key);
	                $this->arrtoxml($value, $rootNodeName, $node);
	            }
	            else{
	               	$value = htmlentities($value);
	               	$xml->addChild($key,$value);
	            }
	        }
	        return $xml->asXML();
	    }		
		//curl
		public function curl_post($url,$data,$referer=''){
			$post_str = '';
			if(is_array($data)){
				foreach ( $post_arr as $k => $v ) {
					$post_str .= $k . '=' . $v . '&';
				}
				$post_str = substr ( $post_str, 0, - 1 );
			}else{
				$post_str=$data;
			}
			$curl = curl_init ();
			curl_setopt ( $curl, CURLOPT_URL, $url ); //要访问的地址 即要登录的地址页面	
			//curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, 1 ); // 从证书中检查SSL加密算法是否存在
			curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, false ); // 对认证证书来源的检查
			//curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header_arr );
			curl_setopt ( $curl, CURLOPT_POST, 1 ); // 发送一个常规的Post请求
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $post_str ); // Post提交的数据包
			curl_setopt ( $curl, CURLOPT_FOLLOWLOCATION, 0 ); // 使用自动跳转
			//curl_setopt ( $curl, CURLOPT_COOKIEJAR, $cookie_file ); // 存放Cookie信息的文件名称
			//curl_setopt ( $curl, CURLOPT_COOKIEFILE, $cookie_file ); // 读取上面所储存的Cookie信息
			curl_setopt ( $curl, CURLOPT_REFERER, $referer ); //设置Referer
			curl_setopt ( $curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 5.1; rv:9.0.1) Gecko/20100101 Firefox/9.0.1" ); // 模拟用户使用的浏览器
			curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
			curl_setopt ( $curl, CURLOPT_HEADER, false ); //获取header信息
			$result = curl_exec ( $curl );
			return $result;
		}
	}
	$zhuzher=new zhuzher();
			$data['hotelId']=1010;
			$data['memberCardNo']="00000252";
	$rs=$zhuzher->query("zhuzher.member.getMemberInfo",$data);
?>