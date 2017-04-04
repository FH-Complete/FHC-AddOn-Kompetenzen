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
// header für no cache
header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
// content type setzen
header("Content-type: application/xhtml+xml");

require_once('../../../config/vilesci.config.inc.php');
require_once('../kompetenz.class.php');
require_once('../../../include/datum.class.php');

if(isset($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	$person_id = '';

if(isset($_GET['kompetenz_id']))
	$kompetenz_id = $_GET['kompetenz_id'];
else
	$kompetenz_id = null;

$datum = new datum();

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$rdf_url='http://www.technikum-wien.at/kompetenzen';

echo '
	<RDF:RDF
		xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
		xmlns:KPT="'.$rdf_url.'/rdf#"
	>

   <RDF:Seq about="'.$rdf_url.'/liste">';


$kompetenz = new kompetenz();
if($person_id!='')
{
	if($kompetenz->getKompetenzPerson($person_id))
		foreach ($kompetenz->result as $row)
			draw_content($row);
	else
		die($kompetenz->errormsg);
}
elseif($kompetenz_id!='')
{
	if($kompetenz->load($kompetenz_id))
		draw_content($kompetenz);
	else
		die($kompetenz->errormsg);
}
echo '</RDF:Seq>
</RDF:RDF>';


function draw_content($row)
{
	global $rdf_url, $datum;

	if($row->kompetenzniveaustufe_id!='')
	{
		$kompetenz = new kompetenz();
		$kompetenz->loadNiveaustufe($row->kompetenzniveaustufe_id);
		$kompetenzniveaustufe = $kompetenz->stufe;
	}
	else
		$kompetenzniveaustufe='';

	$kompetenz = new kompetenz();
	$kompetenz->loadTyp($row->kompetenztyp_id);
	$kompetenztyp = $kompetenz->bezeichnung
	;
	echo '
      <RDF:li>
         <RDF:Description  id="'.$row->kompetenz_id.'"  about="'.$rdf_url.'/'.$row->kompetenz_id.'" >
         	<KPT:kompetenz_id><![CDATA['.$row->kompetenz_id.']]></KPT:kompetenz_id>
            <KPT:person_id><![CDATA['.$row->person_id.']]></KPT:person_id>
            <KPT:kompetenztyp_id><![CDATA['.$row->kompetenztyp_id.']]></KPT:kompetenztyp_id>
            <KPT:kompetenzniveaustufe_id><![CDATA['.$row->kompetenzniveaustufe_id.']]></KPT:kompetenzniveaustufe_id>
            <KPT:kompetenzerwerbstyp_kurzbz><![CDATA['.$row->kompetenzerwerbstyp_kurzbz.']]></KPT:kompetenzerwerbstyp_kurzbz>
            <KPT:dms_id><![CDATA['.$row->dms_id.']]></KPT:dms_id>
            <KPT:bezeichnung><![CDATA['.$row->bezeichnung.']]></KPT:bezeichnung>
            <KPT:kompetenzniveau><![CDATA['.$row->kompetenzniveau.']]></KPT:kompetenzniveau>
            <KPT:beginn><![CDATA['.$datum->formatDatum($row->beginn,'d.m.Y').']]></KPT:beginn>
            <KPT:ende><![CDATA['.$datum->formatDatum($row->ende,'d.m.Y').']]></KPT:ende>
            <KPT:ort><![CDATA['.$row->ort.']]></KPT:ort>
            <KPT:kompetenztyp><![CDATA['.$kompetenztyp.']]></KPT:kompetenztyp>
            <KPT:kompetenzniveaustufe><![CDATA['.$kompetenzniveaustufe.']]></KPT:kompetenzniveaustufe>
         </RDF:Description>
      </RDF:li>';

}

?>
