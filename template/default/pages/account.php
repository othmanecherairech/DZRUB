<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$errMessage = '';
	if(isset($_POST['change_pass'])){
		if (MD5($_POST['old_password']) != $data['pass']) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_357'].'</div>';
		}elseif(!checkPwd($_POST['password'],$_POST['password2'])) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_63'].'</div>';
		}else{
			$enpass = MD5($_POST['password']);
			$db->Query("UPDATE `users` SET `pass`='".$enpass."' WHERE `id`='".$data['id']."'");
			$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_64'].'</div>';
		}
	}elseif(isset($_POST['change_email'])){
		$email = $db->EscapeString($_POST['email']);
		$password = $db->EscapeString($_POST['password']);
		$subs = $db->EscapeString($_POST['subscribe']);
		$subs = ($subs != 0 && $subs != 1 ? 1 : $subs);

		if (MD5($_POST['password']) != $data['pass']) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_357'].'</div>';
		}elseif(!isEmail($email)) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_65'].'</div>';
		}elseif($db->QueryGetNumRows("SELECT id FROM `users` WHERE `email`='".$email."' LIMIT 1") > 0 && $data['email'] != $email){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_66'].'</div>';
		}else{
			$db->Query("UPDATE `users` SET `email`='".$email."', `newsletter`='".$subs."' WHERE `id`='".$data['id']."'");
			$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_67'].'</div>';
		}
	}

	$change_limit = ($data['premium'] > 0 ? $config['c_v_limit'] : $config['c_c_limit']);
	if(isset($_POST['change_info'])){
		$gender = $db->EscapeString($_POST['gender']);
		$country = $db->EscapeString($_POST['country']);
		
		$sql = $db->Query("SELECT code FROM `list_countries` ORDER BY country");
		$ctrs = array();
		while ($row = $db->FetchArray($sql)) {
			$ctrs[] = $row['code'];
		}

		if($gender != 1 && $gender != 2){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_208'].'</div>';
		}elseif(!in_array($country, $ctrs) || $country == '0'){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_209'].'</div>';
		}elseif($data['c_changes'] >= $change_limit){
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_222'], array('-NUM-' => $change_limit)).'</div>';
		}else{
			$db->Query("UPDATE `users` SET `country`='".$country."', `c_changes`=`c_changes`+'1', `sex`='".$gender."' WHERE `id`='".$data['id']."'");
			$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_211'].'</div>';
		}
	}

    if(isset($_POST['del_acc'])){
        $pass = MD5($_POST['password']);
        if($db->QueryGetNumRows("SELECT id FROM `users` WHERE `id`='".$data['id']."' AND `pass`='".$pass."'") == 0){
            $errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_357'].'</div>';
        }else{
            $db->Query("INSERT INTO `users_deleted` (`id`,`email`,`login`,`pass`,`sex`,`country`,`time`) values('".$data['id']."','".$data['email']."','".$data['login']."','".$data['pass']."','".$data['sex']."','".$data['country']."',NOW())");
            $db->Query("DELETE FROM `users` WHERE `id` = '".$data['id']."' AND `pass`='".$pass."'");
            if(isset($_COOKIE['PESAutoLogin'])){
                setcookie('PESAutoLogin', '0', time()-604800);
            }
            session_destroy();
            redirect($config['site_url']);
        }
    }
