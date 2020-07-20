<?php

$GATEWAY = [
  // '04eeefca1179',
];

// if(is_readable('./nodes.json')) {
//   $nodelistRaw = file_get_contents('./nodes.json');
// }

// TODO:
// =========================
// - Für alle Domains anpassen


$nodelistRaw = file_get_contents('https://han.karte.neanderfunk.de/data/nodes.json');

// $nodelistRaw = file_get_contents('https://nefhlgl2tp.karte.neanderfunk.de/data/nodes.json');

// $nodelistRaw = file_get_contents('https://mon.karte.neanderfunk.de/data/nodes.json');

// https://han.karte.neanderfunk.de/data/nodes.json

$nodeObj = json_decode($nodelistRaw, true);

//var_dump($nodeObj);

$whitelists = [];
$ipwl = [];

foreach($nodeObj['nodes'] as $node) {
    $hn = $node['nodeinfo']['hostname'] ?? 'notset';
    $id = $node['nodeinfo']['node_id'];


    // "ffnef-hlg-2"
    if(1 === preg_match('/ffnef-[a-z]{3}-[0-9]{1}/', $hn)) {
        $GATEWAY[] = $id;
        continue;
    }

    // Neue Supernodes: (wird für den umzug in die neue Infrastruktur noch nicht gebraucht)
    // "amalthea_FFNEFd08"
    // autoupdate branch: gateways
    $updatebranch = $node['nodeinfo']['software']['autoupdater']['branch'] ?? 'notset';
    if($updatebranch !== 'notset' && $updatebranch === 'gateways') {
        $GATEWAY[] = $id;
        continue;
    }
}

foreach($nodeObj['nodes'] as $node) {
    $hn = $node['nodeinfo']['hostname'] ?? 'notset';
    $id = $node['nodeinfo']['node_id'];
    $gw = $node['statistics']['gateway'] ?? 'notset';
    $gwnh = $node['statistics']['gateway_nexthop'] ?? 'notset';
    $ipv6 = $node['nodeinfo']['network']['addresses'][0] ?? 'noip';
    if(strpos($ipv6, 'fe80') === 0) {
        $ipv6 = $node['nodeinfo']['network']['addresses'][1] ?? 'noip';
    }
    if(in_array($id, $GATEWAY)) {
        continue;
    }
    if($gw == 'notset') {
        continue;
    }
    //if(in_array($gwnh, $GATEWAY)) {
    if($gw == $gwnh) {
        // Direkt vor ffnef gw
        $whitelists[0][] = $hn . ' - ' . $id . '- GWNH: ' . $gwnh;
        $ipwl[0][] = $ipv6;
    } else {
        $node2 = getNode($gwnh, $nodeObj['nodes']);
        if($node2 === false) {
            echo "Fehler beim holen der Node aufetreten. Node (".$gwnh.") nicht gefunden.";
        } else {
            nodeTree($node2, 1, $node);
        }
    }
    //$whitelists[$gwnh][] = $hn . ' - ' . $id;
    // echo  $hn
    // . ' - ' . $id
    // . ' - GW: ' . $gw
    // . ' - GW-NH: ' . $gwnh
    // . "\n";
}

// Sortierung umdrehen
krsort($whitelists);
krsort($ipwl);

// echo "\n".json_encode($whitelists)."\n";
// echo "\n".json_encode($ipwl)."\n";

$i = 1;
echo "======================================================\n";
foreach($ipwl as $wlzeile) {
    echo "\nWhitelist Welle ".$i."\n\n";
    echo implode("\n", $wlzeile);
    $i++;
    echo "\n-------------------------------------------------\n";
}

function nodeTree($node, $ebene, &$orig)
{
    global $ipwl;
    global $whitelists;
    global $nodeObj;
    //echo "Ebene ".$ebene;
    $hn = $node['nodeinfo']['hostname'] ?? 'notset';
    $id = $node['nodeinfo']['node_id'];
    $gw = $node['statistics']['gateway'] ?? 'notset';
    $gwnh = $node['statistics']['gateway_nexthop'] ?? 'notset';

    $Orighn = $orig['nodeinfo']['hostname'] ?? 'notset';
    $Origid = $orig['nodeinfo']['node_id'];
    $Origgw = $orig['statistics']['gateway'] ?? 'notset';
    $Origgwnh = $orig['statistics']['gateway_nexthop'] ?? 'notset';
    $ipv6 = $orig['nodeinfo']['network']['addresses'][0] ?? 'noip';
    if(strpos($ipv6, 'fe80') === 0) {
        $ipv6 = $orig['nodeinfo']['network']['addresses'][1] ?? 'noip';
    }

    if($gw == $gwnh) {
        // gw = gw nexthop
        $whitelists[$ebene][] = $Orighn . ' - ' . $Origid . '- GWNH: ' . $Origgwnh;
        $ipwl[$ebene][] = $ipv6;
    } else {
        $node2 = getNode($gwnh, $nodeObj['nodes']);
        if($node2 === false) {
            echo "Fehler beim holen der Node aufetreten. Node (".$gwnh.") nicht gefunden.";
        } else {
            nodeTree($node2, ($ebene+1), $orig);
        }

    }
}

function getNode($id, $nodes)
{
    foreach($nodes as $node) {
      $idNode = $node['nodeinfo']['node_id'];
      if(isset($node['nodeinfo']['node_id']) && $id == $idNode) {
          return $node;
      }
    }
    return false;
}

?>
