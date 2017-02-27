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
/**
 * FH-Complete Addon Template Datenbank Check
 *
 * Prueft und aktualisiert die Datenbank
 */
require_once('../../config/system.config.inc.php');
require_once('../../include/basis_db.class.php');
require_once('../../include/functions.inc.php');
require_once('../../include/benutzerberechtigung.class.php');

// Datenbank Verbindung
$db = new basis_db();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
        "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<link rel="stylesheet" href="../../skin/fhcomplete.css" type="text/css">
	<link rel="stylesheet" href="../../skin/vilesci.css" type="text/css">
	<title>Addon Datenbank Check</title>
</head>
<body>
<h1>Addon Datenbank Check</h1>';

$uid = get_uid();
$rechte = new benutzerberechtigung();
$rechte->getBerechtigungen($uid);

if(!$rechte->isBerechtigt('basis/addon', null, 'suid'))
{
	exit('Sie haben keine Berechtigung für die Verwaltung von Addons');
}

echo '<h2>Aktualisierung der Datenbank</h2>';

// Code fuer die Datenbankanpassungen
if($result = $db->db_query("SELECT schema_name FROM information_schema.schemata WHERE schema_name = 'addon'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "CREATE SCHEMA addon;
				GRANT USAGE ON SCHEMA addon TO vilesci;
				GRANT USAGE ON SCHEMA addon TO web;
				";

		if(!$db->db_query($qry))
			echo '<strong>Schema addon: '.$db->db_last_error().'</strong><br>';
		else
			echo ' Neues Schema addon hinzugefügt<br>';
	}
}
// Tabelle fuer die Kompetenzen
if(!$result = @$db->db_query("SELECT 1 FROM addon.tbl_kompetenz"))
{

	$qry = "CREATE TABLE addon.tbl_kompetenztyp
			(
				kompetenztyp_id integer NOT NULL,
				kompetenztyp_kurzbz varchar(32),
				bezeichnung varchar(256),
				kompetenztyp_parent_id integer
			);

			CREATE SEQUENCE addon.seq_kompetenztyp_kompetenztyp_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;

			GRANT SELECT, UPDATE ON SEQUENCE addon.seq_kompetenztyp_kompetenztyp_id TO vilesci;

			ALTER TABLE addon.tbl_kompetenztyp ADD CONSTRAINT pk_kompetenztyp PRIMARY KEY (kompetenztyp_id);
    		ALTER TABLE addon.tbl_kompetenztyp ALTER COLUMN kompetenztyp_id SET DEFAULT nextval('addon.seq_kompetenztyp_kompetenztyp_id');
		    ALTER TABLE addon.tbl_kompetenztyp ADD CONSTRAINT fk_kompetenztyp_kompetenztyp_parent_id FOREIGN KEY(kompetenztyp_parent_id) REFERENCES addon.tbl_kompetenztyp (kompetenztyp_id) ON DELETE RESTRICT ON UPDATE CASCADE;

			GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_kompetenztyp TO vilesci;

			CREATE TABLE addon.tbl_kompetenzniveaustufe
			(
				kompetenzniveaustufe_id integer NOT NULL,
				kompetenztyp_id integer NOT NULL,
				stufe varchar(64)
			);

			CREATE SEQUENCE addon.seq_kompetenzniveaustufe_kompetenzniveaustufe_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;

			GRANT SELECT, UPDATE ON SEQUENCE addon.seq_kompetenzniveaustufe_kompetenzniveaustufe_id TO vilesci;

			ALTER TABLE addon.tbl_kompetenzniveaustufe ADD CONSTRAINT pk_kompetenzniveaustufe PRIMARY KEY (kompetenzniveaustufe_id);
    		ALTER TABLE addon.tbl_kompetenzniveaustufe ALTER COLUMN kompetenzniveaustufe_id SET DEFAULT nextval('addon.seq_kompetenzniveaustufe_kompetenzniveaustufe_id');
		    ALTER TABLE addon.tbl_kompetenzniveaustufe ADD CONSTRAINT fk_kompetenzniveaustufe_kompetenztyp FOREIGN KEY(kompetenztyp_id) REFERENCES addon.tbl_kompetenztyp (kompetenztyp_id) ON DELETE RESTRICT ON UPDATE CASCADE;

			GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_kompetenzniveaustufe TO vilesci;

			CREATE TABLE addon.tbl_kompetenzerwerbstyp
			(
				kompetenzerwerbstyp_kurzbz varchar(32) NOT NULL,
				bezeichnung varchar(256)
			);

			ALTER TABLE addon.tbl_kompetenzerwerbstyp ADD CONSTRAINT pk_kompetenzerwerbstyp PRIMARY KEY (kompetenzerwerbstyp_kurzbz);

			GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_kompetenzerwerbstyp TO vilesci;

			CREATE TABLE addon.tbl_kompetenz
			(
				kompetenz_id integer NOT NULL,
				person_id integer NOT NULL,
				kompetenztyp_id integer NOT NULL,
				kompetenzniveaustufe_id integer,
				kompetenzerwerbstyp_kurzbz varchar(32),
				dms_id integer,
				bezeichnung varchar(256),
				kompetenzniveau varchar(256),
				beginn date,
				ende date,
				ort varchar(256),
				insertamum timestamp,
				insertvon varchar(32),
				updateamum timestamp,
				updatevon varchar(32)
			);

			CREATE SEQUENCE addon.seq_kompetenz_kompetenz_id
		 	INCREMENT BY 1
		 	NO MAXVALUE
			NO MINVALUE
			CACHE 1;

			GRANT SELECT, UPDATE ON SEQUENCE addon.seq_kompetenz_kompetenz_id TO vilesci;

			ALTER TABLE addon.tbl_kompetenz ADD CONSTRAINT pk_kompetenz PRIMARY KEY (kompetenz_id);
    		ALTER TABLE addon.tbl_kompetenz ALTER COLUMN kompetenz_id SET DEFAULT nextval('addon.seq_kompetenz_kompetenz_id');

		    ALTER TABLE addon.tbl_kompetenz ADD CONSTRAINT fk_person_kompetenz FOREIGN KEY(person_id) REFERENCES public.tbl_person (person_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		    ALTER TABLE addon.tbl_kompetenz ADD CONSTRAINT fk_kompetenztyp_kompetenz FOREIGN KEY(kompetenztyp_id) REFERENCES addon.tbl_kompetenztyp (kompetenztyp_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		    ALTER TABLE addon.tbl_kompetenz ADD CONSTRAINT fk_kompetenzniveaustufe_kompetenz FOREIGN KEY(kompetenzniveaustufe_id) REFERENCES addon.tbl_kompetenzniveaustufe (kompetenzniveaustufe_id) ON DELETE RESTRICT ON UPDATE CASCADE;
		    ALTER TABLE addon.tbl_kompetenz ADD CONSTRAINT fk_kompetenzerwerbstyp_kompetenz FOREIGN KEY(kompetenzerwerbstyp_kurzbz) REFERENCES addon.tbl_kompetenzerwerbstyp (kompetenzerwerbstyp_kurzbz) ON DELETE RESTRICT ON UPDATE CASCADE;
		    ALTER TABLE addon.tbl_kompetenz ADD CONSTRAINT fk_dms_kompetenz FOREIGN KEY(dms_id) REFERENCES campus.tbl_dms (dms_id) ON DELETE RESTRICT ON UPDATE CASCADE;

			GRANT SELECT, UPDATE, INSERT, DELETE ON addon.tbl_kompetenz TO vilesci;

			";

	if(!$db->db_query($qry))
		echo '<strong>kompetenzen: '.$db->db_last_error().'</strong><br>';
	else
		echo ' Tabellen fuer Kompetenz Addon hinzugefuegt!<br>';

}

//Neue Berechtigung für das Addon hinzufügen
if($result = $db->db_query("SELECT * FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='addon/kompetenzen'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung)
				VALUES('addon/kompetenzen','Addon Kompetenzen');";

		if(!$db->db_query($qry))
			echo '<strong>Berechtigung: '.$db->db_last_error().'</strong><br>';
		else
			echo 'Neue Berechtigung addon/kompetenzen hinzugefuegt!<br>';
	}
}

// DMS-Kategorie fuer Kompetenzen-Dokumente anlegen
if($result = @$db->db_query("SELECT 1 FROM campus.tbl_dms_kategorie WHERE kategorie_kurzbz='Kompetenzen' LIMIT 1"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "
		INSERT INTO campus.tbl_dms_kategorie(kategorie_kurzbz,bezeichnung,beschreibung,parent_kategorie_kurzbz) VALUES('Kompetenzen','Kompetenzen','Dokumente zu Kompetenzen',NULL);
		";

		if(!$db->db_query($qry))
			echo '<strong>campus.tbl_dms_kategorie '.$db->db_last_error().'</strong><br>';
		else
			echo ' campus.tbl_dms_kategorie: Kategorie Kompetenzen angelegt. <font style="color:red"><b>Sie sollten den Zugriff auf diese Kategorie mit einer Gruppe (zB CMS_LOCK) sperren</b></font><br>';
	}
}

//Neue Berechtigung für das Addon hinzufügen
if($result = $db->db_query("SELECT * FROM system.tbl_berechtigung WHERE berechtigung_kurzbz='addon/kompetenzenAdmin'"))
{
	if($db->db_num_rows($result)==0)
	{
		$qry = "INSERT INTO system.tbl_berechtigung(berechtigung_kurzbz, beschreibung)
				VALUES('addon/kompetenzenAdmin','Addon Kompetenzen Administration');";

		if(!$db->db_query($qry))
			echo '<strong>Berechtigung: '.$db->db_last_error().'</strong><br>';
		else
			echo 'Neue Berechtigung addon/kompetenzen hinzugefuegt!<br>';
	}
}

echo '<br>Aktualisierung abgeschlossen<br><br>';
echo '<h2>Gegenprüfung</h2>';

// Liste der verwendeten Tabellen / Spalten des Addons
$tabellen=array(
	"addon.tbl_kompetenztyp"  => array("kompetenztyp_id","kompetenztyp_kurzbz","bezeichnung","kompetenztyp_parent_id"),
	"addon.tbl_kompetenz" => array("kompetenz_id","person_id","kompetenztyp_id","kompetenzniveaustufe_id","kompetenzerwerbstyp_kurzbz","dms_id","bezeichnung","kompetenzniveau","beginn","ende","ort","insertamum","insertvon","updateamum","updatevon"),
	"addon.tbl_kompetenzerwerbstyp" => array("kompetenzerwerbstyp_kurzbz","bezeichnung"),
	"addon.tbl_kompetenzniveaustufe"=>array("kompetenzniveaustufe_id","kompetenztyp_id","stufe")
);


$tabs=array_keys($tabellen);
$i=0;
foreach ($tabellen AS $attribute)
{
	$sql_attr='';
	foreach($attribute AS $attr)
		$sql_attr.=$attr.',';
	$sql_attr=substr($sql_attr, 0, -1);

	if (!@$db->db_query('SELECT '.$sql_attr.' FROM '.$tabs[$i].' LIMIT 1;'))
		echo '<BR><strong>'.$tabs[$i].': '.$db->db_last_error().' </strong><BR>';
	else
		echo $tabs[$i].': OK - ';
	flush();
	$i++;
}
?>
