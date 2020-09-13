<?php
define("IN_MYBB", 1);
//define("NO_ONLINE", 1); // Wenn Seite nicht in Wer ist online-Liste auftauchen soll

/*
* Dies ist ein Hauspunkteplugin. Auf dieser Extraseite werden alle Häuser und deren Aktuelle Punktzahl angezeigt.
*/
error_reporting ( -1 );
ini_set ( 'display_errors', true );
require("global.php");
global $db, $mybb, $templates, $housepoints, $all, $housepoints_bit, $page,  $user ;

    $group_sect = $db->simple_select("usergroups", "gid,title", "gid IN ('" . str_replace(',', '\',\'', $mybb->settings['hp_groups']) . "')");

    while ($house = $db->fetch_array($group_sect)) {

        $select = $db->query("SELECT *
    FROM " . TABLE_PREFIX . "users u
    LEFT JOIN " . TABLE_PREFIX . "usergroups ug
    ON u.usergroup = ug.gid
    LEFT JOIN " . TABLE_PREFIX . "userfields uf
    ON uf.ufid = u.uid
    WHERE u.usergroup = $house[gid]
    Order by hp_points DESC, username ASC
    ");

        $all = 0;

        $student = "";

        while ($row = $db->fetch_array($select)) {

            $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
            $user = build_profile_link($username, $row['uid']);

            $postpoints = $row['hp_points'];
            $student .= $user . " - " . $postpoints . " Hauspunkte <br />";

            $all = $all + $postpoints;


        }
        $all = number_format($all, '0', ',', '.');
        eval("\$housepoints_bit .= \"" . $templates->get("housepoints_bit") . "\";");
    }


    eval("\$page = \"" . $templates->get("housepoints") . "\";"); // Hier wird das erstellte Template geladen
    output_page($page);


?>
