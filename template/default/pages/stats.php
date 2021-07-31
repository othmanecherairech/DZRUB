<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	if($config['banner_system'] != 0){
		$banner_stats = $db->QueryFetchArray("SELECT COUNT(*) AS `total`, SUM(`views`) AS `views`, SUM(`clicks`) AS `clicks` FROM `banners`");
	}
	if($config['allow_withdraw'] == 1){
		$total_paid = $db->QueryFetchArray("SELECT COUNT(*) AS `payouts`, SUM(`amount`) AS `total` FROM `requests` WHERE `paid`='1'");
	}
?>
    <main role="main" class="container">
      <div class="row">
		<?php 
			if($is_online) {
				require_once(BASE_PATH.'/template/'.$config['theme'].'/common/sidebar.php');
			}
		?>
		<div class="col-md-<?=($is_online ? 9 : 12)?>">
			<div class="my-3 p-3 bg-white rounded box-shadow box-style">
				<div id="blue-box">
					<div class="title">
						<?=$lang['b_82']?>
					</div>
					<div class="content">
						<div class="table-responsive-sm">
							<table class="table table-striped table-sm text-center">
								<thead class="thead-dark">
									<tr><th scope="col"><?=$lang['b_135']?></th><th scope="col"><?=$lang['b_136']?></th><th scope="col"><?=$lang['b_137']?></th><th scope="col"><?=$lang['b_138']?></th></tr>
								</thead>
								<tbody id="members_stats" class="table-primary text-dark">
									<tr id="loading_stats"><td colspan="4"><i class="fa fa-refresh fa-spin fa-3x fa-fw my-1"></i></td></tr>
								</tbody>
								<thead class="thead-dark">
									<tr><th scope="col" colspan="4"><b><?=$lang['b_139']?></b></th></tr>
								</thead>
							</table>
						</div>

						<table class="table table-striped table-sm text-center">
							<thead class="thead-dark">
								<tr><th scope="col" width="40%"><?=$lang['b_31']?></th><th scope="col" width="30%"><?=$lang['b_140']?></th><th scope="col" width="30%"><?=$lang['b_141']?></td></tr>
							<thead class="thead-dark">
							<tbody id="page_stats_body" class="table-primary text-dark">
								<tr id="loading_stats"><td colspan="3"><i class="fa fa-refresh fa-spin fa-3x fa-fw my-3"></i></td></tr>
							</tbody>
							<thead class="thead-dark">
								<tr id="page_stats_foot"></tr>
								<tr><th scope="col" colspan="4"><b><?=$lang['b_140']?></b></th></tr>
							</thead>
						</table>
						<?php
							$tops = $db->QueryFetchArrayAll("SELECT a.uid, SUM(a.today_clicks) AS clicks, b.login FROM user_clicks a LEFT JOIN users b ON b.id = a.uid WHERE b.login != '' GROUP BY a.uid ORDER BY clicks DESC LIMIT 3");
							if($tops){
						?>
						<table class="table table-striped table-sm text-center">
							<thead class="thead-dark">
								<tr><th scope="col"><i class="fa fa-trophy text-warning" aria-hidden="true"></i></th><th scope="col"><i class="fa fa-trophy text-light" aria-hidden="true"></i></th><th scope="col"><i class="fa fa-trophy text-danger" aria-hidden="true"></i></th></tr>
							</thead>
							<tbody class="table-primary text-dark">
								<?php
									echo '<tr>';
									foreach($tops as $top){
										echo '<td>'.$top['login'].'</td>';
									}
									echo '</tr>';
								?>
							</tbody>
							<thead class="thead-dark">
								<tr><th scope="col" colspan="3"><b><?=$lang['b_239']?></b></th></tr>
							</thead>
						</table>
						<?php }if($config['allow_withdraw'] == 1){ ?>
						<table class="table table-striped table-sm text-center">
							<thead class="thead-dark">
								<tr><th scope="col"><?=$lang['b_321']?></th><th scope="col"><?=$lang['b_322']?></th></tr>
							</thead>
							<tbody class="table-primary text-dark">
								<tr><td><?=number_format($total_paid['payouts'])?></td><td><?=get_currency_symbol($config['']).number_format($total_paid['total'], 2)?></td></tr>
							</tbody>
							<thead class="thead-dark">
								<tr><th scope="col" colspan="3"><b><?=$lang['b_320']?></b></th></tr>
							</thead>
						</table>
						<?php }if($config['banner_system'] != 0){ ?>
						<table class="table table-striped table-sm text-center">
							<thead class="thead-dark">
								<tr><th scope="col"><?=$lang['banners_02']?></th><th scope="col"><?=$lang['banners_03']?></th><th scope="col"><?=$lang['banners_04']?></th></tr>
							</thead>
							<tbody class="table-primary text-dark">
								<tr><td><?=number_format($banner_stats['total'])?></td><td><?=number_format($banner_stats['views'])?></td><td><?=number_format($banner_stats['clicks'])?></td></tr>
							</tbody>
							<thead class="thead-dark">
								<tr><th scope="col" colspan="3"><b><?=$lang['banners_01']?></b></th></tr>
							</thead>
						</table>
					<?php } ?>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>
	<script>
		$.getJSON('system/ajax.php?a=getStats', function (c) {
			$('#members_stats').html(c['members']);
			$('#page_stats_body').html(c['pages_body']);
			$('#page_stats_foot').html(c['pages_foot']);
		});
	</script>