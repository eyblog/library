<?php
header("Content-type: text/html; charset=utf-8"); 
	/***
	*国旅接口API
	*DESede[加密类]
	*cits  [操作类]
	*----query(body,apiId)查询方法。body：条件，apiId：方法名，return：array();
	*----curl(url,data)	  curl。return string;
	*
	***/
	class cits{
		//构造函数
		function __construct(){
			//配置
			$this->config=array(
				'url'=>'http://202.96.57.16:7014/cits-json/api.html',	//接口地址
				'key'=>'citshn',//密钥
				'user'=>'henan',//帐号
				'host'=>'zz.cits.cn',//站点名称
			);
		}
		//查询
		function query($body, $apiId){
	        //构造数据并进行加密
	        $body = json_encode($body);
	        $des = new DESede(); //实例化加密模块
	        $data['requestBody'] = $des->encrypt($body, $this->config['key']); //3DES+base64_decode加密
	        $token = $this->config['key'] . $data['requestBody'] . $this->config['user'] . $this->config['host'] . $apiId . '';
	        $header['token'] = md5($token);
	        $header['user'] = $this->config['user'];
	        $header['apiId'] = $apiId;
	        $header['site'] = $this->config['host'];
	        $header['deviceId'] = '';
	        $data['requestHeader'] = json_encode($header);
	        $rs = $this->curl($this->config['url'], $data);
	        $rs = json_decode($rs, true); //转为数组
	        $rs['responseBody'] = $des->decrypt($rs['responseBody'], $this->config['key']); //解密
	        return $rs;
		}
		//获取城市列表
	    function getCity($nums=10){
	        $data['hostName'] = 'zz.cits.cn';
	        $data['rowNum'] = $nums;
	        //国内地区列表
	        $res = $this->query($data, 'getDomesticGroupHotDest');
	        $slist = json_decode($res['responseBody'], true);
	        if(is_array($slist) && !empty($slist)){
		        foreach ($slist as $k => $v) {
		            $qlist[$k]['dest_area'] = $v['dest_area'];
		        }
		        $rs['qlist'] = $this->a_array_unique($qlist); //地区列表        
		        $rs['slist'] = $slist; //省份列表
	        }else{
	        	$rs['qlist']=false;
	        	$rs['slist']=false;
	        }
	        //外国地区列表
	        $rs1 = $this->query($data, 'getOutboundGroupHotDest');
	        $wbody = json_decode($rs1['responseBody'], true);
	        if(is_array($wbody) && !empty($wbody)){
		        foreach ($wbody as $k => $v) {
		            $zlist[$k]['dest_continent_id'] = $v['dest_continent_id'];
		            $zlist[$k]['dest_continent_name'] = $v['dest_continent_name'];
		        }
		        $rs['wzlist'] = $this->a_array_unique($zlist); //州列表
		        $rs['wglist'] = $wbody; //国家列表
	        }else{
				$rs['wzlist']=false;
				$rs['wglist']=false;
	        }
	        return $rs;
	    }
	    //热门线路
		function getDomestic($data, $tid, $f, $t = 1){
			$rs = $this->query($data, $f);
			$body = json_decode($rs['responseBody'], true);
			if($t == 1){
				//首页调用
				//import("ORG.Util.Page"); // 导入分页类
				//$Page = new Page($body['PageVo']['totalCount'], 5); // 实例化分页类 传入总记录数和每页显示的记录数
				//$llist['show'] = $Page->show(); // 分页显示输出
				$data = $body['ResultInfoLs'];
			}elseif($t == 2){
				//abroad调用
				$data = $body;
			}
			$llist['content'] = $data;
			return $llist;
		}	    	
	    //去重复
	    function a_array_unique($array) {
	        $out = array();
	        foreach ($array as $key => $value) {
	            if (!in_array($value, $out)) {
	                $out[$key] = $value;
	            }
	        }
	        return $out;
	    }	    	
	    //curl
	    function curl($url, $data = array()) {
	        //对空格进行转义
	        $url = str_replace(' ', '+', $url);
	        $ch = curl_init();
	        //设置选项，包括URL
	        curl_setopt($ch, CURLOPT_URL, $url);
	        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	        curl_setopt($ch, CURLOPT_HEADER, 0);
	        curl_setopt($ch, CURLOPT_TIMEOUT, 3);  //定义超时3秒钟  
	        // POST数据
	        curl_setopt($ch, CURLOPT_POST, 1);
	        // 把post的变量加上
	        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));    //所需传的数组用http_bulid_query()函数处理一下，就ok了
	        //执行并获取url地址的内容
	        $output = curl_exec($ch);
	        $errorCode = curl_errno($ch);
	        //释放curl句柄
	        curl_close($ch);
	        if (0 !== $errorCode) {
	            return false;
	        }
	        return $output;
	    }
	}

class DESede{
/**
* 加密
* @param $data 待加密明文
* @param $key DES私钥
* @param $use3des 是否启用3DES加密，默认启用
*/
function encrypt($data='', $key='', $use3des = true)
{
if (empty($data) || empty($key))
{
return False;
}
$cipher = $use3des ? MCRYPT_TRIPLEDES : MCRYPT_DES;
$modes = MCRYPT_MODE_ECB;
# Add PKCS7 padding.
$block = mcrypt_get_block_size($cipher, $modes);
$pad = $block - (strlen($data) % $block);
$data .= str_repeat(chr($pad), $pad);
$iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher, $modes), MCRYPT_RAND);
$encrypted = @mcrypt_encrypt($cipher, $key, $data, $modes, $iv);
return base64_encode($encrypted);//base64_encode
//return $encrypted;
}
/**
* 解密
* @param $data 待解密密文
* @param $key DES私钥
* @param $use3des 是否启用3DES加密，默认启用
*/
function decrypt($data='', $key='', $use3des = true)
{
if (empty($data) || empty($key))
{
return False;
}
$data=base64_decode($data);
$cipher = $use3des ? MCRYPT_TRIPLEDES : MCRYPT_DES;
$modes = MCRYPT_MODE_ECB;
$iv = mcrypt_create_iv(mcrypt_get_iv_size($cipher, $modes), MCRYPT_RAND);
$data = @mcrypt_decrypt($cipher, $key, $data, $modes, $iv);
# Strip padding out.
$block = mcrypt_get_block_size($cipher, $modes);
$pad = ord($data[($len = strlen($data)) - 1]);
$decrypted = substr($data, 0, strlen($data) - $pad);
return $decrypted;
}
}	
?>