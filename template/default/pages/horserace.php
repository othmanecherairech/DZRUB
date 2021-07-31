<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
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
						<?=$lang['hs_01']?>
					</div>
					<div class="content">
						<?php
							if(isset($_GET['history'])){
								$last_race = $db->QueryFetchArray("SELECT * FROM `hs_rounds` WHERE `active`='0' ORDER BY `end_time` DESC LIMIT 1");
						?>
						<div class="infobox">
							<center><object id="flashcontent" data="template/<?=$config['theme']?>/static/images/horserace/horserace.swf" type="application/x-shockwave-flash" height="300" width="540"><param value="transparent" name="wmode"><param value="horses=Lucky strike,Flash,Blitz,Runner,Thunder&winners=<?=$last_race['horses']?>&race_datum=<?=date('d M Y', $last_race['end_time'])?>&race_tijd=<?=date('H:i', $last_race['end_time'])?>&text_herhaling=<?php echo $lang['hs_02']; ?>" name="flashvars"></object></center>
						</div>
						<table class="table table-striped table-sm table-responsive-sm">
							<?php
								function get_horse($id){
									global $db;
									$horse_name = $db->QueryFetchArray("SELECT horse FROM `hs_horses` WHERE `id`='".$id."'");
									return $horse_name['horse'];
								}

								$races = $db->QueryFetchArrayAll("SELECT id,horses,end_time FROM `hs_rounds` WHERE `active`='0' ORDER BY `end_time` DESC LIMIT 5");
								foreach($races as $race){
									$horse_id = explode(',', $race['horses']);
							?>
								<thead class="thead-dark">
									<tr><th colspan="5"><?php echo $lang['hs_03']; ?> #<?=$race['id']?> - <?=date('H:i', $race['end_time'])?></th></tr>
								</thead>
								<tbody class="table-primary text-dark">	
									<tr class="text-center">
										<td><b>#1 - <?=get_horse($horse_id[0])?></b></td>	
										<td><b>#2 - <?=get_horse($horse_id[1])?></b></td>	
										<td><b>#3 - <?=get_horse($horse_id[2])?></b></td>
										<td><b>#4 - <?=get_horse($horse_id[3])?></b></td>
										<td><b>#5 - <?=get_horse($horse_id[4])?></b></td>
									</tr>
									<tr class="text-center">
										<td><img src="template/<?=$config['theme']?>/static/images/horserace/horses/horse_<?=$horse_id[0]?>.png" width="90"></td>	
										<td><img src="template/<?=$config['theme']?>/static/images/horserace/horses/horse_<?=$horse_id[1]?>.png" width="90"></td>	
										<td><img src="template/<?=$config['theme']?>/static/images/horserace/horses/horse_<?=$horse_id[2]?>.png" width="90"></td>
										<td><img src="template/<?=$config['theme']?>/static/images/horserace/horses/horse_<?=$horse_id[3]?>.png" width="90"></td>
										<td><img src="template/<?=$config['theme']?>/static/images/horserace/horses/horse_<?=$horse_id[4]?>.png" width="90"></td>
									</tr>
									<tr><td style="background:transparent" colspan="5">&nbsp;</td></tr>
								</tbody>
							<?php }?>
						</table>
						<?php
							}else{
								$hs_round = $db->QueryFetchArray("SELECT * FROM `hs_rounds` WHERE `active`='1' ORDER BY started DESC LIMIT 1");
								$timeleft = $hs_round['end_time'] - time();

								$last_buy = unserialize($hs_round['buy_timestamps']);

								$errMessage = '';
								if(isset($_POST['submit']) ){
									$tickets = 0;
									foreach($_POST['horse'] as $horse => $key){
										$tickets = $tickets + NumbersOnly($_POST['bet'][$key], 0);
									}
									
									$money = ($tickets * $config['hs_ticket_price']);
									if($tickets == 0){
										$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['hs_04'].'</div>';
									}elseif($money > $data['coins']){
										$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['hs_05'], array('-TICKETS-' => $tickets, '-PRICE-' => $config['hs_ticket_price'])).'</div>';
									}elseif($tickets > $config['hs_max_tickets']){
										$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['hs_06'], array('-LIMIT-' => number_format($config['hs_max_tickets']))).'</div>';
									}else{
										foreach($_POST['horse'] as $horse => $key){
											if(!empty($_POST['bet'][$key])){
												$key = $db->EscapeString($key);
												$ticket = $db->EscapeString(NumbersOnly($_POST['bet'][$key], 0));
												$horse = $db->QueryFetchArray("SELECT * FROM `hs_horses` WHERE `id`='".$key."'");

												$players = unserialize($horse['players']);
												$players[$data['id']] = array(
													'tickets' => ($ticket + (empty($players[$data['id']]['tickets']) ? 0 : $players[$data['id']]['tickets'])),
													'money' => (($ticket * $config['hs_ticket_price']) + (empty($players[$data['id']]['money']) ? 0 : $players[$data['id']]['money']))
												);

												$db->Query("UPDATE `hs_horses` SET `total_tickets`=`total_tickets`+'".$ticket."', `players`='".serialize($players)."' WHERE `id`='".$key."'");
											}
										}
										
										$last_buy[$data['id']] = time();
										$db->Query("UPDATE `hs_rounds` SET `buy_timestamps`='".serialize($last_buy)."' WHERE `id`='".$hs_round['id']."'");
										$db->Query("UPDATE `users` SET `coins`=`coins`-'".$money."' WHERE `id`='".$data['id']."'");

										$errMessage = '<div class="alert alert-success" role="alert">'.lang_rep($lang['hs_07'], array('-TICKETS-' => $tickets, '-PRICE-' => number_format($money))).'</div>';
									}
								}
								
								echo $errMessage;
							?>
							<div class="infobox">
								<p><?php echo lang_rep($lang['hs_08'], array('-PRICE-' => number_format($config['hs_ticket_price']))); ?></p>
								<p><?php echo lang_rep($lang['hs_09'], array('-TIME-' => date('d M Y - H:00', (time()+3600)))); ?></p>
							</div>
						<?php
							if($hs_round['id'] != ''){
						?>
							<form method="POST">
								<table class="table table-striped table-sm table-responsive-sm">
									<?php
										$total_tickets = $db->QueryFetchArray("SELECT SUM(`total_tickets`) AS `tickets` FROM `hs_horses`");
										$horses = $db->QueryFetchArrayAll("SELECT * FROM `hs_horses`");
										foreach($horses as $horse){
											
											$owned_tickets = 0;
											$players = unserialize($horse['players']);
											if(!empty($players[$data['id']]['tickets']))
											{
												$owned_tickets = $players[$data['id']]['tickets'];
											}
									?>
										<thead class="thead-dark">
											<tr><th colspan="8"><b><?=$horse['horse']?></b></th></tr>
										</thead>
										<tbody class="table-primary text-dark">	
											<tr>
												<td rowspan="3"><img src="template/<?=$config['theme']?>/static/images/horserace/horses/horse_<?=$horse['id']?>.png" class="img-fluid"></td>
												<td><img src="template/<?=$config['theme']?>/static/images/horserace/chart_curve.png"></td>
												<td><?php echo $lang['hs_10']; ?></td>
												<td><?=$horse['speed']?>%</td>
												<td><img src="template/<?=$config['theme']?>/static/images/horserace/chart_bar.png"></td>
												<td><?php echo $lang['hs_11']; ?></td>
												<td colspan="2">
													<b><?=$horse['winchance']?>%</b>
												</td>
											</tr>
											<tr>
												<td><img src="template/<?=$config['theme']?>/static/images/horserace/stats_health.png"></td>
												<td><?php echo $lang['hs_12']; ?></td>
												<td><?=$horse['condition']?>%</td>
												<td><img src="template/<?=$config['theme']?>/static/images/horserace/coins_add.png"></td>
												<td><?php echo $lang['hs_13']; ?></td>
												<td colspan="2"><?=$horse['payment']?> x <?php echo $lang['hs_14']; ?></td>
											</tr>
											<tr>
												<td><img src="template/<?=$config['theme']?>/static/images/horserace/cog.png"></td>
												<td><?php echo $lang['hs_15']; ?></td>
												<td><?=($total_tickets['tickets'] > 0 ? percent($horse['total_tickets'], $total_tickets['tickets']) : 0)?>%</td>
												<td><img src="template/<?=$config['theme']?>/static/images/horserace/tag_blue.png"></td>
												<td><?php echo $lang['hs_16']; ?></td>
												<td colspan="2"><?=number_format($owned_tickets)?></td>
											</tr>
											<tr>
												<td colspan="4"> </td>
												<td><img src="template/<?=$config['theme']?>/static/images/horserace/tag_blue_add.png"></td>
												<td><?php echo $lang['hs_17']; ?></td>
												<td>
													<input name="horse[<?=$horse['id']?>]" value="<?=$horse['id']?>" type="hidden">
													<input name="bet[<?=$horse['id']?>]" size="2" type="text" class="form-control form-control-sm" placeholder="<?=$config['hs_max_tickets']?>">
												</td>
												<td>
													<input value="<?php echo $lang['hs_18']; ?>" name="submit" class="btn btn-primary btn-sm" type="submit">
												</td>
											</tr>
											<tr>
												<td colspan="8" style="background:transparent">&nbsp;</td>
											</tr>
										</tbody>
									<?php } ?>
									</table>
								</form>
							<?php }} ?>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>