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

require_once('../../../config/vilesci.config.inc.php');
require_once('../../../include/functions.inc.php');

$user = get_uid();
loadVariables($user);
?>
// *********** Globale Variablen *****************//
var AddonKompetenzenTreeDatasource; //Datasource des AddonKompetenzenTrees
var AddonKompetenzenSelectKompetenz_id=null; //AddonKompetenzenzurodnung die nach dem Refresh markiert werden soll
var AddonKompetenzen_Person_id;
// ********** Observer und Listener ************* //

// ****
// * Observer fuer AddonKompetenzen Tree
// * startet Rebuild nachdem das Refresh
// * der datasource fertig ist
// ****
var AddonKompetenzenTreeSinkObserver =
{
	onBeginLoad : function(pSink) {},
	onInterrupt : function(pSink) {},
	onResume : function(pSink) {},
	onError : function(pSink, pStatus, pError) {},
	onEndLoad : function(pSink)
	{
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
		document.getElementById('addon-kompetenzen-tree').builder.rebuild();
	}
};

// ****
// * Nach dem Rebuild wird die AddonKompetenzenzuordnung wieder
// * markiert
// ****
var AddonKompetenzenTreeListener =
{
  willRebuild : function(builder) {  },
  didRebuild : function(builder)
  {
  	  //timeout nur bei Mozilla notwendig da sonst die rows
  	  //noch keine values haben. Ab Seamonkey funktionierts auch
  	  //ohne dem setTimeout
  	  
      window.setTimeout(AddonKompetenzenTreeSelectZuordnung,10);
  }
};


// ***************** KEY Events ************************* //


// ****************** FUNKTIONEN ************************** //


// ****
// * Laedt den AddonKompetenzentree
// ****
function AddonKompetenzenLoad(person_id)
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	AddonKompetenzen_Person_id = person_id;
	
	// *** AddonKompetenzen ***
	AddonKompetenzentree = document.getElementById('addon-kompetenzen-tree');
	url='<?php echo APP_ROOT;?>addons/kompetenzen/xml/kompetenzen.rdf.php?person_id='+person_id+"&"+gettimestamp();
	
	//Alte DS entfernen
	var oldDatasources = AddonKompetenzentree.database.GetDataSources();
	while(oldDatasources.hasMoreElements())
	{
		AddonKompetenzentree.database.RemoveDataSource(oldDatasources.getNext());
	}
	//Refresh damit die entfernten DS auch wirklich entfernt werden
	AddonKompetenzentree.builder.rebuild();
	
	try
	{
		AddonKompetenzenTreeDatasource.removeXMLSinkObserver(AddonKompetenzenTreeSinkObserver);
		AddonKompetenzentree.builder.removeListener(AddonKompetenzenTreeListener);
	}
	catch(e)
	{}
	
	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
	AddonKompetenzenTreeDatasource = rdfService.GetDataSource(url);
	AddonKompetenzenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
	AddonKompetenzenTreeDatasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
	AddonKompetenzentree.database.AddDataSource(AddonKompetenzenTreeDatasource);
	AddonKompetenzenTreeDatasource.addXMLSinkObserver(AddonKompetenzenTreeSinkObserver);
	AddonKompetenzentree.builder.addListener(AddonKompetenzenTreeListener);
}

// ********** AddonKompetenzen ******************

// ****
// * Selectiert die AddonKompetenzenzuordnung nachdem der Tree
// * rebuildet wurde.
// ****
function AddonKompetenzenTreeSelectZuordnung()
{
	var tree=document.getElementById('addon-kompetenzen-tree');
	if(tree.view)
		var items = tree.view.rowCount; //Anzahl der Zeilen ermitteln
	else
		return false;

	//In der globalen Variable ist die zu selektierende Buchung gespeichert
	if(AddonKompetenzenSelectKompetenz_id!=null)
	{
	   	for(var i=0;i<items;i++)
	   	{
	   		//id der row holen
			AddonKompetenzen_id=getTreeCellText(tree, "addon-kompetenzen-tree-kompetenz_id", i);
			
			//wenn dies die zu selektierende Zeile ist
			if(AddonKompetenzen_id == AddonKompetenzenSelectKompetenz_id)
			{
				//Zeile markieren
				tree.view.selection.select(i);
				//Sicherstellen, dass die Zeile im sichtbaren Bereich liegt
				tree.treeBoxObject.ensureRowIsVisible(i);
				AddonKompetenzenSelectKompetenz_id=null;
				return true;
			}
	   	}
	}
}

