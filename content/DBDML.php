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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at>
 */
/*
 * Datenbankaktionen für Kompetenzen Addon
 */
require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');
require_once('../kompetenz.class.php');

$user = get_uid();

$db = new basis_db();
$return = false;
$errormsg = 'unknown';
$data = '';
$error = false;

$type = isset($_POST['type'])?$_POST['type']:'';

//Berechtigungen laden
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($user);
if(!$rechte->isBerechtigt('addon/kompetenzen'))
{
	$return = false;
	$errormsg = 'Keine Berechtigung';
	$data = '';
	$error = true;
}

if(!$error)
{
	switch($type)
	{
		case 'saveKompetenzen':
			
			if(!$rechte->isBerechtigt('addon/kompetenzen',null,'sui'))
			{
				$return = false;
				$errormsg = 'Sie haben keine Berechtigung für diesen Vorgang';
				$data = '';
				$error = true;
			}
			else 
			{
				$kompetenz = new kompetenz();
				$kompetenz->kompetenz_id = $_POST['kompetenz_id'];
				$kompetenz->person_id = $_POST['person_id'];
				$kompetenz->kompetenztyp_id = $_POST['kompetenztyp_id'];
				$kompetenz->kompetenzniveaustufe_id = $_POST['kompetenzniveaustufe_id'];
				$kompetenz->kompetenzerwerbstyp_kurzbz = $_POST['kompetenzerwerbstyp_kurzbz'];
				$kompetenz->bezeichnung = $_POST['bezeichnung'];
				$kompetenz->kompetenzniveau = $_POST['kompetenzniveau'];
				$kompetenz->beginn = $_POST['beginn'];
				$kompetenz->ende = $_POST['ende'];
				$kompetenz->ort = $_POST['ort'];
				
				$kompetenz->new = ((isset($_POST['neu']) && $_POST['neu']=='true')?true:false);
				
				if($kompetenz->new)
				{
					$kompetenz->insertamum=date('Y-m-d H:i:s');
					$kompetenz->insertvon = $user;
				}
				
				$kompetenz->updateamum = date('Y-m-d H:i:s');
				$kompetenz->updatevon = $user;
				
				if($kompetenz->save())
				{
					$data = $kompetenz->kompetenz_id;
					$return = true;
				}
				else
				{				
					$errormsg=$kompetenz->errormsg;
					$return = false;
				}
			}
					
			break;
			
		case 'deleteKompetenzen':
			//Loeschen eines Eintrages
			
			if(!$rechte->isBerechtigt('addon/kompetenzen',null,'suid'))
			{
				$return = false;
				$errormsg = 'Sie haben keine Berechtigung für diesen Vorgang';
				$data = '';
				$error = true;
			}
			else 
			{
				$kompetenz = new kompetenz();
				if($kompetenz->delete($_POST['kompetenz_id']))
				{
					$return = true;
				}
				else
				{
					$return = false;
					$errormsg = $kompetenz->errormsg;
				}
			}
			break;
	
		default:
			$return = false;
			$errormsg = 'Unkown type';
			$data = '';
	}
}

//RDF mit den Returnwerden ausgeben
echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<RDF:RDF
	xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
	xmlns:NC="http://home.netscape.com/NC-rdf#"
	xmlns:DBDML="http://www.technikum-wien.at/dbdml/rdf#"
>
  <RDF:Seq RDF:about="http://www.technikum-wien.at/dbdml/msg">
	<RDF:li>
    	<RDF:Description RDF:about="http://www.technikum-wien.at/dbdml/0" >
    		<DBDML:return>'.($return?'true':'false').'</DBDML:return>
        	<DBDML:errormsg><![CDATA['.$errormsg.']]></DBDML:errormsg>
        	<DBDML:data><![CDATA['.$data.']]></DBDML:data>
        </RDF:Description>
	</RDF:li>
  </RDF:Seq>
</RDF:RDF>
';
?>