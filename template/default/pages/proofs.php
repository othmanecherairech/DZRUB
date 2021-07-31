<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
	
	$errMessage = '';
	$db_query = '';
	if($is_online && isset($_GET['upload']) && $_GET['upload'] == 'all') {
		$db_query = " AND c.id = '".$data['id']."'";
	}
	
	$p_data = $db->QueryFetchArray("SELECT COUNT(a.id) AS total, SUM(a.amount) AS money FROM requests a LEFT JOIN payment_proofs b ON b.p_id = a.id AND b.approved = '1' LEFT JOIN users c ON c.id = a.user WHERE a.paid = '1'".$db_query);
	$bpp = 18;
	$page = (isset($_GET['p']) ? intval($_GET['p']) : 0);
	$begin = ($page >= 0 ? ($page*$bpp) : 0);
	$proofs = $db->QueryFetchArrayAll("SELECT a.id, a.user, a.amount, UNIX_TIMESTAMP(a.date) AS date, b.proof, c.id AS u_id, c.login FROM requests a LEFT JOIN payment_proofs b ON b.p_id = a.id AND b.approved = '1' LEFT JOIN users c ON c.id = a.user WHERE a.paid = '1'".$db_query." ORDER BY a.date DESC LIMIT ".$begin.", ".$bpp."");
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
						<?=$lang['b_303'].($is_online ? ' - <a href="'.GenerateURL('proofs&upload=all').'">'.$lang['b_316'].'</a>': '')?> 
					</div>
					<div class="content">
						<center>
							<div class="infobox" style="width:210px;margin:-15px 2px 10px;display:inline-block"><b><?=$lang['b_321']?>: <span style="font-weight:620;color:red"><?=$p_data['total']?></span></b></div>
							<div class="infobox" style="width:210px;margin:-15px 2px 10px;display:inline-block"><b><?=$lang['b_322']?>: <span style="font-weight:620;color:yellow"><?=get_currency_symbol($config['']).(empty($p_data['money']) ? '0.00' : $p_data['money'])?></span></b></div>
						</center>
						<?php
							$is_valid = false;
							if($is_online && isset($_GET['upload']) && is_numeric($_GET['upload'])) {
								$request = $db->EscapeString($_GET['upload']);
								$check_valid = $db->QueryFetchArray("SELECT COUNT(*) AS `total` FROM `requests` WHERE (`user`='".$data['id']."' AND `id`='".$request."') AND `proof`='0'");
										
								if($check_valid['total'] > 0){
									$is_valid = true;
								}
							}
							
							if($is_valid != false) {
								if(isset($_POST['submit'])){
									$request = $db->EscapeString( $_POST['request']);
									$request = $db->QueryFetchArray("SELECT * FROM `requests` WHERE (`user`='".$data['id']."' AND `id`='".$request."') AND `proof`='0' LIMIT 1");

									$MAX_SIZE = 750;	// Max image size in kb
									function getExtension($str) {
										if($str == 'image/jpeg'){
											return 'jpg';
										}elseif($str == 'image/png'){
											return 'png';
										}elseif($str == 'image/gif'){
											return 'gif';
										}
									}

									if(!empty($request) && $_FILES['cons_image']['name']){
										$tmpFile = $_FILES['cons_image']['tmp_name'];
										$b_info = getimagesize($tmpFile);
										$extension = getExtension($b_info['mime']);
										
										if(empty($request['id'])){
											$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_304'].'</div>';
										}elseif($b_info['mime'] != 'image/jpeg' && $b_info['mime'] != 'image/png' && $b_info['mime'] != 'image/gif'){
											$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_171'].'</div>';
										}elseif(filesize($tmpFile) > $MAX_SIZE*1024){
											$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_305'], array('-SIZE-' => $MAX_SIZE)).'</div>';
										}else{
											$image_name = 'p-'.MD5($data['id'].'_'.$request['id'].'_'.time()).'.'.$extension;
											$copied = copy($tmpFile, BASE_PATH.'/files/proofs/'.$image_name);

											if(!$copied){
												echo '<div class="msg"><div class="error"><b>ERROR:</b> Image wasn\'t uploaded, please contact site admin!</div></div>';
											}else{
												$proof = '/files/proofs/'.$image_name;
												$db->Query("INSERT INTO `payment_proofs` (p_id, u_id, proof, proof_date, approved) VALUES('".$request['id']."', '".$data['id']."', '".$proof."', '".time()."', '0')");
												$db->Query("UPDATE `requests` SET `proof`='1' WHERE `id`='".$request['id']."'");

												$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_306'].'</div>';
											}
										}
									}else{
										$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_25'].'</div>';
									}
								}
								
								echo $errMessage;
						?>
							<form method="post" enctype="multipart/form-data">
								<div class="form-row">
									<div class="form-group col-md-6">
										<label for="proof"><?=$lang['b_308']?></label>
										<div class="custom-file" id="customFile" lang="es">
											<input type="file" class="custom-file-input" id="proof" name="cons_image" aria-describedby="fileHelp">
											<label class="custom-file-label" for="proof"><?=$lang['b_309']?></label>
										</div>
									</div>
									<div class="form-group col-md-6">
									  <label for="payout"><?=$lang['b_310']?></label>
									  <select id="payout" name="request" class="form-control custom-select">
										<?php
											$requests = $db->QueryFetchArrayAll("SELECT id, amount, UNIX_TIMESTAMP(date) AS date FROM `requests` WHERE `user`='".$data['id']."' AND (`paid`='1' AND `proof`='0') ORDER BY `date` ASC");
											foreach($requests as $request){
												echo '<option value="'.$request['id'].'"'.(isset($_GET['upload']) && $_GET['upload'] == $request['id'] ? ' selected' : '').'>'.$lang['b_312'].' #'.$request['id'].' - '.$lang['b_103'].': '.$request['amount'].' '.get_currency_symbol($config['']).' - '.$lang['b_106'].': '.date('d M Y', $request['date']).'</option>';
											}
										?>
									  </select>
									</div>
									<button type="submit" name="submit" class="btn btn-primary"><?=$lang['b_58']?></button>
								</div>
							</form>
						<?php
							} else {
								foreach($proofs as $proof){
						?>
							<div id="proof_wrapper">
							  <div class="username"><?=(empty($proof['login']) ? 'N/A' : $proof['login'])?></div>
							  <div class="imgwrapper">
							  <?php if(empty($proof['proof'])) { ?>
								<?php if($is_online && $data['id'] == $proof['u_id']) { ?>
									<a href="<?=GenerateURL('proofs&upload='.$proof['id'])?>"><img src="static/img/upload_proof.png" align="absmiddle" height="65" width="90" title="<?=($lang['b_272'].': '.$proof['login'].' - '.$lang['b_103'].': $'.$proof['amount'].' - '.date('d M Y', $proof['date']))?>"></a>
								<?php } else { ?>
									<img src="static/img/no_proof.png" align="absmiddle" height="65" width="90" title="<?=($lang['b_272'].': '.$proof['login'].' - '.$lang['b_103'].': $'.$proof['amount'].' - '.date('d M Y', $proof['date']))?>">
								<?php } ?>
							  <?php }else{ ?>
								<a class="proof_image" href="<?=$proof['proof']?>" title="<?=($lang['b_272'].': '.$proof['login'].' - '.$lang['b_103'].': $'.$proof['amount'].' - '.date('d M Y', $proof['date']))?>"><img class="proof_img" src="<?=$proof['proof']?>" align="absmiddle" height="65" width="90"></a>
							  <?php }?>
							  </div>
							  <div class="date"><?=date('d M Y', $proof['date'])?></div>
							  <div class="amount"><?=get_currency_symbol($config['']).$proof['amount']?></div>
							</div>
						<?php } ?>
						<div class="clearfix"></div>
						<?php
							if(ceil($p_data['total']/$bpp) > 1) {
								if($page == 0) {
									$left = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Previous</a></li>';
								}else{
									$left = '<li class="page-item"><a class="page-link" href="'.GenerateURL('proofs&p='.($page-1)).'">Previous</a></li>';
								}
								
								$total_pages = (number_format(($p_data['total']/$bpp), 0)-1);
								$middle = '<li class="page-item active"><a class="page-link" href="javascript:void(0)">'.($page+1).' - '.($total_pages+1).'</a></li>';

								if($page >= $total_pages) {
									$right = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Next</a></li>';
								}else{
									$right = '<li class="page-item"><a class="page-link" href="'.GenerateURL('proofs&p='.($page+1)).'">Next</a></li>';
								}
								
								echo '<nav aria-label="navigation"><ul class="pagination justify-content-center">'.$left.$middle.$right.'</ul></nav>';
							}
						?>
						<?php } ?>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>