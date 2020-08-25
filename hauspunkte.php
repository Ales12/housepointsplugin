<?php
define("IN_MYBB", 1);
//define("NO_ONLINE", 1); // Wenn Seite nicht in Wer ist online-Liste auftauchen soll

/*
* Dies ist ein Hauspunkteplugin. Auf dieser Extraseite werden alle Häuser und deren Aktuelle Punktzahl angezeigt.
*/
error_reporting ( -1 );
ini_set ( 'display_errors', true );
require("global.php");
global $db, $mybb;

add_breadcrumb("Hauspunkte", "hauspunkte.php");



$group_sect = $db->simple_select("usergroups", "gid,title", "gid IN ('".str_replace(',', '\',\'', $mybb->settings['hp_groups'])."')");

while($house = $db->fetch_array($group_sect)){

    $select = $db->query("SELECT *
    FROM ".TABLE_PREFIX."users u
    LEFT JOIN ".TABLE_PREFIX."usergroups ug
    ON u.usergroup = ug.gid
    LEFT JOIN ".TABLE_PREFIX."userfields uf
    ON uf.ufid = u.uid
    WHERE u.usergroup = $house[gid]
    Order by username ASC
    ");

    $postfaktor = $mybb->settings['hp_post'];

    $wantedfaktor = $mybb->settings['wanted_points'];

    $all = 0;

$student = "";
   
    while($row = $db->fetch_array($select)){
        $postpoints = ($row['postnum'] * $postfaktor) + $row['hp_points'] + 15;
        $username = format_name ($row['username'], $row['usergroup'], $row['displaygroup']);
        $user = build_profile_link ($username, $row['uid']);


        if($mybb->settings['hp_quest'] == 1){
            $postpoints = $postpoints + $row['questpoints'];
        }

        $fid = $row['fid'.intval($mybb->settings['hp_gesuche_adop']).''];
        if(preg_match("/a/i", "$fid")){
            $postpoints = $postpoints + $wantedfaktor;
        }
     $student .= $user." - ". $postpoints." Hauspunkte <br />";
        
        $all = $all + $postpoints;
    }

    eval("\$housepoints_bit .= \"".$templates->get("housepoints_bit")."\";");
}



eval("\$page = \"".$templates->get("housepoints")."\";"); // Hier wird das erstellte Template geladen
output_page($page);

?>