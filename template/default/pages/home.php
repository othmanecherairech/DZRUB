<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
?>
	<script type="text/javascript"> 
		$(document).ready(function(){$.ajaxSetup({cache:false});setInterval(function(){if(document.hasFocus()){$.getJSON('system/ajax.php?a=getSideStats',function(c){$('#user_count').fadeOut().html(c['members']).fadeIn();$('#exchange_count').fadeOut().html(c['exchanges']).fadeIn();$('#payout_count').fadeOut().html(c['payouts']).fadeIn()})}},6000)});
	</script>
    <main role="main" class="container">
      <div class="row">
		<div class="col-12">
			<div class="my-3 p-3 bg-white rounded box-shadow box-style">
				<div id="home-box">
					<div class="content">
						<?=$lang['index_desc']?>
						<p class="mt-1 text-center"><img src="template/<?=$config['theme']?>/static/images/social-intro.png" class="img-fluid" /></p>
						<div class="clearfix"></div>
					</div>
				</div>

				<div id="home-statistics">
					<?php
							$sUsers = $db->QueryFetchArray("SELECT COUNT(*) AS total FROM `users`");
							$sCash = $db->QueryFetchArray("SELECT SUM(`amount`) AS `total` FROM `requests` WHERE `paid`='1'");
							$sClick = $db->QueryFetchArray("SELECT SUM(`value`) AS `total` FROM `web_stats`");
							echo lang_rep($lang['b_366'], array('-USERS-' => '<i class="fa fa-users"></i> <b id="user_count">'.number_format($sUsers['total']).'</b>', '-EXCHANGES-' => '<i class="fa fa-refresh"></i> <b id="exchange_count">'.number_format($sClick['total']).'</b>', '-CASH-' => '<i class="fa fa-credit-card"></i> <b id="payout_count">'.get_currency_symbol($config['']).number_format($sCash['total'], 2).'</b>'))
						?>
				</div>
				
				<div id="home-bottom-box">
					<div class="content">
						<?=$lang['index_desc_1']?>
						<div class="clearfix"></div>
					</div>
				</div>
				
				<div id="home-exchange-box">
					<div class="content text-center">
						<h2><?=$lang['b_91']?></h2>
						<?=$lang['b_92']?><br />
						<a href="<?=GenerateURL('register')?>"><?=hook_filter('index_icons',"")?></a><br /><br />
						<a class="btn btn-success btn-lg mt-2" href="<?=GenerateURL('register')?>"><?=$lang['b_165']?></a>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>