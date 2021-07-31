<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
	
	$show_banner = true;
	if ($is_online && $data['membership'] > 0) {
		$show_banner = false;
	}

	if($config['banner_system'] != 0 && $show_banner){
		$ad_banner = $db->QueryFetchArray("SELECT `code` FROM `ad_codes` WHERE `status`='1' AND (`size`='0' OR `size`='1') ORDER BY rand() LIMIT 1");

		$internal_banner = true;
		if(!empty($ad_banner['code']) && rand(1,2) == 1 && $is_online)
		{
			$internal_banner = false;
		}
		
		if($internal_banner)
		{
			$banner = $db->QueryFetchArray("SELECT id,banner_url FROM `banners` WHERE `expiration`>'".time()."' ORDER BY rand() LIMIT 1");
			if(!empty($banner['id']))
			{
				$db->Query("UPDATE `banners` SET `views`=`views`+'1' WHERE `id`='".$banner['id']."'");

				echo '<div class="container"><div class="mx-auto"><div class="my-3 py-3 px-md-2 bg-white rounded box-shadow box-style text-center">';
				echo '<span class="mx-1"><a href="'.$config['secure_url'].'/?go_banner='.$banner['id'].'" target="_blank"><img src="'.$config['secure_url'].$banner['banner_url'].'" class="img-fluid" alt="Banner #'.$banner['id'].'" border="0" /></a></span>';
				echo '</div></div></div>';
			}
			elseif(!empty($ad_banner['code']))
			{
				$banner_code = htmlspecialchars_decode($ad_banner['code']);
			
				echo '<div class="container"><div class="mx-auto"><div class="my-3 py-3 px-md-2 bg-white rounded box-shadow box-style text-center">';
				echo '<span class="mx-1">'.$banner_code.'</span>';
				echo '</div></div></div>';
			}
		}
		elseif(!empty($ad_banner['code']))
		{
			$banner_code = htmlspecialchars_decode($ad_banner['code']);
			
			echo '<div class="container"><div class="mx-auto"><div class="my-3 py-3 px-md-2 bg-white rounded box-shadow box-style text-center">';
			echo '<span class="mx-1">'.$banner_code.'</span>';
			echo '</div></div></div>';
		}
	}
?>
	<script type="text/javascript">
		var url = window.location.href;
		function langSelect(selectobj){
			if(url.indexOf("?") > 0) {
				url += '&peslang='+selectobj;
			} else {
				url += '?peslang='+selectobj;
			}
			window.location.replace(url)
		} 
	</script>
	<div class="clearfix"></div>
	<footer class="footer mt-3">
		<nav class="navbar static-bottom navbar-expand-sm navbar-dark">
		 <div class="container">
		  <span class="navbar-brand copyright"><b>RUBdz v<?=$config['version']?> </b>
          <?=eval(base64_decode(''))?></span>
		  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#footer_collapse" aria-controls="footer_collapse" aria-expanded="false">
			<span class="navbar-toggler-icon"></span>
		  </button>
		  <div class="collapse navbar-collapse" id="footer_collapse">

<div align="Center" id="bancode_6698"><script src="//multibux.org/bancode.php?id=6698" async></script></div>

			<ul class="navbar-nav ml-auto">

<div id="bancode_6697"><script src="//multibux.org/bancode.php?id=6697" async></script></div>


			  <li class="nav-item dropup">
				<a class="nav-link dropdown-toggle" href="javascript:void(0)" id="footer_menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-bars"></i> <?=$lang['b_399']?></a>
				<div class="dropdown-menu" aria-labelledby="footer_menu">
				  <?php if($config['allow_withdraw'] == 1) { ?>
					<a class="dropdown-item" href="<?=GenerateURL('proofs')?>"><?=$lang['b_303']?></a>
				  <?php } ?>
				  <a class="dropdown-item" href="<?=GenerateURL('stats')?>"><?=$lang['b_82']?></a>
				  <a class="dropdown-item" href="<?=GenerateURL('faq')?>"><?=$lang['b_06']?></a>
				  <a class="dropdown-item" href="<?=GenerateURL('contact')?>"><?=$lang['b_47']?></a>
				</div>
			  </li>
			  <li class="nav-item dropup">
				<a class="nav-link dropdown-toggle" href="javascript:void(0)" id="language_menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-language"></i> <?=$lang['b_398']?></a>
				<div class="dropdown-menu" aria-labelledby="language_menu">
				  <?=$lang_select?>
				</div>
			  </li>
			</ul>
		  </div>
		 </div>
		</nav>
	  </footer>
	<?php if(!empty($config['analytics_id'])) { ?>
	<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $config['analytics_id']; ?>"></script>
	<script>
	  window.dataLayer = window.dataLayer || [];
	  function gtag(){dataLayer.push(arguments);}
	  gtag('js', new Date());

	  gtag('config', '<?php echo $config['analytics_id']; ?>');
	</script>
	<?php 
		}

		// Detect AdBlock
		if($is_online)
		{
	?>
		<script src="static/js/noadblock.js"></script>
		<script> if(typeof  NoAdBlock === 'undefined') {$(document).ready(function() {window.location.replace("<?php echo GenerateURL('locked', true); ?>")});} else {<?php echo (!isset($adblock_locked) ? 'noAdBlock.on(true, function() {window.location.replace("'.GenerateURL('locked', true).'")});' : 'noAdBlock.on(false, function() {window.location.replace("'.$config['secure_url'].'")});'); ?>} </script>
	<?php } ?>
	
  </body>
</html>