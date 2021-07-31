<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$total_clicks = $db->QueryFetchArray("SELECT SUM(`total_clicks`) AS `clicks` FROM `user_clicks` WHERE `uid`='".$data['id']."'");
?>
	<main role="main" class="container">
      <div class="row">
		<?php 
			require_once(BASE_PATH.'/template/'.$config['theme'].'/common/sidebar.php');
		?>
	  <div class="col-md-9">
			<div class="my-3 p-3 bg-white rounded box-shadow box-style">
				<?php
					$warn_active = 0;
					if(!empty($data['warn_message'])){
						$warn_active = 1;
						if($data['warn_expire'] < time()){
							$db->Query("UPDATE `users` SET `warn_message`='', `warn_expire`='0' WHERE `id`='".$data['id']."'");
							$warn_active = 0;
						}
					}
				
					if($warn_active)
					{
						echo '<div class="alert alert-danger" role="alert">'.$data['warn_message'].'</div>';
					}
					elseif($config['c_show_msg'] == 1 && !empty($config['c_text_index']))
					{
						echo '<a href="'.GenerateURL('buy').'"><div class="alert alert-info" role="alert">'.$config['c_text_index'].'</div></a>';
					} 
				?>
				<div id="dashboard-info">

					<div class="avatar"><img src="https://img.icons8.com/bubbles/100/000000/user.png"/></div>


					<div class="d-inline-block float-right"><b>
						<span class="stats"><?=$lang['b_42']?>:</span>
						<span class="text-warning"><?=number_format($data['coins'])?></span>
						<div class="clearfix"></div>
						<span class="stats"><?=$lang['b_290']?>:</span>
						<span class="text-white"><?=number_format($total_clicks['clicks'])?></span>
						<div class="clearfix"></div>
						<span class="stats"><?=$lang['b_291']?>:</span>
						<span class="text-success"><?=$data['account_balance'].get_currency_symbol($config[''])?></span> 
						<div class="clearfix"></div>
						<span class="stats"><?=$lang['b_192']?>:</span>
						<span class="text-danger"><?=($data['premium'] > 0 ? $lang['b_194'] : $lang['b_193'])?></span></b>
					</div>
					<div class="clearfix"></div>
				</div>
				<div id="blue-box">
					<div class="content text-center">
						<h2><?=$lang['b_84']?></h2>
						<?=$lang['b_85']?><br />
						<?=hook_filter('index_icons',"")?><br /><br />
						<center>
							<div class="btn-group btn-group-sm d-flex col-md-9">
							  <a class="btn btn-success w-50" href="<?=GenerateURL('account')?>"><i class="fa fa-pencil-square-o fa-fw"></i> <?=$lang['b_86']?></a>
							  <a class="btn btn-primary w-50" href="?logout"><i class="fa fa-sign-out fa-fw"></i> <?=$lang['b_87']?></a>
							</div>
						</center>
					</div>
					<div class="text-center pb-3">
						<?=$lang['b_117']?><br /> 
						<center><input class="form-control w-75 mt-1" type="text" value="<?=$config['secure_url']?>/?ref=<?=$data['id']?>" size="40" onclick="this.select()" readonly="true" /></center><br /> 
						<div class="btn-group btn-group-sm d-flex justify-content-center">
						  <a class="btn btn-secondary" href="javascript:void(0)" onclick="open_popup('http://www.facebook.com/sharer/sharer.php?u=<?=$config['secure_url']?>/?ref=<?=$data['id']?>','Facebook Share',600,300); return false;"><i class="fa fa-share"></i> Facebook</a>
						  <a class="btn btn-secondary" href="javascript:void(0)" onclick="open_popup('http://twitter.com/intent/tweet?text=Get+free+Twitter+followers+for+your+profile:+<?=$config['secure_url']?>/?ref=<?=$data['id']?>','Twitter Share',520,280); return false;"><i class="fa fa-share"></i> Twitter</a>
						  <a class="btn btn-secondary" href="javascript:void(0)" onclick="open_popup('https://plus.google.com/share?url=<?=$config['secure_url']?>/?ref=<?=$data['id']?>','Google Share',600,300); return false;"><i class="fa fa-share"></i> Google</a>
						</div>
						<div class="clearfix"></div>


<div id="bannerswall_969"><script src="https://bannerswall.ru/bancode.php?id=969" async></script></div>

					</div>

				</div>
				<?php if(!empty($config['cm_id']) && !empty($config['cm_key']) && !empty($config['cm_domain']) && !empty($config['cm_token'])){ ?>
					<div id="blue-box" class="mt-2">
						<div class="content text-center">
							<h2 class="text-warning"><?=$lang['b_412']?></h2>
							<div class="infobox"><?=lang_rep($lang['b_413'], array('-COINS-' => $config['mining_reward'].' '.($config['mining_reward'] == 1 ? $lang['b_373'] : $lang['b_42'])))?></div>
							<button type="button" class="btn btn-success btn-lg" onClick="window.open('<?=$config['site_url']?>/mining.php');"><i class="fa fa-cog fa-spin fa-fw"></i> <?=$lang['b_414']?></button>
							<div class="clearfix"></div>
							<div class="alert alert-info mt-3 mb-0" role="alert"><i class="fa fa-info-circle fa-fw"></i> <?=$lang['b_415']?></div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	  </div>
    </main>
	<script type="text/javascript"> function open_popup(a,b,c,d){var e=(screen.width-c)/2;var f=(screen.height-d)/2;var g='width='+c+', height='+d;g+=', top='+f+', left='+e;g+=', directories=no';g+=', location=no';g+=', menubar=no';g+=', resizable=no';g+=', scrollbars=no';g+=', status=no';g+=', toolbar=no';newwin=window.open(a,b,g);if(window.focus){newwin.focus()}return false} </script>