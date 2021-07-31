<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	if($config['captcha_sys'] == 1){
		include('system/libs/recaptcha/autoload.php');
	}elseif($config['captcha_sys'] == 2){
		include('system/libs/solvemedialib.php');
	}
	
	$errMessage = '';
	$change_pass = false;
	$captcha_valid = true;
	if(isset($_GET['hash']) && $_GET['hash'] > 0 && is_numeric($_GET['hash'])){
		$hash = $db->EscapeString($_GET['hash']);
		$rec = $db->QueryFetchArray("SELECT id,login,email FROM `users` WHERE `rec_hash`='".$hash."' LIMIT 1");
		
		if(!empty($rec['id'])){
			$change_pass = true;
		}

		if($change_pass && isset($_POST['change']) && isset($_SESSION['token']) && $_SESSION['token'] == $_POST['token']) {
			if($config['captcha_sys'] == 1 || $config['captcha_sys'] == 2){
				$captcha_valid = 0;
				if($config['captcha_sys'] == 1){
					$recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_sec']);
					$recaptcha = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
				
					if($recaptcha->isSuccess()){
						$captcha_valid = 1;
					}else{
						$recaptcha_error = $resp->error;
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
			}elseif(!checkPwd($_POST['pass1'],$_POST['pass2'])) {
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_63'].'</div>';
			}else{
				$db->Query("UPDATE `users` SET `pass`='".MD5($_POST['pass1'])."', `rec_hash`='0' WHERE `email`='".$rec['email']."'");
				$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_64'].'</div>';
			}
		}
	}

	if(isset($_POST['send']) && isset($_SESSION['token']) && $_SESSION['token'] == $_POST['token']) {
		$email = $db->EscapeString($_POST['email']);
		$rec = $db->QueryFetchArray("SELECT id,login FROM `users` WHERE `email`='".$email."' LIMIT 1");

		if($config['captcha_sys'] == 1 || $config['captcha_sys'] == 2){
			$captcha_valid = 0;
			if($config['captcha_sys'] == 1){
				$recaptcha = new \ReCaptcha\ReCaptcha($config['recaptcha_sec']);
				$recaptcha = $recaptcha->verify($_POST['g-recaptcha-response'], $_SERVER['REMOTE_ADDR']);
			
				if($recaptcha->isSuccess()){
					$captcha_valid = 1;
				}else{
					$recaptcha_error = $resp->error;
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
		}elseif($_POST['email'] == ""){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_65'].'</div>';
		}elseif(empty($rec['login'])){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_110'].'</div>';
		}else{
			$newhash = rand(100000,9999999);
			$recover_url = GenerateURL('recover&hash='.$newhash, true);
			$db->Query("UPDATE `users` SET `rec_hash`='".$newhash."' WHERE `email`='".$email."'");

			if($config['mail_delivery_method'] == 1){
				$mailer->isSMTP();
				$mailer->Host = $config['smtp_host'];
				$mailer->Port = $config['smtp_port'];

				if(!empty($config['smtp_auth'])){
					$mailer->SMTPSecure = $config['smtp_auth'];
				}
				$mailer->SMTPAuth = (empty($config['smtp_username']) || empty($config['smtp_password']) ? false : true);
				if(!empty($config['smtp_username']) && !empty($config['smtp_password'])){
					$mailer->Username = $config['smtp_username'];
					$mailer->Password = $config['smtp_password'];
				}
			}

			$mailer->AddAddress($email, $rec['login']);
			$mailer->SetFrom((!empty($config['noreply_email']) ? $config['noreply_email'] : $config['site_email']), $config['site_name']);
			$mailer->Subject = $lang['b_15'];
			$mailer->MsgHTML('<html>
								<body style="font-family: Verdana; color: #333333; font-size: 12px;">
									<table style="width: 400px; margin: 0px auto;">
										<tr style="text-align: center;">
											<td style="border-bottom: solid 1px #cccccc;"><h1 style="margin: 0; font-size: 20px;"><a href="'.$config['site_url'].'" style="text-decoration:none;color:#333333"><b>'.$config['site_name'].'</b></a></h1><h2 style="text-align: right; font-size: 14px; margin: 7px 0 10px 0;">'.$lang['b_15'].'</h2></td>
										</tr>
										<tr style="text-align: justify;">
											<td style="padding-top: 15px; padding-bottom: 15px;">
												Hello '.$rec['login'].',
												<br /><br />
												You asked to recover your password.<br />To get your new password, access this URL:<br />
												<a href="'.$recover_url.'">'.$recover_url.'</a>
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

			$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_111'].'</div>';
		}
	}
?>
    <main role="main" class="container">
      <div class="row">
		<div class="col-12">
			<div class="my-3 p-3 bg-white rounded box-shadow box-style">
				<div id="blue-box">
					<div class="title">
						<?=($change_pass ? $lang['b_68'] : $lang['b_112'])?>
					</div>
					<div class="content">
						<?=$errMessage?>
						<form method="post">
						<input type="hidden" name="token" value="<?=GenGlobalToken()?>">
						<?php
							if($change_pass) {
						?>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="password"><?=$lang['b_71']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-key"></i></div></div>
								<input type="password" class="form-control" id="password" name="pass1" placeholder="X8df!90EO">
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="repeat_password"><?=$lang['b_72']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-key"></i></div></div>
								<input type="password" class="form-control" id="repeat_password" name="pass2" placeholder="X8df!90EO">
							  </div>
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
						  <button type="submit" name="change" class="btn btn-primary"><?=$lang['b_58']?></button>
						<?php } else { ?>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="email"><?=$lang['b_70']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-envelope"></i></div></div>
								<input type="email" class="form-control" id="email" name="email" placeholder="<?=$lang['b_70']?>">
							  </div>
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
						  <button type="submit" name="send" class="btn btn-primary"><?=$lang['b_52']?></button>
						<?php } ?>
						</form>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>