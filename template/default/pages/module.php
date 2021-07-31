<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	if(isset($_GET['md']) && file_exists(BASE_PATH.'/system/modules/'.$_GET['md'].'/module.php')){
		$module = $_GET['md'];
	}else{
		redirect($config['site_url']);
	}
?> 
	<main role="main" class="container">
      <div class="row">
		<?php 
			require_once(BASE_PATH.'/template/'.$config['theme'].'/common/sidebar.php');
		?>
	  <div class="col-md-9">
			<div class="my-3 p-3 bg-white rounded box-shadow box-style">
				<div id="blue-box">
					<?php
						$clicks = $db->QueryFetchArray("SELECT SUM(`today_clicks`) AS `total` FROM `user_clicks` WHERE `uid`='".$data['id']."'");

						if($data['premium'] == 0 && $config['clicks_limit'] != 0 && $clicks['total'] >= $config['clicks_limit']){
							echo '<div class="alert alert-danger" role="alert">'.lang_rep($lang['b_297'], array('-CLICKS-' => $config['clicks_limit'])).'</div>';
						}else{
							include(BASE_PATH.'/system/modules/'.$module.'/module.php');
						}
					?>
				</div>
			</div>
		</div>
	  </div>
    </main>