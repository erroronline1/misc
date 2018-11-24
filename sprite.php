<?php
if ($_FILES['svg']){

	$symbol=array();
	if ($_FILES['sprite']['tmp_name']){
		$fp = fopen($_FILES['sprite']['tmp_name'], "r") or die ("Kann vorhandene Datei nicht lesen.");
		$fcontent = fread($fp, filesize($_FILES['sprite']['tmp_name']));
		preg_match_all('/(<symbol.*?id="(.*?)".*?\/symbol>)/is',$fcontent,$existing,PREG_SET_ORDER);	
		foreach ($existing as $set){
			$symbol[$set[2]]=$set[1]."\n";
		}
		fclose($fp);
	}

	foreach($_FILES['svg']['tmp_name'] as $index=>$void){
		$fp = fopen($_FILES['svg']['tmp_name'][$index], "r") or die ("Kann Datei zur Erg&auml;nzung nicht lesen.");
		$fcontent = fread($fp, filesize($_FILES['svg']['tmp_name'][$index]));
		$id=substr($_FILES['svg']['name'][$index],0,strpos($_FILES['svg']['name'][$index],"."));
		preg_match('/.*viewBox="(.*?)".*/is',$fcontent,$viewbox);
		preg_match('/.*<path(.*?)>.*/is',$fcontent,$path);
		if (!array_key_exists($id,$symbol)) $symbol[$id]='<symbol id="'.$id.'" viewBox="'.$viewbox[1].'"><title id="'.$id.'-title">'.$id.'</title><path'.$path[1].(substr($path[1],-1)=="/"?"":"/").'></symbol>';
		fclose($fp);
		unlink($_FILES['svg']['tmp_name'][$index]);
	}

	unlink($_FILES['sprite']['tmp_name']);
	ksort($symbol);

	$dump='<svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
';
	foreach($symbol as $out) $dump.=str_replace("\n","",$out)."\n";
	$dump.="</svg>";

	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=".(!$_POST['filename']?"sprite":strtok($_POST['filename'],".")).".svg");
	ob_start();	
	echo $dump;
	ob_end_flush();
}
else {?>
<!DOCTYPE html>
<html lang="de"><head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>SVG-Sprite Generator</title>
<link href='https://fonts.googleapis.com/css?family=Raleway:100' rel='stylesheet' type='text/css'>
<link rel="icon" href="http://erroronline.one/column1/icon128.png" type="image/png" sizes="128x128" />
<link rel="apple-touch-icon" href="http://erroronline.one/column1/icon192.png" sizes="192x192" />
<link rel="icon" href="http://erroronline.one/column1/icon192.png" type="image/png" sizes="192x192" />
<link rel="shortcut icon" type="image/x-icon" href="http://erroronline.one/column1/icon256.ico" />
<link rel="mask-icon" href="http://erroronline.one/column1/icon.svg" />
<meta property="og:image" content="http://erroronline.one/column1/icon192.png">
<meta property="twitter:image" content="http://erroronline.one/column1/icon192.png">
<meta property="og:image:width" content="192">
<meta property="og:image:height" content="192">
<link rel="image_src" href="http://erroronline.one/column1/icon192.png">
</head>
<style type="text/css">
body{font-size:1em; font-family:Raleway; margin:auto;}
input, select, label, textarea {font-size:1em; font-family:Raleway; float:right; display:inline-block;}
#header {max-width:25em; margin:auto; padding:.1em; position:relative; top:0; z-index:1}
#form {max-width: 25em; background-color:#ccc; margin:auto; margin-bottom:3em; padding:.1em;}
#content {position:relative; z-index:2; background-color:#ccc;}
.nopanel{min-height:1em; line-height:2em; padding:.2em;}
.panel{min-height:1em; line-height:2em; background-color:#fff; border-radius:.2em; margin-bottom:.1em; padding:.2em; overflow:auto;}
img {max-width:100%;}
h1, h2 {font-variant:small-caps;}
h1 {font-size:6em;}
</style>
<body>
<form action="sprite.php" target="spritedump" method="post" enctype="multipart/form-data" id="form">
<div class="panel" id="header" style=" text-align:center;"><h2>assemble your own</h2><h1>sprite</h1></div>
<div id="content">
<div class="nopanel">input</div>
<div class="panel">ggf. svg-sprite zur erweiterung <input type="file" name="sprite" size="8" /></div>
<div class="panel">svg-dateien hinzuf&uuml;gen <input type="file" name="svg[]" multiple="multiple" size="8" /></div>
<div class="nopanel">output</div>
<div class="panel">dateiname (falls nicht erweitert wird) <input type="text" name="filename" placeholder="sprite.svg" size="8" /></div>
<div class="nopanel"><input type="submit" value="generieren" /><br /></div>
<small>
<code>
&lt;svg xmlns="http://www.w3.org/2000/svg" style="display: none;"&gt;<br />
&lt;symbol id="inputfile" viewBox="0 0 448 512"&gt;<br />
&lt;title id="inputfile-title"&gt;inputfile&lt;/title&gt;<br />
&lt;path lorem ipsum /&gt;<br >
&lt;/symbol&gt;<br />
...<br />
&lt;/svg&gt;<br /><br />
</code>
gleiche ids werden nicht ersetzt sondern die urspr&uuml;nglichen daten beibehalten!
</small>
<div class="panel"><a href="http://erroronline.one"><img src="/../column1/icon.svg" height="64" title="error on line 1" alt="error on line 1" style="width:100%;" /></a></div>
</div>
</div>
</form>
<script language="javascript">
	function el(ement){return document.getElementById(ement);}
	window.addEventListener("scroll", function(){el('header').style.top = (window.pageYOffset / 3 )+'px';}, false);
</script>
</body>
</html>
<?php }?>