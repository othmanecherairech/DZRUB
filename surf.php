<?php
	define('BASEPATH', true);
	require('system/init.php');
	if(!$is_online){
		redirect($config['site_url']);
	}

	$surfType = ($data['premium'] > 0 && isset($config['vip_surf_type']) ? $config['vip_surf_type'] : $config['surf_type']);

	if($surfType == 2){
		redirect('index.php');
	}

	$x = parse_url($config['site_url']);

	if(isset($_GET['skip']) && is_numeric($_GET['skip']))
	{
		$skip = $db->EscapeString($_GET['skip']);
		if($db->QueryGetNumRows("SELECT * FROM `surfed` WHERE `site_id`='".$skip."' AND `user_id`='".$data['id']."' LIMIT 1") == 0)
		{
			$db->Query("INSERT INTO `surfed` (user_id, site_id) VALUES('".$data['id']."', '".$skip."')");
		}
	}

	$skip_msg = (isset($_GET['skip']) && isset($_GET['bd']) ? 'Previous website was skipped because frame breaker was detected!' : '');

	if($config['banner_system'] != 0)
	{
		$ad_banner = $db->QueryFetchArray("SELECT `code` FROM `ad_codes` WHERE `status`='1' AND `size`='0' ORDER BY rand() LIMIT 1");
		$banner = $db->QueryFetchArray("SELECT id,banner_url FROM `banners` WHERE `expiration`>'".time()."' ORDER BY rand() LIMIT 1");

		$valid = true;
		if(!empty($ad_banner['code']) && rand(1,2) == 1)
		{
			$valid = false;
		}
		
		if(!empty($banner['id']) && $valid)
		{
			$db->Query("UPDATE `banners` SET `views`=`views`+'1' WHERE `id`='".$banner['id']."'");
			$banner_code = '<a href="'.$config['site_url'].'/?go_banner='.$banner['id'].'" target="_blank"><img src="'.$banner['banner_url'].'" style="max-width:468px" class="img-fluid" border="0" /></a>';
		}
		else
		{
			$banner_code = htmlspecialchars_decode($ad_banner['code']);
		}
	}

	$dbt_value = '';
	if($config['target_system'] != 2){
		$dbt_value = " AND (a.country = '0' OR FIND_IN_SET('".$data['country']."', a.country)) AND (a.sex = '".$data['sex']."' OR a.sex = '0')";
	}

	$sit['id'] = 0;
	if($surfType != 1)
	{
		$sit = $db->QueryFetchArray("SELECT a.id, a.url, a.title, a.cpc FROM surf a LEFT JOIN users b ON b.id = a.user LEFT JOIN surfed c ON c.user_id = '".$data['id']."' AND c.site_id = a.id WHERE (a.confirm = '0' AND a.active = '0') AND (a.max_clicks > a.clicks OR a.max_clicks = '0') AND (a.daily_clicks > a.today_clicks OR a.daily_clicks = '0') AND (b.coins >= a.cpc AND a.cpc >= '2') AND (c.site_id IS NULL AND a.user !='".$data['id']."')".$dbt_value." ORDER BY a.cpc DESC, b.premium DESC, RAND() LIMIT 1");
	}
	elseif(isset($_GET['sid']) && is_numeric($_GET['sid']))
	{
		$sid = $db->EscapeString($_GET['sid']);
		if($db->QueryGetNumRows("SELECT * FROM `surfed` WHERE `site_id`='".$sid."' AND `user_id`='".$data['id']."' LIMIT 1") == 0)
		{
			$sit = $db->QueryFetchArray("SELECT a.id, a.url, a.title, a.cpc FROM surf a LEFT JOIN users b ON b.id = a.user LEFT JOIN surfed c ON c.user_id = '".$data['id']."' AND c.site_id = a.id WHERE a.id = '".$sid."' AND (a.confirm = '0' AND a.active = '0') AND (a.max_clicks > a.clicks OR a.max_clicks = '0') AND (a.daily_clicks > a.today_clicks OR a.daily_clicks = '0') AND (b.coins >= a.cpc AND a.cpc >= '2') AND (c.site_id IS NULL AND a.user !='".$data['id']."')".$dbt_value." LIMIT 1");
		}
	}

	if($config['surf_time_type'] == 1)
	{
		$surf_time = ($config['surf_time']*($sit['cpc']-1));
	}
	else
	{
		$surf_time = $config['surf_time'];
	}

	if($sit['id'] > 0)
	{
		$key = $surf_time+time();
		
		$result	= $db->Query("INSERT INTO `module_session` (`user_id`,`page_id`,`ses_key`,`module`,`timestamp`)VALUES('".$data['id']."','".$sit['id']."','".$key."','surf','".time()."') ON DUPLICATE KEY UPDATE `ses_key`='".$key."'");
	
		// Detect YouTube URL
		$youtube = getYoutubeID($sit['url']);
		if($youtube !== false)
		{
			$sit['url'] = 'http://www.youtube.com/embed/'.$youtube.'?autoplay=1';
		}
	}

	// Generate Security Token
	$token = GenSurfToken();