// ****
// * Wenn ein AddonKompetenzen ausgewaehlt wird, dann
// * werden die zugehoerigen Details geladen
// ****
function AddonKompetenzenAuswahl()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('addon-kompetenzen-tree');

	if (tree.currentIndex==-1) 
		return;
		
	AddonKompetenzenDetailDisableFields(false);

	document.getElementById('addon-kompetenzen-checkbox-neu').checked=false;

	//Ausgewaehlte Nr holen
	var kompetenz_id=getTreeCellText(tree, "addon-kompetenzen-tree-kompetenz_id", tree.currentIndex);

	if(kompetenz_id=='')
		return;
	
	//Daten holen
	var url = '<?php echo APP_ROOT ?>addons/kompetenzen/xml/kompetenzen.rdf.php?kompetenz_id='+kompetenz_id+'&'+gettimestamp();

	var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].
                   getService(Components.interfaces.nsIRDFService);

    var dsource = rdfService.GetDataSourceBlocking(url);

	var subject = rdfService.GetResource("http://www.technikum-wien.at/kompetenzen/"+kompetenz_id);

	var predicateNS = "http://www.technikum-wien.at/kompetenzen/rdf";

	//Daten holen
	var person_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#person_id" ));
	var kompetenz_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kompetenz_id" ));
	var kompetenztyp_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kompetenztyp_id" ));
	var kompetenzniveaustufe_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kompetenzniveaustufe_id" ));
	var kompetenzerwerbstyp_kurzbz = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kompetenzerwerbstyp_kurzbz" ));
	var dms_id = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#dms_id" ));
	var bezeichnung = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#bezeichnung" ));
	var kompetenzniveau = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#kompetenzniveau" ));
	var beginn = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#beginn" ));
	var ende = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ende" ));
	var ort = getTargetHelper(dsource,subject,rdfService.GetResource( predicateNS + "#ort" ));

	document.getElementById('addon-kompetenzen-textbox-person_id').value=person_id;
	document.getElementById('addon-kompetenzen-textbox-kompetenz_id').value=kompetenz_id;
	document.getElementById('addon-kompetenzen-textbox-bezeichnung').value=bezeichnung;
	document.getElementById('addon-kompetenzen-textbox-kompetenzniveau').value=kompetenzniveau;
	document.getElementById('addon-kompetenzen-textbox-ort').value=ort;
	document.getElementById('addon-kompetenzen-textbox-beginn').value=beginn;
	document.getElementById('addon-kompetenzen-textbox-ende').value=ende;
	document.getElementById('addon-kompetenzen-menulist-kompetenztyp').value=kompetenztyp_id;
	AddonKompetenzenTypChange();
	
	document.getElementById('addon-kompetenzen-menulist-kompetenzniveaustufe').value=kompetenzniveaustufe_id;
	document.getElementById('addon-kompetenzen-menulist-kompetenzerwerbstyp_kurzbz').value=kompetenzerwerbstyp_kurzbz;
}

// ****
// * Aktiviert / Deaktiviert die AddonKompetenzen Felder
// ****
function AddonKompetenzenDisableFields(val)
{
	document.getElementById('addon-kompetenzen-button-neu').disabled=val;
	document.getElementById('addon-kompetenzen-button-loeschen').disabled=val;
	AddonKompetenzenDetailDisableFields(true);
}

