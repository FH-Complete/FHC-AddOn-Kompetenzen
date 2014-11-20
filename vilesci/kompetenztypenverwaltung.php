<?php
/* Copyright (C) 2013 FH Technikum-Wien
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307, USA.
 *
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../kompetenz.class.php');

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../../skin/vilesci.css" type="text/css">
	<title>Verwaltung der Kompetenztypen</title>
</head>
<body>
<h1>Verwaltung der Kompetenztypen</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

$db = new basis_db();

if(!$rechte->isBerechtigt('basis/addon'))
{
	die('Sie haben keine Berechtigung fuer diese Seite');
}

$action = isset($_GET['action'])?$_GET['action']:'';
$id = isset($_GET['id'])?$_GET['id']:'';


if(isset($_POST['save_kompetenztyp']))
{
	if(isset($_POST['kompetenztyp_id']))
		$kompetenztyp_id = $_POST['kompetenztyp_id'];
	else
		$kompetenztyp_id='';
	$kompetenztyp_kurzbz = $_POST['kompetenztyp_kurzbz'];
	$bezeichnung = $_POST['bezeichnung'];
	$kompetenztyp_parent_id = $_POST['kompetenztyp_parent_id'];
	
	$kompetenz = new kompetenz();
	if($kompetenztyp_id!='')
	{
		$kompetenz->loadTyp($_POST['kompetenztyp_id']);
		$kompetenz->new = false;
	}
	else
		$kompetenz->new=true;
	
	$kompetenz->kompetenztyp_kurzbz=$kompetenztyp_kurzbz;
	$kompetenz->bezeichnung = $bezeichnung;
	
	$kompetenz->kompetenztyp_parent_id = $kompetenztyp_parent_id;
	if($kompetenz->saveTyp())
	{
		echo '<span class="ok">Erfolgreich gespeichert</span>';
		$id = $kompetenz->kompetenztyp_id;
		$action='niveaustufen';
	}
	else
	{
		echo '<span class="error">Fehler beim Speicher:'.$kompetenz->errormsg.'</span>';
	}
}
if($action=='deletetyp')
{
	$kompetenz = new kompetenz();
	if($kompetenz->deleteTyp($id))
		echo '<span class="ok">Erfolgreich entfernt</span>';
	else
		echo '<span class="error">Fehler beim Entfernen:'.$kompetenz->errormsg.'</span>';
		
	$id='';
}

echo '<table><tr><td valign="top">';

// Uebersichtsliste ueber die Typen
$kompetenztyp = new kompetenz();
$kompetenztyp->getKompetenztypen();

echo '<ul>';
foreach($kompetenztyp->kompetenztypen as $row)
{
	if($row['kompetenztyp_parent_id']=='')
	{
		// Root Nodes
		echo '<li>';
		printItem($row['kompetenztyp_id'], $row['bezeichnung']);		
		addChilds($row['kompetenztyp_id']);
		echo '</li>';
	}
}
echo '</ul>';

function printItem($kompetenztyp_id, $bezeichnung)
{
	global $db, $id;
	 
	if($id==$kompetenztyp_id)
		$style='style="text-decoration: underline"';
	else
		$style='';
	echo '<a href="kompetenztypenverwaltung.php?action=edittyp&id='.$kompetenztyp_id.'" '.$style.'>
			'.$db->convert_html_chars($bezeichnung).' <img src="../../../skin/images/edit.png" height="13px" title="Eintrag editieren"/></a>
			<a href="kompetenztypenverwaltung.php?action=deletetyp&id='.$kompetenztyp_id.'">
			<img src="../../../skin/images/delete_x.png" height="13px" title="Eintrag entfernen"/></a>';
}

function addChilds($id)
{
	global $data, $kompetenztyp, $db;
	
	echo '<ul>';
	foreach($kompetenztyp->kompetenztypen as $row)
	{
		if($row['kompetenztyp_parent_id']==$id)
		{
			//Child Node
			echo '<li>';
			printItem($row['kompetenztyp_id'],$row['bezeichnung']);
			addChilds($row['kompetenztyp_id']);
			echo '</li>';
		}
	}
	echo '</ul>';
}
echo '<hr><a href="kompetenztypenverwaltung.php?action=typneu">Neuen Typ hinzufügen</a>';
echo '</td>
<td width="50px">&nbsp;</td>
<td valign=top>';

// Bearbeiten von Typen
/*if($action=='edittyp' || $action=='typneu' || $action=='niveaustufen' || $action=='niveaustufenedit')
{*/
	$kompetenz = new kompetenz();
	
	if($id!='')
	{
		echo '<h2>Bearbeiten des Typs</h2>';
		if(!$kompetenz->loadTyp($id))
		{
			echo '<span class="error">Fehler beim Laden des Eintrags: '.$kompetenz->errormsg.'</span>';
		}
	}
	else
		echo '<h2>Neuer Typ</h2>';
	
	
	echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
	echo '<input type="hidden" name="kompetenztyp_id" value="'.$db->convert_html_chars($kompetenz->kompetenztyp_id).'" />';
	echo '<table>';
	echo '<tr><td>Kurzbezeichnung</td><td><input type="text" name="kompetenztyp_kurzbz" maxlength="64" value="'.$db->convert_html_chars($kompetenz->kompetenztyp_kurzbz).'" /></td></tr>';
	echo '<tr><td>Bezeichnung</td><td><input type="text" name="bezeichnung" maxlength="256" value="'.$db->convert_html_chars($kompetenz->bezeichnung).'" /></td></tr>';
	echo '<tr><td>Übergeordneter Typ</td><td><select name="kompetenztyp_parent_id">
	<option value="">-</option>';
	foreach($kompetenztyp->result as $row)
	{
		if($row->kompetenztyp_id==$kompetenz->kompetenztyp_parent_id)
			$selected = 'selected';
		else
			$selected='';
		
		echo '<option value="'.$row->kompetenztyp_id.'" '.$selected.'>'.$db->convert_html_chars($row->bezeichnung).'</option>';
	}
	echo '</select></td></tr>';
	echo '<tr><td></td><td><input type="submit" name="save_kompetenztyp" value="Speichern" /></td></tr>';
	echo '</table></form>';

	/*
	if($id!='')
	{
		echo '<br><br><a href="kompetenztypenverwaltung.php?action=niveaustufen&id='.$id.'">Niveaustufen editieren</a>';
	}*/
	
