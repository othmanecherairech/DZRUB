<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
	$starttime = microtime(true);

	// Email confirmation
	$actMessage = false;
	if(isset($_GET['activate']) && $_GET['activate'] > 0){
		$code = $db->EscapeString($_GET['activate']);
		if($db->QueryGetNumRows("SELECT id FROM `users` WHERE `activate`='".$code."' LIMIT 1") > 0){
			if($site['refsys'] == 1 && $site['aff_click_req'] == 0){
				$ref = $db->QueryFetchArray("SELECT id,ref FROM `users` WHERE `activate`='".$code."'");
				if($ref['ref'] > 0){
					$db->Query("UPDATE `users` SET `coins`=`coins`+'".$site['ref_coins']."' WHERE `id`='".$ref['ref']."'");
					$db->Query("UPDATE `users` SET `ref_paid`='1' WHERE `activate`='".$code."'");
					
					if($site['paysys'] == 1 && $site['ref_cash'] > 0){
						affiliate_commission($ref['ref'], $ref['id'], $site['ref_cash'], 'referral_activity');
					}
				}
			}

			$db->Query("UPDATE `users` SET `activate`='0' WHERE `activate`='".$code."'");
			$actMessage = '<div class="alert alert-success mb-0" role="alert">'.$lang['b_23'].'</div>';
		}
	}

	$errMessage = '';
	$login_error = false;
	if(isset($_POST['connect'])) {
		$ip_address = ip2long(VisitorIP());
		$attempts = $db->QueryFetchArray("SELECT count,time FROM `wrong_logins` WHERE `ip_address`='".$ip_address."'");

		if($attempts['count'] >= $config['login_attempts'] && $attempts['time'] > (time() - (60*$config['login_wait_time']))) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_364'], array('-MIN-' => $config['login_wait_time'])).'</div>';
			$login_error = true;
		} elseif(blacklist_check(VisitorIP(), 3)) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_295'], array('-IP-' => VisitorIP())).'</div>';
			$login_error = true;
		} else {
			$login = $db->EscapeString($_POST['user']);
			$pass = MD5($_POST['password']);
			$data = $db->QueryFetchArray("SELECT id,login,banned,activate FROM `users` WHERE (`login`='".$login."' OR `email`='".$login."') AND `pass`='".$pass."'");

			if($data['banned'] > 0) {
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_02'].'</div>';
				$login_error = true;
			} elseif($data['activate'] > 0) {
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_03'].'</div>';
				$login_error = true;
			} elseif(!empty($data['id'])) {
				$db->Query("UPDATE `users` SET `log_ip`='".VisitorIP()."', `online`=NOW() WHERE `id`='".$data['id']."'");
				$db->Query("DELETE FROM `wrong_logins` WHERE `ip_address`='".$ip_address."'");
		
				// Store login info
				$browser = $db->EscapeString($_SERVER['HTTP_USER_AGENT']);
				$db->Query("INSERT INTO `user_logins` (`uid`,`ip`,`info`,`time`) VALUES ('".$data['id']."','".$ip_address."','".$browser."',NOW())");

				// Auto-login user
				if(isset($_POST['remember'])){
					setcookie('PESAutoLogin', 'ses_user='.$data['login'].'&ses_hash='.$pass, time()+604800, '/');
				}
				
				// Set Session
				$_SESSION['EX_login'] = $data['id'];
				
				// Multi-account prevent
				setcookie('PESAccExist', $data['login'], time()+604800, '/');
				
				// Reload page
				redirect('index.php');
			} else {
				$db->Query("INSERT INTO `wrong_logins` (`ip_address`,`count`,`time`) VALUES ('".$ip_address."','1','".time()."') ON DUPLICATE KEY UPDATE `count`=`count`+'1', `time`='".time()."'");

				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_04'].'</div>';
				$login_error = true;
			}
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head><title><?=$config['site_name']?></title>
	<meta http-equiv="Content-type" content="text/html; charset=<?=$conf['lang_charset']?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<meta name="description" content="<?=$config['site_description']?>" />
	<meta name="keywords" content="<?=$config['site_keywords']?>" />
	<meta name="author" content="MafiaNet (c) MN-Shop.com" />
	<meta name="version" content="<?=$config['version']?>" />
	<link href="static/css/bootstrap.min.css" rel="stylesheet" />
	<link href="static/css/fontawesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
	<link href="template/<?=$config['theme']?>/static/theme.css?v=<?=$config['version']?>" rel="stylesheet" />
	<link rel="shortcut icon" href="static/favicon.ico" type="image/x-icon" />
	<script src="static/js/jquery.min.js" type="text/javascript"></script>
    <script src="static/js/popper.min.js" type="text/javascript"></script>
    <script src="static/js/bootstrap.min.js" type="text/javascript"></script>

	<?php if($login_error == true) { ?>
	<script> $(document).ready(function() { $('#login_box').modal('show'); }); </script>
	<?php } ?>
	<?php if($actMessage != false) { ?>
	<script> $(document).ready(function() { $('#confirm_box').modal('show'); }); </script>
	<?php } ?>

      </head>
        <body class="bg-light">
	<nav class="navbar navbar-expand-md navbar-dark">
	  <div class="container">
        <a class="navbar-brand" href="<?=$config['secure_url']?>"><i class="fa fa-rub"></i> <?=$config['site_logo']?></a>

<div align="Center" id="bancode_6689"><script src="//multibux.org/bancode.php?id=6689" async></script></div>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar" aria-controls="navbar" aria-expanded="false">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-collapse collapse" id="navbar">
          <ul class="navbar-nav ml-auto">
			<?php if($is_online) { ?>
				<li class="nav-item">
				  <a class="nav-link" href="<?=GenerateURL('buy')?>"><i class="fa fa-credit-card fa-fw"></i> <?=$lang['b_07']?></a>
				</li>
				<li class="nav-item">
				  <a class="nav-link" href="<?=GenerateURL('membership')?>"><i class="fa fa-star fa-fw"></i> <?=$lang['b_08']?></a>
				</li>
				<li class="nav-item">
				  <a class="nav-link" href="<?=GenerateURL('affiliates')?>"><i class="fa fa-list fa-fw"></i> <?=$lang['b_12']?></a>
				</li>
				<?php
					$count_jobs = $db->QueryGetNumRows("SELECT `id` FROM `jobs` WHERE `id` NOT IN (SELECT `job_id` FROM `jobs_done` WHERE `uid`='".$data['id']."' AND `status`!='2')");
				?>
				<li class="nav-item">
				  <a class="nav-link" href="<?=GenerateURL('jobs')?>"><i class="fa fa-briefcase fa-fw"></i> <?=$lang['b_403']?> <span class="badge badge-light"><?=$count_jobs?></span></a>
				</li>
				<li class="nav-item dropdown">
				  <a class="nav-link dropdown-toggle" href="javascript:void(0)" id="dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-plus"></i> More</a>
				  <div class="dropdown-menu" aria-labelledby="dropdown">
					<a class="dropdown-item" href="<?=GenerateURL('coupons')?>"><i class="fa fa-ticket fa-fw"></i> <?=$lang['b_10']?></a>
					<?php if($config['transfer_status'] != 1){  ?>
					<a class="dropdown-item" href="<?=GenerateURL('transfer')?>"><i class="fa fa-paper-plane fa-fw"></i> <?=$lang['b_11']?></a>
					<?php } ?>
					<?php if($config['allow_withdraw'] == 1){  ?>
					<a class="dropdown-item" href="<?=GenerateURL('withdraw')?>"><i class="fa fa-credit-card fa-fw"></i> <?=$lang['b_97']?></a>
					<?php } ?>
					<?php if($config['convert_enabled'] == 1){  ?>
					<a class="dropdown-item" href="<?=GenerateURL('convert')?>"><i class="fa fa-exchange fa-fw"></i> <?=$lang['b_268']?></a>
					<?php } ?>
					<a class="dropdown-item" href="<?=GenerateURL('banners')?>"><i class="fa fa-picture-o fa-fw"></i> <?=$lang['b_189']?></a>
					<a class="dropdown-item" href="<?=GenerateURL('rewards')?>"><i class="fa fa-check-circle-o fa-fw"></i> <?=$lang['b_326']?></a>
					<a class="dropdown-item" href="<?=GenerateURL('horserace')?>"><i class="fa fa-flag-checkered fa-fw"></i> <?=$lang['hs_01']?></a>
					<a class="dropdown-item" href="<?=GenerateURL('blog')?>"><i class="fa fa-rss fa-fw"></i> <?=$lang['b_287']?></a>
					<?php if($data['admin'] == 1){  ?>
					<div class="dropdown-divider"></div>
					<a class="dropdown-item" href="<?=$config['secure_url']?>/admin-panel/" target="_blank"><i class="fa fa-lock fa-fw"></i> Admin Panel</a>
					<?php } ?>
				  </div>
				</li>
			<?php } else { ?>
				<li class="nav-item">
				  <a class="nav-link" href="javascript:void(0)" data-toggle="modal" data-target="#login_box"><i class="fa fa-sign-in fa-fw"></i> <?=$lang['b_13']?></a>
				</li>
				<?php if($config['reg_status'] == 0) { ?>
					<li class="nav-item">
					  <a class="nav-link" href="<?=GenerateURL('register')?>"><i class="fa fa-key fa-fw"></i> <?=$lang['b_05']?></a>
					</li>
				<?php } ?>
			<?php } ?>
          </ul>
        </div>
      </div>
    </nav>

<?php if(!$is_online) { ?>
	<div class="modal fade text-center" id="login_box">
	  <div class="modal-dialog modal-dialog-centered">
		<div class="col-lg-8 col-sm-8 col-12 main-section">
		  <div class="modal-content">
			<div class="col-lg-12 col-sm-12 col-12 user-img">
			  <img src="template/<?=$config['theme']?>/static/images/login.png" alt="<?=$lang['b_13']?>"/>
			</div>
			<div class="col-lg-12 col-sm-12 col-12 user-name">
			  <button type="button" class="close" data-dismiss="modal">×</button>
			</div>
			<div class="col-lg-12 col-sm-12 col-12 form-input">
			  <form method="post" action="">
				<?=$errMessage?>
				<div class="form-group">
					<div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-user"></i></div></div>
						<input type="text" class="form-control" name="user" placeholder="<?=$lang['b_14']?>"/>
					</div>
				</div>
				<div class="form-group">
					<div class="input-group mb-2 mr-sm-2">
						<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-key"></i></div></div>
						<input type="password" class="form-control" name="password" placeholder="<?=$lang['b_15']?>"/>
					</div>
				</div>
				<div class="form-group text-left">
				  <input type="checkbox" name="remember" /> <?=$lang['b_229']?>
				</div>
				<button type="submit" name="connect" class="btn btn-success"><?=$lang['b_13']?></button>
			  </form>
			</div>
			<div class="col-lg-12 col-sm-12 col-12 link-part">
				<a href="<?=GenerateURL('recover')?>"><?=$lang['b_16']?></a>
			</div>
		  </div>
		</div>
	  </div>
	</div>
	<?php if(!empty($actMessage)) { ?>
	<div class="modal fade text-center" id="confirm_box">
	  <div class="modal-dialog modal-dialog-centered">
		<div class="col-lg-8 col-sm-8 col-12 main-section">
		  <div class="modal-content p-3">
			<?=$actMessage?>
		  </div>
		</div>
	  </div>
	</div>
	<?php } ?>
<?php } ?>