// ****
// * Aktiviert / Deaktiviert die AddonKompetenzendetail Felder
// ****
function AddonKompetenzenDetailDisableFields(val)
{
	document.getElementById('addon-kompetenzen-textbox-bezeichnung').disabled=val;
	document.getElementById('addon-kompetenzen-textbox-kompetenzniveau').disabled=val;
	document.getElementById('addon-kompetenzen-textbox-ort').disabled=val;
	document.getElementById('addon-kompetenzen-textbox-beginn').disabled=val;
	document.getElementById('addon-kompetenzen-textbox-ende').disabled=val;
	document.getElementById('addon-kompetenzen-menulist-kompetenzerwerbstyp_kurzbz').disabled=val;
	document.getElementById('addon-kompetenzen-menulist-kompetenztyp').disabled=val;
	document.getElementById('addon-kompetenzen-menulist-kompetenzniveaustufe').disabled=val;
	document.getElementById('addon-kompetenzen-button-speichern').disabled=val;

	if(val)
		AddonKompetenzenDetailResetFields();
}

// ****
// * Resetet die AddonKompetenzendetail Felder
// ****
function AddonKompetenzenDetailResetFields()
{
	document.getElementById('addon-kompetenzen-textbox-bezeichnung').value='';
	document.getElementById('addon-kompetenzen-textbox-kompetenzniveau').value='';
	document.getElementById('addon-kompetenzen-textbox-ort').value='';
	document.getElementById('addon-kompetenzen-textbox-beginn').value='';
	document.getElementById('addon-kompetenzen-textbox-ende').value='';
	document.getElementById('addon-kompetenzen-menulist-kompetenzerwerbstyp_kurzbz').value='';
}

// ****
// * Loescht eine AddonKompetenzenzuordnung
// ****
function AddonKompetenzenDelete()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var tree = document.getElementById('addon-kompetenzen-tree');

	if (tree.currentIndex==-1) return;

	AddonKompetenzenDetailDisableFields(false);

	//Ausgewaehlte Nr holen
	var kompetenz_id=getTreeCellText(tree, "addon-kompetenzen-tree-kompetenz_id", tree.currentIndex);
		
	if(confirm('Diesen Eintrag wirklich loeschen?'))
	{
		var url = '<?php echo APP_ROOT ?>addons/kompetenzen/content/DBDML.php';
		var req = new phpRequest(url,'','');

		req.add('type', 'deleteKompetenzen');
		req.add('kompetenz_id', kompetenz_id);

		var response = req.executePOST();

		var val =  new ParseReturnValue(response)

		if (!val.dbdml_return)
		{
			if(val.dbdml_errormsg=='')
				alert(response)
			else
				alert(val.dbdml_errormsg)
		}
		else
		{
			AddonKompetenzenDetailDisableFields(true);
			AddonKompetenzenTreeDatasource.Refresh(false);
		}
	}
}

// ****
// * Speichert die Kompetenz Details
// ****
function AddonKompetenzenDetailSpeichern()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

	var neu = document.getElementById('addon-kompetenzen-checkbox-neu').checked;
	var person_id = document.getElementById('addon-kompetenzen-textbox-person_id').value;
	var kompetenz_id = document.getElementById('addon-kompetenzen-textbox-kompetenz_id').value;
	var bezeichnung = document.getElementById('addon-kompetenzen-textbox-bezeichnung').value;
	var kompetenzniveau = document.getElementById('addon-kompetenzen-textbox-kompetenzniveau').value;
	var beginn = document.getElementById('addon-kompetenzen-textbox-beginn').value;
	var ende = document.getElementById('addon-kompetenzen-textbox-ende').value;
	var ort = document.getElementById('addon-kompetenzen-textbox-ort').value;
	var kompetenzerwerbstyp_kurzbz = document.getElementById('addon-kompetenzen-menulist-kompetenzerwerbstyp_kurzbz').value;
	var kompetenztyp_id = document.getElementById('addon-kompetenzen-menulist-kompetenztyp').value;
	var kompetenzniveaustufe_id = document.getElementById('addon-kompetenzen-menulist-kompetenzniveaustufe').value;
	
	if(beginn!='' && !CheckDatum(beginn))
	{
		alert('Beginn Datum ist ungueltig');
		return false;
	}
	if(ende!='' && !CheckDatum(ende))
	{
		alert('Ende Datum ist ungueltig');
		return false;
	}
		
	var url = '<?php echo APP_ROOT ?>addons/kompetenzen/content/DBDML.php';
	var req = new phpRequest(url,'','');

	req.add('type', 'saveKompetenzen');

	req.add('neu', neu);
	req.add('person_id', person_id);
	req.add('kompetenz_id', kompetenz_id);
	req.add('bezeichnung', bezeichnung);
	req.add('kompetenzniveau', kompetenzniveau);
	req.add('beginn', ConvertDateToISO(beginn));
	req.add('ende', ConvertDateToISO(ende));
	req.add('ort', ort);
	req.add('kompetenzerwerbstyp_kurzbz',kompetenzerwerbstyp_kurzbz);
	req.add('kompetenztyp_id',kompetenztyp_id);
	req.add('kompetenzniveaustufe_id',kompetenzniveaustufe_id);
	
	var response = req.executePOST();

	var val =  new ParseReturnValue(response)

	if (!val.dbdml_return)
	{
		if(val.dbdml_errormsg=='')
			alert(response)
		else
			alert(val.dbdml_errormsg)
	}
	else
	{
		AddonKompetenzenSelectKompetenz_id=val.dbdml_data;
		AddonKompetenzenLoad(person_id);
	}
}

