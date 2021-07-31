<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$can_withdraw = 1;
	if($config['proof_required'] == 1){
		$check = $db->QueryGetNumRows("SELECT a.id FROM requests a LEFT JOIN payment_proofs b ON b.p_id = a.id WHERE (a.user = '".$data['id']."' AND a.paid = '1') AND (a.proof = '0' OR b.approved = '0')");
		if($check > 0){
			$can_withdraw = 0;
		}
	}
	
	$errMessage = '';
	if(isset($_POST['submit'])){
		$cash = $db->EscapeString($_POST['cash']);
		$pemail = $db->EscapeString($_POST['email']);
		$gateway = $db->EscapeString($_POST['gateway']);

		$valid = false;
		if($gateway == 'paypal' && $config['paypal_status'] == 1){
			$valid = true;
		}elseif($gateway == 'payeer' && $config['payeer_status'] == 1){
			$valid = true;
		}elseif($gateway == 'bitcoin' && $config['cp_status'] == 1){
			$valid = true;
		}
		
		if($valid){
			if(!$can_withdraw) {
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_318'].'</div>';
			}elseif(!is_numeric($cash) || $cash < $config['pay_min']){
				$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_98'], array('-MIN-' => $config['pay_min'])).'</div>';
			}elseif(time()-(86400*$config['aff_reg_days']) < strtotime($data['signup'])){
				$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_243'], array('-DAYS-' => $config['aff_reg_days'])).'</div>';
			}elseif($_POST['cash'] > $data['account_balance']){
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_99'].'</div>';
			}elseif(!isEmail($_POST['email'])){
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_100'].'</div>';
			}else{
				$clicks = $db->QueryFetchArray("SELECT SUM(`total_clicks`) AS `total_clicks` FROM `user_clicks` WHERE `uid`='".$data['id']."'");
				if($clicks['total_clicks'] < $config['aff_req_clicks']){
					$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_324'], array('-NUM-' => $config['aff_req_clicks'], '-DONE-' => $clicks['total_clicks'])).'</div>';
				}else{
					$db->Query("INSERT INTO `requests` (user, paypal, amount, date, gateway) VALUES('".$data['id']."', '".$pemail."', '".$cash."', NOW(), '".$gateway."')");
					$db->Query("UPDATE `users` SET `account_balance`=`account_balance`-'".$cash."' WHERE `id`='".$data['id']."'");			
					$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_101'].'</div>';
				}
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
			<div class="my-3 ml-md-2 p-3 bg-white rounded box-shadow box-style">
				<?=$errMessage?>
				<div id="blue-box">
					<div class="title">
						<?=$lang['b_97']?>
					</div>
					<div class="content">
						<form method="post">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="cash"><?=$lang['b_103']?> (<?=get_currency_symbol($config[''])?>)</label></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-ruble"></i></div></div>
								<input type="text" class="form-control" id="cash" name="cash" placeholder="<?=$config['pay_min']?>">
							  </div>

                              <div align="Center" id="bancode_6695"><script src="//multibux.org/bancode.php?id=6695" async></script></div>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="email"><?=$lang['b_104']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-envelope"></i></div></div>
								<input type="text" class="form-control" id="email" name="email" placeholder="<?=$data['email']?>">
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="gateway"><?=$lang['b_226']?></label>
							  <select id="gateway" name="gateway" onchange="setSelect()" class="custom-select">
								<?php if($config['paypal_status'] == 1){ ?><option value="paypal">PayPal</option><?php } ?>
								<?php if($config['payeer_status'] == 1){ ?><option value="payeer">Payeer</option><?php } ?>
								<?php if($config['cp_status'] == 1){ ?><option value="bitcoin">Bitcoin</option><?php } ?>
							  </select>
							</div>
						  </div>
						  <p><input type="submit" class="btn btn-primary d-inline" name="submit" value="<?=$lang['b_58']?>" /></p>
						</form>
					</div>
				</div>
				<div id="blue-box" class="p-2 mt-2"><?=lang_rep($lang['b_102'], array('-SUM-' => $config['pay_min']))?></div>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_257']?>
					</div>
					<div class="content">
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th width="20">#</td>
									<th><?=$lang['b_103']?></th>
									<th><?=$lang['b_104']?></th>
									<th><?=$lang['b_258']?></th>
									<th><?=$lang['b_106']?></th>
									<th><?=$lang['b_75']?></th>
									<th><?=$lang['b_315']?></th>
								</tr>
							</thead>
							<tfoot class="thead-dark">
								<tr>
									<th>#</td>
									<th><?=$lang['b_103']?></th>
									<th><?=$lang['b_105']?></th>
									<th><?=$lang['b_258']?></th>
									<th><?=$lang['b_106']?></th>
									<th><?=$lang['b_75']?></th>
									<th><?=$lang['b_315']?></th>
								</tr>
							</tfoot>
							<tbody class="table-primary text-dark">
							<?php
								$requests = $db->QueryFetchArrayAll("SELECT id,amount,paypal,date,paid,gateway,reason,proof FROM `requests` WHERE `user`='".$data['id']."' AND `gateway`!='accb' ORDER BY `date` DESC LIMIT 10");
								if(!$requests){ 
									echo '<tr><td colspan="7" align="center"><b>'.$lang['b_250'].'</b></td><tr>';
								}else{
									foreach($requests as $request){
							?>	
								<tr>
									<td><?=$request['id']?></td>
									<td><?=$request['amount'].get_currency_symbol($config[''])?></td>
									<td><?=(!empty($request['paypal']) ? $request['paypal'] : 'N/A')?></td>
									<td><?=ucfirst($request['gateway'])?></td>
									<td><?=$request['date']?></td>
									<td><?=($request['paid'] == 0 ? '<span class="text-info">'.$lang['b_107'].'</span>' : ($request['paid'] == 2 ? '<span class="text-danger" title="'.$request['reason'].'">'.$lang['b_108'].'</span>' : '<span class="text-success">'.$lang['b_109'].'</span'))?></td>
									<td><?=($request['proof'] == 0 && $request['paid'] == 1 ? '<a href="'.GenerateURL('proofs&upload='.$request['id']).'">'.$lang['b_316'].'</a>' : ($request['proof'] == 1 && $request['paid'] == 1 ? '<span class="text-green">'.$lang['b_317'].'</span>' : 'N/A'))?></td>
								</tr>
							<?php }} ?>
							</tbody>
						</table>
					</div>
                    <div align="Center" id="bancode_6696"><script src="//multibux.org/bancode.php?id=6696" async></script></div>
				</div>
			</div>
		</div>
	  </div>
    </main>