//}

// Speichern von Niveaustufen
if($action=='niveaustufesave')
{
	$stufe = $_POST['stufe'];
	$kompetenzniveaustufe_id= (isset($_POST['kompetenzniveaustufe_id'])?$_POST['kompetenzniveaustufe_id']:'');
	
	$kompetenz = new kompetenz();
	if($kompetenzniveaustufe_id!='')
	{
		$kompetenz->loadNiveaustufe($kompetenzniveaustufe_id);
		$kompetenz->new=false;
	}
	else
	{
		$kompetenz->new=true;
		$kompetenz->kompetenztyp_id=$id;
	}
	
	$kompetenz->stufe=$stufe;
	if($kompetenz->saveNiveaustufe())
		echo '<span class="ok">Daten erfolgreich gespeichert</span>';
	else
		echo '<span class="error">Fehler beim Speichern der Daten:'.$kompetenz->errormsg.'</span>';
	
	$action='niveaustufen';
}

// Loeschen von Niveaustufen
if($action=='niveaustufedelete')
{
	if(isset($_GET['kompetenzniveaustufe_id']))
	{
		$kompetenz = new kompetenz();
		$kompetenzniveaustufe_id=$_GET['kompetenzniveaustufe_id'];
		if($kompetenz->deleteNiveaustufe($kompetenzniveaustufe_id))
			echo '<span class="ok">Erfolgreich entfernt</span>';
		else
			echo '<span class="error">Fehler beim Löschen: '.$kompetenz->errormsg.'</span>';
		$action='niveaustufen';
	}
	else
	{
		die('Falsche Parameterübergabe');
	}
}

// Uebersicht der Niveaustufen
if($action=='niveaustufen' || $action=='niveaustufenedit' || $action=='edittyp')
{
	$kompetenz = new kompetenz();
	if($kompetenz->loadTyp($id))
	{	
		echo '<h2>Niveaustufen von '.$kompetenz->bezeichnung.'</h2>';
		
		$kompetenz->getNiveaustufe($id);
		echo '<ul>';
		foreach($kompetenz->result as $row)
		{
			if($action=='niveaustufenedit' && $_GET['kompetenzniveaustufe_id']==$row->kompetenzniveaustufe_id)
			{
				echo '<li>
				<form action="kompetenztypenverwaltung.php?action=niveaustufesave&id='.$db->convert_html_chars($id).'" method="POST">
				<input type="hidden" name="kompetenzniveaustufe_id" value="'.$db->convert_html_chars($row->kompetenzniveaustufe_id).'" />
				<input type="text" name="stufe" size="10" maxlength="64" value="'.$db->convert_html_chars($row->stufe).'" />
				<input type="submit" name="save" value="ändern" />
				</form></li>';				
			}
			else
			{
				echo '
					<li>
						<a href="kompetenztypenverwaltung.php?action=niveaustufenedit&id='.$id.'&kompetenzniveaustufe_id='.$row->kompetenzniveaustufe_id.'">'.$row->stufe.'</a>
						&nbsp;<a href="kompetenztypenverwaltung.php?action=niveaustufedelete&id='.$id.'&kompetenzniveaustufe_id='.$row->kompetenzniveaustufe_id.'"><img src="../../../skin/images/delete_x.png" title="Eintrag entfernen" height="10px"></a>
					</li>';
			}
		}
		echo '<li>
			<form action="kompetenztypenverwaltung.php?action=niveaustufesave&id='.$id.'" method="POST">
			<input type="text" maxlength="64" size="10" name="stufe" />
			<input type="submit" value="Neu anlegen" name="save"/>
			</form>
			</li>';
		echo '</ul>';
	}
	else
		echo '<span class="error">Fehler beim Laden des Typs: '.$kompetenz->errormsg.'</span>';
}
echo '</td></tr></table>
</body>
</html>';
?>