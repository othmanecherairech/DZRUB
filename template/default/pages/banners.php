<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
	$errMessage = '';
?> 
	<main role="main" class="container">
      <div class="row">
		<?php 
			require_once(BASE_PATH.'/template/'.$config['theme'].'/common/sidebar.php');
		?>
	  <div class="col-md-9">
			<div class="my-3 p-3 bg-white rounded box-shadow box-style">
				<div id="blue-box">
					<div class="title">
						<?=$lang['b_179']?>
					</div>
					<div class="content">
						<?php
							if($config['banner_system'] == 2 && $data['premium'] == 0){
						?>
							<div class="alert alert-danger" role="alert"><?=$lang['b_234']?></div>
						<?
						}else{
							if(isset($_GET['add'])){
							if(isset($_POST['submit'])){
								$url = $db->EscapeString($_POST['url']);
								$pack = $db->EscapeString($_POST['pack']);
								$pack = $db->QueryFetchArray("SELECT * FROM `ad_packs` WHERE `id`='".$pack."'");

								$MAX_SIZE = 500;	// Max banner size in kb
								function getExtension($str) {
									if($str == 'image/jpeg'){
										return 'jpg';
									}elseif($str == 'image/png'){
										return 'png';
									}elseif($str == 'image/gif'){
										return 'gif';
									}
								}
								function random_string($length) {
									$key = '';
									$keys = array_merge(range(0, 9), range('a', 'z'));
									for ($i = 0; $i < $length; $i++) {
										$key .= $keys[array_rand($keys)];
									}
									return $key;
								}

								if(!empty($url) && !empty($pack) && $_FILES['cons_image']['name']){
									$tmpFile = $_FILES['cons_image']['tmp_name'];
									$b_info = getimagesize($tmpFile);
									$extension = getExtension($b_info['mime']);
									
									if($pack['id'] == ''){
										$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_168'].'</div>';
									}elseif($pack['price'] > $data['account_balance']){
										$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_99'].'</div>';
									}elseif(!preg_match("|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i", $url) || substr($url,-4) == '.exe'){
										$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_167'].'</div>';
									}elseif($b_info['mime'] != 'image/jpeg' && $b_info['mime'] != 'image/png' && $b_info['mime'] != 'image/gif'){
										$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_171'].'</div>';
									}elseif($pack['type'] == 0 && ($b_info[0] != '468' && $b_info[1] != '60')){
										$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_338'], array('-SIZE-' => '468x60')).'</div>';
									}elseif($pack['type'] == 1 && $b_info[0] != '728' && $b_info[1] != '90'){
										$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_338'], array('-SIZE-' => '728x90')).'</div>';
									}elseif(filesize($tmpFile) > $MAX_SIZE*1024){
										$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_305'], array('-SIZE-' => $MAX_SIZE)).'</div>';
									}else{	
										$image_name = 'b-'.$data['id'].'_'.($pack['type'] == 1 ? '728x90' : '648x60').'_'.random_string(rand(7,14)).'.'.$extension;
										$copied = copy($tmpFile, BASE_PATH.'/files/banners/'.$image_name);

										if(!$copied){
											$errMessage = '<div class="alert alert-danger" role="alert"><b>ERROR:</b> Banner wasn\'t uploaded, please contact your site admin!</div>';
										}else{
											$banner = '/files/banners/'.$image_name;
											$expiration = ($pack['days']*86400)+time();
											$db->Query("UPDATE `users` SET `account_balance`=`account_balance`-'".$pack['price']."' WHERE `id`='".$data['id']."'");
											$db->Query("UPDATE `ad_packs` SET `bought`=`bought`+'1' WHERE `id`='".$pack['id']."'");
											$db->Query("INSERT INTO `banners` (user, banner_url, site_url, expiration, type) VALUES('".$data['id']."', '".$banner."', '".$url."', '".$expiration."', '".$pack['type']."')");
											
											if($config['paysys'] == 1 && $data['ref'] > 0 && $data['ref_paid'] == 1 && $pack['price'] > 0){
												$commission = number_format(($pack['price']/100) * $config['ref_sale'], 2);
												affiliate_commission($data['ref'], $data['id'], $commission, 'banner_purchase');	
											}
											
											$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_170'].'</div>';
										}
									}
								}else{
									$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_25'].'</div>';
								}
							}
						?>
							<script type="text/javascript"> function getOptions(){var b=$("#size").val();$.get('system/ajax.php?a=bannerPacks&type='+b,function(a){$('#bPacks').html(a);$('#load').hide();$('#bPacks').show()})} </script>
							<?=$errMessage?>
							<form method="post" enctype="multipart/form-data">
							  <div class="form-row">
								<div class="form-group col-md-6">
								  <label for="url"><?=$lang['b_174']?></label>
								  <div class="input-group mb-2 mr-sm-2">
									<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-link"></i></div></div>
									<input type="text" class="form-control" id="url" name="url" placeholder="http://">
								  </div>
								</div>
								<div class="form-group col-md-6">
									<label for="banner"><?=$lang['b_175']?></label>
									<div class="custom-file" id="customFile" lang="es">
										<input type="file" class="custom-file-input" id="proof" name="cons_image" aria-describedby="banner">
										<label class="custom-file-label" for="banner"><?=$lang['b_175']?></label>
									</div>
								</div>
							  </div>
							  <div class="form-row">
								<div class="form-group col-md-6">
								  <label for="size"><?=$lang['b_339']?></label>
								  <select id="size" name="size" onchange="getOptions()" class="custom-select">
									<option value="0">468x60</option>
									<option value="1">728x90</option>
								  </select>
								  </div>
								<div class="form-group col-md-6">
								  <label for="bPacks"><?=$lang['b_177']?></label>
								  <select id="bPacks" name="pack" class="custom-select">
									<?php
										$packs = $db->QueryFetchArrayAll("SELECT * FROM `ad_packs` WHERE `type`='0' ORDER BY `price` ASC");
										foreach($packs as $pack){
											echo '<option value="'.$pack['id'].'" '.(isset($_POST['pack']) && $_POST['pack'] == $pack['id'] ? ' selected' : '').'">'.$pack['days'].' '.$lang['b_178'].' - '.get_currency_symbol($config['']).$pack['price'].'</option>';
										}
									?>
								  </select>
								</div>
							  </div>
							  <p><input type="submit" name="submit" class="btn btn-primary d" value="<?=$lang['b_58']?>" /></p>
							</form>
						<?php 
							}elseif(isset($_GET['edit'])){
							$id = $db->EscapeString($_GET['edit']);
							$banner = $db->QueryFetchArray("SELECT * FROM `banners` WHERE `id`='".$id."' AND `user`='".$data['id']."'");

							if(empty($banner['id'])){
								redirect(GenerateURL('banners'));
							}

							if(isset($_POST['delete'])){
								$db->Query("DELETE FROM `banners` WHERE `id`='".$id."' AND `user`='".$data['id']."'");
								redirect(GenerateURL('banners'));
							}elseif(isset($_POST['update'])){
								$pack = $db->EscapeString($_POST['pack']);
								$pack = $db->QueryFetchArray("SELECT * FROM `ad_packs` WHERE `id`='".$pack."'");

								if(empty($pack['id'])){
									$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_168'].'</div>';
								}elseif($pack['price'] > $data['account_balance']){
									$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_99'].'</div>';
								}elseif(!empty($banner['id']) && $banner['type'] == $pack['type']){
									$banner['expiration'] = ($banner['expiration'] > 0 ? ($pack['days']*86400)+$banner['expiration'] : ($pack['days']*86400)+time());
									$db->Query("UPDATE `users` SET `account_balance`=`account_balance`-'".$pack['price']."' WHERE `id`='".$data['id']."'");
									$db->Query("UPDATE `ad_packs` SET `bought`=`bought`+'1' WHERE `id`='".$pack['id']."'");
									$db->Query("UPDATE `banners` SET `expiration`='".$banner['expiration']."' WHERE `id`='".$id."' AND `user`='".$data['id']."'");
									
									if($config['paysys'] == 1 && $data['ref'] > 0 && $data['ref_paid'] == 1 && $pack['price'] > 0){
										$commission = number_format(($pack['price']/100) * $config['ref_sale'], 2);
										affiliate_commission($data['ref'], $data['id'], $commission, 'banner_update');
									}
									
									$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_74'].'</div>';
								}
							}
							echo $errMessage;
							?>
							<div class="infobox"><b><?=$lang['b_186']?>:</b> <?=($banner['expiration'] == 0 ? 'Expired' : date('d-m-Y H:i', $banner['expiration']))?></div>
							<form method="post">
							  <div class="form-row">
								<div class="form-group col-md-6">
								  <label for="pack"><?=$lang['b_177']?></label>
								  <select id="pack" name="pack" class="custom-select">
									<?php
										$packs = $db->QueryFetchArrayAll("SELECT * FROM `ad_packs` WHERE `type`='".$banner['type']."' ORDER BY `price` ASC");
										foreach($packs as $pack){echo '<option value="'.$pack['id'].'"'.(isset($_POST["pack"]) && $_POST["pack"] == $pack['id'] ? ' selected' : '').'>'.$pack['days'].' '.$lang['b_178'].' - '.get_currency_symbol($config['']).$pack['price'].'</option>';}
									?>
								  </select>
								</div>
							  </div>
							  <p><input type="submit" name="update" class="btn btn-primary" value="<?=$lang['b_188']?>" /> <input type="submit" name="delete" class="btn btn-danger" onclick="return confirm('<?=$lang['b_80']?>');" value="<?=$lang['b_81']?>" /></p>
							</form>
							</div>
						<?php }else{ ?>
						<a class="btn btn-success mb-2 w-50 text-center float-right" href="<?=GenerateURL('banners&add')?>"><?=$lang['b_173']?></a>
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr><th><?=$lang['b_182']?></th><th><?=$lang['b_183']?></th><th><?=$lang['b_184']?></th><th><?=$lang['b_339']?></th><th><?=$lang['b_75']?></th><th><?=$lang['b_185']?></th></tr>
							</thead>
							<tbody class="table-primary text-dark">
								<?php
									$banners = $db->QueryFetchArrayAll("SELECT * FROM `banners` WHERE `user`='".$data['id']."'");
									foreach($banners as $banner){
										$status = ($banner['expiration'] != 0 ? '<font color="green">'.$lang['b_180'].'</font>' : ($banner['status'] == 2 ? '<font color="red"><b>'.$lang['b_78'].'</b></font>' : '<font color="red">'.$lang['b_181'].'</font>'));
								?>
									<tr><td><a href="<?=$banner['site_url']?>" title="<?=$banner['site_url']?>" class="img-fluid" target="_blank"><img src="<?=$config['secure_url'].$banner['banner_url']?>" width="280" border="0" /></a></td><td><?=number_format($banner['views'])?></td><td><?=number_format($banner['clicks'])?></td><td align="center"><?=($banner['type'] == 1 ? '728x90' : '468x60')?></td><td><?=$status?></td><td align="center"><?php if($banner['status'] != 2){ ?><a href="<?=GenerateURL('banners&edit='.$banner['id'])?>"><?=$lang['b_96']?></a><?php } ?></td></tr>
								<?php } ?>				
							</tbody>
						</table>
						<?php }} ?>	
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>