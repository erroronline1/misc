<?php

if ($_POST['dia'] && $_POST['px']){

$dia=$_POST['dia'];
$px=$_POST['px'];
$units=$_POST['units'];
$showruler=$_POST['showruler'];
$rotate=$_POST['rotate'];
$tofibo=$_POST['tofibo'];
$degfibo=$_POST['degfibo'];
$notice=$_POST['notice'];
$panning=$_POST['panning'];
$bgcol=$_POST['bgcol'];
$fgcol=$_POST['fgcol'];
$font="../column1/raleway-thin.ttf";
$pixel=preg_split("/\D+/",$px);
$dia=str_replace(",",".",$dia);

//settings
	$ruler = imagecreatetruecolor($pixel[1]*$panning, $pixel[0]);
	$bg = imagecolorallocate($ruler, hexdec(substr($bgcol,1,2)), hexdec(substr($bgcol,3,2)), hexdec(substr($bgcol,5,2))); imagefilledrectangle($ruler,0,0,$pixel[1]*$panning,$pixel[0],$bg);
	$fc = imagecolorallocate($ruler, hexdec(substr($fgcol,1,2)), hexdec(substr($fgcol,3,2)), hexdec(substr($fgcol,5,2)));
	$fontsize=array(2,1);
	$uf=array(array(25.4,10,2.54),array(12,12,1)); //unitfactors
	$unit=$units=="mm"?0:1;
	
//pixeldichte
	$ratio=$pixel[1]/$pixel[0];
	$alpha=atan($ratio);
	$height=cos($alpha)*$dia; // in "
	$width=$height*$ratio*$panning; // in "
	$ppmm=$pixel[0]/$height/$uf[$unit][0];	

//lineal
if ($showruler){
	for ($i=$ppmm; $i<$pixel[0];$i+=$ppmm){
		$ten++;
		imageline($ruler,0,$i, $ten%$uf[$unit][1]?($ten%($uf[$unit][1]/2)?$ppmm*1:$ppmm*1.4):$ppmm*2 ,$i, $fc);
		if ($ten%$uf[$unit][1]==0) imagettftext($ruler, $ppmm*$fontsize[$unit], 0, $ppmm*2.5, $i+($fontsize[$unit]/2), $fc, $font, $ten/$uf[$unit][1]);
	}
}


//fibonacci/surface - degrees
if ($tofibo){
	//berechne fibonacchi-folge für maximal 13 schritte und bestimme maximale dimensionen für den ausgabebildschirm
	//der start[]-punkt von der oberen rechten ecke aus wird an dieser stelle in einheiten berechnet (cm / zoll)
	$fibonacci=array(1,1);
	if ($rotate) {$start=array(1,2); $maxwidth=1; $maxheight=2; }//minimum presets
	else		 {$start=array(2,0); $maxwidth=2; $maxheight=1; }//minimum presets
	for ($i=2;$i<13;$i++){
		$fibonacci[$i]=$fibonacci[$i-1]+$fibonacci[$i-2];


		if ($rotate){
			$maxheight+= $i%2!=0?$fibonacci[$i]:0;
			if ($maxheight>$height*$uf[$unit][2]) {$maxheight-= $i%2!=0?$fibonacci[$i]:0; break;}
			else $start[1]+= $i>4&&($i-1)%4==0 ? $fibonacci[$i]:0;

			$maxwidth+= $i%2==0?$fibonacci[$i]:0;
			if ($maxwidth>$width*$uf[$unit][2]) break;
			else $start[0]+= $i%4==0 ? $fibonacci[$i]:0; 

			}
		else {
			$maxwidth+= $i%2!=0?$fibonacci[$i]:0;
			if ($maxwidth>$width*$uf[$unit][2]) break;
			else $start[0]+= $i>4&&($i-1)%4==0 ? $fibonacci[$i]:0;
	
			$maxheight+= $i%2==0?$fibonacci[$i]:0;
			if ($maxheight>$height*$uf[$unit][2]) {$maxheight-= $i%2==0?$fibonacci[$i]:0; break;}
			else $start[1]+= $i==2||($i-2)%4==0 ? $fibonacci[$i]:0; 
		}
	}
	array_pop($fibonacci); //löscht den letzten wert der ohnehin zu viel ist
	$directions=array(array(1,1, 90,180, 0,-1),array(1,-1, 0,90, -1,0),array(-1,-1, 270,0, 0,1 ),array(-1,1, 180,270, 1,0)); //rechts, oben, links, unten - kreisauschnitt - kreismittelpunkt
	$dir=$rotate?1:0;
	
	//der startpunkt wird von einheiten in pixel konvertiert
	$start[0]=$pixel[1]*$panning-$start[0]*$ppmm*$uf[$unit][1]; $start[1]=$start[1]*$ppmm*$uf[$unit][1];
	
	$maxloops= is_numeric($tofibo)&&$tofibo+1<count($fibonacci)?$tofibo+1:count($fibonacci);
	$degfibo=is_numeric($degfibo)?$degfibo:$fibonacci[$maxloops-1];
	for($draw=0;$draw<$maxloops;$draw++){
		imagerectangle($ruler,$start[0],$start[1],
												$start[0]+=$directions[$dir][0]*$fibonacci[$draw]*$ppmm*$uf[$unit][1],
												$start[1]+=$directions[$dir][1]*$fibonacci[$draw]*$ppmm*$uf[$unit][1],$fc);
		imagearc($ruler,
												$start[0]+$directions[$dir][4]*$fibonacci[$draw]*$ppmm*$uf[$unit][1],
												$start[1]+$directions[$dir][5]*$fibonacci[$draw]*$ppmm*$uf[$unit][1],
												$fibonacci[$draw]*$ppmm*$uf[$unit][1]*2,$fibonacci[$draw]*$ppmm*$uf[$unit][1]*2,
												$directions[$dir][2],$directions[$dir][3],$fc);

		if ($degfibo==$fibonacci[$draw]){
			$degs=array(array(180,270),array(270,360),array(0,90),array(90,180));
			$deg2=-15;
			for ($deg=$degs[$dir][0];$deg<=$degs[$dir][1];$deg+=5){
				
				$fx=$start[0]+$directions[$dir][4]*$fibonacci[$draw]*$ppmm*$uf[$unit][1] + cos(deg2rad($deg))*$fibonacci[$draw]*$ppmm*$uf[$unit][1]*($deg%15==0?.9:.95);
				$fy=$start[1]+$directions[$dir][5]*$fibonacci[$draw]*$ppmm*$uf[$unit][1] - sin(deg2rad($deg))*$fibonacci[$draw]*$ppmm*$uf[$unit][1]*($deg%15==0?.9:.95);
				$tx=$start[0]+$directions[$dir][4]*$fibonacci[$draw]*$ppmm*$uf[$unit][1] + cos(deg2rad($deg))*$fibonacci[$draw]*$ppmm*$uf[$unit][1];
				$ty=$start[1]+$directions[$dir][5]*$fibonacci[$draw]*$ppmm*$uf[$unit][1] - sin(deg2rad($deg))*$fibonacci[$draw]*$ppmm*$uf[$unit][1];
								
				imageline($ruler,$fx,$fy,$tx,$ty, $fc);

				if ($deg%15==0){
				imagettftext($ruler, $ppmm*$fontsize[$unit], 0, 
					$start[0]+$directions[$dir][4]*$fibonacci[$draw]*$ppmm*$uf[$unit][1] + cos(deg2rad($deg))*$fibonacci[$draw]*$ppmm*$uf[$unit][1]*.9,
					$start[1]+$directions[$dir][5]*$fibonacci[$draw]*$ppmm*$uf[$unit][1] - sin(deg2rad($deg))*$fibonacci[$draw]*$ppmm*$uf[$unit][1]*.9,
					$fc, $font, $deg2+=15);
				}
			}
		}
		else imagettftext($ruler, $ppmm*$fontsize[$unit], 0, 
												$start[0]-$directions[$dir][0]*$fibonacci[$draw]*$ppmm*$uf[$unit][1]/2,
												$start[1]-$directions[$dir][1]*$fibonacci[$draw]*$ppmm*$uf[$unit][1]/2,
												$fc, $font, $fibonacci[$draw]);
		
		$dir+=$dir>=3?-3:1;
	}
}
	
	//höhe gemäß max- flächendarstellung
	if ($notice) imagettftext($ruler, $ppmm*$fontsize[$unit], 0, $ppmm*$uf[1][1], $ppmm*$uf[$unit][1]*$maxheight+$ppmm*$fontsize[$unit]*1.5, $fc, $font, $notice);

	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=ruler.jpg");
	ob_start();	
	imagejpeg($ruler,NULL,100);
	ob_end_flush();
	imagedestroy($ruler);
}
?>


