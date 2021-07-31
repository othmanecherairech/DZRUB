<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	if($config['captcha_sys'] == 1){
		include('system/libs/recaptcha/autoload.php');
	}elseif($config['captcha_sys'] == 2){
		include('system/libs/solvemedialib.php');
	}

	$errMessage = '';
	if(isset($_POST['send'])) {

		$captcha_valid = 1;
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
		}elseif(empty($_POST['name'])){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_55'].'</div>';
		}elseif(empty($_POST['email'])){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_56'].'</div>';
		}elseif(empty($_POST['message'])){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_57'].'</div>';
		}else{
			$subject = 'PES Contact'.($is_online && $data['premium'] > 0 ? ' - VIP Member' : '');
			$message = (!empty($data['login']) ? '<b>Sender Username:</b> '.$data['login'].'<br />' : '<b>Sender Name:</b> '.$_POST['name'].'<br />').'<b>Sender Email:</b> '.$_POST['email'].'<br /> <b>Sender IP:</b> '.$_SERVER['REMOTE_ADDR'].'<br />-------------------------------------<br /> <b>Website URL:</b> '.$config['site_url'].'<br /><br /> ---------------Message---------------<br /><br />'.nl2br($_POST['message']);
			$header = "From: ".$_POST['email']."\r\n".
					  "MIME-Version: 1.0\r\n".
					  "Content-Type: text/html;charset=utf-8";
			mail($config['site_email'],$subject,$message,$header);
			$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_53'].'</div>';
		}
	}
?> 
 <main role="main" class="container">
      <div class="row">
		<?php 
			if($is_online) {
				require_once(BASE_PATH.'/template/'.$config['theme'].'/common/sidebar.php');
			}
		?>
		<div class="col-md-<?=($is_online ? 9 : 12)?>">
			<div class="my-3 p-3 bg-white rounded box-shadow box-style">
				<div id="blue-box">
					<div class="title">
						<?=$lang['b_47']?>
					</div>
					<div class="content">
						<?=$errMessage?>
						<form method="post">
						  <input type="hidden" name="token" value="<?=GenRegisterToken()?>">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="name"><?=$lang['b_48']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-user"></i></div></div>
								<input type="text" class="form-control" id="name" name="name" placeholder="John_Doe" required="required">
							  </div>
							 </div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="email"><?=$lang['b_49']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-envelope"></i></div></div>
								<input type="email" class="form-control" id="email" name="email" placeholder="name@domain.com" required="required">
							  </div>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-12">
							  <label for="message"><?=$lang['b_50']?></label>
							  <textarea class="form-control" id="message" name="message" rows="3" required="required"></textarea>
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
						</form>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>