?> 
	<main role="main" class="container">
      <div class="row">
		<?php 
			require_once(BASE_PATH.'/template/'.$config['theme'].'/common/sidebar.php');
		?>
	  <div class="col-md-9">
			<div class="my-3 p-3 bg-white rounded box-shadow box-style">
				<?=$errMessage?>
				<div id="blue-box">
					<div class="title">
						<?=$lang['b_69']?>
					</div>
					<div class="content">
						<form method="post">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="email"><?=$lang['b_70']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-envelope"></i></div></div>
								<input type="text" class="form-control" id="email" name="email" placeholder="<?=$data['email']?>">
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="password"><?=$lang['b_15']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-key"></i></div></div>
								<input type="password" class="form-control" id="password" name="password" placeholder="">
								<input type="submit" class="btn btn-primary d-inline" name="change_email" value="<?=$lang['b_58']?>" />
							  </div>
						    </div>
							<div class="form-group col-md-6">
								<?=$lang['b_245']?> <input type="radio" name="subscribe" value="1" <?=(!isset($_POST['subscribe']) && $data['newsletter'] == 1 ? 'checked="checked" ' : (isset($_POST['subscribe']) && $_POST['subscribe'] == 1 ? 'checked="checked" ' : ''))?>/> <?=$lang['b_124']?> <input type="radio" name="subscribe" value="0" <?=(!isset($_POST['subscribe']) && $data['newsletter'] == 0 ? 'checked="checked" ' : (isset($_POST['subscribe']) && $_POST['subscribe'] == 0 ? 'checked="checked" ' : ''))?>/> <?=$lang['b_125']?>
							</div>
						  </div>
						</form>
					</div>
				</div>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_206']?>
					</div>
					<div class="content">
						<form method="post">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="gender"><?=$lang['b_202']?></label>
							  <select name="gender" class="custom-select"<?=($data['c_changes'] >= $change_limit && $data['sex'] != 0 ? ' disabled' : '')?>>
								<option value="0"></option>
								<option value="1"<?=($data['sex'] == 1 ? ' selected' : '')?>><?=$lang['b_203']?></option>
								<option value="2"<?=($data['sex'] == 2 ? ' selected' : '')?>><?=$lang['b_204']?></option>
							  </select>
							</div>
							<div class="form-group col-md-6">
							  <label for="country"><?=$lang['b_201']?></label>
							  <select name="country" class="custom-select"<?=($data['c_changes'] >= $change_limit && $data['country'] != '0' ? ' disabled' : '')?>>
								<option value="0"></option>
								<?php
									$countries = $db->QueryFetchArrayAll("SELECT country,code FROM `list_countries` ORDER BY country ASC"); 
									foreach($countries as $country){ 
										echo '<option value="'.$country['code'].'"'.($data['country'] == $country['code'] ? ' selected' : '').'>'.$country['country'].'</option>';
									}
								?>
							  </select>
						    </div>
							<?php if($data['c_changes'] < $change_limit || $data['sex'] == '0' || $data['country'] == '0'){ ?>
							<div class="form-group col-md-6">
							  <input type="submit" class="btn btn-primary d-inline" name="change_info" value="<?=$lang['b_58']?>" />
							 </div>
							<?php } ?>
						  </div>
						</form>
					</div>
				</div>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_68']?>
					</div>
					<div class="content">
						<form method="post">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="old_password"><?=$lang['b_356']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-key"></i></div></div>
								<input type="password" class="form-control" id="old_password" name="old_password" placeholder="*******">
							  </div>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="password"><?=$lang['b_71']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-key"></i></div></div>
								<input type="password" class="form-control" id="password" name="password" placeholder="Shd67SHB">
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="password2"><?=$lang['b_72']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-key"></i></div></div>
								<input type="password" class="form-control" id="password2" name="password2" placeholder="Shd67SHB">
								<input type="submit" class="btn btn-primary d-inline" name="change_pass" value="<?=$lang['b_68']?>" />
							  </div>
						    </div>
						  </div>
						</form>
                        
					</div>
				</div>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_276']?>
					</div>
					<div class="content">
						<form method="post">
							<div class="form-group col-md-6">
							  <label for="password"><?=$lang['b_15']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-key"></i></div></div>
								<input type="password" class="form-control" id="password" name="password" placeholder="Shd67SHB">
								<input type="submit" class="btn btn-danger d-inline" name="del_acc" value="<?=$lang['b_276']?>" onclick="return confirm('You sure you want to delete your account?');" />
							  </div>
						    </div>
						  </div>
						</form>
					</div>
				</div>
                
			</div>
	  </div>
    </main>