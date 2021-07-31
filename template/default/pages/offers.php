<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	/* Load offerwall settings */
	$ow_config = array();
	$ow_configs = $db->QueryFetchArrayAll("SELECT config_name,config_value FROM `offerwall_config`");
	foreach ($ow_configs as $con)
	{
		$ow_config[$con['config_name']] = $con['config_value'];
	}
	unset($ow_configs); 
	
	$method = (isset($_GET['md']) ? $_GET['md'] : 0);
	$title = $lang['b_411'];
	$not_setup = false;
	switch($method) {
		case 'bitswall' :
			if(empty($ow_config['bitswall_key']) || empty($ow_config['bitswall_secret'])) {
				$not_setup = true;
			}
			$title = 'Bitswall';
			$offer_wall = '<iframe src="https://bitswall.net/offerwall/'.$ow_config['bitswall_key'].'/'.$data['id'].'" style="width:100%;height:690px;border:0;border-radius:5px;"></iframe>';
			break;
		case 'adworkmedia' :
			if(empty($ow_config['adwork_id'])) {
				$not_setup = true;
			}
			$title = 'AdWorkMedia';
			$offer_wall = '<iframe src="https://lockwall.xyz/wall/'.$ow_config['adwork_id'].'/'.$data['id'].'" style="width:100%;height:690px;border:0;border-radius:5px;" /></iframe>';
			break;
		case 'wannads' :
			if(empty($ow_config['wannads_key'])) {
				$not_setup = true;
			}
			$title = 'Wannads';
			$offer_wall = '<iframe src="https://wall.wannads.com/wall?apiKey='.$ow_config['wannads_key'].'&userId='.$data['id'].'" style="width:100%;height:690px;border:0;border-radius:5px;"></iframe>';
			break;
		case 'jungle' :
			if(empty($ow_config['mtt_reward']) || empty($ow_config['mtt_url'])) {
				$not_setup = true;
			}
			$title = 'JungleSurvey';
			$offer_wall = '<iframe src="'.$ow_config['mtt_url'].$data['id'].'" style="width:96%;height:800px;border:0;border-radius:5px;"></iframe>';
			break;
		default :
			if(empty($ow_config['kiwiwall_id'])) {
				$not_setup = true;
			}
			$title = 'KiwiWall';
			$offer_wall = '<iframe src="https://www.kiwiwall.com/wall/'.$ow_config['kiwiwall_id'].'/'.$data['id'].'" style="width:100%;height:690px;border:0;border-radius:5px;"></iframe>';
			break;
	}
	
	if($not_setup){
		redirect($config['site_url']);
	}
?>
	<main role="main" class="container">
      <div class="row">
		<?php 
			require_once(BASE_PATH.'/template/'.$config['theme'].'/common/sidebar.php');
		?>
		<div class="col-md-9">
			<div class="my-3 ml-md-2 p-3 bg-white rounded box-shadow box-style">
			  <div id="blue-box">
				<div class="title">
					<?=$title?>
				</div>
				<div class="content">
					<?php echo $offer_wall; ?>
				</div>
			</div>
		  </div>
		</div>
	  </div>
    </main>