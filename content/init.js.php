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
/**
 * Initialisierung des Addons
 */
?>
addon.push( 
{
	init: function() 
	{
		// Hinzufuegen eines zusaetzlichen Tabs bei Mitarbeitern mit einem Label darin
		var tabitem = document.createElement("tab");
		tabitem.setAttribute("id","addon-kompetenzen-tab-mitarbeiter");
		tabitem.setAttribute("label","Kompetenzen");
		var mitarbeitertabs = document.getElementById("mitarbeiter-tabs");
		mitarbeitertabs.appendChild(tabitem);

		var mitarbeiteriframe = document.createElement("iframe");
		mitarbeiteriframe.setAttribute("id","addon-kompetenzen-tabpannel-mitarbeiter-iframe");

		var mitarbeitertabpanels=document.getElementById("mitarbeiter-tabpanels-main");
		mitarbeitertabpanels.appendChild(mitarbeiteriframe);


		var tabitem = document.createElement("tab");
		tabitem.setAttribute("id","addon-kompetenzen-tab-student");
		tabitem.setAttribute("label","Kompetenzen");
		var studierendentabs = document.getElementById("student-content-tabs");
		studierendentabs.appendChild(tabitem);

		var studierendeniframe = document.createElement("iframe");
		studierendeniframe.setAttribute("id","addon-kompetenzen-tabpannel-student-iframe");

		var studierendentabpanels=document.getElementById("student-tabpanels-main");
		studierendentabpanels.appendChild(studierendeniframe);
	},
	selectMitarbeiter: function(person_id, mitarbeiter_uid)
	{
		//alert("Addon Kompetenzen SelectStudent "+prestudent_id+" personID "+person_id+" uid "+student_uid);
		var iframe = document.getElementById("addon-kompetenzen-tabpannel-mitarbeiter-iframe");
		iframe.setAttribute("src","../addons/kompetenzen/content/kompetenzen.xul.php?person_id="+person_id);
	},
	selectStudent: function(person_id, prestudent_id, student_uid)
	{
		//alert("Addon Kompetenzen SelectStudent "+prestudent_id+" personID "+person_id+" uid "+student_uid);
		var iframe = document.getElementById("addon-kompetenzen-tabpannel-student-iframe");
		iframe.setAttribute("src","../addons/kompetenzen/content/kompetenzen.xul.php?person_id="+person_id);
	},
	selectVerband: function(item)
	{
	},
	selectInstitut: function(institut)
	{
	},
	selectLektor: function(lektor)
	{
	}
});
