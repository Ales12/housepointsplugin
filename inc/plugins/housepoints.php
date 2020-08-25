<?php
/**
 * Created by PhpStorm.
 * User: Alex
 * Date: 03.10.2017
 * Time: 14:03
 */

if(!defined("IN_MYBB"))
{
    die("Direct initialization of this file is not allowed.");
}

//alle hooks
$plugins->add_hook('global_intermediate', 'houspoints_global');
$plugins->add_hook('member_profile_end', 'housepoints_profile');
$plugins->add_hook('newthread_do_newthread_start', 'wanted_newthread');
$plugins->add_hook('newreply_do_newreply_start', 'houspoints_inplay');
$plugins->add_hook('memberlist_user', 'housepoints_memberlist_bit');
$plugins->add_hook('postbit', 'housepoints_postbit');
$plugins->add_hook('modcp_nav', 'housepoints_modcp_nav');
$plugins->add_hook('modcp_start', 'housepoints_modcp');

function housepoints_info()
{
    return array(
        "name" => "Hauspunkte",
        "description" => "Dieser Plugin ermöglicht die Automatische Berechnung von Hauspunkten.",
        "website" => "",
        "author" => "Ales",
        "authorwebsite" => "",
        "version" => "2.0",
        "compatibility" => "18*"
    );
}

