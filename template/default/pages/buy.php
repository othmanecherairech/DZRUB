<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$errMessage = '';
	if(isset($_POST['submit']) && isset($_POST['pack_id'])){
		$pid = $db->EscapeString($_POST['pack_id']);
		$pack = $db->QueryFetchArray("SELECT id,coins,price FROM `c_pack` WHERE `id`='".$pid."'");
		
		$price = ($config['c_discount'] > 0 ? number_format($pack['price'] * ((100-$config['c_discount']) / 100), 2) : $pack['price']);
		if($pack['id'] < 1){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_262'].'</div>';
		}elseif($data['account_balance'] < $price){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_263'].' <a href="'.GenerateURL('bank').'"><b>'.$lang['b_256'].'...</b></a></div>';
		}else{
			$db->Query("UPDATE `users` SET `account_balance`=`account_balance`-'".$price."', `coins`=`coins`+'".$pack['coins']."' WHERE `id`='".$data['id']."'");
			$db->Query("INSERT INTO `user_transactions` (`user_id`,`type`,`value`,`cash`,`date`)VALUES('".$data['id']."','0','".$pack['coins']."','".$pack['price']."','".time()."')");

			if($config['paysys'] == 1 && $data['ref'] > 0 && $data['ref_paid'] == 1 && $price > 0){
				$commission = number_format(($price/100) * $config['ref_sale'], 2);
				affiliate_commission($data['ref'], $data['id'], $commission, 'coins_purchase');
			}
			
			$errMessage = '<div class="alert alert-success" role="alert">'.lang_rep($lang['b_264'], array('-NUM-' => $pack['coins'].' '.$lang['b_156'])).'</div>';
		}
	}
	elseif(isset($_POST['user_submit']) && isset($_POST['user_pack_id']))
	{
		$pid = $db->EscapeString($_POST['user_pack_id']);
		$pack = $db->QueryFetchArray("SELECT * FROM `sell_coins` WHERE `id`='".$pid."' AND `sold`='0' AND `seller_id`!='".$data['id']."'");

		if($pack['id'] < 1){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_262'].'</div>';
		}elseif($data['account_balance'] < $pack['price']){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_263'].' <a href="'.GenerateURL('bank').'"><b>'.$lang['b_256'].'...</b></a></div>';
		}else{
			$user_fees = (($pack['price']/100) * $config['sc_fees']);
			$user_revenue = ($pack['price'] - $user_fees);
			
			$db->Query("UPDATE `users` SET `account_balance`=`account_balance`-'".$pack['price']."', `coins`=`coins`+'".$pack['coins']."' WHERE `id`='".$data['id']."'");
			$db->Query("UPDATE `users` SET `account_balance`=`account_balance`+'".$user_revenue."' WHERE `id`='".$pack['seller_id']."'");
			$db->Query("UPDATE `sell_coins` SET `buyer_id`='".$data['id']."', `fees`='".$user_fees."', `sold`='1', `sold_time`='".time()."' WHERE `id`='".$pack['id']."'");
			$db->Query("INSERT INTO `user_transactions` (`user_id`,`type`,`value`,`cash`,`date`)VALUES('".$data['id']."','0','".$pack['coins']."','".$pack['price']."','".time()."')");

			if($config['paysys'] == 1 && $data['ref'] > 0 && $data['ref_paid'] == 1 && $pack['price'] > 0){
				$commission = number_format(($pack['price']/100) * $config['ref_sale'], 2);
				affiliate_commission($data['ref'], $data['id'], $commission, 'coins_purchase');
			}
			
			$errMessage = '<div class="alert alert-success" role="alert">'.lang_rep($lang['b_264'], array('-NUM-' => $pack['coins'].' '.$lang['b_156'])).'</div>';
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
						<?=$lang['b_07']?>
					</div>
					<div class="content">
						<?php
							$packs = $db->QueryFetchArrayAll("SELECT id,coins,price FROM `c_pack` ORDER BY `id` ASC");
							if(!$packs){
								echo '<div class="alert alert-danger" role="alert">'.$lang['b_43'].'</div>';
							}else{
								foreach($packs as $pack){
									$price = ($config['c_discount'] > 0 ? number_format($pack['price'] * ((100-$config['c_discount']) / 100), 2) : $pack['price']);
						?>
							<div id="purchase-coins">
								<div class="header">
									<span style="font-size:16px"><?=number_format($pack['coins']).' '.$lang['b_42'].' = '.($config[''] == '' ? get_currency_symbol('USD') : get_currency_symbol($config[''])).$price?></span><br /> 
									<span style="font-size:12px;color:#efefef">1 <?=$lang['b_373']?> = <?=($config['c_discount'] > 0 ? '<s>'.get_currency_symbol($config['']).number_format($pack['price']/$pack['coins'], 4).'</s> -> '.get_currency_symbol($config['']).number_format($price/$pack['coins'], 4) : get_currency_symbol($config['']).number_format($price/$pack['coins'], 4))?> </span>
								</div>
								<form method="POST">
									<input type="hidden" name="pack_id" value="<?=$pack['id']?>" />
									<button type="submit" name="submit" class="btn btn-primary"><i class="fa fa-shopping-cart fa-fw"></i> <?=$lang['b_199']?></button>
								</form>
							</div>
						<?php
								}
							}
						?>
						<div class="clearfix"></div>
					</div>
				</div>
				<?php
					$count = $db->QueryGetNumRows("SELECT * FROM sell_coins WHERE sold = '0' AND `seller_id`!='".$data['id']."'");
					$bpp = 8;
					$page = (isset($_GET['p']) ? intval($_GET['p']) : 0);
					$begin = ($page >= 0 ? ($page*$bpp) : 0);
					
					$packs = $db->QueryFetchArrayAll("SELECT a.*, b.login FROM sell_coins a LEFT JOIN users b ON b.id = a.seller_id WHERE a.sold = '0' AND a.seller_id != '".$data['id']."' ORDER BY a.coin_value ASC, a.price ASC LIMIT ".$begin.", ".$bpp."");
					if($packs){
				?>	
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_374']?>
					</div>
					<div class="content">
						<?php
							foreach($packs as $pack){
						?>
							<div id="user_pack">
								<div class="hdr">
									<span style="font-size:16px"><?=number_format($pack['coins']).' '.$lang['b_42'].' = '.get_currency_symbol($config['']).$pack['price']?></span><br /> 
									<span style="font-size:12px;color:#efefef">1 <?=$lang['b_373']?> = <?=get_currency_symbol($config['']).number_format($pack['price']/$pack['coins'], 4)?> </span>
								</div>
								<form method="POST">
									<span class="seller"><b><?=$lang['b_371']?>:</b> <?=$pack['login']?></span>
									<input type="hidden" name="user_pack_id" value="<?=$pack['id']?>" />
									<button type="submit" name="user_submit" class="btn btn-primary btn-sm"><i class="fa fa-shopping-cart fa-fw"></i> <?=$lang['b_199']?></button>
								</form>
							</div>
						<?php } ?>
                        
						<div class="clearfix"></div>
						<?php
							if(ceil($count/$bpp) > 1) {
								if($page == 0) {
									$left = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Previous</a></li>';
								}else{
									$left = '<li class="page-item"><a class="page-link" href="'.GenerateURL('buy&p='.($page-1)).'">Previous</a></li>';
								}
								
								$total_pages = (number_format(($count/$bpp), 0)-1);
								$middle = '<li class="page-item active"><a class="page-link" href="javascript:void(0)">'.($page+1).' - '.($total_pages+1).'</a></li>';

								if($page >= $total_pages) {
									$right = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Next</a></li>';
								}else{
									$right = '<li class="page-item"><a class="page-link" href="'.GenerateURL('buy&p='.($page+1)).'">Next</a></li>';
								}
								
								echo '<nav aria-label="navigation"><ul class="pagination justify-content-center">'.$left.$middle.$right.'</ul></nav>';
							}
						?>
						<div class="clearfix"></div>
                        
					</div>
                    
				</div>
				<?php } ?>
                <div align="Center" id="bannerswall_971"><script src="https://bannerswall.ru/bancode.php?id=971" async></script></div>
			</div>

            
		</div>
	  </div>
    </main>