// ****
// * Neues AddonKompetenzen anlegen
// ****
function AddonKompetenzenNeu()
{
	var now = new Date();
	var jahr = now.getFullYear();

	var monat = now.getMonth()+1;

	if(monat<10)
		monat='0'+monat;
	var tag = now.getDate();
	if(tag<10)
		tag='0'+tag;

	document.getElementById('addon-kompetenzen-checkbox-neu').checked=true;
	AddonKompetenzenDetailDisableFields(false);
	AddonKompetenzenDetailResetFields();
	document.getElementById('addon-kompetenzen-textbox-person_id').value = AddonKompetenzen_Person_id;
	document.getElementById('addon-kompetenzen-textbox-kompetenz_id').value = '';
}

// ****
// * Wird aufgerufen, wenn der AddonKompetenzentyp geändert wird
// * Wenn als AddonKompetenzentyp Inventar ausgewaehlt wird, dann wird ein DropDown fuer die 
// * Inventarnummer angezeigt, sonst ein Feld fuer die Nummer und Beschreibung
// ****
function AddonKompetenzenTypChange()
{
	netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	var menulistkompetenztyp = document.getElementById('addon-kompetenzen-menulist-kompetenztyp');
	var menulistniveaustufe = document.getElementById('addon-kompetenzen-menulist-kompetenzniveaustufe');
	var v = menulistkompetenztyp.value;
	
	if(v.length>0)
	{		
		var url = '<?php echo APP_ROOT; ?>addons/kompetenzen/xml/kompetenzniveaustufe.rdf.php?kompetenztyp_id='+encodeURIComponent(v)+'&'+gettimestamp();

		var oldDatasources = menulistniveaustufe.database.GetDataSources();
		while(oldDatasources.hasMoreElements())
		{
			menulistniveaustufe.database.RemoveDataSource(oldDatasources.getNext());
		}
		//Refresh damit die entfernten DS auch wirklich entfernt werden
		menulistniveaustufe.builder.rebuild();
	
		var rdfService = Components.classes["@mozilla.org/rdf/rdf-service;1"].getService(Components.interfaces.nsIRDFService);
		var datasource = rdfService.GetDataSourceBlocking(url);
		datasource.QueryInterface(Components.interfaces.nsIRDFRemoteDataSource);
		datasource.QueryInterface(Components.interfaces.nsIRDFXMLSink);
		menulistniveaustufe.database.AddDataSource(datasource);
		menulistniveaustufe.builder.rebuild();
	}
}


function parseRDFString(str, url)
{

	try {
		netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
	} catch(e) {
		alert(e);
		return;
	}

  var memoryDS = Components.classes["@mozilla.org/rdf/datasource;1?name=in-memory-datasource"].createInstance(Components.interfaces.nsIRDFDataSource);

  var ios=Components.classes["@mozilla.org/network/io-service;1"].getService(Components.interfaces.nsIIOService);
  baseUri=ios.newURI(url,null,null);

  var parser=Components.classes["@mozilla.org/rdf/xml-parser;1"].createInstance(Components.interfaces.nsIRDFXMLParser);
  parser.parseString(memoryDS,baseUri,str);

  return memoryDS;
}

