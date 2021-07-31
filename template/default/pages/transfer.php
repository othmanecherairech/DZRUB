<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
	
	$minimum_days = 3;		// Minimum days from registration, to transfer coins
	$timecheck = ((86400*$minimum_days)+strtotime($data['signup']));

	$errMessage = '';
	if(isset($_POST['submit'])) {
		$username = $db->EscapeString($_POST['username']);
		$amount = $db->EscapeString($_POST['amount']);
		$rid = $db->QueryFetchArray("SELECT id FROM `users` WHERE `login`='".$username."' AND `banned`='0' LIMIT 1");
		
		if($config['transfer_status'] == 2 && $data['premium'] == 0){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_231'].'</div>';
		}elseif($timecheck > time()){
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_228'], array('-DAYS-' => $minimum_days)).'</div>';
		}elseif($data['coins'] < $amount){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_146'].'</div>';
		}elseif($data['login'] == $username){
			$errMessage = '<div class="alert alert-danger" role="alert">You cannot transfer coins to yourself!</div>';
		}elseif($amount < 10 || $amount > $data['coins'] || !is_numeric($amount)){
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_148'], array('-MAX-' => $data['coins'])).'</div>';
		}elseif(empty($rid['id'])){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_147'].'</div>';
		}else{
			$fee = ($config['transfer_fee']/100*$amount);
			$fee = ($fee < 1 ? 1 : $fee);
			$tamount = ($amount-$fee);

			$db->Query("UPDATE `users` SET `coins`=`coins`-'".$amount."' WHERE `id`='".$data['id']."'");
			$db->Query("UPDATE `users` SET `coins`=`coins`+'".round($tamount)."' WHERE `login`='".$username."'");
			$db->Query("INSERT INTO `c_transfers` (`receiver`, `sender`, `coins`, `date`)VALUES('".$rid['id']."', '".$data['login']."', '".$tamount."', '".time()."')");
			$errMessage = '<div class="alert alert-success" role="alert">'.lang_rep($lang['b_149'], array('-SENT-' => $amount, '-USER-' => $username, '-RECEIVED-' => round($tamount), '-FEE-' => $config['transfer_fee'])).'</div></div> <script> $("#c_coins").html('.($data['coins']-$amount).'); </script>';
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
				<?=$errMessage?>
				<div id="blue-box">
					<div class="title">
						<?=$lang['b_11']?>
					</div>
					<div class="content">
						<form method="post">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="username"><?=$lang['b_150']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-user"></i></div></div>
								<input type="text" class="form-control" id="username" name="username" placeholder="<?=$lang['b_122']?>">
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="amount"><?=$lang['b_151']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-check-circle"></i></div></div>
								<input type="number" class="form-control" id="amount" name="amount" placeholder="100" aria-describedby="amount_help">
								<input type="submit" class="btn btn-primary btn-sm" name="submit" value="<?=$lang['b_52']?>" />
							  </div>
							  <small id="amount_help" class="form-text text-white">
								<b><?=$lang['b_152']?>:</b> <?=$config['transfer_fee']?>%
							  </small>
							</div>
						  </div>
						</form>
					</div>
				</div>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_153']?>
					</div>
					<div class="content">
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th><?=$lang['b_154']?></th>
									<th><?=$lang['b_155']?></td>
									<th><?=$lang['b_42']?></td>
									<th class="w-25"><?=$lang['b_106']?></th>
								</tr>
							</thead>
							<tfoot class="thead-dark">
								<tr><th colspan="4" class="text-center"><?=$lang['b_153']?></th></tr>
							</tfoot>
							<tbody class="table-primary text-dark">
							<?php
								$transfers = $db->QueryFetchArrayAll("SELECT id,sender,coins,date FROM `c_transfers` WHERE `receiver`='".$data['id']."' ORDER BY date DESC LIMIT 10");
								if(!$transfers){
									echo '<tr><td colspan="4" class="text-center">'.$lang['b_157'].'</td></tr>';
								}

								foreach($transfers as $transfer){
							?>	
								<tr>
									<td><?=$transfer['id']?></td>
									<td><?=$transfer['sender']?></td>
									<td><?=$transfer['coins']?> <?=$lang['b_156']?></td>
									<td><?=date('d F Y - H:i:s', $transfer['date'])?></td>
								</tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
            <div align="Center" id="bannerswall_974"><script src="https://bannerswall.ru/bancode.php?id=974" async></script></div>
		</div>
	  </div>
    </main>