?>
<html style="overflow-y: hidden;">
<head><title><?=(!empty($sit['title']) ? $sit['title'].' - '.$config['site_name'] : $config['site_name'])?></title>
    <link rel="stylesheet" href="system/modules/surf/static/css.css" type="text/css">
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.2/jquery.min.js"></script>
	<script type="text/javascript" src="system/modules/surf/static/js.js"></script>
</head>
<body style="overflow:hidden; margin:0px; height:100%;">
<?php if(!empty($sit['id'])) {?>
	<script type="text/javascript">
		var domain = '<?=$x['host']?>';
		var auto_surf = <?=($surfType == 1 ? 0 : 1)?>;
		var token = '<?=$token?>';
		var sid = '<?=$sit['id']?>';
		var hash = '<?=MD5(rand(1000,9999))?>';
		var barsize = 1;
		var maxbarsize = 250;
		var numbercounter = <?=$surf_time?>;
		var numbercounter_n = <?=$surf_time?>;
		var adtimer = null;
		var focusFlag = 1;
		var fc_override = <?=($config['surf_fc_req'] == 1 ? 0 : 1)?>;
		var fc_skip = <?=($config['surf_fb_skip'] == 1 ? 1 : 0)?>;
		var buster_listener = 1;
		var buster = 0;
		var buster_red = '?skip=<?=$sit['id']?>&bd';
		var surf_file = 'surf.php';
		var can_leave = <?=($sit['id'] == 0 ? 'true' : 'false')?>;
		var report_msg1 = '<?=$db->EscapeString($lang['b_277'])?>';
		var report_msg2 = '<?=$db->EscapeString($lang['b_236'])?>';
		var report_msg3 = '<?=$db->EscapeString($lang['b_237'])?>';
		eval(function(p,a,c,k,e,r){e=function(c){return c.toString(a)};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('4 7(a,b,c){8 e=9(f);g(e){$.h({i:"j",5:"k/l.m",n:o,p:"q="+a+"&5="+b+"&r="+c+"&s="+e,t:4(d){u(d){6\'1\':0(v);w(a,\'1\');3;6\'2\':0(x);3;y:0(z);3}}})}}',36,36,'alert|||break|function|url|case|report_page|var|prompt||||||report_msg1|if|ajax|type|POST|system|report|php|cache|false|data|id|module|reason|success|switch|report_msg2|skipuser|report_msg4|default|report_msg3'.split('|'),0,{}))
		window.onbeforeunload = <?=($config['surf_fb_skip'] == 1 ? 'bust' : 'function () {if (can_leave == false) {var a = "";var b = b || window.event;if (b) {b.returnValue = a;}return a;}}')?>;
	</script>
<?php } ?>
    <table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
      <tr>
        <td id="surfbar_td" align="center" height="68" valign="top">
          <div style='height:100%;'>
          
          <center>
            <table style="color:white;" width="100%" border="0" height="100%">
              <tr>
                <td class="logo"><?=$config['site_logo']?></td>
                
                <td width="470" class="nowrap" align="center" valign="center">
                <div align="Center" id="bancode_6689"><script src="//multibux.org/bancode.php?id=6689" async></script></div>
				  <?php if(!empty($sit['id'])){?>
                  
                  <div id="loadingdiv"><img src="system/modules/surf/static/img/loader.gif" border="0" alt="Loading..." /></div>
                  <div id="timerdiv" style='display:none;'>
                    <center>
                    <table cellpadding="0" cellspacing="0">
                      <tr>
                        <td>
                          <div style='width:304px;'>
                          <table cellpadding="0" cellspacing="0" width="100%"><tr>
                            <td width="250" style="background:white; border:2px solid #f0f0f0;">
                              <div id="progressbar" style="background:url('system/modules/surf/static/img/progressbar.gif'); width:1px; float:left; height:22px;"></div>
                            </td>
                            <td style="background:#f0f0f0;">
                              <div style="color:black; font-size:16px; line-height:22px; font-family:arial; font-weight:bold; text-align:center;" id="numbercounterspan"><?=$surf_time?></div>
                            </td>
                          </tr></table>
                          </div>                 
                        </td>
                      </tr>
                    </table>
                    </center>
                  </div>
                  <div id="focusdiv" style='display:none;'>
                    <center>
                      <table cellpadding="0" cellspacing="0" style='color:white;' border="0"><tr>
                        <td width="40"><img src="system/modules/surf/static/img/errormsg.png"></td>
                        <td>
                          <div style="padding:10px;">
                            <b>You need to keep this window in focus.</b><br>
                            <a style="color:white;" href='javascript:void(0);'>Click <u>here</u> to continue</a>
                          </div>
                        </td>
                      </tr></table>
                    </center>
                  </div>
                  <div id="result_msg" style="position:relative; display:none">
                     <table valign="center" align="center" cellpadding="0" cellspacing="0" width="100%"><tbody><tr>
                       <td><div style="color: white; opacity: 1;" id="show_msg"><img src="system/modules/surf/static/img/loader.gif" border="0" alt="Loading..." /></div></td>
                     </tr></tbody></table>
                  </div>
				<?php }?>
				</td>
                <td id="bannertd" style="display:none;"><?if(!empty($banner_code)){?><div class='bannerrotator bannerrotator_clickads'><?=$banner_code?></div><?}?></td>
                <td align="right" valign="top">                  
				<?php if(!empty($sit['id'])) { ?>
                  <img class="cursor" onclick="buster_listener=0; openInNewWindow('<?=$sit['url']?>');" src="system/modules/surf/static/img/icon_openadtab.png" align="absmiddle" width="11" height="10" title="Open website in a new tab">
				  <a href="javascript:void(0)" onclick="report_page('<?=$sit['id']?>','<?=base64_encode($sit['url'])?>','surf');"><img src="system/modules/surf/static/img/report.png" alt="Report" title="Report" border="0"></a>
				<?php }?>
				</td>
              </tr>
            </table>
          </center>
          </div>
          <script>
              checkbanner();
              function checkbanner() {
                  var w = $(document).width();
                  if(w>1340) $(getObject('bannertd')).fadeIn('medium');
                  else $(getObject('bannertd')).fadeOut('medium');
              }
              $(window).resize(function() {
                  checkbanner();
              });
			  startbusterbreaker();
			  window.setTimeout(function() {showtimer();}, 0);
          </script>
        </td>
      </tr>
	  <tr><td id="skipped_td"><?if($sit['id'] > 0){?><?=$lang['b_143']?>: <?=number_format($data['coins'])?> | <?=lang_rep($lang['b_144'], array('-NUM-' => ($sit['cpc']-1)))?> | <a href="?skip=<?=$sit['id']?>"><?=$lang['b_145']?></a><?} if(!empty($skip_msg)){?><span style="float:right"><?=$skip_msg?></span><?}?></td></tr>
      <tr style='height:100%;background:white;'>
        <td>
          <iframe id="pes_frame" src="<?=($sit['id'] == '' ? ($surfType != 1 ? 'system/modules/surf/static/nocoins.html' : 'system/modules/surf/static/nopage.html') : hideref($sit['url'], ($config['hideref'] == 1 ? 1 : ($config['hideref'] == 2 ? 2 : 0))))?>" frameborder="0" style="width:100%; height:100%; overflow-x:hidden;" vspace="0" hspace="0"></iframe>
        </td>
      </tr>
    </table>
	<?php if(!empty($config['analytics_id'])) { ?>
	<script type="text/javascript">
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', '<?=$config['analytics_id']?>']);
	  _gaq.push(['_trackPageview']);

	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	</script>
	<?php } ?>
</body>
</html>
<?php
	$db->Close();
?>