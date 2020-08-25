****
README HAUSPUNKTE
***

ACHTUNG! Der Plugin wurde, was seine Punkte berechnung angeht, neu gestaltet. Es wird empfohlen, da auch eine neue Tabellenspalte eingefügt wurde, den Plugin neu zu installieren.
Bitte sichert zuvor eure Usertabelle! Auch wurden alle Templates unbenannt, deswegen erst deinstallieren, dann neu hochladen!

Stundengläser sind wie immer im CSS einzufärben, indem man ihnen den Namen der Gruppen gibt.

####
DATENBANK
####

Das Feld hp_wanted wurde hinzugefügt:
 $db->query("ALTER TABLE `".TABLE_PREFIX."users` ADD `hp_wanted` int(11) NOT NULL DEFAULT '0' AFTER `hp_points`;");
 
 ###
 templates
 ###
 
 # housepoints
 
 <html>
<head>
<title>{$settings['bbname']} - Hauspunkte</title>
{$headerinclude}
</head>
<body>
{$header}
<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
<tr>
<td class="thead"><strong>Hausepunkte - Die Übersicht</strong></td>
</tr>
<tr>
<td class="trow1" align="center">
	<table width="500px" style="margin:auto;"><tr><td>
{$housepoints_bit}
		</td>
</tr>
</table>
</td>
</tr>
</table>
{$footer}
</body>
</html>


# housepoints_bit

<div style="width: 40%; height: 380px; float: left; margin: 5px 5px;">
<div class="{$house['title']}" style="font-size: 130px; text-align:center; opacity: 0.3;"><i class="fas fa-hourglass"></i></div>
		<div style="text-align:center; font-size: 30px; text-transform:uppercase; width: 98px; margin: -45px auto 0 auto;"  class="{$house['title']}">{$all}</div>
	<div style="text-align:center; font-size: 20px; text-transform:uppercase; padding-top: 3px;">{$house['title']}</div>
	<div style="max-height: 200px; overflow:auto; text-align: center;" class="smalltext">{$student}</div>
</div>

#housepoints_header

<table style="margin:auto; width: 200px;"><tr><td class="thead"><strong>Hauspunkte</strong></td></tr></td>
<tr><td class="trow1">{$housepoints_header_bit}</td></tr>
</table>

# housepoints_header_bit

<div style="width: 40%; height: 80px; float: left; margin: 5px 5px;">
<div class="{$house['title']}" style="font-size: 50px; text-align:center; opacity: 0.3;"><i class="fas fa-hourglass"></i></div>
		<div style="text-align:right; font-size: 15px; text-transform:uppercase; width: 25px; margin: -18px auto 0 auto;"  class="{$house['title']}">{$all}</div>
	<div style="text-align:center; font-size: 10px; text-transform:uppercase;">{$house['title']}</div>
</div>

# housepoints_memberlist_bit
<br /><strong>{$housepoints} Hauspunkte</strong>

# housepoints_menu

<li><a href="hauspunkte.php">Hauspunkte</a></li>

housepoints_postbit

Hauspunkte: {$housepoints}

# housepoints_profile_options
<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" width="100%" class="tborder">
<tr>
<td  class="thead"><strong>Hauspunkte</strong></td>
</tr>

<tr>
<td class="trow2"><table border="0" cellspacing="5" cellpadding="{$theme['tablespace']}" class="tborder" style="width: 50%; margin:auto;">
	<form id="housepoints" method="post" action="member.php?action=profile&uid={$memprofile['uid']}">
		<tr><td class="trow2"><b>Pluspunkte</b></td><td class='trow1'><input type="text" name="pluspoints" id="pluspoints" value="" class="textbox" /></td>
<td align="center"><input type="submit" name="plus" value="eintragen" id="submit" class="button"></td></tr></form>
		<form id="housepoints" method="post" action="member.php?action=profile&uid={$memprofile['uid']}">
		<tr><td class="trow2"><b>Minuspunkte</b></td><td class='trow1'><input type="text" name="minuspoints" id="minuspoints" value="" class="textbox" /></td>
<td align="center"><input type="submit" name="minus" value="eintragen" id="submit" class="button"></td></tr></form>
{$housepoints_wanted}
</table>
</td>
</tr>

</table>
<br />


#housepoints_profile_points

<strong>Hauspunkte</strong> {$housepoints}

# housepoints_wanted

<tr><td class="trow1" colspan="3" align="center"><a href="member.php?action=profile&wanted={$memprofile['uid']}">Punkte für Angenommenes Gesuche vergeben.</a></td></tr>

# housepoints_modcp
<html>
<head>
<title>{$mybb->settings['bbname']} - Hauspunkte</title>
{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
		<tr>
			{$modcp_nav}
			<td valign="top">
				<table border="0" cellspacing="{$theme['borderwidth']}" cellpadding="{$theme['tablespace']}" class="tborder">
			<tr><td class="thead" ><h1>Hauspunkte</h1></td> </tr>
					<tr><td class="trow1" align="center">
						{$resert}
						<table width="500px" style="margin:auto">
							<tr><td class="thead"><h2>Charakter</h2></td><td class="thead"><h2>Hauspunkte</h2></td></tr>
							{$housepoints_user}
						</table>
						</td></tr>
			</table>

</body>
</html>

# housepoints_modcp_bit
<tr><td class="trow1">{$user}</td><td class="trow2">{$housepoints} Hauspunkte</td></tr>

###
Zusätze
##

### Hauspunkte im Postbit an anderen Ort bringen
Da es leider über dne Plugin NICHT möglich ist, in den postbit_author zu packen, müsst ihr das manuell machen (am besten über Patches),


suche:
eval("\$post['user_details'] = \"".$templates->get("postbit_author_user")."\";");

füge darüber ein:
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
	
	
## Punkte bei WoB

um jemanden Automatisch Punkte zukommen zu lassen, wenn er angenommen wurde, könnt ihr, insofern in die Erweiterung 'WOB mit einem Klick verteilen (User annehmen)' eingefügt habt, wie folgt erweitern:

showthread.php

sucht:
$insert_array = $db->update_query("users", $new_record, "uid = '$authorid'");

darunter:
 $db->query("UPDATE ".TABLE_PREFIX."users SET hp_points = 15  WHERE uid = '".$authorid."'");
 
 --> Ersetzt die 15 mit dem Wert, den ihr dafür vergebt.
