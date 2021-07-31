<?php
    if(! defined('BASEPATH') ){ exit('Unable to view file.'); }

	$errMessage = '';
	if(isset($_POST['submit'])){
		$job_id = $db->EscapeString($_POST['job_id']);
		$requirement = $db->EscapeString($_POST['requirement']);

		$job = $db->QueryFetchArray("SELECT * FROM `jobs` WHERE `id`='".$job_id."' LIMIT 1");
		if(empty($job['id'])) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_405'].'</div>';
		} elseif(empty($requirement)) {
			$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_25'].'</div>';
		} else {
			$check_job = $db->QueryFetchArray("SELECT * FROM `jobs_done` WHERE `uid`=".$data['id']." AND `job_id`='".$job_id."' ORDER BY `time` DESC LIMIT 1");

			if(empty($check_job['id']) || $check_job['status'] == 2) {
				$db->Query("INSERT INTO `jobs_done` (`job_id`, `uid`, `requirement`, `reward`,`type`,`time`) VALUES ('".$job_id."','".$data['id']."','".$requirement."','".$job['reward']."','".$job['type']."','".time()."')");
				$errMessage = '<div class="alert alert-success" role="alert">'.$lang['b_406'].'</div>';
			} elseif($check_job['status'] == 1) {
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_407'].'</div>';
			} else {
				$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_408'].'</div>';
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
                        <?=$lang['b_403']?>
                    </div>
                    <div class="content">
					<?php
						$jobs = $db->QueryFetchArrayAll("SELECT * FROM `jobs` ORDER BY `time` DESC");

						foreach($jobs as $job) {
							$job_status = $db->QueryFetchArray("SELECT * FROM `jobs_done` WHERE `job_id`='".$job['id']."' AND `uid`='".$data['id']."' ORDER BY `time` DESC LIMIT 1");
							
							$description = htmlspecialchars_decode($job['description']);
							$description = html_entity_decode($description);
							$description = str_replace("&lt;","<",str_replace("&gt;",">",$description));
					?>
						<div class="card mb-2">
						  <div class="card-header">
							<h1 class="text-success"><?=$job['title']?></h1>
							<p class="text-danger text-center mb-0"><?=$lang['b_404']?>*** <?=$job['']?>  <?=$lang['b_400']?>: <?=($job['type'] == 1 ? number_format($job['reward'], 0).' '.$lang['b_246'] : ($job['type'] == 2 ? get_currency_symbol($config['']).$job['reward'] : number_format($job['reward'], 0).' '.$lang['b_156']))?></p>
						  </div>
						  <div class="card-body text-dark">
							<?php 
								echo $description;

								if(empty($job_status['id']) || $job_status['status'] == 2) {
							?>
								<form method="post">
								  <input type="hidden" name="job_id" value="<?=$job['id']?>">
								  <div class="form-row">
									<div class="form-group col-md-12">
									  <div class="input-group mt-3 mb-0">
										<div class="input-group-prepend"><div class="input-group-text"><i class="fa fa-link"></i></div></div>
										<input type="text" class="form-control" name="requirement" placeholder="<?=$job['requirement']?>">
										<input type="submit" class="btn btn-primary d-inline" name="submit" value="<?=$lang['b_58']?>" />
									  </div>
									</div>
								  </div>
								</form>
							<?php
								} elseif($job_status['status'] == 0) {
									echo '<div class="alert alert-info text-center mb-0" role="alert">'.$lang['b_409'].'</div>';
								} else { 
									echo '<div class="alert alert-success text-center mb-0" role="alert">'.lang_rep($lang['b_410'], array('-REWARD-' => ($job_status['type'] == 1 ? number_format($job_status['reward'], 0).' '.$lang['b_246'] : ($job_status['type'] == 2 ? get_currency_symbol($config['']).$job_status['reward'] : number_format($job_status['reward'], 0).' '.$lang['b_156'])))).'</div>';
								}
							?>
						  </div>
						</div>
					<?php } ?>
                    
                    </div>
                </div>
            </div>
            <div align="Center" id="bannerswall_973"><script src="https://bannerswall.ru/bancode.php?id=973" async></script></div>
        </div>
      </div>
    </main>