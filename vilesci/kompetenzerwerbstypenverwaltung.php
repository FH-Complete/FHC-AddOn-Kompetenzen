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

if(isset($_POST['save']))
{
	if(isset($_POST['kompetenzerwerbstyp_kurzbz']))
		$kompetenzerwerbstyp_kurzbz = $_POST['kompetenzerwerbstyp_kurzbz'];
	
	if(isset($_POST['kompetenzerwerbstyp_kurzbz_original']))
		$kompetenzerwerbstyp_kurzbz_original = $_POST['kompetenzerwerbstyp_kurzbz_original'];
	else
		$kompetenzerwerbstyp_kurzbz_original = '';
	
	$bezeichnung = $_POST['bezeichnung'];
	
	$kompetenz = new kompetenz();
	if($kompetenzerwerbstyp_kurzbz_original!='')
	{
		$kompetenz->loadErwerbstyp($_POST['kompetenzerwerbstyp_kurzbz']);
		$kompetenz->new = false;
		$kompetenz->kompetenzerwerbstyp_kurzbz_original = $kompetenzerwerbstyp_kurzbz_original;
	}
	else
		$kompetenz->new=true;
	
	$kompetenz->kompetenzerwerbstyp_kurzbz=$kompetenzerwerbstyp_kurzbz;
	$kompetenz->bezeichnung = $bezeichnung;
	
	if($kompetenz->saveErwerbstyp())
	{
		echo '<span class="ok">Erfolgreich gespeichert</span>';
	}
	else
	{
		echo '<span class="error">Fehler beim Speicher:'.$kompetenz->errormsg.'</span>';
	}
}
if($action=='delete')
{
	if(isset($_GET['kompetenzerwerbstyp_kurzbz']))
	{
		$kompetenz = new kompetenz();
		if($kompetenz->deleteErwerbstyp($_GET['kompetenzerwerbstyp_kurzbz']))
			echo '<span class="ok">Erfolgreich entfernt</span>';
		else
			echo '<span class="error">Fehler beim Entfernen: '.$kompetenz->errormsg.'</span>';		
	}
}

$kompetenz = new kompetenz();
$erwerbstypen = $kompetenz->getErwerbstypen();

echo '<ul>';
foreach($erwerbstypen as $kurzbz=>$bezeichnung)
{
	if($action=='edit' && $_GET['kompetenzerwerbstyp_kurzbz']==$kurzbz)
	{
		echo '<li>
		<form action="kompetenzerwerbstypenverwaltung.php?action=save" method="POST">
		<input type="hidden" name="kompetenzerwerbstyp_kurzbz_original" value="'.$db->convert_html_chars($kurzbz).'" /> 
		Bezeichnung <input type="text" name="bezeichnung" maxlength="256" value="'.$db->convert_html_chars($bezeichnung).'" />
		Kurzbz <input type="text" name="kompetenzerwerbstyp_kurzbz" value="'.$db->convert_html_chars($kurzbz).'" maxlength="32" />
		<input type="submit" name="save" value="ändern" />
		</form></li>';				
	}
	else
	{
		echo '
			<li>
				<a href="kompetenzerwerbstypenverwaltung.php?action=edit&kompetenzerwerbstyp_kurzbz='.$db->convert_html_chars($kurzbz).'">'.$db->convert_html_chars($bezeichnung.' ('.$kurzbz.')').'</a>
				&nbsp;<a href="kompetenzerwerbstypenverwaltung.php?action=delete&kompetenzerwerbstyp_kurzbz='.$kurzbz.'"><img src="../../../skin/images/delete_x.png" title="Eintrag entfernen" height="10px"></a>
			</li>';
	}
}

echo '<li>
<form action="kompetenzerwerbstypenverwaltung.php?action=save" method="POST">
<input type="hidden" name="kompetenzerwerbstyp_kurzbz_original" value="" />
Bezeichnung <input type="text" maxlength="256" name="bezeichnung" />
Kurzbz <input type="text" name="kompetenzerwerbstyp_kurzbz" value="" maxlength="32"/>
<input type="submit" value="Neu anlegen" name="save"/>
</form>
</li>';

echo '</ul>';
	
echo '
</body>
</html>';
?>