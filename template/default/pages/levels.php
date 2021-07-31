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
						<?=$lang['b_349']?>
					</div>
					<div class="content">
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th></th>
									<th><?=$lang['b_350']?></th>
									<th><?=$lang['b_328']?></th>
									<th><?=$lang['b_351']?></th>
									<th class="text-center"><?=$lang['b_352']?></th>
								</tr>
							</thead>
							<tbody class="table-primary text-dark">
								<?php
									$levels = $db->QueryFetchArrayAll("SELECT * FROM `levels` ORDER BY level ASC");
									$myLevel = userLevel($data['id']);
									foreach($levels as $level){
										echo '<tr'.($myLevel == $level['level'] ? ' class="bg-danger"' : '').'><td><img src="'.$level['image'].'"></td><td>Level <b>'.$level['level'].'</b></td><td><b>'.number_format($level['requirements']).' exchanges</b></td><td class="text-success"><b>'.$level['free_bonus'].' '.$lang['b_42'].'</b></td><td class="text-center text-success"><b>'.$level['vip_bonus'].' '.$lang['b_42'].'</b></td></tr>';
									}
								?>
							</tbody>
							<tfoot class="thead-dark">
								<tr>
									<th></th>
									<th><?=$lang['b_350']?></th>
									<th><?=$lang['b_328']?></th>
									<th><?=$lang['b_351']?></th>
									<th><?=$lang['b_352']?></th>
								</tr>
							</tfoot>
						</table>
					</div>
				</div>
				<div id="blue-box" class="p-2 mt-2">
					<b><?=$lang['b_351']?></b> = <?=$lang['b_353']?><br />
					<b><?=$lang['b_352']?></b> = <?=$lang['b_354']?>
				</div>
			</div>
		</div>
	  </div>
    </main>