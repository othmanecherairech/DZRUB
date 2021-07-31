<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	if($config['captcha_sys'] == 1){
		include('system/libs/recaptcha/autoload.php');
	}elseif($config['captcha_sys'] == 2){
		include('system/libs/solvemedialib.php');
	}
	
	$IP = VisitorIP();
	$IP = ($IP != '' ? $IP : 0);

	$sql = $db->Query("SELECT code FROM `list_countries` ORDER BY country");
	$ctrs = array();
	while ($row = $db->FetchArray($sql)) {
		$ctrs[] = $row['code'];
	}

	$c_done = 0;
	if($config['auto_country'] == 1){
		$a_country = iptocountry($IP);
		if(in_array($a_country, $ctrs)){
			$c_done = 1;
			$country = $a_country;
		}
	}

	$errMessage = '';
	if(isset($_POST['register']) && isset($_SESSION['register_token']) && $_SESSION['register_token'] == $_POST['token']){
		$name = $db->EscapeString($_POST['username']);
		$email = $db->EscapeString($_POST['email']);
		$email2 = $db->EscapeString($_POST['email2']);
		$gender = $db->EscapeString($_POST['gender']);
		$pass1 = $db->EscapeString($_POST['password']);
		$pass2 = $db->EscapeString($_POST['password2']);
		$subs = (isset($_POST['subscribe']) ? 1 : 0);
		
		if($c_done == 0){
			$country = $db->EscapeString($_POST['country']);
		}
		
		$captcha_valid = 1;
		if($config['captcha_sys'] == 1 || $config['captcha_sys'] == 2){
			$captcha_valid = 0;
			if($config['captcha_sys'] == 1){
				$recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_sec']);
				$recaptcha = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
			
				if($recaptcha->isSuccess()){
					$captcha_valid = 1;
				}else{
					$captcha_valid = 0;
				}
			}elseif($config['captcha_sys'] == 2){
				$solvemedia_response = solvemedia_check_answer($config['solvemedia_v'],$_SERVER["REMOTE_ADDR"],$_POST["adcopy_challenge"],$_POST["adcopy_response"],$config['solvemedia_h']);
				if(!$solvemedia_response->is_valid){
					$recaptcha_error = $solvemedia_response->error;
					$captcha_valid = 0;
				}else{
					$captcha_valid = 1;
				}
			}
		}
		
		if(!$captcha_valid){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_54'].'</div>';
		}elseif(!isUserID($name)) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_129'].'</div>';
		}elseif(!isEmail($email)) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_65'] .'</div>';
		}elseif(!checkPwd($pass1,$pass2)) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_63'].'</div>';
		}elseif($email != $email2) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_279'] .'</div>';
		}elseif($gender != 1 && $gender != 2) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_208'].'</div>';
		}elseif(blacklist_check($email, 1)) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_294'], array('-EMAIL-' => $email)).'</div>';
		}elseif(blacklist_check($IP, 3)) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_295'], array('-IP-' => $IP)).'</div>';
		}elseif($db->QueryGetNumRows("SELECT id FROM `users` WHERE `login`='".$name."' OR `email`='".$email."' LIMIT 1") > 0) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_127'].'</div>';
		}elseif($config['more_per_ip'] != 1 && isset($_COOKIE['PESAccExist'])) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_128'].'</div>';
		}elseif($config['more_per_ip'] != 1 && $db->QueryGetNumRows("SELECT id FROM `users` WHERE `IP`='".$IP."' OR `log_ip`='".$IP."' LIMIT 1") > 0) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_128'].'</div>';
		}elseif($c_done == 0 && !in_array($country, $ctrs)) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_209'].'</div>';
		}else{
			$ref_paid = 0;
			$activate = 0;
			$referal = (isset($_COOKIE['PlusREF']) ? $db->EscapeString($_COOKIE['PlusREF']) : 0);

			if($referal != 0 && $db->QueryGetNumRows("SELECT id FROM `users` WHERE `id`='".$referal."' LIMIT 1") == 0) {
				$referal = 0;
			}

			if($config['reg_reqmail'] == 0){
				$activate = rand(1000000, 999999999);

				if($config['mail_delivery_method'] == 1){
					$mailer->isSMTP();
					$mailer->Host = $config['smtp_host'];
					$mailer->Port = $config['smtp_port'];

					if(!empty($config['smtp_auth'])){
						$mailer->SMTPSecure = $config['smtp_auth'];
					}
					$mailer->SMTPAuth = (empty($config['smtp_username']) || empty($config['smtp_password']) ? false : true);
					if($mailer->SMTPAuth){
						$mailer->Username = $config['smtp_username'];
						$mailer->Password = $config['smtp_password'];
					}
				}
				
				$mailer->AddAddress($email, $name);
				$mailer->SetFrom((!empty($config['noreply_email']) ? $config['noreply_email'] : $config['site_email']), $config['site_name']);
				$mailer->Subject = $lang['b_130'];
				$mailer->MsgHTML('<html>
									<body style="font-family: Verdana; color: #333333; font-size: 12px;">
										<table style="width: 400px; margin: 0px auto;">
											<tr style="text-align: center;">
												<td style="border-bottom: solid 1px #cccccc;"><h1 style="margin: 0; font-size: 20px;"><a href="'.$config['site_url'].'" style="text-decoration:none;color:#333333"><b>'.$config['site_name'].'</b></a></h1><h2 style="text-align: right; font-size: 14px; margin: 7px 0 10px 0;">'.$lang['b_130'].'</h2></td>
											</tr>
											<tr style="text-align: justify;">
												<td style="padding-top: 15px; padding-bottom: 15px;">
													Hello '.$name.',
													<br /><br />
													Click on this link to activate your account:<br />
													<a href="'.$config['site_url'].'/?activate='.$activate.'">'.$config['site_url'].'/?activate='.$activate.'</a>
												</td>
											</tr>
											<tr style="text-align: right; color: #777777;">
												<td style="padding-top: 10px; border-top: solid 1px #cccccc;">
													Best Regards!
												</td>
											</tr>
										</table>
									</body>
								</html>');
				$mailer->Send();
			}else{
				if($referal > 0 && is_numeric($referal) && $config['refsys'] == 1 && $config['aff_click_req'] == 0){
					$user = $db->QueryFetchArray("SELECT id FROM `users` WHERE `id`='".$referal."' LIMIT 1");
					if($user['id'] > 0){
						$add_cash = $config['paysys'] == 1 ? ", `account_balance`=`account_balance`+'".$config['ref_cash']."'" : '';
						$db->Query("UPDATE `users` SET `coins`=`coins`+'".$config['ref_coins']."'".$add_cash." WHERE `id`='".$user['id']."'");
						$ref_paid = 1;
					}
				}
			}

			$passc = MD5($pass1);
			if(isset($_COOKIE['PESRefSource'])){
				$ref_source = $db->EscapeString($_COOKIE['PESRefSource']);
			}else{
				$ref_source = '0';
			}
			
			if(!isset($_COOKIE['PESAccExist'])){
				setcookie('PESAccExist', $name, time()+604800, '/');
			}
			
			$db->Query("INSERT INTO `users`(email,login,country,sex,coins,account_balance,IP,pass,ref,ref_paid,signup,newsletter,activate,ref_source) values('".$email."','".$name."','".$country."','".$gender."','".$config['reg_coins']."','".$config['reg_cash']."','".$IP."','".$passc."','".$referal."','".$ref_paid."',NOW(),'".$subs."','".$activate."','".$ref_source."')");
			$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_131'].' '.($config['reg_reqmail'] == 0 ? $lang['b_132'] : $lang['b_133']).'</div>';
		}
	}
