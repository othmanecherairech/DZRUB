<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$total_levels = $db->QueryGetNumRows("SELECT `id` FROM `levels`");
	$level_stats = userLevel($data['id'], 4);
?>
	<div class="col-md-3">
		<div class="my-3">
			<div id="sidebar-block" class="box-shadow rounded box-style"> 
				<div class="title"><img src="https://img.icons8.com/bubbles/50/000000/user.png"/><b><?=$data['login']?></b></div>
				<div class="inner">
					<a class="btn btn-success btn-sm w-100" href="<?=GenerateURL('addpage')?>"><i class="fa fa-plus"></i> <?=$lang['b_19']?></a>
					
					<a class="btn btn-primary btn-sm w-100 my-1" href="<?=GenerateURL('mypages')?>"><i class="fa fa-list"></i> <?=$lang['b_20']?></a>
					<div class="block">
						<div class="data">
							<div class="row">
								<div class="col-3"><i class="fa fa-check-circle fa-2x fa-fw text-warning"></i></div>
								<div class="col-9 no-space"><?=$lang['b_42']?> <div class="text-warning"><b id="c_coins"><?=number_format($data['coins']).' '.$lang['b_156']?></b></div></div>	           
							</div>
						</div>
						<div class="data">
							<div class="row">
								<div class="col-3"><i class="fa fa-ruble fa-2x fa-fw text-success"></i></div>
								<div class="col-9 no-space"><?=$lang['b_255']?> <div><b class="text-success"><?=(get_currency_symbol($config['']).' '.$data['account_balance'])?></b></div></div>	           
							</div>
						</div>
						<div class="data">
							<div class="row">
								<div class="col-3"><i class="fa fa-star fa-2x fa-fw text-info"></i></div>
								<div class="col-9 no-space"><?=$lang['b_192']?> <div><a href="<?=GenerateURL('membership')?>" class="text-info"><?=($data['premium'] > 0 ? '<b class="text-danger">'.$lang['b_194'].'</b>' : '<b>'.$lang['b_193'].'</b>')?></a></div></div>	           
							</div>
						</div>
					</div>

<div id="bancode_6690"><script src="//multibux.org/bancode.php?id=6690" async></script></div>


					<div class="level my-1">
						<div class="row">
							<div class="col-3 justify-content-center align-self-center">
								<a href="<?=GenerateURL('levels')?>"><img src="<?=$level_stats['image']?>" title="<?=$lang['b_350'].' '.$level_stats['level']?>" border="0" /></a>
							</div>
							<div class="col-9 mt-2 no-space">
								<b><?=$lang['b_350'].' '.$level_stats['level']?></b> / <?=($lang['b_350'].' '.$total_levels)?>
								<p class="mt-2"><?php echo $lang['b_09']; ?>: <b><?=$level_stats['reward']?> coins</b></p>
							</div>
						</div>
						<div class="progress position-relative">
							<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?=$level_stats['progress']?>%" aria-valuenow="<?=$level_stats['progress']?>" aria-valuemin="0" aria-valuemax="100"></div>
							<small class="justify-content-center d-flex position-absolute w-100"><?=lang_rep($lang['b_419'], array('-REMAIN-' => $level_stats['remaining'], '-LEVEL-' => $level_stats['next_level']))?></small>

						</div>
					</div>

<div id="bancode_6691"><script src="//multibux.org/bancode.php?id=6691" async></script></div>

					<div class="btn-group btn-group-sm d-flex justify-content-center">
					  <a class="btn btn-success w-100" href="<?=GenerateURL('bank')?>"><?=$lang['b_256']?></a>
					  <a class="btn btn-primary w-100" href="<?=GenerateURL('sellcoins')?>"><?=$lang['b_375']?></a>
					</div>
					<div class="dropdown d-flex mt-1">
					  <button class="btn btn-sm btn-info mb-1 w-100" type="button" id="OffersMenu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
						<i class="fa fa-list-alt fa-fw"></i> <?=$lang['b_411']?>
					  </button>
					  <div class="dropdown-menu" aria-labelledby="OffersMenu">
						<a class="dropdown-item" href="<?=GenerateURL('offers&md=bitswall')?>">BitsWall</a>
						<a class="dropdown-item" href="<?=GenerateURL('offers&md=wannads')?>">Wannads</a>
						<a class="dropdown-item" href="<?=GenerateURL('offers&md=jungle')?>">JungleSurvey</a>
						<a class="dropdown-item" href="<?=GenerateURL('offers&md=adworkmedia')?>">AdWorkMedia</a>
						<a class="dropdown-item" href="<?=GenerateURL('offers&md=kiwiwall')?>">KiwiWall</a>
					  </div>
					</div>
					<?php if(!empty($config['cm_id']) && !empty($config['cm_key']) && !empty($config['cm_domain']) && !empty($config['cm_token'])){ ?>
						<button type="button" class="btn btn-danger btn-sm w-100" onClick="window.open('<?=$config['site_url']?>/mining.php');"><i class="fa fa-cog fa-spin fa-fw"></i> <?=$lang['b_414']?></button>
					<?php } ?>
				</div>
			</div>
		</div>
		<div class="my-3">
			<div id="sidebar-block" class="box-shadow rounded box-style"> 
				<div class="title"><i class="fa fa-check-circle fa-lg"></i> <?=$lang['b_22']?></div>
				<div class="inner">
					<?=hook_filter('sidebar_exchange_menu', '')?>
				</div>
<div id="bancode_6692"><script src="//multibux.org/bancode.php?id=6692" async></script></div>

			</div>
		</div>
	</div>