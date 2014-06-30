<?php

if(!@$template || !@$chord || !@$size){echo "Missing data."; return;}

$chord = str_replace("@","#",$chord);
$chord = str_replace("%","/",$chord);
$template = str_pad(substr(trim($template),0,6),6," ");

$height = $size;
$width = str_replace(",",".",round($height*0.8,2));
		
$prsty = str_split($template,1);

$min = 10000;
$max = 0;
$posun = 0;
foreach($prsty as $prst){
		if(!is_numeric($prst)) continue;
		if($prst > $max) $max = $prst;
		if($prst < $min && $prst != 0) $min = $prst;
}
if($max > 4 && $min > 1){
		$posun = $min;
		foreach($prsty as &$prst){
				if(is_numeric($prst) && $prst != 0) $prst = $prst - $posun + 1;
		}
		
}

$barre = $prsty[5];
$barre_stop = 5;

for($struna = 5;$struna >= 0; $struna--){
		if($prsty[$struna] < $barre || !$prsty[$struna] || !is_numeric($prsty[$struna])) break;
		
		if($prsty[$struna] == $barre){
				$barre_stop = $struna;
		}
}

//header("Content-type:image/svg+xml");

?>
<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" height="<?=$height?>px" width="<?=$width?>px" viewBox="0 0 160 190">
		
		<!-- struktura -->
		<line x1="8" y1="70" x2="112" y2="70" style="stroke:black;stroke-width:<?=($posun ? 4 : 8)?>"/>
		
		<line x1="10" y1="70" x2="10" y2="190" style="stroke:black;stroke-width:4"/>
		<line x1="30" y1="70" x2="30" y2="190" style="stroke:black;stroke-width:4"/>
		<line x1="50" y1="70" x2="50" y2="190" style="stroke:black;stroke-width:4"/>
		<line x1="70" y1="70" x2="70" y2="190" style="stroke:black;stroke-width:4"/>
		<line x1="90" y1="70" x2="90" y2="190" style="stroke:black;stroke-width:4"/>
		<line x1="110" y1="70" x2="110" y2="190" style="stroke:black;stroke-width:4"/>
		
		<line x1="10" y1="100" x2="110" y2="100" style="stroke:black;stroke-width:4"/>
		<line x1="10" y1="130" x2="110" y2="130" style="stroke:black;stroke-width:4"/>
		<line x1="10" y1="160" x2="110" y2="160" style="stroke:black;stroke-width:4"/>
		
		<?php if($posun): ?>
		<text x="120" y="97" fill="black" font-size="40" font-family="Calibri,sans-serif"><?=$posun?></text>
		<?php endif; ?>
		
		<!-- puntíky -->
		<?php
foreach($prsty as $struna => $prazec){  
		if($prazec === " ") continue;    
		if(strtolower($prazec) === "x"){
				$x1 = 3 + $struna*20;
				$x2 = 17 + $struna*20;
				echo "<line x1=\"$x1\" y1=\"48\" x2=\"$x2\" y2=\"62\" style=\"stroke:black;stroke-width:2\"/>";
				echo "<line x1=\"$x1\" y1=\"62\" x2=\"$x2\" y2=\"48\" style=\"stroke:black;stroke-width:2\"/>";
				continue;
		}
		
		$x = 10 + $struna*20;
		$y = 55 + $prazec*30;
		if($y === 55) echo "<circle cx=\"$x\" cy=\"$y\" r=\"7\" fill=\"transparent\" stroke=\"black\" stroke-width=\"2\" />";
		else echo "<circle cx=\"$x\" cy=\"$y\" r=\"7\" fill=\"black\"/>";
}
		?>
		
		<!-- barré --> 
		<?php if($barre_stop < 5): ?>
		<line x1="<?=(10+$barre_stop*20)?>" y1="<?=(55 + $barre*30)?>" x2="110" y2="<?=(55 + $barre*30)?>" style="stroke:black;stroke-width:14;stroke-linecap:round;"/>
		<?php endif; ?>
		
		<!-- nazev akordu -->
		<text x="5" y="38" fill="black" font-size="40" font-family="dejavusans,sans-serif"><?=$chord?></text>
		
</svg>