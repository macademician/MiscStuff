<?php
include('simple_html_dom.php');
include('misc_tag.php');

function cmpDistance($a, $b) {
    if ($a['distance'] == $b['distance']) {
        return 0;
    }
    return ($a['distance'] < $b['distance']) ? -1 : 1;
}

$res = array();

if (isset($_GET["lat"]) && isset($_GET["lgt"])) {
	$lat = $_GET["lat"];
	$lgt = $_GET["lgt"];
} else {
	return;
}

$url = "http://tag.mobitrans.fr/index.php?p=43&I=c024eym&selecteur=2&arret=1";
$latitude = "&latitude=".$lat;
$longitude = "&longitude=".$lgt;
$fullUrl = $url.$latitude.$longitude;
$osmUrl = "http://www.openstreetmap.org/#map=19/".$latitude."/".$longitude;

$html = file_get_html($fullUrl);


if(count($html->find('div.error')) == 1) {
	echo "<a href=".$fullUrl.">Mobitrans</a> <br />";
	echo json_encode("error");
	exit();
}

$resultNode = $html->find('div.corpsL')[0];

$nearStations = $resultNode->find('div');
$linesForStations = array();

foreach ($nearStations as $aLine) {
	$lineSpanNode = $aLine->find('span')[1];
	$lineName = $lineSpanNode->innertext;
   $lineName = getLineName($lineName);
	$stations = $aLine->find('a');
	$stationList = array();
	foreach ($stations as $aStation) {
		$stationName = utf8_encode($aStation->innertext);
		$distance = $aStation->next_sibling()->innertext;
		$pattern = "/(\d*)m/";
		preg_match($pattern, $distance, $matches);
		$distance = $matches[1];
		parse_str($aStation->href, $urlarray);
		$stationId = $urlarray['id'];

		$stationInfo['station'] = $stationName;
		$stationInfo['distance'] = intval($distance);
		$stationInfo['stationID'] = intval($stationId);
		array_push($stationList, $stationInfo);
      if (! array_key_exists($stationName,$linesForStations)) {
         $linesForStations[$stationName] = array();
         $linesForStations[$stationName]['name'] = $stationName;
         $linesForStations[$stationName]['distance'] = intval($distance);
         $linesForStations[$stationName]['lines'] = array(); 
      }
         array_push($linesForStations[$stationName]['lines'], array($lineName,intval($stationId)));       
	}
	$lineInfo['line'] = $lineName;
	$lineInfo['bgcolor'] = getColorForLine($lineName)['bg'];
	$lineInfo['fgcolor'] = getColorForLine($lineName)['fg'];
	$lineInfo['stations'] = $stationList;

	array_push($res,$lineInfo);
}

uasort($linesForStations,'cmpDistance');


if(isset($_GET['json'])) {
	header("Content-type: application/json");
	echo json_encode($res);
} else if (isset($_GET['vardump'])) {
	echo "<a href=".$fullUrl.">Sur Mobitrans</a> - <a href=".$osmUrl.">Position sur OpenStreetMap</a><br/>";
	echo var_dump($res);
   var_dump($linesForStations);
}
?>