<!DOCTYPE html>
<html lang="de">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ruler on your hand</title>
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
<script>
function detect(){
	var devicePixelRatio = window.devicePixelRatio || 1;
	var width = screen.width * devicePixelRatio;
	var height = screen.height * devicePixelRatio;
	document.getElementById('res').value=height+'x'+width;
}
</script>
<body>
<form action="ruler.php" method="post" enctype="multipart/form-data" id="form">
<div class="panel" id="header" style=" text-align:center;"><h2>make your handheld a</h2><h1>ruler</h1></div>
<div id="content">
<div class="nopanel">herstellerangaben zur bildschirmgr&ouml;&szlig;e</div>
<div class="panel">diagonale in zoll <input type="text" name="dia" value="<?php echo $dia;?>" size="8" /></div>
<div class="panel">aufl&ouml;sung pixel (h&ouml;he x breite) <input type="text" name="px" value="<?php echo $px?$px:'';?>" size="8" id="res" /><input type="button" onclick="detect();" value="ermitteln"/></div>
<div class="nopanel">gew&uuml;nschte darstellung</div>
<div class="panel">ma&szlig;einheit <label for="unitmm"> cm</label><input type="radio" name="units" value="mm"  <?php if ($units=="mm")echo "checked=\"checked\"";?> id="unitmm" /> <label for="unitinch"> zoll</label><input type="radio" name="units" value="inch" <?php if ($units=="inch")echo "checked=\"checked\"";?> id="unitinch" /></div>
<div class="panel">l&auml;ngenskala <input type="checkbox" name="showruler" value="1" <?php if ($showruler=="1")echo "checked=\"checked\"";?> /></div>
<div class="panel">fl&auml;che <a href="#/" onclick="alert('es werden fl&auml;chen eingeblendet, die als orientierung dienen k&ouml;nnen. der zun&auml;chst zu errechnende maximalwert kann sp&auml;ter reduziert werden.');">(?)</a><label for="rotate"> 90° gedreht</label> <input type="checkbox" name="rotate" id="rotate" value="1"<?php if ($rotate) echo " checked=\"checked\"";?> /><select name="tofibo"><option value="0">keine</option>
<?php
if ($fibonacci) for ($i=1;$i<count($fibonacci);$i++){ echo "<option value=\"".$i."\" ".($tofibo==$i?"selected==\"selected\"":"").">bis ".$fibonacci[$i]."&sup2;</option>\n";}
?>
<option value="max"<?php if ($tofibo=="max")echo "selected=\"selected\"";?>>maximal</option></select>
</div>
<div class="panel">winkel <select name="degfibo"><option value="0">keine</option>
<?php
if ($fibonacci) for ($i=1;$i<count($fibonacci);$i++){ echo "<option value=\"".$fibonacci[$i]."\" ".($degfibo==$fibonacci[$i]?"selected==\"selected\"":"").">in fl&auml;che ".$fibonacci[$i]."</option>\n";}
?>
<option value="max"<?php if ($degfibo=="max")echo "selected=\"selected\"";?>>in letzter fl&auml;che</option></select>
</div>
<div class="panel">notiz <a href="#/" onclick="alert('z.b. &pi; ist ungef&auml;hr 3,1415926535897932384626433832795\nkreisumfang 2&pi;r\nkreisfl&auml;che &pi;r&sup2;\nkugelvolumen 4/3 &pi;r&sup3;\nwas man eben so braucht...');">(?)</a> <textarea name="notice"><?php echo $notice;?></textarea></div>
<div class="panel">bildbreite <a href="#/" onclick="alert('als hintergrundbild in homescreens gleitet das bild ggf. mit...');">(?)</a> <select name="panning"><option value="1">keine &auml;nderung</option>
<?php
for ($i=1.5;$i<3;$i+=.5){ echo "<option value=\"".$i."\" ".($panning==$i?"selected==\"selected\"":"").">x ".$i."</option>\n";}
?></select>
</div>


<div class="nopanel">farben</div>
<div class="panel">hintergrundfarbe <input type="color" name="bgcol" value="<?php echo $bgcol;?>" size="8" /></div>
<div class="panel">linien- und schriftfarbe <input type="color" name="fgcol" value="<?php echo $fgcol;?>" size="8" /></div>
<div class="nopanel"><input type="submit" value="generieren" /></div>
<small>die genauigkeit h&auml;ngt von der tats&auml;chlichen bildschirmdiagonale und eventuell der pixel-form ab.</small>
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