function housepoints_install()
{

    global $db, $mybb;
    /*
    * hier werden nun alle Datenbankänderungen und die, die neue Tabellen dazu bekommen aktiviert.
    */

    $db->query("ALTER TABLE `".TABLE_PREFIX."users` ADD `hp_points` int(11) NOT NULL AFTER `sourceeditor`;");
    $db->query("ALTER TABLE `".TABLE_PREFIX."users` ADD `hp_wanted` int(11) NOT NULL DEFAULT '0' AFTER `hp_points`;");
    //Tabelle für die Datenbank generieren
    $db->query("CREATE TABLE ".TABLE_PREFIX."housepoints (
   `hid` int(11) NOT NULL AUTO_INCREMENT,
   `uid` int(11) NOT NULL,
   `points` varchar(500) NOT NULL,
   `reason` varchar(500) NOT NULL,
   PRIMARY KEY (`hid`),
   KEY `hid` (`hid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");


    /*
     * nun kommen die Einstellungen
     */
    $setting_group = array(
        'name' => 'housepoints',
        'title' => 'Hauspunkte',
        'description' => 'Hier kannst du nun alle Einstellungen für deine Hauspunkte vornehmen',
        'disporder' => 2,
        'isdefault' => 0
    );

    $gid = $db->insert_query ("settinggroups", $setting_group);

    $setting_array = array(
        'name' => 'hp_post',
        'title' => 'Punkte für Inplaypost',
        'description' => 'Punkteanzahl, welche man pro Inplaypost bekommt:',
        'optionscode' => 'numeric',
        'value' => '10',
        'disporder' => 1,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);


    $setting_array = array(
        'name' => 'hp_gesuche_creat',
        'title' => 'Erstellte Gesuche mit berechnen?',
        'description' => 'Sollen Gesuche, die erstellt wurden, mit berechnet werden?',
        'optionscode' => 'yesno',
        'value' => '0',
        'disporder' => 5,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'wanted_points',
        'title' => 'Punkte für Gesuche',
        'description' => 'Punkte für Gesuche erstellung und Übernahme.',
        'optionscode' => 'numeric',
        'value' => '10',
        'disporder' => 6,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);


    $setting_array = array(
        'name' => 'hp_gesuche_kat',
        'title' => 'Gesuchsforum',
        'description' => 'Gib hier die ID des Gesuchsforum an?',
        'optionscode' => 'numeric',
        'value' => '1',
        'disporder' => 7,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'hp_inplay',
        'title' => 'Inplaykategorie ID',
        'description' => 'Gib hier die ID des Inplays an?',
        'optionscode' => 'numeric',
        'value' => '2',
        'disporder' => 8,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'hp_ip_max',
        'title' => 'Postlänge',
        'description' => 'Ab welcher Postlänge soll der User weitere Punkte bekommen? (0, wenn nicht berechnet werden soll)',
        'optionscode' => 'numeric',
        'value' => '10000',
        'disporder' => 9,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'hp_ip_points',
        'title' => 'Zusatzpunkte Postlänge',
        'description' => 'Wie viele Zusatzpunkte bekommt man, wenn man die Postlänge überschritten hat? ',
        'optionscode' => 'numeric',
        'value' => '10',
        'disporder' => 10,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);


    $setting_array = array(
        'name' => 'hp_groups',
        'title' => 'Gruppenauswahl',
        'description' => 'Welche Gruppen sollen beachtet werden? 
        Bitte so eintragen: 1,2,3,4',
        'optionscode' => 'text',
        'value' => '8,9,10,11',
        'disporder' => 11,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    $setting_array = array(
        'name' => 'hp_quest',
        'title' => 'Questsystem',
        'description' => 'Ist das Questsystem von Ales installiert?',
        'optionscode' => 'yesno',
        'value' => '0',
        'disporder' => 5,
        "gid" => (int)$gid
    );
    $db->insert_query ('settings', $setting_array);

    rebuild_settings();


    $insert_array = array(
        'title' => 'housepoints',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - Hauspunkte</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
<tr>
<td class="thead"><strong>Hauspunkte - Die Übersicht</strong></td>
</tr>
<tr>
<td class="trow1" align="center">
	<table width="100%" style="margin:auto;"><tr><td>
{$housepoints_bit}
		</td>
</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);
    $insert_array = array(
        'title' => 'housepoints_bit',
        'template' => $db->escape_string('<div style="width: 40%; height: 380px; float: left; margin: 5px 5px;">
<div class="{$house[\'title\']}" style="font-size: 130px; text-align:center; opacity: 0.3;"><i class="fas fa-hourglass"></i></div>
		<div style="text-align:center; font-size: 30px; text-transform:uppercase; width: 98px; margin: -45px auto 0 auto;"  class="{$house[\'title\']}">{$all}</div>
	<div style="text-align:center; font-size: 20px; text-transform:uppercase; padding-top: 3px;">{$house[\'title\']}</div>
	<div style="max-height: 200px; overflow:auto; text-align: center;" class="smalltext">{$student}</div>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_header',
        'template' => $db->escape_string('<table style="margin:auto; width: 250px;"><tr><td class="thead"><strong>Hauspunkte</strong></td></tr></td>
<tr><td class="trow1">{$housepoints_header_bit}</td></tr>
</table>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_header_bit',
        'template' => $db->escape_string('<div style="width: 40%; height: 80px; float: left; margin: 5px 5px;">
<div class="{$house[\'title\']}" style="font-size: 50px; text-align:center; opacity: 0.3;"><i class="fas fa-hourglass"></i></div>
		<div style="text-align:right; font-size: 15px; text-transform:uppercase; width: 25px; margin: -18px auto 0 auto;"  class="{$house[\'title\']}">{$all}</div>
	<div style="text-align:center; font-size: 10px; text-transform:uppercase;">{$house[\'title\']}</div>
</div>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_menu',
        'template' => $db->escape_string('<li><a href="hauspunkte.php">Hauspunkte</a></li>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_postbit',
        'template' => $db->escape_string('Hauspunkte: {$post[\'hp_points\']}'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_profile_options',
        'template' => $db->escape_string('<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" width="100%" class="tborder">
<tr>
<td  class="thead"><strong>Hauspunkte</strong></td>
</tr>

<tr>
<td class="trow2"><table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 50%; margin:auto;">
	<form id="housepoints" method="post" action="member.php?action=profile&uid={$memprofile[\'uid\']}">
		<tr><td class="trow2"><b>Pluspunkte</b></td><td class=\'trow1\'><input type="text" name="pluspoints" id="pluspoints" value="" class="textbox" /></td>
<td align="center"><input type="submit" name="plus" value="eintragen" id="submit" class="button"></td></tr></form>
		<form id="housepoints" method="post" action="member.php?action=profile&uid={$memprofile[\'uid\']}">
		<tr><td class="trow2"><b>Minuspunkte</b></td><td class=\'trow1\'><input type="text" name="minuspoints" id="minuspoints" value="" class="textbox" /></td>
<td align="center"><input type="submit" name="minus" value="eintragen" id="submit" class="button"></td></tr></form>
	{$housepoints_wanted}</table>
</td>
</tr>
</table>
<br />'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_profile_points',
        'template' => $db->escape_string('<strong>Hauspunkte</strong> {$housepoints}'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_profile_wanted',
        'template' => $db->escape_string('<tr><td class="trow1" colspan="3" align="center"><a href="member.php?action=profile&wanted={$memprofile[\'uid\']}">Punkte für Angenommenes Gesuche vergeben.</a></td></tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_modcp',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - Hauspunkte</title>
{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
		<tr>
			{$modcp_nav}
			<td valign="top">
				<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
			<tr><td class="thead" ><h1>Hauspunkte</h1></td> </tr>
					<tr><td class="trow1" align="center">
						<h2>Alle Punkte zurücksetzen</h2>
						<div class="smalltext" style="text-align: center; font-size: 13px;">{$points_reset}</div>
						<h2>allen Schüler Punkte gutschreiben</h2>
						<form id="housepoints" method="post" action="modcp.php?action=housepoints">
	<table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 50%; margin:auto;">
	<form id="housepoints" method="post" action="modcp.php?action=housepoints">
		<tr><td class=\'trow1\'><input type="text" name="housepoints" id="housepoints" placeholder="+/- Punkte" class="textbox" /></td><td class=\'trow1\'><input type="text" name="reason" id="reason" placeholder="Begründung für Punkte" class="textbox" /></td>
<td align="center"><input type="submit" name="allpoints" value="bei allen Punkte eintragen" id="submit" class="button"></td></tr></form>
	</table>
							<br />
							<h2>Bestimmte Schüler Punkte abziehen</h2>
								<table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 50%; margin:auto;">
	<form id="housepoints" method="post" action="modcp.php?action=housepoints">
		<tr><td class="trow1"><select name="students[]" size="5" multiple>{$students}</select> </td><td class=\'trow1\'><input type="text" name="housepoints" id="housepoints" placeholder="+/- Punkte" class="textbox" /></td><td class=\'trow1\'><input type="text" name="reason" id="reason" placeholder="Begründung für Punkte" class="textbox" /></td>
<td align="center"><input type="submit" name="viewpoints" value="Punkte eintragen" id="submit" class="button"></td></tr></form>
	</table>
							<h2>Alle Schüler</h2>
						<table width="80%" style="margin:auto">
							<tr><td class="thead"><h2>Charakter</h2></td><td class="thead"><h2>Hauspunkte</h2></td><td class="thead"><h2>Plus/Minus Punkte</h2></td></tr>
							{$housepoints_user}
						</table>
						</td></tr>
			</table>
							</td></tr>
			</table>
	{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_modcp_bit',
        'template' => $db->escape_string('<tr><td class="trow1" align="center"><div class="username" style="font-size: 13px; padding: 0;">{$user}</div></td><td class="trow2" align="center"><div style="font-size: 13px;">{$housepoints} Hauspunkte</div></td><td class="trow1">
	<table border="0" cellspacing="5" cellpadding="{$theme[\'tablespace\']}" class="tborder" style="width: 50%; margin:auto;">
	<form id="housepoints" method="post" action="modcp.php?action=housepoints">
<input type="hidden" name="uid" id="uid" value="{$userid}" class="textbox">
		<tr><td class=\'trow1\'><input type="text" name="housepoints" id="housepoints" placeholder="+/- Punkte" class="textbox" /></td><td class=\'trow1\'><input type="text" name="reason" id="reason" placeholder="Begründung für Punkte" class="textbox" /></td>
<td align="center"><input type="submit" name="points" value="eintragen" id="submit" class="button"></td></tr></form>
	</table></td></tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_modcp_protocol',
        'template' => $db->escape_string('<html>
<head>
<title>{$mybb->settings[\'bbname\']} - Hauspunkte Protokol</title>
{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
		<tr>
			{$modcp_nav}
			<td valign="top">
				<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
			<tr><td class="thead" ><h1>Hauspunkte Protokoll</h1></td> </tr>
					<tr><td class="trow1">
						
						<table width="80%" style="margin:auto">
							<tr><td class="thead"><h2>Charakter</h2></td><td class="thead"><h2>Hauspunkte</h2></td><td class="thead"><h2>Begründung</h2></td></tr>
							{$housepoints_protocol_user}
						</table>
						</td></tr>
			</table>
							</td></tr>
			</table>
	{$footer}
</body>
</html>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
        'title' => 'housepoints_modcp_protocol_bit',
        'template' => $db->escape_string('<tr><td class="trow1" align="center"><div class="username" style="font-size: 13px; padding: 0;">{$user}</div></td><td class="trow2" align="center"><div style="font-size: 13px;">{$housepoints} Hauspunkte</div></td><td class="trow1" align="center"><div style="font-size: 13px;">wegen {$reason}</div></td></tr>'),
        'sid' => '-1',
        'version' => '',
        'dateline' => TIME_NOW
    );
    $db->insert_query("templates", $insert_array);

}


function housepoints_is_installed()
{
    global $db;

    if($db->field_exists("hp_points", "users"))
    {
        return true;
    }
    return false;
}

function housepoints_uninstall()
{
    global $db;

    $db->query("DELETE FROM ".TABLE_PREFIX."settinggroups WHERE name='housepoints'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='hp_post'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='hp_plus'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='hp_minus'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='hp_gesuche_creat'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='wanted_points'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='hp_gesuche_kat'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='hp_inplay'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='hp_ip_max'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='hp_ip_points'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='hp_groups'");
    $db->query("DELETE FROM ".TABLE_PREFIX."settings WHERE name='hp_quest'");


    if($db->field_exists("hp_points", "users")){
        $db->drop_column("users", "hp_points");
    }
    if($db->field_exists("hp_wanted", "users")){
        $db->drop_column("users", "hp_wanted");
    }
    if($db->table_exists("housepoints"))
    {
        $db->drop_table("housepoints");
    }


    $db->delete_query("templates", "title LIKE '%housepoints%'");
    rebuild_settings();
}


function housepoints_activate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$menu_calendar}')."#i", '{$menu_calendar} {$housepoints_menu} ');
    find_replace_templatesets("member_profile", "#".preg_quote('{$modoptions}')."#i", '{$profile_hp_options} {$modoptions} ');
    find_replace_templatesets("member_profile", "#".preg_quote('{$online_status}')."#i", '{$online_status} <br /> {$profile_points} ');
    find_replace_templatesets("header", "#".preg_quote('{$pm_notice}')."#i", '{$housepoints_header} {$pm_notice}');
    find_replace_templatesets("memberlist_user", "#".preg_quote('{$user[\'userstars\']} ')."#i", '{$user[\'userstars\']} <br /> {$housepoints_memberlist_bit}');
    find_replace_templatesets("modcp_nav_users", "#".preg_quote('	{$nav_editprofile} ')."#i", '	{$nav_editprofile} <br /> {$modcp_hp}');



}

function housepoints_deactivate()
{
    require MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("header", "#".preg_quote('{$housepoints_menu}')."#i", '', 0);
    find_replace_templatesets("member_profile", "#".preg_quote('{$profile_hp_options}')."#i", '', 0);
    find_replace_templatesets("member_profile", "#".preg_quote('<br /> {$profile_points}')."#i", '', 0);
    find_replace_templatesets("header", "#".preg_quote('{$housepoints_header}')."#i", '', 0);
    find_replace_templatesets("memberlist_user", "#".preg_quote('<br />{$housepoints_memberlist_bit}')."#i", '', 0);
    find_replace_templatesets("modcp_nav_users", "#".preg_quote('<br />{$modcp_hp}')."#i", '', 0);
}


//Link im Header, wird nach dem Kalender angezeigt
function houspoints_global(){
    global $templates, $db, $mybb, $housepoints_menu, $wanted_fid,$plusfaktor, $punkte_pergesuch, $all,$elsepoints, $wanted_field, $housepoints_header,$housepoints_header_bit ;

    eval("\$housepoints_menu = \"" . $templates->get ("housepoints_menu") . "\";");


//Und hier ist der Inhalt für die Headertabelle
    $group_sect = $db->simple_select("usergroups", "gid,title", "gid IN ('".str_replace(',', '\',\'', $mybb->settings['hp_groups'])."')");

    while($house = $db->fetch_array($group_sect)){

        $select = $db->query("SELECT *
    FROM ".TABLE_PREFIX."users u
    LEFT JOIN ".TABLE_PREFIX."usergroups ug
    ON u.usergroup = ug.gid
    LEFT JOIN ".TABLE_PREFIX."userfields uf
    ON uf.ufid = u.uid
    WHERE u.usergroup = $house[gid]
    ");


        $all = 0;


        while($row = $db->fetch_array($select)){
            $housepoints = $row['hp_points'];


            $all = $all + $housepoints;
        }
        $all = number_format($all, '0', ',', '.');
        eval("\$housepoints_header_bit .= \"".$templates->get("housepoints_header_bit")."\";");
    }



    eval("\$housepoints_header = \"".$templates->get("housepoints_header")."\";"); // Hier wird das erstellte Template geladen
}

//funktionen im profil
function housepoints_profile(){
    global $mybb, $db, $templates, $profile_hp_options, $memprofile, $alt_bg, $housepoints, $profile_points, $profile_points_mobile;

    //Punkte hinzuaddieren bzw abziehen

    if($mybb->usergroup['canmodcp'] == 1 OR $mybb->usergroup['canacp'] == 1) {
        $userid = $memprofile['uid'];

        if ($_POST['plus']) {
            $pluspoints = $_POST['pluspoints'];
            $reason = $_POST['reason'];
            $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points + '" . $pluspoints . "'  WHERE uid = '" . $userid . "'");
            $new_record = array(
                "uid" => $db->escape_string($userid),
                "points" => $db->escape_string($pluspoints),
                "reason" => $db->escape_string($reason)
            );


            $db->insert_query ("housepoints", $new_record);
            redirect("member.php?action=profile&uid={$userid}");
        }

        if ($_POST['minus']) {
            $minuspoints = $_POST['minuspoints'];
            $reason = $_POST['reason'];
            $new_record = array(
                "uid" => $db->escape_string($userid),
                "points" => $db->escape_string("-".$minuspoints),
                "reason" => $db->escape_string($reason)
            );


            $db->insert_query ("housepoints", $new_record);
            $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points - '" . $minuspoints . "'  WHERE uid = '" . $userid . "'");
            redirect("member.php?action=profile&uid={$userid}");
        }

        if ($memprofile['hp_wanted'] == '0') {
            $wantedpoints = $mybb->settings['wanted_points'];
            eval("\$housepoints_wanted .= \"" . $templates->get("housepoints_wanted") . "\";");

            $wanted = $mybb->input['wanted'];
            if ($wanted) {
                $reason = "Gesuchsübernahme";
                $new_record = array(
                    "uid" => $db->escape_string($userid),
                    "points" => $db->escape_string($wantedpoints),
                    "reason" => $db->escape_string($reason)
                );
                $db->insert_query ("housepoints", $new_record);
                $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points + '" . $wantedpoints . "', hp_wanted = '1'  WHERE uid = '" . $userid . "'");
                redirect("member.php?action=profile&uid={$userid}");
            }

        }

        if($memprofile['usergroup'] == 8 OR $memprofile['usergroup'] == 9 OR $memprofile['usergroup'] == 10 OR $memprofile['usergroup'] == 11){
            eval("\$profile_hp_options = \"" . $templates->get("housepoints_profile_options") . "\";");
        }

    }

  /*
     * die Hauspunkte noch im Profil ausgeben
     */


    $select = $db->query("SELECT *, hp_points
    FROM ".TABLE_PREFIX."users
    where uid = '".$memprofile[uid]."'
    ");

    $row = $db->fetch_array($select);
    $housepoints = number_format($row['hp_points'], '0', ',', '.');
            eval("\$profile_points = \"". $templates->get("housepoints_profile_points")."\";");

}


function wanted_newthread(){
    global $templates, $mybb, $db, $forum, $post;


    $uid = $mybb->user['uid'];
    //Gesuch


    if($mybb->settings['hp_gesuche_creat'] == 1) {
        $gesuchs_id = $mybb->settings['hp_gesuche_kat'];


        $forum['parentlist'] = "," . $forum['parentlist'] . ",";
        if (preg_match("/,$gesuchs_id,/i", $forum['parentlist'])) {
            if(is_member($mybb->settings['hp_groups'])){

                $wantedpoints = $mybb->settings['wanted_points'];
                $new_record = array(
                    "uid" => $db->escape_string($uid),
                    "points" => $db->escape_string($wantedpoints),
                    "reason" => $db->escape_string("Gesuche erstellt")
                );


                $db->insert_query("housepoints", $new_record);
                $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points + '" . $wantedpoints . "'  WHERE uid = $uid");
            }

        }
    }


//länge schauen (neues Thread)

    //Inplaykategorie
    $ip_id = $mybb->settings['hp_inplay'];

    //Zusätzliche Punkte bei Längeren Post
    $min_postlength = $mybb->settings['hp_ip_max'];

    //Zusatzpunkte für den Post
    $morepostpoints = $mybb->settings['hp_ip_points'];

    //Punkte
    $postpoints = $mybb->settings['hp_post'];

    //Alle IDs des Inplays
    $inplay = explode(",", $ip_id);

    $forum['parentlist'] = ",".$forum['parentlist'].",";
    if(preg_match("/,$ip_id,/i", $forum['parentlist'])) {
        //es wird über die Fids itariert
        foreach ($inplay as $ip) {

            $select = $db->query("SELECT *
    FROM " . TABLE_PREFIX . "posts p
    LEFT JOIN " . TABLE_PREFIX . "forums f
    ON p.fid = f.fid
    WHERE f.fid = $ip
    ");

            $data = $db->fetch_array($select);

            //Punkte pro Post werden drauf gerechnet.
            $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points + '" . $postpoints . "'  WHERE uid = $uid");

            //Wenn jemand fleißig war, bekommt er zusätzliche Punkte
            if (strlen($data['message']) >= $min_postlength) {

                $postpoints = $postpoints + $morepostpoints;
                $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points + '" . $morepostpoints . "'  WHERE uid = $uid");
            }



        }
    }
}

//Postlänge in einer neuen Antwort
function houspoints_inplay()
{
    global $db, $mybb, $templates, $forum, $post, $fid;
    $uid = $mybb->user['uid'];
    //Inplaykategorie
    $ip_id = $mybb->settings['hp_inplay'];

    //Zusätzliche Punkte bei Längeren Post
    $min_postlength = $mybb->settings['hp_ip_max'];

    //Zusatzpunkte für den Post
    $morepostpoints = $mybb->settings['hp_ip_points'];

    //Punkte
    $postpoints = $mybb->settings['hp_post'];
    $forum['parentlist'] = ",".$forum['parentlist'].",";
    if(preg_match("/,$ip_id,/i", $forum['parentlist'])) {
        //es wird über die Fids itariert
        $inplay = explode(",", $ip_id);

        foreach ($inplay as $ip) {


            $select = $db->query("SELECT *
    FROM " . TABLE_PREFIX . "posts p
    LEFT JOIN " . TABLE_PREFIX . "forums f
    ON p.fid = f.fid
    LEFT JOIN " . TABLE_PREFIX . "threads t
    ON f.fid = t.fid
    WHERE f.fid = $ip
    ");

            $data = $db->fetch_array($select);


            //Punkte pro Post werden drauf gerechnet.
            $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points + '" . $postpoints . "'  WHERE uid = $uid");

            if (strlen($data['message']) >= $min_postlength && $min_postlength != 0) {

                $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points + '" . $morepostpoints . "'  WHERE uid = $uid");
            }

        }
    }

}


//Mitgliederliste, wird unter die Sterne angezeigt
function housepoints_memberlist_bit(&$user){
    global $mybb, $db, $templates, $user, $housepoints, $alt_bg, $housepoints_memberlist;

    $uid = $user['uid'];


    $group_sect = $db->simple_select("usergroups", "gid,title", "gid IN ('".str_replace(',', '\',\'', $mybb->settings['hp_groups'])."')");
    while($house = $db->fetch_array($group_sect)) {

        $select = $db->query("SELECT *
          FROM " . TABLE_PREFIX . "users 
          WHERE usergroup = $house[gid]
          AND uid = $uid
          ");
        $housepoints = "";

        while ($row = $db->fetch_array($select)) {

            $housepoints = number_format($row['hp_points'], '0', ',', '.');

            $housepoints = "<div><b>".$housepoints. "</b> Hausepunkte</div>";

            return $user;
        }
    }
}

function housepoints_postbit(&$post)
{
    global $mybb, $db, $templates, $housepoints;

    $uid = $post['uid'];
    $post['housepoints'] = "";

    $group_sect = $db->simple_select("usergroups", "gid,title", "gid IN ('" . str_replace(',', '\',\'', $mybb->settings['hp_groups']) . "')");

    while ($house = $db->fetch_array($group_sect)) {

        $select = $db->query("SELECT *
          FROM " . TABLE_PREFIX . "users 
          WHERE usergroup = $house[gid]
          AND uid = $uid
          ");

        $post['housepoints'] = "";

        while ($row = $db->fetch_array($select)) {

            $post['hp_points'] = number_format($row['hp_points'], '0', ',', '.');

            eval("\$post['housepoints'] = \"" . $templates->get("housepoints_postbit") . "\";");

        }


    }
}


function housepoints_modcp_nav(){
    global $db, $templates, $mybb, $modcp_hp;
    $modcp_hp = "<tr><td class=\"trow1 smalltext\"><a href='modcp.php?action=housepoints' class=\"modcp_nav_item modcp_nav_editprofile\">Hauspunkte</a></td></tr>
<tr><td class=\"trow1 smalltext\"><a href='modcp.php?action=housepoints_protocol' class=\"modcp_nav_item modcp_nav_editprofile\">Hauspunkte Protokoll</a></td></tr>
";
}

function housepoints_modcp(){
    global $mybb, $templates, $lang, $header, $points_reset, $headerinclude, $footer, $page, $db, $charaktere, $date, $thread, $regdate, $modcp_nav, $students;

    if($mybb->get_input('action') == 'housepoints')
    {
        // Add a breadcrumb
        add_breadcrumb('Hauspunkte Gesamtübersicht', "modcp.php?action=housepoints");

        $points_reset = "<a href='modcp.php?action=housepoints&reset=all'>alle Punkte zurücksetzen</a>";


        $allgid = $mybb->settings['hp_groups'];
        $gids = explode(",", $allgid);


        $charas =    $select = $db->query("SELECT *
          FROM " . TABLE_PREFIX . "users 
          WHERE usergroup IN ('" . str_replace(',', '\',\'', $mybb->settings['hp_groups']) . "')
         ORDER BY username ASC
          ");

        while($chara = $db->fetch_array($charas)){
            $uid = $chara['uid'];
            $student = $chara['username'];

            $students .= "<option value='{$uid}'>{$student}</option> ";

        }




        foreach ($gids as $gid){



            $select = $db->query("SELECT *
          FROM " . TABLE_PREFIX . "users 
          WHERE usergroup = $gid
          order by hp_points desc, username asc
          ");


            while ($row = $db->fetch_array($select)) {

                $housepoints = number_format($row['hp_points'], '0', ',', '.');
                $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
                $user = build_profile_link($username, $row['uid']);
                $userid = $row['uid'];
                eval("\$housepoints_user .= \"" . $templates->get("housepoints_modcp_bit") . "\";"); // Hier wird das erstellte Template geladen

            }


        }


        //Punkte hinzufügen

        if ($_POST['points']) {
            $userid = $_POST['uid'];
            $housepoints = $_POST['housepoints'];
            $reason = $_POST['reason'];
            $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points + '" . $housepoints . "'  WHERE uid = '" . $userid . "'");
            $new_record = array(
                "uid" => $db->escape_string($userid),
                "points" => $db->escape_string($housepoints),
                "reason" => $db->escape_string($reason)
            );


            $db->insert_query ("housepoints", $new_record);
            redirect("modcp.php?action=housepoints");
        }

        /*
         * Wenn allen Schülern Punkte gutgeschrieben oder abgezogen werden sollen
         * dann passiert das hier.
         */
        if ($_POST['allpoints']) {
            $housepoints = $_POST['housepoints'];
            $reason = $_POST['reason'];
            $allgid = $mybb->settings['hp_groups'];

            $gids = explode(",", $allgid);

            foreach ($gids as $gid){

                $charas = $db->query("SELECT *
                FROM ".TABLE_PREFIX."users
                where usergroup = '".$gid."'
                ");

                while($chara = $db->fetch_array($charas)){

                    $uid = $chara['uid'];
                    $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points + '" . $housepoints . "'  WHERE uid = '" . $uid . "'");
                    $new_record = array(
                        "uid" => $db->escape_string($uid),
                        "points" => $db->escape_string($housepoints),
                        "reason" => $db->escape_string($reason)
                    );


                    $db->insert_query ("housepoints", $new_record);
                }


            }
            redirect("modcp.php?action=housepoints");

        }


        /*
         * wenn nur bestimmten Schüler/Charakteren Punkte gutgeschrieben oder abgezogen werden
         */
        if ($_POST['viewpoints']) {
            $housepoints = $_POST['housepoints'];
            $reason = $_POST['reason'];
                    $students = $_POST['students'];

                    foreach ($students as $student){
                    $uid = $student;
                        $db->query("UPDATE " . TABLE_PREFIX . "users SET hp_points = hp_points + '" . $housepoints . "'  WHERE uid = '" . $uid . "'");
                        $new_record = array(
                            "uid" => $db->escape_string($uid),
                            "points" => $db->escape_string($housepoints),
                            "reason" => $db->escape_string($reason)
                        );


                        $db->insert_query ("housepoints", $new_record);

                    }

            redirect("modcp.php?action=housepoints");

        }

        $reset = $mybb->input['reset'];
        if($reset){
            $db->query("UPDATE ".TABLE_PREFIX."users SET hp_points = 0");
            redirect("modcp.php?action=housepoints");
        }

        eval("\$page = \"" . $templates->get("housepoints_modcp") . "\";"); // Hier wird das erstellte Template geladen
        output_page($page);

    }


    if($mybb->get_input('action') == 'housepoints_protocol')
    {
        // Add a breadcrumb
        add_breadcrumb('Hauspunkte Protokol', "modcp.php?action=housepoints_protocol");

        $points_reset = "<a href='modcp.php?action=housepoints&reset=all'>alle Punkte zurücksetzen</a>";



        $select = $db->query("SELECT *
          FROM " . TABLE_PREFIX . "housepoints h 
         LEFT JOIN " . TABLE_PREFIX . "users u
          on (h.uid = u.uid)
          order by hid desc, username asc
          ");


        while ($row = $db->fetch_array($select)) {

            $housepoints = $row['points'];
            $username = format_name($row['username'], $row['usergroup'], $row['displaygroup']);
            $user = build_profile_link($username, $row['uid']);
            $reason = $row['reason'];
            eval("\$housepoints_protocol_user .= \"" . $templates->get("housepoints_modcp_protocol_bit") . "\";"); // Hier wird das erstellte Template geladen

        }


        eval("\$page = \"" . $templates->get("housepoints_modcp_protocol") . "\";"); // Hier wird das erstellte Template geladen
        output_page($page);

    }
}

//wer ist wo
$plugins->add_hook('fetch_wol_activity_end', 'housepoints_user_activity');
$plugins->add_hook('build_friendly_wol_location_end', 'housepoints_location_activity');

function housepoints_user_activity($user_activity){
    global $user;

    if(my_strpos($user['location'], "hauspunkte.php") !== false) {
        $user_activity['activity'] = "hauspunkte.php";
    }

    return $user_activity;
}

function housepoints_location_activity($plugin_array) {
    global $db, $mybb, $lang;

    if($plugin_array['user_activity']['activity'] == "hauspunkte.php")
    {
        $plugin_array['location_name'] = "Sieht sich die <b><a href='hauspunkte.php'>Hauspunkte</a></b> an.";
    }


    return $plugin_array;
}


/**
 * Was passiert wenn ein User gelöscht wird
 * Informationen aus dem Protokoll löschen, so dass keine Gästeinfos mehr vorhanden sind.
 */
$plugins->add_hook("admin_user_users_delete_commit_end", "user_delete");
function user_delete()
{
    global $db, $cache, $mybb, $user;

    $todelete = (int)$user['uid'];
    $username = $db->escape_string($user['username']);

    $db->delete_query('housepoints', "uid = " . $todelete . "");
}
?>
