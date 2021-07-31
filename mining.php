<?php
	define('BASEPATH', true);
	require('system/init.php');
	if(!$is_online || empty($config['cm_key']) || empty($config['cm_id']) || empty($config['cm_domain'])){
		redirect($config['site_url']);
	}

	$x = parse_url($config['site_url']);
	
	if($config['banner_system'] != 0)
	{
		$ad_banner = $db->QueryFetchArray("SELECT `code` FROM `ad_codes` WHERE `status`='1' AND `size`='0' ORDER BY rand() LIMIT 1");
		$banner = $db->QueryFetchArray("SELECT id,banner_url FROM `banners` WHERE `expiration`>'".time()."' ORDER BY rand() LIMIT 1");

		$valid = true;
		if(!empty($ad_banner['code']) && rand(1,2) == 1)
		{
			$valid = false;
		}
		
		if(!empty($banner['id']) && $valid)
		{
			$db->Query("UPDATE `banners` SET `views`=`views`+'1' WHERE `id`='".$banner['id']."'");
			$banner_code = '<a href="'.$config['site_url'].'/?go_banner='.$banner['id'].'" target="_blank"><img src="'.$banner['banner_url'].'" style="max-width:468px" class="img-fluid" border="0" /></a>';
		}
		else
		{
			$banner_code = htmlspecialchars_decode($ad_banner['code']);
		}
	}
	
	function connect($uid, $hash_key, $cm_id, $cm_domain, $token, $domain = '') {
		if(empty($domain)){
			$domain = str_replace("www.","", $_SERVER['HTTP_HOST']);
		}

		$qry_str = 'a=data&product=pespro&uid='.$uid.'&token='.$token.'&hash='.$hash_key.'&cm_id='.$cm_id.'&domain='.$domain;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://'.trim($cm_domain).'/system/api.php');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $qry_str);
		$result = trim(curl_exec($ch));
		curl_close($ch);
		
		return $result;
	}
	
	$uid = md5($data['id'].'-'.$data['username']);
	$connect = connect($uid, $config['cm_key'], $config['cm_id'], $config['cm_domain'], $config['cm_token']);
	
	$valid = false;
	$errMessage = '<div class="alert alert-info" role="alert"><i class="fa fa-cog fa-spin fa-fw"></i> '.$lang['b_301'].'...</div>';
	if(empty($connect)){
		$errMessage = '<div class="alert alert-danger" role="alert"><i class="fa fa-times fa-fw"></i> '.$lang['b_417'].'</div>';
	} else {
		$connect = json_decode($connect, true);
		
		if($connect['status'] != 'ok'){
			$errMessage = '<div class="alert alert-danger" role="alert"><i class="fa fa-times fa-fw"></i> <b>'.$lang['b_418'].':</b> '.$connect['message'].'</div>';
		} else {
			$valid = true;
		}
	}

	// Generate Security Token
	$token = GenMiningToken();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html style="height:100%">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?=$lang['b_416']?> - <?=$config['site_name']?></title>
	<link href="static/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
	<link href="static/css/fontawesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
	<link href="static/mining/style.css" rel="stylesheet" type="text/css" />
	<link rel="shortcut icon" href="static/favicon.ico" type="image/x-icon" />
	<script type="text/javascript" src="static/js/jquery.min.js"></script>
	<script type="text/javascript" src="static/js/bootstrap.min.js"></script>
	<script type="text/javascript" src="static/mining/main.js"></script>
</head>
<body>
	<div class="surfbar">
		<span class="logo"><i class="fa fa-cog fa-spin fa-fw"></i> <?=$config['site_logo']?></span>    
		<span id="status"><?=$errMessage?></span>
		<span id="progress"></span>
		<span id="banner"><?=(empty($banner_code) ? '' : $banner_code)?></span>
	</div>

	<iframe src="<?=(!$valid ? 'static/mining/nopage.html' : $connect['website'])?>" id="frame" frameborder="0"></iframe>

	<script type="text/javascript">
		var secs=<?=(!$valid ? 30 : 60)?>;
		var token = '<?=$token?>';
		var hash = '<?=MD5(rand(1000,9999))?>';
		var sid = '<?=(!$valid ? 'no_page' : $connect['sid'])?>';
		var siteurl = '<?=(!$valid ? '' : $connect['website'])?>';
		var adwait = '<?=$lang['b_301']?>...';
		var surf_file = 'mining.php';
		$(document).ready(function() {window.setTimeout(function() {showadbar()}, 1000);});
	</script>
	<?php if(!empty($config['analytics_id'])) { ?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $config['analytics_id']; ?>"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', '<?php echo $config['analytics_id']; ?>');
	</script>
	<?php } ?>
</body>
</html>
<?php
	$db->Close();
?>