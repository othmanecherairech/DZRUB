<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$rewards = $db->QueryFetchArrayAll("SELECT * FROM `activity_rewards` ORDER BY `exchanges` ASC");
	$myLevel = userLevel($data['id'], 0);
	$myBonus = ($data['premium'] > 0 ? $myLevel['vip_bonus'] : $myLevel['free_bonus']);

	if(!$is_online || empty($myBonus) && empty($rewards)){
		redirect('index.php');
	}

	function r_time($seconds) {
		$measures = array(
			'day'=>24*60*60,
			'hour'=>60*60,
			'minute'=>60,
			'second'=>1,
		);
		foreach ($measures as $label=>$amount) {
			if ($seconds >= $amount) {  
				$howMany = floor($seconds / $amount);
				return $howMany." ".$label.($howMany > 1 ? "s" : "");
			}
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
						<?=$lang['b_09']?>
					</div>
					<div class="content">
						<h2 class="text-center"><?=lang_rep($lang['b_40'], array('-NUM-' => $myBonus))?></h2>
						<?php
							if(($data['daily_bonus']+86400) < time()){
								$cf_bonus = $db->QueryFetchArray("SELECT SUM(`today_clicks`) AS `clicks` FROM `user_clicks` WHERE `uid`='".$data['id']."'");
								$cf_bonus = ($cf_bonus['clicks'] > 0 ? $cf_bonus['clicks'] : 0);
						?>
							<script type="text/javascript">
								var msg1 = '<?=$db->EscapeString(lang_rep($lang['b_38'], array('-NUM-' => ($data['premium'] > 0 ? $myLevel['vip_bonus'] : $myLevel['free_bonus']))), 0)?>';
								var msg2 = '<?=$db->EscapeString($lang['b_39'], 0)?>';
								function checkBonus(){$("#bonus").hide();$("#txtHint").html('<center><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></center>');$.ajax({type:"GET",url:"system/ajax.php?a=dailyBonus",cache:false,success:function(a){if(a==1){$("#txtHint").html('<div class="alert alert-success" role="alert">'+msg1+'</div>')}else{$("#txtHint").html('<div class="alert alert-danger" role="alert">'+msg2+'</div>')}}})}
							</script>
							<div id="txtHint"></div>
						<?php if($cf_bonus < $config['crf_bonus']){ ?>
							<div class="alert alert-danger" role="alert"><?=lang_rep($lang['b_225'], array('-NUM-' => $config['crf_bonus'], '-REM-' => ($config['crf_bonus'] - $cf_bonus)))?></div>
							<div class="progress">
							  <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="<?=percent($cf_bonus, $config['crf_bonus'])?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=percent($cf_bonus, $config['crf_bonus'])?>%"></div>
							</div>
						<?php }else{ ?>
							<center><input type="button" id="bonus" class="btn btn-primary" onclick="checkBonus()" value="<?=$lang['b_166']?>" /></center>
						<?php
								}
							}else{
						?>
							<div class="alert alert-danger" role="alert"><?=lang_rep($lang['b_41'], array('-TIME-' => r_time(($data['daily_bonus']+86400)-time())))?></div>
						<?php } ?>
					</div>
				</div>
				<?php
					if($rewards){
						$total_clicks = $db->QueryFetchArray("SELECT SUM(`total_clicks`) AS `clicks` FROM `user_clicks` WHERE `uid`='".$data['id']."'");
						$total_clicks = $total_clicks['clicks'];
				?>
				<script type="text/javascript">
					function getReward(rID) {
						$("#claim_"+rID).html('<center><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></center>');
						$("#rewardMSG").html('<center><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></center>');
						$.getJSON('system/ajax.php?a=getReward&rID='+rID, function (c) {
							if(c['type'] == 'success'){
								$("#claim_"+rID).html('<span class="btn btn-primary btn-sm disabled"><?=$lang['b_331']?></span>');
								$("#rewardMSG").html('<div class="alert alert-success" role="alert">' + c['message'] + '</div>')
							} else {
								$("#claim_"+rID).html('<a href="javascript:void(0)" onclick="getReward('+rID+')" class="btn btn-primary btn-sm"><?=$lang['b_329']?></a>');
								$("#rewardMSG").html('<div class="alert alert-danger" role="alert">' + c['message'] + '</div>')
							}
						});
					}
				</script>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_326']?>
					</div>
					<div class="content">	
						<div id="rewardMSG">
							<div class="alert alert-info" role="alert"><?=lang_rep($lang['b_336'], array('-NUM-' => $total_clicks))?></div>
						</div>
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th>#</th>
									<th><?=$lang['b_328']?></th>
									<th><?=$lang['b_327']?></th>
									<th><?=$lang['b_329']?></th>
								</tr>
							</thead>
							<tbody class="table-primary text-dark">
							<?php
								foreach($rewards as $reward){
									$claimed = $db->QueryGetNumRows("SELECT * FROM `activity_rewards_claims` WHERE `reward_id`='".$reward['id']."' AND `user_id`='".$data['id']."' LIMIT 1");
							?>
								<tr><td><?=$reward['id']?></td><td style="text-align:left"><?=lang_rep($lang['b_330'], array('-NUM-' => number_format($reward['exchanges'])))?></b></td><td class="text-success"><b><?=number_format($reward['reward']).' '.($reward['type'] == 1 ? $lang['b_246'] : $lang['b_156'])?></b></td><td id="claim_<?=$reward['id']?>" class="text-center"><?=($claimed > 0 ? '<span class="btn btn-primary btn-sm disabled">'.$lang['b_331'].'</span>' : ($total_clicks >= $reward['exchanges'] ? '<a href="javascript:void(0)" onclick="getReward('.$reward['id'].')" class="btn btn-primary btn-sm">'.$lang['b_329'].'</a>' : '<span class="btn btn-primary btn-sm disabled">'.$lang['b_332'].'</span>'))?></td></tr>
							<?php } ?>
							</tbody>
							<tfoot class="thead-dark">
								<tr>
									<th>#</th>
									<th><?=$lang['b_328']?></th>
									<th><?=$lang['b_327']?></th>
									<th><?=$lang['b_329']?></th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
				<?php } ?>
			</div>
            <div align="Center" id="bannerswall_975"><script src="https://bannerswall.ru/bancode.php?id=975" async></script></div>
		</div>
	  </div>
    </main>