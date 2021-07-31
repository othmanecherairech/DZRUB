<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
	
	$faqs = $db->QueryFetchArrayAll("SELECT question,answer FROM `faq` ORDER BY id ASC");
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
						<?=$lang['b_06']?>
					</div>
					<div class="content">
						<?php
							if(count($faqs) == 0){
								echo '<div class="alert alert-info" role="alert">'.$lang['b_250'].'</div>';
							}
						?>
						<div id="accordion">
						  <?php
							$j = 0;
							foreach($faqs as $faq){
								$j++;
						  ?>
						  <div class="card">
							<div class="card-header" id="heading_<?=$j?>">
							  <h5 class="mb-0">
								<button class="btn btn-link<?=($j > 1 ? ' collapsed' : '')?>" data-toggle="collapse" data-target="#faq_<?=$j?>" aria-expanded="<?=($j > 1 ? 'false' : 'true')?>" aria-controls="collapse_<?=$j?>">
								  <?=$faq['question']?>
								</button>
							  </h5>
							</div>
							<div id="faq_<?=$j?>" class="collapse<?=($j > 1 ? '' : ' show')?>" aria-labelledby="heading_<?=$j?>" data-parent="#accordion">
							  <div class="card-body text-dark">
								<?=BBCode(nl2br($faq['answer']))?>
							  </div>
							</div>
						  </div>
						  <?php } ?>
						</div>
					</div>
				</div>
			</div>
		</div>
	  </div>
    </main>