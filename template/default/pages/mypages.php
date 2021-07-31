<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$target_system = true;
	if($config['target_system'] == 1){
		if($data['premium'] > 0){
			$target_system = true;
		}else{
			$target_system = false;
		}
	}elseif($config['target_system'] == 2){
		$target_system = false;
	}

	$custom = '';
	if(isset($_GET['p'])){
		$page = $_GET['p'];
		$table = hook_filter($_GET['p'].'_info', 'db');
		$custom = ($_GET['p'] == 'surf' && $config['surf_type'] != 2 ? " AND `confirm`!='1'" : '');
		$custom = ($_GET['p'] == 'ad_short' ? " AND `confirm`='1'" : '');
	}else{
		redirect(GenerateURL('mypages&p=surf'));
	}

	if($table == 'db'){
		redirect($config['site_url']);
	}

	$mysites = $db->QueryFetchArrayAll("SELECT * FROM `".$table."` WHERE `user`='".$data['id']."'".$custom);
?> 
	<script type="text/javascript"> function goSelect(selectobj){ window.location.href='<?=GenerateURL('mypages&p=')?>'+selectobj; } </script>
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
						<?=$lang['b_20']?>
					</div>
					<div class="content">
						<div class="mb-1">
							<select class="custom-select" onChange="goSelect(this.value)"><?=hook_filter('site_menu', "")?></select>
						</div>
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th><?=$lang['b_33']?></th>
									<th><?=$lang['b_94']?></th>
									<th><?=$lang['b_346']?></th>
									<th><?=$lang['b_95']?></th>
									<th><?=$lang['b_75']?></th>
									<th><?=$lang['b_96']?></th>
								</tr>
							</thead>
							<tbody class="table-primary text-dark">
							<?php
								foreach($mysites as $mysite) {
									$status = ($mysite['active'] == 0 ? '<span class="text-success">'.$lang['b_76'].'</span>' : ($mysite['active'] == 2 ? '<span class="text-danger"><b>'.$lang['b_78'].'</b></span>' : '<span class="text-danger">'.$lang['b_77'].'</span>'));
							?>
								<tr><td class="text-left"><?=truncate($mysite['title'], 30)?></td><td><?=number_format($mysite['clicks']).' / '.($mysite['max_clicks'] == 0 ? '&#8734;' : $mysite['max_clicks'])?></td><td><?=number_format($mysite['today_clicks']).' / '.($mysite['daily_clicks'] == 0 ? '&#8734;' : $mysite['daily_clicks'])?></td><td><?=$mysite['cpc']?></td><td><?=$status?></td><td><?php if($mysite['active'] != 2){ ?><a href="<?=GenerateURL('editpage&x='.$mysite['id'].'&t='.$page)?>"><?=$lang['b_96']?></a><?php }?></td></tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>