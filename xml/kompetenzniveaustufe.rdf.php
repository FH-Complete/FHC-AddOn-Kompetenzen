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

echo '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';

$rdf_url='http://www.technikum-wien.at/kompetenzniveaustufe';

echo '
	<RDF:RDF
		xmlns:RDF="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
		xmlns:KPT="'.$rdf_url.'/rdf#"
	>

   <RDF:Seq about="'.$rdf_url.'/liste">';

if(isset($_GET['kompetenztyp_id']))
	$kompetenztyp_id = $_GET['kompetenztyp_id'];
else
	die('Fehlerhafter Parameter kompetenztyp_id');

echo '
      <RDF:li>
         <RDF:Description  id=""  about="'.$rdf_url.'/" >
         	<KPT:kompetenzniveaustufe_id></KPT:kompetenzniveaustufe_id>
            <KPT:kompetenztyp_id></KPT:kompetenztyp_id>
            <KPT:stufe><![CDATA[-- keine Auswahl --]]></KPT:stufe>
         </RDF:Description>
      </RDF:li>';
$kompetenz = new kompetenz();
if($kompetenz->getNiveaustufe($kompetenztyp_id))
{
	foreach($kompetenz->result as $row)
	{
		echo '
	      <RDF:li>
	         <RDF:Description  id="'.$row->kompetenzniveaustufe_id.'"  about="'.$rdf_url.'/'.$row->kompetenzniveaustufe_id.'" >
	         	<KPT:kompetenzniveaustufe_id><![CDATA['.$row->kompetenzniveaustufe_id.']]></KPT:kompetenzniveaustufe_id>
	            <KPT:kompetenztyp_id><![CDATA['.$row->kompetenztyp_id.']]></KPT:kompetenztyp_id>
	            <KPT:stufe><![CDATA['.$row->stufe.']]></KPT:stufe>
	         </RDF:Description>
	      </RDF:li>';
	}
}
else
{
	die($kompetenz->errormsg);
}

echo '</RDF:Seq>
</RDF:RDF>';
?>