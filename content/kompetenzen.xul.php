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
 * Authors: Andreas Oesterreicher <andreas.oesterreicher@technikum-wien.at> and
 */

header("Cache-Control: no-cache");
header("Cache-Control: post-check=0, pre-check=0",false);
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Pragma: no-cache");
header("Content-type: application/vnd.mozilla.xul+xml");

require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');
require_once('../../../include/benutzerberechtigung.class.php');

echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";

echo '<?xml-stylesheet href="'.APP_ROOT.'skin/tempus.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/bindings.css" type="text/css"?>';
echo '<?xml-stylesheet href="'.APP_ROOT.'content/datepicker/datepicker.css" type="text/css"?>';

if(isset($_GET['person_id']) && is_numeric($_GET['person_id']))
	$person_id = $_GET['person_id'];
else
	die('Parameter person_id muss uebergeben werden');

$uid = get_uid();
?>

<window id="addon-kompetenzen-window" title="Kompetenzen"
        xmlns="http://www.mozilla.org/keymaster/gatekeeper/there.is.only.xul"
        onload="AddonKompetenzenLoad(<?php echo $person_id; ?>);"
        >

<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>addons/kompetenzen/content/kompetenzen.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/functions.js.php" />
<script type="application/x-javascript" src="<?php echo APP_ROOT; ?>content/phpRequest.js.php" />

<vbox>
<popupset>
	<menupopup id="addon-kompetenzen-tree-popup">
		<menuitem label="Entfernen" oncommand="AddonKompetenzenDelete();" id="addon-kompetenzen-tree-popup-delete" hidden="false"/>
	</menupopup>
