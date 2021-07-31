<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$errMessage = '';
	if(isset($_POST['submit']) && isset($_POST['gateway'])){
		$cash = $db->EscapeString($_POST['cash']);
		$gateway = $db->EscapeString($_POST['gateway']);
		
		$minimum = 1;
		if($_POST['gateway'] == 'paypal'){
			$minimum = $config['paypal_minimum'];
		}elseif($_POST['gateway'] == 'payeer'){
			$minimum = $config['payeer_minimum'];
		}elseif($_POST['gateway'] == 'coinpayments'){
			$minimum = $config['cp_minimum'];
		}
		
		if(!is_numeric($cash)){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_253'].'</div>';
		}elseif($cash < $minimum){
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_254'], array('-MIN-' => get_currency_symbol($config['']).' '.number_format($minimum, 2))).'</div>';
		}else{
			$redurl = $config['site_url'].'/system/payments/'.$gateway.'/add_cash.php?cash='.$cash;
			redirect($redurl);
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
						<?=$lang['b_256']?>
					</div>
					<div class="content">
						<form method="post">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="cash"><?=$lang['b_256']?> (<?=get_currency_symbol($config[''])?>)</label></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-credit-card"></i></div></div>
								<input type="text" class="form-control" id="cash" name="cash" placeholder="1.00">
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="gateway"><?=$lang['b_226']?></label>
							  <select id="gateway" name="gateway" class="custom-select">
								<?php if($config['paypal_status'] > 0){ ?><option value="paypal">PayPal</option><?php } ?>
								<?php if($config['payeer_status'] > 0){ ?><option value="payeer">Payeer</option><?php } ?>
								<?php if($config['cp_status'] > 0){ ?><option value="coinpayments">Crypto Currencies</option><?php } ?>
							  </select>
							</div>
						  </div>
						  <p><input type="submit" class="btn btn-primary d-inline" name="submit" value="<?=$lang['b_58']?>" /></p>
						</form>
                        <b><p><CENTER><FONT COLOR="#F4F9F9" FACE="Arial" SIZE="4">Please send money into your Payeer account *** P73356476 ***</FONT></CENTER></p></b>
                        <b><p><CENTER><FONT COLOR="#FF0000" FACE="Arial" SIZE="5">note :</FONT></CENTER></p></b>
                        <b><p><CENTER><FONT COLOR="#F7FD04" FACE="Arial" SIZE="4">When sending money, do not forget to write the "<FONT COLOR="#F4F9F9">Name</FONT>" with which you registered on the site</FONT></CENTER></p></b>

<div align="Center" id="bancode_6693"><script src="//multibux.org/bancode.php?id=6693" async></script></div>

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
									<th width="20">#</th>
									<th><?=$lang['b_103']?></th>
									<th><?=$lang['b_104']?></th>
									<th><?=$lang['b_258']?></th>
									<th><?=$lang['b_106']?></th>
									<th><?=$lang['b_75']?></th>
								</tr>
							</thead>
							<tfoot class="thead-dark">
								<tr>
									<th>#</th>
									<th><?=$lang['b_103']?></th>
									<th><?=$lang['b_104']?></th>
									<th><?=$lang['b_258']?></th>
									<th><?=$lang['b_106']?></th>
									<th><?=$lang['b_75']?></th>
								</tr>
							</tfoot>
							<tbody class="table-primary text-dark">
							<?php
								$trans = $db->QueryFetchArrayAll("SELECT id,money,gateway,date,paid,payer_email FROM `transactions` WHERE `user_id`='".$data['id']."' ORDER BY `date` DESC LIMIT 10");
								if(!$trans){ 
									echo '<tr><td colspan="6" class="text-center"><b>'.$lang['b_250'].'</b></td><tr>';
								}else{
									foreach($trans as $tran){
							?>	
								<tr>
									<td><?=$tran['id']?></td>
									<td><?=get_currency_symbol($config['']).$tran['money']?></td>
									<td><?=(empty($tran['payer_email']) ? 'N/A' : $tran['payer_email'])?></td>
									<td><?=ucfirst($tran['gateway'])?></td>
									<td><?=$tran['date']?></td>
									<td><?=($tran['paid'] == 1 ? '<span class="text-success"><b>'.$lang['b_259'].'</b></span>' : '<span class="text-warning"><b>'.$lang['b_260'].'</b></span>')?></td>
								</tr>
							<?php }} ?>
							</tbody>
						</table>
					</div>
                    <div align="Center" id="bancode_6694"><script src="//multibux.org/bancode.php?id=6694" async></script></div>
				</div>
                			</div>
		</div>


	  </div>
    </main>