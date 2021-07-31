<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$errMessage = '';
	if(isset($_POST['submit'])){
		$coins = $db->EscapeString($_POST['coins']);

		if(!is_numeric($coins)){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_253'].'</div>';
		}elseif($data['coins'] < $coins){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_146'].'</div>';
		}elseif($coins < $config['min_convert']){
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_265'], array('-MIN-' => $config['min_convert'])).'</div>';
		}else{
			$cash = floor(((1/$config['convert_rate'])*$coins) * 100) / 100;
			
			if($cash > 0) {
				$db->Query("UPDATE `users` SET `coins`=`coins`-'".$coins."', `account_balance`=`account_balance`+'".$cash."' WHERE `id`='".$data['id']."'");
				$db->Query("INSERT INTO `coins_to_cash` (user, coins, cash, conv_rate, date) VALUES('".$data['id']."', '".$coins."', '".$cash."', '".$config['convert_rate']."', '".time()."')");
			}

			$errMessage = '<div class="alert alert-success" role="alert">'.lang_rep($lang['b_266'], array('-NUM-' => $coins, '-CASH-' => get_currency_symbol($config['']).' '.$cash)).'</div>';
		}
	}
?> 
	<script>
		function get_amount(value){
			if(value > 0) {
				var amount = Math.floor((<?=(1/$config['convert_rate'])?>*value)*100)/100;
				$('#amount-final').html('<?=$lang['b_269']?> <b><?=get_currency_symbol($config[''])?> '+ amount +'</b>');
			}
		}
	</script>
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
						<?=$lang['b_268']?>
					</div>
					<div class="content">
						<form method="post">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="coins"><?=$lang['b_267']?></label></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-check-circle"></i></div></div>
								<input type="number" class="form-control" id="coins" name="coins" oninput="get_amount(this.value)" maxlength="7" placeholder="<?=$config['convert_rate']?>">
								<input type="submit" class="btn btn-primary d-inline" name="submit" value="<?=$lang['b_58']?>"  aria-describedby="amount-final" />
							  </div>
							  <small id="amount-final" class="form-text text-white"><?=$lang['b_269']?> <b><?=get_currency_symbol($config[''])?>1.00</b></small>
							</div>
						  </div>
<div align="Center" id="bannerswall_976"><script src="https://bannerswall.ru/bancode.php?id=976" async></script></div>

						</form>
					</div>
				</div>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_257']?>
					</div>
					<div class="content">
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th>#</td>
									<th><?=$lang['b_103']?></th>
									<th><?=$lang['b_42']?></th>
									<th><?=$lang['b_106']?></th>
									<th><?=$lang['b_75']?></th>
								</tr>
							</thead>
							<tfoot class="thead-dark">
								<tr>
									<th>#</th>
									<th><?=$lang['b_103']?></th>
									<th><?=$lang['b_42']?></th>
									<th><?=$lang['b_106']?></th>
									<th><?=$lang['b_75']?></th>
								</tr>
							</tfoot>
							<tbody class="table-primary text-dark">
							<?php
								$trans = $db->QueryFetchArrayAll("SELECT id,coins,cash,date FROM `coins_to_cash` WHERE `user`='".$data['id']."' ORDER BY `date` DESC LIMIT 10");
								if(!$trans){ 
									echo '<tr><td colspan="6" class="text-center"><b>'.$lang['b_250'].'</b></td><tr>';
								}else{
									foreach($trans as $tran){
							?>	
								<tr>
									<td><?=$tran['id']?></td>
									<td><?=get_currency_symbol($config['']).$tran['cash']?></td>
									<td><?=number_format($tran['coins'], 0)?></td>
									<td><?=date('Y-m-d h:i',$tran['date'])?></td>
									<td><font color="green"><b><?=$lang['b_259']?></b></font></td>
								</tr>
							<?php }} ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>