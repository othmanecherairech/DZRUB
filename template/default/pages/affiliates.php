<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
	
	$refs = $db->QueryGetNumRows("SELECT id FROM `users` WHERE `ref`='".$data['id']."' AND `activate`='0'");
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
						<?=$lang['b_12']?>
					</div>
					<div class="content">
						<div id="aff-block" class="w-100">              
							<div class="title">Info</div>
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
								<b><?=$lang['b_113']?>:</b>
								<ul>
									<li style="margin-left:20px;"><?=lang_rep($lang['b_114'], array('-NUM-' => $config['ref_coins']))?></li><?if($config['paysys'] == 1){?>
									<li style="margin-left:20px;"><?=lang_rep($lang['b_115'], array('-SUM-' => $config['ref_cash']))?></li>
									<li style="margin-left:20px;"><?=lang_rep($lang['b_116'], array('-NUM-' => $config['ref_sale']))?></li><?}?>
								</ul>
								<?php if($config['aff_click_req'] > 0){ ?>
									<p style="font-size:12px"><b><?=lang_rep($lang['b_251'], array('-NUM-' => $config['aff_click_req']))?></b></p>
								<?php } ?>
							</table>
						</div>
						<div id="aff-block" class="w-100">              
							<div class="title"><?=$lang['b_82']?></div>
							<table width="100%" border="0" cellspacing="0" cellpadding="0">
								<tr>
									<td width="50%">
										<p class="aff_block_p"><?=$lang['b_119']?>:</p>
										<a class="aff_block_p2" href="<?=GenerateURL('referrals')?>"><?=$refs?></a>
									</td>
									<td width="50%">
										<p class="aff_block_p"><?=$lang['b_255']?>:</p>
										<a class="aff_block_p2" href="<?=GenerateURL('bank')?>"><font class="text-success"><?=get_currency_symbol($config[''])?></font><?=$data['account_balance']?></a>
									</td>
								</tr>
							</table>
							<div class="aff_content_bottom"><?=$lang['b_117']?></div>
							<center><input type="text" class="form-control w-75 mt-1" value="<?=$config['secure_url']?>/?ref=<?=$data['id']?>" onclick="this.select()" readonly="true" /></center>
							<?php if($config['paysys'] == 1 && $config['allow_withdraw'] == 1){ ?>
								<center><button onClick="location.href='<?=GenerateURL('bank&withdraw')?>'" class="btn btn-primary mt-2"><?=$lang['b_97']?></button></center>
							<?php } ?>
						</div>
						<div class="clearfix"></div>
						<div id="aff-banner" class="w-100">              
							<div class="title">Banner (468x60)</div><br> 
								<table width="100%" border="0" cellpadding="3" cellspacing="1">
									<tr>
										<td valign="top" align="center">
											<img src="<?=$config['secure_url']?>/promo/banner.png" class="img-fluid" border="0" />
										</td>
									</tr>
									<tr>    
										<td valign="top" align="center">
											<b><?=$lang['b_118']?></b><br>
											<textarea class="form-control w-75" onclick="this.select()" row="3" readonly="true"><a href="<?=$config['secure_url']?>/?ref=<?=$data['id']?>" target="_blank"><img src="<?=$config['secure_url']?>/promo/banner.png" alt="<?=$config['site_name']?>" border="0" /></a></textarea>
										</td>
									</tr>
									<tr>    
										<td valign="top" align="center">
											<b>BB Code</b><br>
											<textarea class="form-control w-75" onclick="this.select()" row="1" readonly="true">[url=<?=$config['secure_url']?>/?ref=<?=$data['id']?>][img]<?=$config['secure_url']?>/promo/banner.png[/img][/url]</textarea>                        
										</td>
									</tr>                   
								</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>