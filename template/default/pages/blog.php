<?php
	if(! defined('BASEPATH') ){ exit('Unable to view file.'); }
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
						<?=$lang['b_287']?>
					</div>
					<div class="content">
						<?php
						if(isset($_GET['bid']) && is_numeric($_GET['bid'])){
							$id = $db->EscapeString($_GET['bid']);
							$blog = $db->QueryFetchArray("SELECT a.*, b.login FROM blog a LEFT JOIN users b ON b.id = a.author WHERE a.id = '".$id."' LIMIT 1");
							if(empty($blog['id'])){
								redirect(GenerateURL('blog'));
							}
							
							if(isset($_GET['cd']) && is_numeric($_GET['cd']) && $data['admin'] == 1){
								$id = $db->EscapeString($_GET['cd']);
								$db->Query("DELETE FROM `blog_comments` WHERE `id`='".$id."'");
							}
							
							$errMessage = '';
							if($config['blog_comments'] == 0){
								$errMessage = '<div class="alert alert-info" role="alert">'.$lang['b_280'].'</div>';
							}elseif(!$is_online){
								$errMessage = '<div class="alert alert-info" role="alert">'.$lang['b_281'].'</div>';
							}else{
								if(isset($_POST['comment']) && !empty($data['id'])){
									$comment = $db->EscapeString($_POST['comment_text']);

									if(strlen($comment) < 20 || strlen($comment) > 255){
										$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_282'].'</div>';
									}elseif($db->QueryGetNumRows("SELECT id FROM `blog_comments` WHERE `author`='".$data['id']."' AND `timestamp`>'".(time()-60)."' LIMIT 1") > 0){
										$errMessage = '<div class="alert alert-danger" role="alert">'.$lang['b_288'].'</div>';
									}else{
										$db->Query("INSERT INTO `blog_comments` (`bid`,`author`,`comment`,`timestamp`)VALUES('".$blog['id']."','".$data['id']."','".$comment."','".time()."')");
									}
								}
							}

							
							$db->Query("UPDATE `blog` SET `views`=`views`+'1' WHERE `id`='".$blog['id']."'");
							$comments = $db->QueryGetNumRows("SELECT id FROM `blog_comments` WHERE `bid`='".$blog['id']."'");
							$content = stripslashes(htmlspecialchars($blog['content']));
							$content = BBCode($content);
							$content = nl2br($content);
						?>
							<div class="card mb-2">
							  <div class="card-header">
								<a href="<?=GenerateURL('blog&bid='.$blog['id'])?>" class="text-dark"><b><?=truncate($blog['title'], 100)?></b></a>
							  </div>
							  <div class="card-body">
								<blockquote class="blockquote mb-0">
								  <p class="text-dark"><?=$content?></p>
								  <footer class="blockquote-footer"><?=$lang['b_292']?>: <i><?=number_format($blog['views'])?></i> | <?=$lang['b_284']?>: <i><?=number_format($comments)?></i> | <?=$lang['b_106']?>: <i><?=date('d M Y H:i', $blog['timestamp'])?></i></footer>
								</blockquote>
							  </div>
							</div>
						<?php
						echo $errMessage;
						if($is_online && $config['blog_comments'] == 1){
							$count = $db->QueryGetNumRows("SELECT id FROM `blog_comments` WHERE `bid`='".$blog['id']."'");
							$bpp = 5;
							$pages = ceil($count/$bpp);
							$page = (isset($_GET['p']) ? intval($_GET['p']) : 0);
							$begin = ($page >= 0 ? ($page*$bpp) : 0);

							$comments = $db->QueryFetchArrayAll("SELECT a.id, a.author, a.comment, a.timestamp, b.login FROM blog_comments a LEFT JOIN users b ON b.id = a.author WHERE a.bid = '".$blog['id']."' ORDER BY a.timestamp DESC LIMIT ".$begin.", ".$bpp);
							foreach($comments as $comm){
						?>
							<div class="comments_wrap w-100">
								<div class="content_top"><?=$comm['login']?> <span class="float-right"><small><i><?=date('d M Y H:i', $comm['timestamp'])?></i><?=($data['admin'] == 1 ? ' - <a href="'.GenerateURL('blog&bid='.$blog['id'].'&cd='.$comm['id']).'" onclick="return confirm(\'Are you sure?\');" style="color:red">Delete</a>' : '')?></small></span></div>
								<div class="content_text">
									<?=nl2br(stripslashes(htmlspecialchars($comm['comment'])))?>
								</div>
							</div>
						<?}
						if($pages > 1){?>
						<div class="infobox" style="width:580px;text-align:center;margin:10px auto 0">
						<?php
							if(ceil($count/$bpp) > 1) {
								if($page == 0) {
									$left = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Previous</a></li>';
								}else{
									$left = '<li class="page-item"><a class="page-link" href="'.GenerateURL('blog&bid='.$blog['id'].'&p='.($page-1)).'">Previous</a></li>';
								}
								
								$total_pages = (number_format(($count/$bpp), 0)-1);
								$middle = '<li class="page-item active"><a class="page-link" href="javascript:void(0)">'.($page+1).' - '.($total_pages+1).'</a></li>';

								if($page >= $total_pages) {
									$right = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Next</a></li>';
								}else{
									$right = '<li class="page-item"><a class="page-link" href="'.GenerateURL('blog&bid='.$blog['id'].'&p='.($page+1)).'">Next</a></li>';
								}
								
								echo '<nav aria-label="navigation"><ul class="pagination justify-content-center">'.$left.$middle.$right.'</ul></nav>';
							}
						?>
						</div>
						<?}?>
							<script type="text/javascript">
								var maxLength = 255;
								function charLimit(el) {
									if (el.value.length > maxLength) return false;
									return true;
								}
								function characterCount(el) {
									var charCount = document.getElementById('charCount');
									if (el.value.length > maxLength) el.value = el.value.substring(0,maxLength);
									if (charCount) charCount.innerHTML = maxLength - el.value.length;
									return true;
								}
							</script>
							<div class="blog_comment w-75"><div class="com_title"><?=$lang['b_285']?></div>
								<form method="post">
									<textarea name="comment_text" class="form-control mb-1" rows="4" onKeyPress="return charLimit(this)" onKeyUp="return characterCount(this)" required="required"></textarea>
									<input type="submit" name="comment" class="btn btn-primary btn-sm ml-1 mb-1" value="<?=$lang['b_58']?>" />
									<span class="float-right mr-1 mt-1"><strong><span id="charCount">255</span></strong> <?=$lang['b_289']?></span>
								</form>
							</div>
							<?php
									}
								}else{

									$count = $db->QueryGetNumRows("SELECT id FROM `blog`");
									$bpp = 3;
									$page = (isset($_GET['p']) ? intval($_GET['p']) : 0);
									$begin = ($page >= 0 ? ($page*$bpp) : 0);
									$blogs = $db->QueryFetchArrayAll("SELECT a.*, b.login FROM blog a LEFT JOIN users b ON b.id = a.author ORDER BY a.timestamp DESC LIMIT ".$begin.", ".$bpp);
									if(!$blogs){
										echo '<div class="alert alert-info" role="alert">'.$lang['b_250'].'</div>';
									}

									foreach($blogs as $blog){
										$comments = $db->QueryGetNumRows("SELECT id FROM `blog_comments` WHERE `bid`='".$blog['id']."'");
										$content = stripslashes(htmlspecialchars($blog['content']));
										$content = truncate($content, 275);
										$content = BBCode($content);
										$content = nl2br($content);
							?>
								<div class="card mb-2">
								  <div class="card-header">
									<a href="<?=GenerateURL('blog&bid='.$blog['id'])?>" class="text-dark"><b><?=truncate($blog['title'], 100)?></b></a>
								  </div>
								  <div class="card-body">
									<blockquote class="blockquote mb-0">
									  <p class="text-dark"><?=$content?></p>
									  <footer class="blockquote-footer"><?=$lang['b_292']?>: <i><?=number_format($blog['views'])?></i> | <?=$lang['b_284']?>: <i><?=number_format($comments)?></i> | <?=$lang['b_106']?>: <i><?=date('d M Y H:i', $blog['timestamp'])?></i> | <a href="<?=GenerateURL('blog&bid='.$blog['id'])?>" class="text-dark"><i><?=$lang['b_286']?></i></a></footer>
									</blockquote>
								  </div>
								</div>
						<?php }?>
						<?php
							if(ceil($count/$bpp) > 1) {
								if($page == 0) {
									$left = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Previous</a></li>';
								}else{
									$left = '<li class="page-item"><a class="page-link" href="'.GenerateURL('blog&p='.($page-1)).'">Previous</a></li>';
								}
								
								$total_pages = (number_format(($count/$bpp), 0)-1);
								$middle = '<li class="page-item active"><a class="page-link" href="javascript:void(0)">'.($page+1).' - '.($total_pages+1).'</a></li>';

								if($page >= $total_pages) {
									$right = '<li class="page-item disabled"><a class="page-link" href="javascript:void(0)">Next</a></li>';
								}else{
									$right = '<li class="page-item"><a class="page-link" href="'.GenerateURL('blog&p='.($page+1)).'">Next</a></li>';
								}
								
								echo '<nav aria-label="navigation"><ul class="pagination justify-content-center">'.$left.$middle.$right.'</ul></nav>';
							}
						?>
						<?php } ?>
					</div>
				</div>
			</div>
            <div align="Center" id="bannerswall_978"><script src="https://bannerswall.ru/bancode.php?id=978" async></script></div> 
		</div>
	  </div>
    </main>