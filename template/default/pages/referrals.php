<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$refs = $db->QueryFetchArray("SELECT COUNT(*) AS `total` FROM `users` WHERE `ref`='".$data['id']."'");
	$commissions = $db->QueryFetchArray("SELECT COUNT(*) AS `total` FROM `affiliate_transactions` WHERE `user`='".$data['id']."'");
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
						<?=$lang['b_121']?> (<?=$refs['total']?>)
					</div>
					<div class="content">
						<?php
							$count = $refs['total'];
							$bpp = 10;
							$page = (isset($_GET['p']) ? intval($_GET['p']) : 0);
							$begin = ($page >= 0 ? ($page*$bpp) : 0);
							$users = $db->QueryFetchArrayAll("SELECT id,login,signup,ref_paid FROM `users` WHERE `ref`='".$data['id']."' ORDER BY `signup` DESC LIMIT ".$begin.",".$bpp);
						?>
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th scope="col" width="20">#</th>
									<th scope="col"><?=$lang['b_122']?></th>
									<th scope="col"><?=$lang['b_106']?></th>
									<th scope="col"><?=$lang['b_244']?></th>
								</tr>
							</thead>
							<tfoot class="thead-dark">
								<tr>
									<th>#</th>
									<th><?=$lang['b_122']?></th>
									<th><?=$lang['b_106']?></th>
									<th><?=$lang['b_244']?></th>
								</tr>
							</tfoot>
							<tbody class="table-primary text-dark">
							<?php
								if(empty($users)){
									echo '<tr><td colspan="4" class="text-center">'.$lang['b_250'].'</td></tr>';
								}

								foreach($users as $user){
							?>	
								<tr>
									<td><?=$user['id']?></td>
									<td><?=$user['login']?></td>
									<td><?=$user['signup']?></td>
									<td><?=($user['ref_paid'] == 1 ? $lang['b_124'] : $lang['b_125'])?></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
						<?php
							if(ceil($count/$bpp) > 1) {
								if($page == 0) {
									$left = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Previous</a></li>';
								}else{
									$left = '<li class="page-item"><a class="page-link" href="'.GenerateURL('referrals&p='.($page-1)).'">Previous</a></li>';
								}
								
								$total_pages = (number_format(($count/$bpp), 0)-1);
								$middle = '<li class="page-item active"><a class="page-link" href="javascript:void(0)">'.($page+1).' - '.($total_pages+1).'</a></li>';

								if($page >= $total_pages) {
									$right = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Next</a></li>';
								}else{
									$right = '<li class="page-item"><a class="page-link" href="'.GenerateURL('referrals&p='.($page+1)).'">Next</a></li>';
								}
								
								echo '<nav aria-label="navigation"><ul class="pagination justify-content-center">'.$left.$middle.$right.'</ul></nav>';
							}
						?>
						<div class="clearfix"></div>
					</div>
				</div>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_340']?> (<?=$commissions['total']?>)
					</div>
					<div class="content">
						<?php
							$count = $commissions['total'];
							$bpp = 10;
							$page = (isset($_GET['p2']) ? intval($_GET['p2']) : 0);
							$begin = ($page >= 0 ? ($page*$bpp) : 0);
							$commissions = $db->QueryFetchArrayAll("SELECT a.id, a.referral, a.commission, a.type, a.date, b.login FROM affiliate_transactions a LEFT JOIN users b ON b.id = a.referral WHERE a.user = '".$data['id']."' ORDER BY a.date DESC LIMIT ".$begin.",".$bpp);
						?>
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th width="20">#</th>
									<th><?=$lang['b_342']?></th>
									<th><?=$lang['b_341']?></th>
									<th><?=$lang['b_31']?></th>
									<th><?=$lang['b_106']?></th>
								</tr>
							</thead>
							<tfoot class="thead-dark">
								<tr>
									<th>#</th>
									<th><?=$lang['b_342']?></th>
									<th><?=$lang['b_341']?></th>
									<th><?=$lang['b_31']?></th>
									<th><?=$lang['b_106']?></th>
								</tr>
							</tfoot>
							<tbody class="table-primary text-dark">
							<?php 
								if(empty($commissions)){
									echo '<tr><td colspan="5" class="text-center">'.$lang['b_250'].'</td></tr>';
								}

								foreach($commissions as $commission){
							?>	
								<tr>
									<td><?=$commission['id']?></td>
									<td><?=$commission['login']?></td>
									<td><font color="green"><?=get_currency_symbol($site['']).$commission['commission']?></font></td>
									<td><?=$commission['type']?></td>
									<td><?=date('d M Y H:i', $commission['date'])?></td>
								</tr>
							<?php }?>
							</tbody>
						</table>
						<?php
							if(ceil($count/$bpp) > 1) {
								if($page == 0) {
									$left = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Previous</a></li>';
								}else{
									$left = '<li class="page-item"><a class="page-link" href="'.GenerateURL('referrals&p2='.($page-1)).'">Previous</a></li>';
								}
								
								$total_pages = (number_format(($count/$bpp), 0)-1);
								$middle = '<li class="page-item active"><a class="page-link" href="javascript:void(0)">'.($page+1).' - '.($total_pages+1).'</a></li>';

								if($page >= $total_pages) {
									$right = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Next</a></li>';
								}else{
									$right = '<li class="page-item"><a class="page-link" href="'.GenerateURL('referrals&p2='.($page+1)).'">Next</a></li>';
								}
								
								echo '<nav aria-label="navigation"><ul class="pagination justify-content-center">'.$left.$middle.$right.'</ul></nav>';
							}
						?>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>