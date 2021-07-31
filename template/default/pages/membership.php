<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$biggestBonus = $db->QueryFetchArray("SELECT free_bonus, vip_bonus FROM `levels` ORDER BY `vip_bonus` DESC LIMIT 1");
	
	$errMessage = '';
	if(isset($_POST['vip_coins']) && $config['vip_purchase'] != 1){
		$pid = $db->EscapeString($_POST['cpid']);
		$vc_pack = $db->QueryFetchArray("SELECT * FROM `p_pack` WHERE `id`='".$pid."' AND `type`='1'");
		if(!empty($vc_pack['id'])){
			if($data['coins'] >= $vc_pack['coins_price']){
				$premium = ($data['premium'] == 0 ? (time()+(86400*$vc_pack['days'])) : ((86400*$vc_pack['days'])+$data['premium']));
				$db->Query("UPDATE `users` SET `coins`=`coins`-'".$vc_pack['coins_price']."', `premium`='".$premium."' WHERE `id`='".$data['id']."'");
				$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_241'].'</div>';
			}else{
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_146'].'</div>';
			}
		}
	}
	
	if(isset($_POST['submit']) && isset($_POST['pid']) && $config['vip_purchase'] != 1){
		$pid = $db->EscapeString($_POST['pid']);
		$pack = $db->QueryFetchArray("SELECT id,days,price FROM `p_pack` WHERE `id`='".$pid."'");
		if(empty($pack['id'])){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_262'].'</div>';
		}elseif($data['account_balance'] < $pack['price']){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_263'].' <a href="'.GenerateURL('bank').'"><b>'.$lang['b_256'].'...</b></a></div>';
		}else{
			$premium = ($data['premium'] == 0 ? (time()+(86400*$pack['days'])) : ((86400*$pack['days'])+$data['premium']));
			$db->Query("UPDATE `users` SET `account_balance`=`account_balance`-'".$pack['price']."', `premium`='".$premium."' WHERE `id`='".$data['id']."'");
			$db->Query("INSERT INTO `user_transactions` (`user_id`,`type`,`value`,`cash`,`date`)VALUES('".$data['id']."','1','".$pack['days']."','".$pack['price']."','".time()."')");

			if($config['paysys'] == 1 && $data['ref'] > 0 && $data['ref_paid'] == 1 && $pack['price'] > 0){
				$commission = number_format(($pack['price']/100) * $config['ref_sale'], 2);
				affiliate_commission($data['ref'], $data['id'], $commission, 'vip_purchase');
			}
			
			$errMessage = '<div class="alert alert-success" role="alert">'.lang_rep($lang['b_264'], array('-NUM-' => $pack['days'].' '.$lang['b_246'])).'</div>';
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
				<div id="blue-box">
					<div class="title">
						<?=$lang['b_08']?>
					</div>
					<div class="content">
						<div class="membership-block text-center w-100"><?=$lang['b_192']?>: <b><?=($data['premium'] > 0 ? $lang['b_194'].(empty($data['premium']) ? '' : ' <small>('.date('d M Y - H:i', $data['premium']).')</small>') : $lang['b_193'])?></b></div>
						<div class="clearfix"></div>
						<?=$errMessage?>
						<div class="row">
							<div class="table-responsive">
								<table class="table table-striped table-hover">
								  <thead class="thead-dark">
									<tr>
										<th class="w-50"><?=$lang['b_192']?></th>
										<th class="text-center"><?=$lang['b_193']?></th>
										<th class="text-center"><?=$lang['b_194']?></th>
									</tr>
								  </thead>
								  <tbody class="table-secondary text-dark">
									<tr>
									  <td class="membership-option"><?=$lang['b_195']?></td>
									  <td class="text-center"><strong><?=$config['free_cpc']?></strong> <?=$lang['b_36']?></td>
									  <td class="text-center"><strong><?=$config['premium_cpc']?></strong> <?=$lang['b_36']?></td>
									</tr>
									<tr>
									  <td class="membership-option"><?=$lang['b_196']?></td>
									  <td class="text-center"><?=$lang['b_355']?> <strong><?=$biggestBonus['free_bonus']?></strong> <?=$lang['b_156']?></td>
									  <td class="text-center"><?=$lang['b_355']?> <strong><?=$biggestBonus['vip_bonus']?></strong> <?=$lang['b_156']?></td>
									</tr>
									<tr>
									  <td class="membership-option"><?=$lang['b_223']?></td>
									  <td class="text-center"><strong><?=$config['c_c_limit']?></strong> <?=$lang['b_224']?></td>
									  <td class="text-center"><strong><?=$config['c_v_limit']?></strong> <?=$lang['b_224']?></td>
									</tr>
									<?php if($config['vip_purchase'] == 1 && $config['vip_monthly_coins'] > 0){ ?>
									<tr>
									  <td class="membership-option"><?=$lang['b_368']?></td>
									  <td class="text-center"><strong>0</strong> <?=$lang['b_156']?></td>
									  <td class="text-center"><strong><?=number_format($config['vip_monthly_coins'])?></strong> <?=$lang['b_156']?></td>
									</tr>
									<?php } ?>
									<tr>
									  <td class="membership-option"><?=$lang['b_395']?></td>
									  <td class="text-center"><?=('<strong>'.$config['req_clicks'].'</strong> '.$lang['b_396'])?></td>
									  <td class="text-center"><?=$lang['b_397']?></td>
									</tr>
									<tr>
									  <td class="membership-option"><?=$lang['b_361']?></td>
									  <td class="text-center"><?=($config['surf_type'] == 1 ? 'Manual Surf <small>('.$lang['b_362'].')</small>' : ($config['surf_type'] == 2 ? 'Popup Surf <small>('.$lang['b_363'].')</small>' : 'Auto Surf <small>('.$lang['b_363'].')</small>'))?></td>
									  <td class="text-center"><?=($config['vip_surf_type'] == 1 ? 'Manual Surf <small>('.$lang['b_362'].')</small>' : ($config['vip_surf_type'] == 2 ? 'Popup Surf <small>('.$lang['b_363'].')</small>' : 'Auto Surf <small>('.$lang['b_363'].')</small>'))?></td>
									</tr>
									<tr>
									  <td class="membership-option"><?=$lang['b_391']?></td>
									  <td class="text-center"><strong><?=$config['free_sc_limit']?></strong> <?=$lang['b_392']?></td>
									  <td class="text-center"><strong><?=$config['vip_sc_limit']?></strong> <?=$lang['b_392']?></td>
									</tr>
									<?php if($config['clicks_limit'] > 0){ ?>
									<tr>
										<td class="membership-option"><?=$lang['b_298']?></td>
										<td class="text-center"><strong><?=number_format($config['clicks_limit'])?></strong> <?=$lang['b_141']?></li></td>
										<td class="text-center"><strong><?=$lang['b_299']?></strong></td>
									</tr>
									<?php } ?>
									<?php if($config['banner_system'] != 0){ ?>
									<tr>
										<td class="membership-option"><?=$lang['b_233']?></td>
										<td class="text-center"><?=($config['banner_system'] == 2 ? '-' : '<i class="fa fa-check"></i>')?></td>
										<td class="text-center"><i class="fa fa-check"></i></td>
									</tr>
									<?php } ?>
									<?php if($config['target_system'] != 2){ ?>
									<tr>
										<td class="membership-option"><?=$lang['b_242']?></td>
										<td class="text-center"><?=($config['target_system'] == 1 ? '-' : '<i class="fa fa-check"></i>')?></td>
										<td class="text-center"><i class="fa fa-check"></i></td>
									</tr>
									<?php } ?>
									<?php if($config['transfer_status'] != 1){ ?>
									<tr>
										<td class="membership-option"><?=$lang['b_232']?></td>
										<td class="text-center"><?=($config['transfer_status'] == 2 ? '-' : '<i class="fa fa-check"></i>')?></td>
										<td class="text-center"><i class="fa fa-check"></i></td>
									</tr>
									<?php } ?>
									<tr>
										<td class="membership-option"><?=$lang['b_197']?></td>
										<td class="text-center">-</td>
										<td class="text-center"><i class="fa fa-check"></i></td>
									</tr>
									<tr>
										<td class="membership-option"><?=$lang['b_293']?></td>
										<td class="text-center">-</td>
										<td class="text-center"><i class="fa fa-check"></i></td>
									</tr>
									<tr>
										<td class="membership-option"><?=$lang['b_325']?></td>
										<td class="text-center">-</td>
										<td class="text-center"><i class="fa fa-check"></i></td>
									</tr>
									<tr>
										<td class="membership-option"><?=($config['vip_purchase'] == 1 ? $lang['b_367'] : $lang['b_198'])?></td>
										<td class="text-center"><?=($config['vip_purchase'] == 1 ? '<strong>'.get_currency_symbol($config['']).'0.00</strong>' : '-')?></td>
										<td class="text-center"><?=($config['vip_purchase'] == 1 ? '<li><strong>'.get_currency_symbol($config['']).$config['vip_subscription_price'].'</strong></li>' : '<i class="fa fa-check"></i>')?></td>
									</tr>
								  </tbody>
								  <tfoot class="thead-dark">
									<tr>
										<th class="w-25"></th>
										<th></th>
										<th class="text-center">
											<?php if($config['vip_purchase'] == 1) { ?>
												<div class="last_child">
													<div class="pay_box">
														<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
															<input type="hidden" name="cmd" value="_xclick-subscriptions">
															<input type="hidden" name="business" value="<?=$config['paypal']?>">
															<input type="hidden" name="item_name" value="VIP Membership">
															<input type="hidden" name="no_note" value="1">
															<input type="hidden" name="no_shipping" value="1">
															<input type="hidden" name="rm" value="1">
															<input type="hidden" name="return" value="<?=GenerateURL('membership&success', true)?>">
															<input type="hidden" name="cancel_return" value="<?=GenerateURL('membership&cancel', true)?>">
															<input type="hidden" name="src" value="1">
															<input type="hidden" name="a3" value="<?=$config['vip_subscription_price']?>">
															<input type="hidden" name="p3" value="1">
															<input type="hidden" name="t3" value="M">
															<input type="hidden" name="" value="<?=($config[''] == '' ? 'USD' : $config[''])?>">
															<input type="hidden" name="bn" value="PP-SubscriptionsBF:btn_subscribeCC_LG.gif:NonHosted">
															<input type="hidden" name="custom" value="<?=($data['id'].'|'.VisitorIP())?>">
															<input type="hidden" name="notify_url" value="<?=$config['site_url']?>/system/payments/paypal/vip_ipn.php">
															<input type="submit" name="submit" value="<?=$lang['b_199']?>" class="btn btn-primary" />
														</form>
													</div>
												</div>
											<?php
												} else {
											?>
													<form method="POST">
														<select name="pid" class="custom-select custom-select-sm mb-2">
															<?php
																$cp_check = $db->QueryFetchArray("SELECT COUNT(*) AS `total` FROM `p_pack` WHERE `type`='1'");
																$packs = $db->QueryFetchArrayAll("SELECT id,days,price FROM `p_pack` WHERE `type`='0' ORDER BY `price` ASC");
																foreach($packs as $pack){
																	echo '<option value="'.$pack['id'].'">'.$pack['days'].' '.$lang['b_158'].' - '.(empty($config['']) ? get_currency_symbol('RUB  ') : get_currency_symbol($config[''])).$pack['price'].'</option>';
																}
															?>
														</select>
														<input type="submit" name="submit" value="<?=$lang['b_199']?>" class="btn btn-primary mb-3" />
													</form>
												<?php if($cp_check['total'] > 0){ ?>
												<b><?=$lang['b_240']?></b>
												<form method="POST">
													<select name="cpid" id="cpid" class="custom-select custom-select-sm mt-3 mb-2">
														<?php
															$packs = $db->QueryFetchArrayAll("SELECT id,days,coins_price FROM `p_pack` WHERE `type`='1' ORDER BY `coins_price` ASC");
															foreach($packs as $pack){
																echo '<option value="'.$pack['id'].'">'.$pack['days'].' '.$lang['b_158'].' - '.$pack['coins_price'].' coins</option>';
															}
														?>
													</select><br />
													<input type="submit" name="vip_coins" value="<?=$lang['b_199']?>" class="btn btn-primary" />
												</form>
											<?php }} ?>
                                            
										</th>
									</tr>
								  </tfoot>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
            <div align="Center" id="bannerswall_972"><script src="https://bannerswall.ru/bancode.php?id=972" async></script></div>
		</div>
        
	  </div>
    </main>