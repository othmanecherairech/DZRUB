<?php
	define('BASEPATH', true);

	// Load System Files
	require('system/init.php');
	
	// Check and execute updates
	if(file_exists(BASE_PATH.'/system/modules/db_update/runSecondUpdate.php')){
		include(BASE_PATH.'/system/modules/db_update/runSecondUpdate.php');
	}

	// Redirect to Secure Page (HTTPS)
	if($config['force_secure'] == 1 && !isset($_SERVER['HTTPS']) || $config['force_secure'] == 1 && $_SERVER['HTTPS'] != 'on')
	{
		header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
		exit;
	}
	
	// Load and execute cron
	include(BASE_PATH.'/system/cron/cron.php');

	// Logout system
	if(isset($_GET['logout']))
	{
		if(isset($_COOKIE['PESAutoLogin'])){
			unset($_COOKIE['PESAutoLogin']); 
			setcookie('PESAutoLogin', '', time(), '/');
		}

		session_destroy();

		redirect($config['site_url']);
	}

	// Referral System
	if(!$is_online && isset($_GET['ref']) && is_numeric($_GET['ref'])){
		setcookie("PlusREF", $db->EscapeString($_GET['ref']), time()+7200);
	}

	if($is_online){
		if($data['ref'] > 0 && $data['ref_paid'] != 1){
			$ref_valid = $db->QueryFetchArray("SELECT SUM(`total_clicks`) AS `clicks` FROM `user_clicks` WHERE `uid`='".$data['id']."' LIMIT 1");
			if($ref_valid['clicks'] >= $config['aff_click_req']){
				$db->Query("UPDATE `users` SET `coins`=`coins`+'".$config['ref_coins']."' WHERE `id`='".$data['ref']."'");
				$db->Query("UPDATE `users` SET `ref_paid`='1' WHERE `id`='".$data['id']."'");
				
				if($config['paysys'] == 1 && $config['ref_cash'] > 0){
					affiliate_commission($data['ref'], $data['id'], $config['ref_cash'], 'referral_activity');
				}
			}
		}
		if($data['premium'] > 0 && $data['premium'] < time()){
			$db->Query("UPDATE `users` SET `premium`='0' WHERE `id`='".$data['id']."'");
		}
	}
	
	// Detect visitor referrer
	if(!$is_online && isset($_SERVER['HTTP_REFERER']) && !isset($_COOKIE['PESRefSource'])){
		$main_domain = parse_url($config['site_url']);
		$http_referer = parse_url($_SERVER['HTTP_REFERER']);
		if(isset($http_referer['host']) && $http_referer['host'] != $main_domain['host']){
			setcookie('PESRefSource', $db->EscapeString($_SERVER['HTTP_REFERER']), time()+1800);
		}
	}
	
	// Banner System
	if(isset($_GET['go_banner']) && is_numeric($_GET['go_banner'])) {
		$banner_id = $db->EscapeString($_GET['go_banner']);
		$banner = $db->QueryFetchArray("SELECT site_url FROM `banners` WHERE `id`='".$banner_id."' LIMIT 1");
		
		if(!empty($banner['site_url'])) {
			$db->Query("UPDATE `banners` SET `clicks`=`clicks`+'1' WHERE `id`='".$banner_id."'");
			redirect($banner['site_url']);
		}
	}

	// Email Unsubscribe
	if(isset($_GET['unsubscribe']) && isset($_GET['um'])) {
		$uid = $db->EscapeString($_GET['unsubscribe']);
		$um = $db->EscapeString($_GET['um']);
		if($db->QueryGetNumRows("SELECT id FROM `users` WHERE `id`='".$uid."' AND MD5(`email`)='".$um."'") > 0) {
			$db->Query("UPDATE `users` SET `newsletter`='0' WHERE `id`='".$uid."' AND MD5(`email`)='".$um."'");
		}
	}
	
	// Remove Footer Branding
	if(file_exists(BASE_PATH.'/system/copyright.php')) {
		include(BASE_PATH.'/system/copyright.php');
	}

	/*
		Load Website
	*/

	// Starting compression
	ob_start();

	if($config['maintenance'] > 0) {
		$config['site_name'] .= ' - '.$lang['b_01']; 
		if($data['admin'] < 1) {
			redirect('maintenance');
		}
	}
	
	if(!$is_online && isset($_GET['ref']) && is_numeric($_GET['ref']) && $config['splash_page'] == 1) {
		if(file_exists(BASE_PATH.'/template/'.$config['theme'].'/pages/splash.php')) {
			include(BASE_PATH.'/template/'.$config['theme'].'/pages/splash.php');
			exit;
		}
	}
	
	// Load Header
	require(BASE_PATH.'/template/'.$config['theme'].'/common/header.php');
	
	// Load Content
	$pages = array(
			// script name => (1 = valid, 0 = disabled), (0 = offline, 1 = online, 2 = doesn't matter), File Location
			'contact' => array(1, 2, 'pages/contact'),
            '1' => array(1, 2, 'pages/1'),
			'faq' => array(1, 2, 'pages/faq'),
			'locked' => array(1, 1, 'pages/locked'),
			'register' => array(($config['reg_status'] == 0 ? 1 : 0), 0, 'pages/register'),
			'recover' => array(1, 0, 'pages/recover'),
			'stats' => array(1, 2, 'pages/stats'),
			'proofs' => array(($config['allow_withdraw'] != 1 ? 0 : 1), 2, 'pages/proofs'),
			'buy' => array(1, 1, 'pages/buy'),
			'membership' => array(1, 1, 'pages/membership'),
			'affiliates' => array(1, 1, 'pages/affiliates'),
			'referrals' => array(1, 1, 'pages/referrals'),
			'coupons' => array(1, 1, 'pages/coupons'),
			'transfer' => array(1, 1, 'pages/transfer'),
			'withdraw' => array(($config['allow_withdraw'] == 1 ? 1 : 0), 1, 'pages/withdraw'),
			'convert' => array(($config['convert_enabled'] == 1 ? 1 : 0), 1, 'pages/convert'),
			'bank' => array(1, 1, 'pages/bank'),
			'sellcoins' => array(1, 1, 'pages/sellcoins'),
			'levels' => array(1, 1, 'pages/levels'),
			'addpage' => array(1, 1, 'pages/addpage'),
			'mypages' => array(1, 1, 'pages/mypages'),
			'editpage' => array(1, 1, 'pages/editpage'),
			'rewards' => array(1, 1, 'pages/rewards'),
			'horserace' => array(1, 1, 'pages/horserace'),
			'banners' => array(1, 1, 'pages/banners'),
			'account' => array(1, 1, 'pages/account'),
			'module' => array(1, 1, 'pages/module'),
			'jobs' => array(1, 1, 'pages/jobs'),
			'offers' => array(1, 1, 'pages/offers'),
			'blog' => array(1, 2, 'pages/blog')
		);
		
	$valid = false;
	if (isset($_GET['page']) && $pages[$_GET['page']][0] == 1) {
		if($is_online && $pages[$_GET['page']][1] == 1){
			$valid = true;
		}elseif(!$is_online && $pages[$_GET['page']][1] == 0){
			$valid = true;
		}elseif($pages[$_GET['page']][1] == 2){
			$valid = true;
		}
	}

	$page = ($is_online ? 'pages/dashboard' : 'pages/home');
	if($valid)
	{
		if(file_exists(BASE_PATH.'/template/'.$config['theme'].'/'.$pages[$_GET['page']][2].'.php'))
		{
			$page = $pages[$_GET['page']][2];
		}
	}
	
	// Load Page
	require(BASE_PATH.'/template/'.$config['theme'].'/'.$page.'.php');
	
	// Load Footer
	require(BASE_PATH.'/template/'.$config['theme'].'/common/footer.php');
	
	// Show website
	ob_end_flush();
	$db->Close();
?>