?>
	<script type="text/javascript">
		function check_username(){var b=$('#username').val();if(b.length<3){$('#username').addClass('is-invalid')}else{$.get("system/ajax.php?a=checkUser",{data:b},function(a){if(a==1){$('#username').removeClass('is-invalid').addClass('is-valid')}else{$('#username').removeClass('is-valid').addClass('is-invalid')}})}}function check_email(){var b=$('#email').val();if(b.length<6){$('#email').addClass('is-invalid')}else{$.get("system/ajax.php?a=checkEmail",{data:b},function(a){if(a==1){$('#email').removeClass('is-invalid').addClass('is-valid')}else{$('#email').removeClass('is-valid').addClass('is-invalid')}})}}function check_email2(){var a=new RegExp(/^(("[\w-+\s]+")|([\w-+]+(?:\.[\w-+]+)*)|("[\w-+\s]+")([\w-+]+(?:\.[\w-+]+)*))(@((?:[\w-+]+\.)*\w[\w-+]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][\d]\.|1[\d]{2}\.|[\d]{1,2}\.))((25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\.){2}(25[0-5]|2[0-4][\d]|1[\d]{2}|[\d]{1,2})\]?$)/i);var b=$('#email').val();var c=$('#repeat_email').val();if(!a.test(c)){$('#repeat_email').removeClass('is-valid').addClass('is-invalid')}else if(b==c){$('#repeat_email').removeClass('is-invalid').addClass('is-valid')}else{$('#repeat_email').removeClass('is-valid').addClass('is-invalid')}}
	</script>

    <main role="main" class="container">
      <div class="row">
		<div class="col-12">
			<div class="my-3 p-3 bg-white rounded box-shadow box-style">
				<div id="blue-box">
					<div class="title">
						<?=$lang['b_58']?>
					</div>
					<div class="content">
						<?=$errMessage?>
						<form method="post">
						  <input type="hidden" name="token" value="<?=GenRegisterToken()?>">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="username"><?=$lang['b_122']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-user"></i></div></div>
								<input type="text" class="form-control" id="username" name="username" placeholder="John_Doe" onchange="check_username()">
							  </div>
							 </div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="email"><?=$lang['b_70']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-envelope"></i></div></div>
								<input type="email" class="form-control" id="email" name="email" placeholder="name@domain.com" onchange="check_email()">
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="repeat_email"><?=$lang['b_278']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-envelope"></i></div></div>
								<input type="email" class="form-control" id="repeat_email" name="email2" placeholder="name@domain.com" onchange="check_email2()">
							  </div>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="password"><?=$lang['b_15']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-key"></i></div></div>
								<input type="password" class="form-control" id="password" name="password" placeholder="X8df!90EO">
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="repeat_password"><?=$lang['b_72']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-key"></i></div></div>
								<input type="password" class="form-control" id="repeat_password" name="password2" placeholder="X8df!90EO">
							  </div>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="gender"><?=$lang['b_202']?></label>
							  <select id="gender" name="gender" class="form-control custom-select">
								<option value="0">Choose...</option>
								<option value="1"><?=$lang['b_203']?></option>
								<option value="2"><?=$lang['b_204']?></option>
							  </select>
							</div>
							<div class="form-group col-md-6">
							  <label for="country"><?=$lang['b_201']?></label>
							  <select id="country" name="country" class="form-control custom-select">
								<?php
									if($c_done == 1){
										$ctr = $db->QueryFetchArray("SELECT country,code FROM `list_countries` WHERE `code`='".$country."'"); 
										echo '<option value="'.$ctr['code'].'">'.$ctr['country'].'</option>';
									}else{
										$countries = $db->QueryFetchArrayAll("SELECT country,code FROM `list_countries` ORDER BY country ASC"); 
										echo '<option value="0">Choose...</option>';
										foreach($countries as $country){ 
											echo '<option value="'.$country['code'].'">'.$country['country'].'</option>';
										}
									}
								?>
							  </select>
							</div>
						  </div>
							<?php 
								if($config['captcha_sys'] == 1 || $config['captcha_sys'] == 2) {
									echo '<p>';
									
									if($config['captcha_sys'] == 1){
										echo '<script src="https://www.google.com/recaptcha/api.js"></script><div class="g-recaptcha" data-sitekey="'.$config['recaptcha_pub'].'"></div>';
									}elseif($config['captcha_sys'] == 2){
										echo solvemedia_get_html($config['solvemedia_c']);
									}

									echo '</p>';
								} 
							  ?>
						  <div class="form-group">
							<div class="form-check">
							  <input class="form-check-input" type="checkbox" id="newsletter" name="subscribe">
							  <label class="form-check-label" for="newsletter">
								<?=$lang['b_245']?>
							  </label>
							</div>
						  </div>
						  <button type="submit" name="register" class="btn btn-primary"><?=$lang['b_58']?></button>
						</form>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>