<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$id = $db->EscapeString($_GET['x']);
	$type = hook_filter($_GET['t'].'_info', 'type');
	$table = hook_filter($_GET['t'].'_info', 'db');

	if($table == 'db'){
		redirect($config['site_url']);
	}

	$mysite = $db->QueryFetchArray("SELECT * FROM `".$table."` WHERE `id`='".$id."' AND `user`='".$data['id']."' AND `active`!='2'");
	if(!$mysite){
		redirect(GenerateURL('mypages&p='.$_GET['t']));
	}

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

	$countries = $db->QueryFetchArrayAll("SELECT * FROM `list_countries` ORDER BY country");

	$errMessage = '';
	$maxcpc = ($data['premium'] > 0 ? $config['premium_cpc'] : $config['free_cpc']);
	if(isset($_POST['delete'])){
		$db->Query("DELETE FROM `".$table."` WHERE `id`='".$id."' AND `user`='".$data['id']."'");
		redirect(GenerateURL('mypages&p='.$_GET['t']));
	}elseif(isset($_POST['update'])){
		$title = $db->EscapeString($_POST['title'], 1);
		$cpc = $db->EscapeString($_POST['cpc']);
		$status = $db->EscapeString($_POST['active']);
		$gender = $db->EscapeString($_POST['gender']);
		$gender = ($target_system ? $gender : 0);
		$daily_clicks = $db->EscapeString(isset($_POST['daily_clicks']) ? $_POST['daily_clicks'] : 0);
		$daily_clicks = ($_POST['daily_clicks_switch'] == 1 ? (is_numeric($daily_clicks) && $daily_clicks > 0 ? $daily_clicks : 0) : 0);
		$max_clicks = $db->EscapeString(isset($_POST['max_clicks']) ? $_POST['max_clicks'] : 0);
		$max_clicks = ($_POST['max_clicks_switch'] == 1 ? (is_numeric($max_clicks) && $max_clicks > $mysite['clicks'] ? $max_clicks : 0) : 0);
		$country = $db->EscapeString($_POST['country']);
		$country = ($target_system ? $country : 0);
		$sCountries = ($target_system ? ($country == 0 ? 0 : $_POST['countries']) : 0);

		$ctrs = array();
		foreach($countries as $row) {
			$ctrs[] = $row['code'];
		}

		$cSelected = '';
		if(!empty($sCountries)){
			foreach ($sCountries as $a=>$value) 
			{
				if(in_array($value, $ctrs)) {
					$cSelected .= $value.',';
				}
			}
		}
		$cSelected = (empty($cSelected) ? 0 : $cSelected);

		if($cpc < 2 || $cpc > $maxcpc || !is_numeric($cpc)){
			$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_29'], array('-MIN-' => '2', '-MAX-' => $maxcpc)).'</div>';
		}elseif($gender < 0 || $gender > 2) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_219'].'</div>';
		}elseif(empty($cSelected) && $country != '0') {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_220'].'</div>';
		}elseif($status == 2){
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_73'].'</div>';
		}else{
			$db->Query("UPDATE `".$table."` SET `title`='".$title."', `daily_clicks`='".$daily_clicks."', `max_clicks`='".$max_clicks."', `cpc`='".$cpc."', `active`='".$status."', `country`='".$cSelected."', `sex`='".$gender."' WHERE `id`='".$id."' AND `user`='".$data['id']."'");
			$mysite = $db->QueryFetchArray("SELECT * FROM `".$table."` WHERE `id`='".$id."' AND `user`='".$data['id']."' AND `active`!='2'");
			$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_74'].'</div>';
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
						<?=$lang['b_212']?>
					</div>
					<div class="content">
						<form method="post">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="url"><?=$lang['b_32']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-link"></i></div></div>
								<input type="text" class="form-control" id="url" value="<?=$mysite['url']?>" disabled>
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="active"><?=$lang['b_75']?></label>
							  <select name="active" class="custom-select" id="active">
								<option value="0"><?=$lang['b_76']?></option>
								<option value="1"<?=(isset($_POST['active']) && $_POST['active'] == 1 ? ' selected' : ($mysite['active'] == 1 ? ' selected' : ''))?>><?=$lang['b_77']?></option>
							  </select>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="title"><?=$lang['b_33']?></label>
							  <div class="input-group mb-2 mr-sm-2">
								<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-pencil"></i></div></div>
								<input type="text" class="form-control" id="title" name="title" value="<?=$mysite['title']?>">
							  </div>
							</div>
							<div class="form-group col-md-6">
							  <label for="cpc"><?=$lang['b_36']?></label>
							  <select name="cpc" class="custom-select" id="cpc"><?for($cpc = 2; $cpc <= $maxcpc; $cpc++) { echo '<option value="'.$cpc.'"'.($mysite['cpc'] == $cpc ? ' selected' : '').'>'.$cpc.' '.$lang['b_156'].'</option>';}?></select>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="daily_clicks"><?=$lang['b_347']?></label>
							  <input type="text" name="daily_clicks" class="form-control" id="daily_clicks" value="<?=($mysite['daily_clicks'] == 0 ? '' : $mysite['daily_clicks'])?>"<?=($mysite['daily_clicks'] == 0 ? ' disabled ' : '')?>/>
							</div>
							<div class="form-group col-md-6">
							  <label for="daily_clicks_switch">&nbsp;</label>
							  <select name="daily_clicks_switch" id="dailyLimitSelect" class="form-control w-50"><option value="0"><?=$lang['b_77']?></option><option value="1"<?=($mysite['daily_clicks'] > 0 ? ' selected' : '')?>><?=$lang['b_76']?></option></select>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="max_clicks"><?=$lang['b_348']?></label>
							  <input type="text" name="max_clicks" class="form-control" id="max_clicks" value="<?=($mysite['max_clicks'] == 0 ? '' : $mysite['max_clicks'])?>"<?=($mysite['max_clicks'] == 0 ? ' disabled ' : '')?>/>
							</div>
							<div class="form-group col-md-6">
							  <label for="max_clicks_switch">&nbsp;</label>
							  <select name="max_clicks_switch" id="totalLimitSelect" class="form-control w-50"><option value="0"><?=$lang['b_77']?></option><option value="1"<?=($mysite['max_clicks'] > 0 ? ' selected' : '')?>><?=$lang['b_76']?></option></select>
							</div>
						  </div>
						  <?php if($target_system){ ?>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="max_clicks"><?=$lang['b_213']?></label>
							  <select name="gender" class="form-control">
								<option value="0"><?=$lang['b_214']?></option>
								<option value="1"<?=($mysite['sex'] == 1 ? ' selected' : '')?>><?=$lang['b_215']?></option>
								<option value="2"<?=($mysite['sex'] == 2 ? ' selected' : '')?>><?=$lang['b_216']?></option>
							  </select>
								<?=$lang['b_217']?>
							  <select name="country" class="form-control" id="select-countries">
								<option value="0"><?=$lang['b_218']?></option>
								<option value="1"<?=($mysite['country'] != '0' ? ' selected' : '')?>><?=$lang['b_344']?></option>
							  </select>
							   <div id="target-select" class="mt-2" style="display:none;">
								<select id="choseCountries" class="form-control" data-placeholder="<?=$lang['b_345']?>..." name="countries[]" multiple>
									<?php
										$sc = array();
										if($mysite['country'] != '0'){
											$sCountries = explode(',', $mysite['country']);
											foreach($sCountries as $c){
												$sc[] = $c;
											}
										}
										
										foreach($countries as $country){
											echo '<option value="'.$country['code'].'"'.(in_array($country['code'],$sc) ? ' selected' : '').'>'.$country['country'].'</option>';
										}
									?>
								</select>
							 </div>
							</div>
						  </div>
						 <?php }?>
						 <p>
							<input type="submit" class="btn btn-primary" name="update" value="<?=$lang['b_79']?>">
							<input type="submit" class="btn btn-danger" name="delete" value="<?=$lang['b_81']?>" onclick="return confirm('<?=$lang['b_80']?>')">
						</p>
					  </form>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>
	<script type="text/javascript">
		$('#select-countries').on('change', function (e) {
			if ($(this).val() == '0') {
				$('#target-select').hide()
			} else {
				$('#target-select').show()
			}
		});
		$('#dailyLimitSelect').on('change', function (e) {
			if ($(this).val() == '0') {
				$("#daily_clicks").prop('disabled', true).val('');
			} else {
				$("#daily_clicks").prop('disabled', false).val('100');
			}
		});
		$('#totalLimitSelect').on('change', function (e) {
			if ($(this).val() == '0') {
				$("#max_clicks").prop('disabled', true).val('');
			} else {
				$("#max_clicks").prop('disabled', false).val('<?=($mysite['clicks'] < 1000 ? 1000 : $mysite['clicks']+1000)?>');
			}
		});
	</script>