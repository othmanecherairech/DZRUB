<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$can_add = true;
	if($config['req_clicks'] > 0 && $data['premium'] == 0)
	{
		$check = $db->QueryFetchArray("SELECT SUM(`total_clicks`) AS `clicks` FROM `user_clicks` WHERE `uid`='".$data['id']."'");
		
		if($check['clicks'] < $config['req_clicks'])
		{
			$can_add = false;
		}
	}

	if($can_add)
	{
		$maxcpc = ($data['premium'] > 0 ? $config['premium_cpc'] : $config['free_cpc']);
		$error = 1;
		$errMessage = '';

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

		if(isset($_POST['type']))
		{
			$type = $db->EscapeString($_POST['type']);
			$cpc = $db->EscapeString($_POST['cpc']);
			$gender = $db->EscapeString($_POST['gender']);
			$gender = ($target_system ? $gender : 0);
			$daily_clicks = $db->EscapeString(isset($_POST['daily_clicks']) ? $_POST['daily_clicks'] : 0);
			$daily_clicks = ($_POST['daily_clicks_switch'] == 1 ? (is_numeric($daily_clicks) && $daily_clicks > 0 ? $daily_clicks : 0) : 0);
			$max_clicks = $db->EscapeString(isset($_POST['max_clicks']) ? $_POST['max_clicks'] : 0);
			$max_clicks = ($_POST['max_clicks_switch'] == 1 ? (is_numeric($max_clicks) && $max_clicks > 0 ? $max_clicks : 0) : 0);
			$ctr = $db->EscapeString($_POST['country']);
			$ctr = ($target_system ? $ctr : 0);
			$sCountries = ($target_system ? ($ctr == 0 ? 0 : $_POST['countries']) : 0);

			$ctrs = array();
			foreach($countries as $row) {
				$ctrs[] = $row['code'];
			}

			$country = '';
			if(!empty($sCountries)){
				foreach ($sCountries as $a=>$value) 
				{
					if(in_array($value, $ctrs)) {
						$country .= $value.',';
					}
				}
			}
			$country = (empty($country) ? 0 : $country);

			if($cpc < 2 || $cpc > $maxcpc || !is_numeric($cpc)){
				$errMessage = '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_29'], array('-MIN-' => '2', '-MAX-' => $maxcpc)).'</div>';
			}elseif($gender < 0 || $gender > 2) {
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_219'].'</div>';
			}elseif(empty($country) && $ctr != '0') {
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_220'].'</div>';
			}else{
				include('system/modules/'.$type.'/addsite.php');
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
						<?=$lang['b_30']?>
					</div>
					<div class="content">
					<?php if(!$can_add) { ?>
						<div class="alert alert-danger" role="alert"><?=lang_rep($lang['b_394'], array('-CLICKS-' => $config['req_clicks']))?></div>
					<?php } else { ?>
					 <form method="post">
						<p>
							<label><?=$lang['b_31']?></label> <br/>
							<select class="custom-select" name="type" id="type">
								<option value='0'></option>
								<?=hook_filter('add_site_select', "")?>
							</select> 
							<span id="load"></span>
						</p>
						<div id="custom_fields"></div>
						<div id="other_fields_msg"><div class="infobox"><center><b><?=$lang['b_302']?></b></center></div></div>
						<span id="other_fields" style="display:none">
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="cpc"><?=$lang['b_36']?></label>
							  <select id="cpc" name="cpc" class="custom-select">
								<?for($cpc = 2; $cpc <= $maxcpc; $cpc++) { echo (isset($_POST["cpc"]) && $_POST["cpc"] == $cpc ? '<option value="'.$cpc.'" selected>'.$cpc.'</option>' : (!isset($_POST["cpc"]) && $cpc == $maxcpc ? '<option value="'.$cpc.'" selected>'.$cpc.' '.$lang['b_156'].'</option>' : '<option value="'.$cpc.'">'.$cpc.' '.$lang['b_156'].'</option>'));}?>
							  </select>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="daily_clicks"><?=$lang['b_347']?></label>
							  <input type="text" class="form-control" name="daily_clicks" id="daily_clicks" disabled />
							</div>
							<div class="form-group col-md-6">
							  <label for="dailyLimitSelect">&nbsp;</label>
							  <select name="daily_clicks_switch" id="dailyLimitSelect" class="form-control w-50">
								<option value="0"><?=$lang['b_77']?></option>
								<option value="1"><?=$lang['b_76']?></option>
							  </select>
							</div>
						  </div>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="max_clicks"><?=$lang['b_348']?></label>
							  <input type="text" class="form-control" name="max_clicks" id="max_clicks" disabled />
							</div>
							<div class="form-group col-md-6">
							  <label for="totalLimitSelect">&nbsp;</label>
							  <select name="max_clicks_switch" id="totalLimitSelect" class="form-control w-50">
								<option value="0"><?=$lang['b_77']?></option>
								<option value="1"><?=$lang['b_76']?></option>
							  </select>
							</div>
						  </div>
						  <?php if($target_system){ ?>
						  <div class="form-row">
							<div class="form-group col-md-6">
							  <label for="max_clicks"><?=$lang['b_213']?></label>
							  <select name="gender" class="form-control">
								<option value="0"><?=$lang['b_214']?></option>
								<option value="1"><?=$lang['b_215']?></option>
								<option value="2"><?=$lang['b_216']?></option>
							  </select>
								<?=$lang['b_217']?>
							  <select name="country" class="form-control" id="select-countries">
								<option value="0"><?=$lang['b_218']?></option>
								<option value="1"><?=$lang['b_344']?></option>
							  </select>
							   <div id="target-select" class="mt-2" style="display:none;">
								<select id="choseCountries" class="form-control" data-placeholder="<?=$lang['b_345']?>..." name="countries[]" multiple>
									<?php
										foreach($countries as $country){
											echo '<option value="'.$country['code'].'">'.$country['country'].'</option>';
										}
									?>
								</select>
							 </div>
							</div>
						  </div>
						 <?php }?>
						 <p><input type="submit" class="btn btn-primary" value="<?=$lang['b_37']?>"></p>
						</span>
					 </form>
					<?php } ?>
				 
                 
                 <div align="Center" id="bannerswall_970"><script src="https://bannerswall.ru/bancode.php?id=970" async></script></div>
                 
                 
                  </div>


				</div>
			</div>



		</div>
	  </div>
    </main>
	<script type="text/javascript"> 
		$('#type').on('change', function(e) {
			var b = $("#type").val();
			if (b == '0') {
				$('#other_fields_msg').show();
				$('#custom_fields').hide();
				$('#other_fields').hide()
			} else {
				$('#load').html('<span class="d-flex justify-content-center mt-3"><i class="fa fa-refresh fa-spin fa-3x fa-fw"></i></span>');
				$.get('system/modules/' + b + '/add_form.php', function(a) {
					$('#custom_fields').html(a);
					$('#other_fields_msg').hide();
					$('#custom_fields').show();
					$('#other_fields').show();
					$('#load').remove()
				})
			}
		});
		$('#select-countries').on('change', function(e) {
			if ($(this).val() == '0') {
				$('#target-select').hide()
			} else {
				$('#target-select').show()
			}
		});
		$('#dailyLimitSelect').on('change', function(e) {
			if ($(this).val() == '0') {
				$("#daily_clicks").prop('disabled', true).val('')
			} else {
				$("#daily_clicks").prop('disabled', false).val('100')
			}
		});
		$('#totalLimitSelect').on('change', function(e) {
			if ($(this).val() == '0') {
				$("#max_clicks").prop('disabled', true).val('')
			} else {
				$("#max_clicks").prop('disabled', false).val('1000')
			}
		});
	</script>