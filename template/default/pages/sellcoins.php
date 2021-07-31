<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$errMessage = '';
	if(isset($_GET['del']) && is_numeric($_GET['del']))
	{
		$pack_id = $db->EscapeString($_GET['del']);
		$pack = $db->QueryFetchArray("SELECT * FROM `sell_coins` WHERE (`id`='".$pack_id."' AND `seller_id`='".$data['id']."') AND `sold`='0' LIMIT 1");
		
		if(!empty($pack['id']))
		{
			$db->Query("UPDATE `users` SET `coins`=`coins`+'".$pack['coins']."' WHERE `id`='".$data['id']."'");
			$db->Query("DELETE FROM `sell_coins` WHERE `id`='".$pack['id']."'");
			
			$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_384'].'</div>';
		}
	}

	if(isset($_POST['add']))
	{
		$coins = $db->EscapeString($_POST['coins']);
		$price = $db->EscapeString($_POST['price']);
		$coin_value = number_format($price/$coins, 4);
		
		if(!is_numeric($coins) || $data['coins'] < $coins)
		{
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_146'].'</div>';
		}
		elseif($coins < $config['sc_min_coins'])
		{
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_386'], array('-COINS-' => $config['sc_min_coins'])).'</div>';
		}
		elseif(!is_numeric($price) || $price < $config['sc_min_price'])
		{
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_387'], array('-PRICE-' => $config['sc_min_price'])).'</div>';
		}
		elseif($coin_value < $config['minimum_sc_value'])
		{
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_388'], array('-VALUE-' => $config['minimum_sc_value'])).'</div>';
		}
		else
		{
			$packs = $db->QueryGetNumRows("SELECT * FROM `sell_coins` WHERE `sold`='0' AND `seller_id`='".$data['id']."'");

			if($data['premium'] == 0 && $packs >= $config['free_sc_limit'])
			{
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_389'].'</div>';
			}
			elseif($data['premium'] > 0 && $packs >= $config['vip_sc_limit'])
			{
				$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_390'], array('-LIMIT-' => $config['vip_sc_limit'])).'</div>';
			}
			else
			{
				$db->Query("UPDATE `users` SET `coins`=`coins`-'".$coins."' WHERE `id`='".$data['id']."'");
				$db->Query("INSERT INTO `sell_coins`(`seller_id`,`coins`,`price`,`coin_value`,`added_time`)VALUES('".$data['id']."','".$coins."','".$price."','".$coin_value."','".time()."')");

				$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_385'].'</div>';
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
			<div class="my-3 p-3 bg-white rounded box-shadow box-style">
				<?=$errMessage?>
				<div id="blue-box">
					<div class="title">
						<?=$lang['b_375']?>
					</div>
					<div class="content">
						<form method="post">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="coins"><?=$lang['b_42']?></label></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-check-circle"></i></div></div>
								<input type="text" class="form-control" id="coins" name="coins" placeholder="1000">
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="price"><?=$lang['b_249']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-credit-card"></i></div></div>
								<input type="text" class="form-control" id="price" name="price" placeholder="0.00">
							  </div>
							</div>
						  </div>
						  <small class="form-text text-white"><i><?=lang_rep($lang['b_393'], array('-FEE-' => $config['sc_fees']))?></i></small>
						  <p><input type="submit" class="btn btn-primary d-inline" name="add" value="<?=$lang['b_376']?>" /></p>
						</form>
					</div>
				</div>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_377']?>
					</div>
					<div class="content">
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th>#</th>
									<th><?=$lang['b_42']?></th>
									<th><?=$lang['b_249']?></th>
									<th><?=$lang['b_378']?></th>
									<th><?=$lang['b_106']?></th>
									<th width="60"><?=$lang['b_185']?></th>
								</tr>
							</thead>
							<tbody class="table-primary text-dark">
							<?php
								$coins = $db->QueryFetchArrayAll("SELECT * FROM `sell_coins` WHERE `seller_id`='".$data['id']."' AND `sold`='0' ORDER BY `added_time` DESC");

								if(empty($coins))
								{
									echo '<tr><td colspan="6">'.$lang['b_250'].'</td></tr>';
								}

								foreach($coins as $coin){
							?>
								<tr><td class="text-left"><?=$coin['id']?></td><td><?=number_format($coin['coins'])?></td><td><?=get_currency_symbol($config['']).number_format($coin['price'], 2)?></td><td><?=get_currency_symbol($config['']).$coin['coin_value']?></td><td><?=date('d M Y - H:i', $coin['added_time'])?></td><td><a href="<?=GenerateURL('sellcoins&del='.$coin['id'])?>" onclick="return confirm('Are you sure you want to remove this coins pack from sale?');"><?=$lang['b_81']?></a></td></tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
				<div id="blue-box" class="mt-2">
					<div class="title">
						<?=$lang['b_383']?>
					</div>
					<div class="content">
						<table class="table table-striped table-sm table-responsive-sm">
							<thead class="thead-dark">
								<tr>
									<th>#</th>
									<th><?=$lang['b_42']?></th>
									<th><?=$lang['b_249']?></th>
									<th><?=$lang['b_379']?></th>
									<th><?=$lang['b_380']?></th>
									<th><?=$lang['b_381']?></th>
									<th><?=$lang['b_382']?></th>
								</tr>
							</thead>
							<tbody class="table-primary text-dark">
							<?php
								$coins = $db->QueryFetchArrayAll("SELECT * FROM `sell_coins` WHERE `seller_id`='".$data['id']."' AND `sold`='1' ORDER BY `sold_time` DESC");

								if(empty($coins))
								{
									echo '<tr><td colspan="7">'.$lang['b_250'].'</td></tr>';
								}

								foreach($coins as $coin){
							?>
								<tr><td class="text-left"><?=$coin['id']?></td><td><?=number_format($coin['coins'])?></td><td><?=get_currency_symbol($config['']).number_format($coin['price'], 2)?></td><td><?=get_currency_symbol($config['']).number_format($coin['fees'], 2)?></td><td><?=get_currency_symbol($config['']).number_format(($coin['price']-$coin['fees']), 2)?></td><td><?=date('d M Y - H:i', $coin['added_time'])?></td><td><?=date('d M Y - H:i', $coin['sold_time'])?></td></tr>
							<?php } ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>