</popupset>
<hbox flex="1">
<grid id="addon-kompetenzen-grid-detail" style="overflow:auto;margin:4px;" flex="1">
		  	<columns  >
				<column flex="1"/>
				<column flex="1"/>
			</columns>
			<rows>
				<row>
					<tree id="addon-kompetenzen-tree" seltype="single" hidecolumnpicker="false" flex="1"
						datasources="rdf:null" ref="http://www.technikum-wien.at/kompetenzen/liste"
						style="margin-left:10px;margin-right:10px;margin-bottom:5px;margin-top: 10px;" height="100px" enableColumnDrag="true"
						onselect="AddonKompetenzenAuswahl()"
						context="addon-kompetenzen-tree-popup"
						flags="dont-build-content"
					>
						<treecols>
							<treecol id="addon-kompetenzen-tree-kompetenz_id" label="ID" flex="2" hidden="true" primary="true"
								class="sortDirectionIndicator"
								sortActive="true"
								sortDirection="ascending"
								sort="rdf:http://www.technikum-wien.at/kompetenzen/rdf#kompetenz_id"/>
							<splitter class="tree-splitter"/>
							<treecol id="addon-kompetenzen-tree-kompetenztyp" label="Typ" flex="5" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/kompetenzen/rdf#kompetenztyp"/>
							<splitter class="tree-splitter"/>
							<treecol id="addon-kompetenzen-tree-kompetenzniveaustufe" label="Niveaustufe" flex="5" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/kompetenzen/rdf#kompetenzniveaustufe"/>
							<splitter class="tree-splitter"/>
							<treecol id="addon-kompetenzen-tree-bezeichnung" label="Bezeichnung" flex="5" hidden="false"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/kompetenzen/rdf#bezeichnung"/>
							<splitter class="tree-splitter"/>
							<treecol id="addon-kompetenzen-tree-ort" label="Ort" flex="5" hidden="true"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/kompetenzen/rdf#ort"/>
							<splitter class="tree-splitter"/>
							<treecol id="addon-kompetenzen-tree-beginn" label="Beginn" flex="5" hidden="true"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/kompetenzen/rdf#beginn"/>
							<splitter class="tree-splitter"/>
							<treecol id="addon-kompetenzen-tree-ende" label="Ende" flex="5" hidden="true"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/kompetenzen/rdf#ende"/>
							<splitter class="tree-splitter"/>
							<treecol id="addon-kompetenzen-tree-kompetenzniveau" label="Niveau" flex="5" hidden="true"
							   class="sortDirectionIndicator"
								sort="rdf:http://www.technikum-wien.at/kompetenzen/rdf#kompetenzniveau"/>
							<splitter class="tree-splitter"/>
						</treecols>

						<template>
							<treechildren flex="1" >
									<treeitem uri="rdf:*">
									<treerow>
										<treecell label="rdf:http://www.technikum-wien.at/kompetenzen/rdf#kompetenz_id"/>
										<treecell label="rdf:http://www.technikum-wien.at/kompetenzen/rdf#kompetenztyp"/>
										<treecell label="rdf:http://www.technikum-wien.at/kompetenzen/rdf#kompetenzniveaustufe"/>
										<treecell label="rdf:http://www.technikum-wien.at/kompetenzen/rdf#bezeichnung"/>
										<treecell label="rdf:http://www.technikum-wien.at/kompetenzen/rdf#ort"/>
										<treecell label="rdf:http://www.technikum-wien.at/kompetenzen/rdf#beginn"/>
										<treecell label="rdf:http://www.technikum-wien.at/kompetenzen/rdf#ende"/>
										<treecell label="rdf:http://www.technikum-wien.at/kompetenzen/rdf#kompetenzniveau"/>
									</treerow>
								</treeitem>
							</treechildren>
						</template>
					</tree>
					<vbox>
						<hbox>
							<button id="addon-kompetenzen-button-neu" label="Neu" oncommand="AddonKompetenzenNeu();"/>
							<button id="addon-kompetenzen-button-loeschen" label="Loeschen" oncommand="AddonKompetenzenDelete();"/>
						</hbox>
						<vbox hidden="true">
							<label value="kompetenzen_id" control="addon-kompetenzen-textbox-kompetenz_id"/>
							<textbox id="addon-kompetenzen-textbox-kompetenz_id" disabled="true"/>
							<label value="person_id" control="addon-kompetenzen-textbox-person_id"/>
							<textbox id="addon-kompetenzen-textbox-person_id" disabled="true"/>
							<label value="Neu" control="addon-kompetenzen-checkbox-neu"/>
							<checkbox id="addon-kompetenzen-checkbox-neu" disabled="true" checked="false"/>
						</vbox>
						<groupbox id="addon-kompetenzen-groupbox" flex="1">
						<caption label="Details"/>
							<grid id="addon-kompetenzen-grid-detail" style="overflow:auto;margin:4px;" flex="1">
							  	<columns  >
									<column flex="1"/>
									<column flex="5"/>
								</columns>
								<rows>
									<row>
										<label value="Typ" control="addon-kompetenzen-menulist-kompetenztyp"/>
										<hbox>
					      					<menulist id="addon-kompetenzen-menulist-kompetenztyp"
											  disabled="true"
									          datasources="<?php echo APP_ROOT.'addons/kompetenzen/xml/kompetenztyp.rdf.php';?>" flex="1"
									          ref="http://www.technikum-wien.at/kompetenztyp/liste"
									          oncommand="AddonKompetenzenTypChange();"
									          >
												<template>
													<menupopup>
														<menuitem value="rdf:http://www.technikum-wien.at/kompetenztyp/rdf#kompetenztyp_id"
											        		      label="rdf:http://www.technikum-wien.at/kompetenztyp/rdf#anzeigename"
														  		  uri="rdf:*"/>
													</menupopup>
												</template>
											</menulist>
											<spacer flex="1" />
										</hbox>
					      			</row>
					      			<row>
										<label value="Niveaustufe" control="addon-kompetenzen-menulist-kompetenzniveaustufe"/>
										<hbox>
					      					<menulist id="addon-kompetenzen-menulist-kompetenzniveaustufe"
											  disabled="true"
									          datasources="rdf:null" flex="1"
									          ref="http://www.technikum-wien.at/kompetenzniveaustufe/liste"
									          >
												<template>
													<menupopup>
														<menuitem value="rdf:http://www.technikum-wien.at/kompetenzniveaustufe/rdf#kompetenzniveaustufe_id"
											        		      label="rdf:http://www.technikum-wien.at/kompetenzniveaustufe/rdf#stufe"
														  		  uri="rdf:*"/>
													</menupopup>
												</template>
											</menulist>
											<spacer flex="1" />
										</hbox>
					      			</row>
					      			<row>
										<label value="Erwerbstyp" control="addon-kompetenzen-menulist-kompetenzerwerbstyp_kurzbz"/>
										<hbox>
					      					<menulist id="addon-kompetenzen-menulist-kompetenzerwerbstyp_kurzbz"
											  disabled="true"
									          datasources="<?php echo APP_ROOT.'addons/kompetenzen/xml/kompetenzerwerbstyp.rdf.php';?>" flex="1"
									          ref="http://www.technikum-wien.at/kompetenzerwerbstyp/liste"
									          >
												<template>
													<menupopup>
														<menuitem value="rdf:http://www.technikum-wien.at/kompetenzerwerbstyp/rdf#kompetenzerwerbstyp_kurzbz"
											        		      label="rdf:http://www.technikum-wien.at/kompetenzerwerbstyp/rdf#bezeichnung"
														  		  uri="rdf:*"/>
													</menupopup>
												</template>
											</menulist>
											<spacer flex="1" />
										</hbox>
					      			</row>
									<row>
										<label value="Bezeichnung" control="addon-kompetenzen-textbox-bezeichnung"/>
				      					<textbox id="addon-kompetenzen-textbox-bezeichnung" disabled="true" maxlength="256"/>
					      			</row>
									<row>
										<label value="Ort" control="addon-kompetenzen-textbox-ort"/>
				      					<textbox id="addon-kompetenzen-textbox-ort" disabled="true" maxlength="256"/>
					      			</row>
									<row>
										<label value="Niveau" control="addon-kompetenzen-textbox-kompetenzniveau"/>
				      					<textbox id="addon-kompetenzen-textbox-kompetenzniveau" disabled="true" maxlength="256"/>
					      			</row>
									<row>
										<label value="Beginn" control="addon-kompetenzen-textbox-beginn"/>
										<hbox>
											<box class="Datum" id="addon-kompetenzen-textbox-beginn" disabled="true" flex="1"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
									<row>
										<label value="Ende" control="addon-kompetenzen-textbox-ende"/>
										<hbox>
											<box class="Datum" id="addon-kompetenzen-textbox-ende" disabled="true" flex="1"/>
					      					<spacer flex="1" />
					      				</hbox>
									</row>
								</rows>
							</grid>
							<hbox>
								<spacer flex="1" />
								<button id="addon-kompetenzen-button-speichern" oncommand="AddonKompetenzenDetailSpeichern()" label="Speichern" disabled="true"/>
							</hbox>
						</groupbox>
					</vbox>
				</row>
		</rows>
</grid>

</hbox>
<spacer flex="1" />
</vbox>
</window>
