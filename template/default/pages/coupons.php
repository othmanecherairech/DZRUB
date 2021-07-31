<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
	
	$errMessage = '<div class="alert alert-info" role="alert">'.$lang['b_62'].'</div>';
	if(isset($_POST['submit'])) {
		$code = $db->EscapeString($_POST['code']);
		
		$ext = $db->QueryFetchArray("SELECT id,coins,uses,type,exchanges FROM `coupons` WHERE `code`='".$code."' AND (`uses`>'0' OR `uses`='u') LIMIT 1");
		$total_clicks = $db->QueryFetchArray("SELECT SUM(`total_clicks`) AS `clicks` FROM `user_clicks` WHERE `uid`='".$data['id']."'");
		$used = $db->QueryGetNumRows("SELECT id FROM `used_coupons` WHERE `user_id`='".$data['id']."' AND `coupon_id`='".$ext['id']."' LIMIT 1");
		if(empty($ext['id']) || $used > 0){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_61'].'</div>';
		}elseif($ext['exchanges'] != 0 && $ext['exchanges'] > $total_clicks['clicks']){
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_337'], array('-NUM-' => number_format($ext['exchanges']))).'</div>';
		}else{
			if($ext['type'] == 1){
				$premium = ($data['premium'] == 0 ? (time()+(86400*$ext['coins'])) : ((86400*$ext['coins'])+$data['premium']));
				$db_add = "`premium`='".$premium."'";
			}else{
				$db_add = "`coins`=`coins`+'".$ext['coins']."'";
			}
			
			$db->Query("UPDATE `users` SET ".$db_add." WHERE `id`='".$data['id']."'");
			$db->Query("UPDATE `coupons` SET ".($ext['uses'] != 'u' ? "`uses`=`uses`-'1', " : '')."`used`=`used`+'1' WHERE `code`='".$code."'");
			$db->Query("INSERT INTO `used_coupons` (user_id, coupon_id) VALUES('".$data['id']."','".$ext['id']."')");
			$errMessage = '<div class="alert alert-success" role="alert">'.lang_rep(($ext['type'] == 1 ? $lang['b_270'] : $lang['b_60']), array('-NUM-' => $ext['coins'])).'</div>';
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
						<?=$lang['b_10']?>
					</div>
					<div class="content">
						<form method="post">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="cpde"><?=$lang['b_10']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-ticket"></i></div></div>
								<input type="text" class="form-control" id="cpde" name="code" placeholder="<?=$lang['b_59']?>">
								<input type="submit" class="btn btn-primary d-inline" name="submit" value="<?=$lang['b_58']?>" />
							  </div>
							 </div>
						  </div>
						</form>
                        <div align="Center" id="bannerswall_977"><script src="https://bannerswall.ru/bancode.php?id=977" async></script></div>
					</div>
				</div>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_365']?>
					</div>
					<div class="content">
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th class="w-25"><?=$lang['b_154']?></th>
									<th class="text-center"><?=$lang['b_59']?></th>
									<th class="text-center"><?=$lang['b_327']?></th>
								</tr>
							</thead>
							<tfoot  class="thead-dark">
								<tr><th colspan="4" class="text-center"><?=$lang['b_365']?></th></tr>
							</tfoot>
							<tbody class="table-primary text-dark">
							<?php
							$coupons = $db->QueryFetchArrayAll("SELECT a.id,b.code,b.coins,b.type FROM used_coupons a LEFT JOIN coupons b ON b.id = a.coupon_id WHERE a.user_id='".$data['id']."' ORDER BY a.id DESC LIMIT 5");
							if(!$coupons){
								echo '<tr><td colspan="4"><center>'.$lang['b_250'].'</center></td></tr>';
							}

							foreach($coupons as $coupon){
							?>	
								<tr>
									<td><?=$coupon['id']?></td>
									<td><?=$coupon['code']?></td>
									<td><?=$coupon['coins'].' '.($coupon['type'] == 1 ? $lang['b_246'] : $lang['b_156'])?></td>
								</tr>
							<?}?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>