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
require_once(dirname(__FILE__).'/../../include/basis_db.class.php');

class kompetenz extends basis_db
{
	public $new;
	public $result = array();

	public $kompetenz_id;					// integer
	public $person_id;						// integer
	public $kompetenztyp_id;				// integer
	public $kompetenzniveaustufe_id;		// integer
	public $kompetenzerwerbstyp_kurzbz;		// varchar(32)
	public $dms_id;							// integer
	public $bezeichnung;					// varchar(256)
	public $kompetenzniveau;				// varchar(256)
	public $beginn;							// date
	public $ende;							// date
	public $ort	;							// varchar(256)
	public $insertamum;						// timestamp
	public $insertvon;						// varchar(32)
	public $updateamum;						// timestamp
	public $updatevon;						// varchar(32)

	public $stufe;
	public $kompetenztyp_kurzbz;
	public $kompetenztyp_parent_id;
	
	public $kompetenztypen=array();
	
	public $kompetenzerwerbstyp_kurzbz_original;	// Kurzbz fuer Updates
	
	/**
	 * Konstruktor
	 * @param ID der Kompetenz die geladen werden soll (Default=null)
	 */
	public function __construct($kompetenz_id=null)
	{
		parent::__construct();
		
		if(!is_null($kompetenz_id))
			$this->load($kompetenz_id);
	}

	/**
	 * Laedt die Kompetenz mit der ID $kompetenz_id
	 * @param  $kompetenz_id ID der zu ladenden Kompetenz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function load($kompetenz_id)
	{
		//Pruefen ob id eine gueltige Zahl ist
		if(!is_numeric($kompetenz_id) || $kompetenz_id == '')
		{
			$this->errormsg = 'Kompetenz_id muss eine Zahl sein';
			return false;
		}

		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM addon.tbl_kompetenz WHERE kompetenz_id=".$this->db_add_param($kompetenz_id, FHC_INTEGER, false);

		if(!$this->db_query($qry))
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}

		if($row = $this->db_fetch_object())
		{
			$this->kompetenz_id = $row->kompetenz_id;
			$this->person_id = $row->person_id;
			$this->kompetenztyp_id = $row->kompetenztyp_id;
			$this->kompetenzniveaustufe_id = $row->kompetenzniveaustufe_id;
			$this->kompetenzerwerbstyp_kurzbz = $row->kompetenzerwerbstyp_kurzbz;
			$this->dms_id = $row->dms_id;
			$this->bezeichnung = $row->bezeichnung;
			$this->kompetenzniveau = $row->kompetenzniveau;
			$this->beginn = $row->beginn;
			$this->ende = $row->ende;
			$this->ort = $row->ort;
			$this->insertamum = $row->insertamum;
			$this->insertvon = $row->insertvon;
			$this->updateamum = $row->updateamum;
			$this->udpatevon = $row->updatevon;
		}
		else
		{
			$this->errormsg = 'Es ist kein Datensatz mit dieser ID vorhanden';
			return false;
		}

		return true;
	}


	/**
	 * Prueft die Variablen auf Gueltigkeit
	 * @return true wenn ok, false im Fehlerfall
	 */
	protected function validate()
	{

		if(mb_strlen($this->kompetenzerwerbstyp_kurzbz)>32)
		{
			$this->errormsg='Kompetenzerwerbstyp darf nicht länger als 32 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->bezeichnung)>256)
		{
			$this->errormsg = 'Bezeichnung darf nicht länger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->kompetenzniveau)>256)
		{
			$this->errormsg = 'Kompetenzniveau darf nicht laenger als 256 Zeichen sein';
			return false;
		}
		if(mb_strlen($this->ort)>256)
		{
			$this->errormsg = 'Ort darf nicht länger als 256 Zeichen sein';
			return false;
		}

		$this->errormsg = '';
		return true;
	}
	
	/**
	 * Speichert den aktuellen Datensatz in die Datenbank
	 * Wenn $neu auf true gesetzt ist wird ein neuer Datensatz angelegt
	 * andernfalls wird der Datensatz mit der ID in $adresse_id aktualisiert
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function save()
	{
		//Variablen pruefen
		if(!$this->validate())
			return false;

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO addon.tbl_kompetenz (person_id, kompetenztyp_id, kompetenzniveaustufe_id, kompetenzerwerbstyp_kurzbz,
					dms_id, bezeichnung, kompetenzniveau, beginn, ende, ort, insertamum, insertvon, updateamum, updatevon) VALUES('.
			      $this->db_add_param($this->person_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->kompetenztyp_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->kompetenzniveaustufe_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->kompetenzerwerbstyp_kurzbz).', '.
			      $this->db_add_param($this->dms_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->bezeichnung).', '.
			      $this->db_add_param($this->kompetenzniveau).','.
			      $this->db_add_param($this->beginn).', '.
			      $this->db_add_param($this->ende).', '.
			      $this->db_add_param($this->ort).', '.
			      $this->db_add_param($this->insertamum).', '.
			      $this->db_add_param($this->insertvon).', '.
			      $this->db_add_param($this->updateamum).', '.
			      $this->db_add_param($this->updatevon).');';
		}
		else
		{
			//Pruefen ob id eine gueltige Zahl ist
			if(!is_numeric($this->kompetenz_id))
			{
				$this->errormsg = 'kompetenz_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE addon.tbl_kompetenz SET'.
				' person_id='.$this->db_add_param($this->person_id, FHC_INTEGER).', '.
				' kompetenztyp_id='.$this->db_add_param($this->kompetenztyp_id, FHC_INTEGER).', '.
				' kompetenzniveaustufe_id='.$this->db_add_param($this->kompetenzniveaustufe_id, FHC_INTEGER).', '.
				' kompetenzerwerbstyp_kurzbz='.$this->db_add_param($this->kompetenzerwerbstyp_kurzbz).', '.
		      	' dms_id='.$this->db_add_param($this->dms_id, FHC_INTEGER).', '.
		      	' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
		      	' kompetenzniveau='.$this->db_add_param($this->kompetenzniveau).', '.
		      	' beginn='.$this->db_add_param($this->beginn).', '.
		      	' ende='.$this->db_add_param($this->ende).','.
		      	' ort='.$this->db_add_param($this->ort).','.
		      	' updateamum='.$this->db_add_param($this->updateamum).','.
		      	' updatevon='.$this->db_add_param($this->updatevon).' '.
		      	'WHERE kompetenz_id='.$this->db_add_param($this->kompetenz_id, FHC_INTEGER, false).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('addon.seq_kompetenz_kompetenz_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->kompetenz_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Kompetenz-Datensatzes';
			return false;
		}
	}

	/**
	 * Loescht den Datenensatz mit der ID die uebergeben wird
	 * @param $kompetenz_id ID die geloescht werden soll
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function delete($kompetenz_id)
	{
		//Pruefen ob kompetenz_id eine gueltige Zahl ist
		if(!is_numeric($kompetenz_id) || $kompetenz_id == '')
		{
			$this->errormsg = 'kompetenz_id muss eine gültige Zahl sein';
			return false;
		}

		//loeschen des Datensatzes
		$qry="DELETE FROM addon.tbl_kompetenz WHERE kompetenz_id=".$this->db_add_param($kompetenz_id, FHC_INTEGER, false).";";

		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}

	/**
	 * Lädt die Erwerbstypen
	 * @return array mit den Erwerbstypen
	 */
	public function getErwerbstypen()
	{
		$erwerbstyp=array();

		$qry = "SELECT * FROM addon.tbl_kompetenzerwerbstyp ORDER BY bezeichnung";	

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$erwerbstyp[$row->kompetenzerwerbstyp_kurzbz]=$row->bezeichnung;
			}
			return $erwerbstyp;
		}
		else
		{
			$this->errormsg='Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt die vorhandenen Niveaustufen 
	 * Optional werden nur die Niveaustufen des angegeben Typs geladen
	 * @param $kompetenztyp_id ID des Typs (optional)
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getNiveaustufe($kompetenztyp_id=null)
	{
		$qry = "SELECT * FROM addon.tbl_kompetenzniveaustufe";
		if(!is_null($kompetenztyp_id))
			$qry.=" WHERE kompetenztyp_id=".$this->db_add_param($kompetenztyp_id, FHC_INTEGER, false);
		$qry.=" ORDER BY stufe";

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new kompetenz();

				$obj->kompetenzniveaustufe_id = $row->kompetenzniveaustufe_id;
				$obj->kompetenztyp_id = $row->kompetenztyp_id;
				$obj->stufe = $row->stufe;
				
				$this->result[] = $obj;
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}

	/**
	 * Laedt die Kompetenztypen
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function getKompetenztypen()
	{
		$qry = "SELECT * FROM addon.tbl_kompetenztyp";
		
		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new kompetenz();

				$obj->kompetenztyp_id = $row->kompetenztyp_id;
				$obj->kompetenztyp_kurzbz = $row->kompetenztyp_kurzbz;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->kompetenztyp_parent_id = $row->kompetenztyp_parent_id;

				$this->result[] = $obj;
				
				$this->kompetenztypen[$row->kompetenztyp_id]['kompetenztyp_id']=$row->kompetenztyp_id;
				$this->kompetenztypen[$row->kompetenztyp_id]['kompetenztyp_kurzbz']=$row->kompetenztyp_kurzbz;
				$this->kompetenztypen[$row->kompetenztyp_id]['bezeichnung']=$row->bezeichnung;
				$this->kompetenztypen[$row->kompetenztyp_id]['kompetenztyp_parent_id']=$row->kompetenztyp_parent_id;
			}
			
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Typen';
			return false;
		}
	}
	
	/**
	 * Laedt die Kompetenzen einer Person
	 * @param  $person_id ID der Person dessen Kompetenzen geladen werden sollen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function getKompetenzPerson($person_id)
	{
		//Daten aus der Datenbank lesen
		$qry = "SELECT * FROM addon.tbl_kompetenz WHERE person_id=".$this->db_add_param($person_id, FHC_INTEGER, false);

		if($result = $this->db_query($qry))
		{
			while($row = $this->db_fetch_object($result))
			{
				$obj = new kompetenz();
				
				$obj->kompetenz_id = $row->kompetenz_id;
				$obj->person_id = $row->person_id;
				$obj->kompetenztyp_id = $row->kompetenztyp_id;
				$obj->kompetenzniveaustufe_id = $row->kompetenzniveaustufe_id;
				$obj->kompetenzerwerbstyp_kurzbz = $row->kompetenzerwerbstyp_kurzbz;
				$obj->dms_id = $row->dms_id;
				$obj->bezeichnung = $row->bezeichnung;
				$obj->kompetenzniveau = $row->kompetenzniveau;
				$obj->beginn = $row->beginn;
				$obj->ende = $row->ende;
				$obj->ort = $row->ort;
				$obj->insertamum = $row->insertamum;
				$obj->insertvon = $row->insertvon;
				$obj->updateamum = $row->updateamum;
				$obj->udpatevon = $row->updatevon;
				
				$this->result[] = $obj;
			}
			return true;	
		}
		else
		{
			$this->errormsg = 'Fehler bei einer Datenbankabfrage';
			return false;
		}	
	}
	
	/**
	 * Laedt eine Kompetenzniveaustufe
	 * @param $kompetenzniveaustufe_id
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadNiveaustufe($kompetenzniveaustufe_id)
	{
		$qry = 'SELECT * FROM addon.tbl_kompetenzniveaustufe WHERE kompetenzniveaustufe_id='.$this->db_add_param($kompetenzniveaustufe_id, FHC_INTEGER, false);
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->kompetenzniveaustufe_id = $row->kompetenzniveaustufe_id;
				$this->kompetenztyp_id = $row->kompetenztyp_id;
				$this->stufe = $row->stufe;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler biem Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt einen Kompetenztyp
	 * @param $kompetenztyp_id
	 * @return boolean true wenn ok, false im Fehlerfall
	 */
	public function loadTyp($kompetenztyp_id)
	{
		$qry = 'SELECT * FROM addon.tbl_kompetenztyp WHERE kompetenztyp_id='.$this->db_add_param($kompetenztyp_id, FHC_INTEGER, false);
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->kompetenztyp_id = $row->kompetenztyp_id;
				$this->kompetenztyp_kurzbz = $row->kompetenztyp_kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				$this->kompetenztyp_parent_id= $row->kompetenztyp_parent_id;
				return true;
			}
			else
			{
				$this->errormsg = 'Fehler biem Laden der Daten';
				return false;
			}
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden der Daten';
			return false;
		}
	}
	
	/**
	 * Speichert Kompetenztypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveTyp()
	{

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO addon.tbl_kompetenztyp (kompetenztyp_kurzbz, bezeichnung, kompetenztyp_parent_id) VALUES('.
			      $this->db_add_param($this->kompetenztyp_kurzbz).', '.
			      $this->db_add_param($this->bezeichnung).', '.
			      $this->db_add_param($this->kompetenztyp_parent_id, FHC_INTEGER).');';
		}
		else
		{
			//Pruefen ob id eine gueltige Zahl ist
			if(!is_numeric($this->kompetenztyp_id))
			{
				$this->errormsg = 'kompetenztyp_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE addon.tbl_kompetenztyp SET'.
				' kompetenztyp_kurzbz='.$this->db_add_param($this->kompetenztyp_kurzbz).', '.
		      	' bezeichnung='.$this->db_add_param($this->bezeichnung).', '.
		      	' kompetenztyp_parent_id='.$this->db_add_param($this->kompetenztyp_parent_id).' '.
		      	'WHERE kompetenztyp_id='.$this->db_add_param($this->kompetenztyp_id, FHC_INTEGER, false).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('addon.seq_kompetenztyp_kompetenztyp_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->kompetenztyp_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Kompetenz-Datensatzes';
			return false;
		}
	}
	
	/**
	 * Speichert Niveaustufen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveNiveaustufe()
	{

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='BEGIN;INSERT INTO addon.tbl_kompetenzniveaustufe (kompetenztyp_id, stufe) VALUES('.
			      $this->db_add_param($this->kompetenztyp_id, FHC_INTEGER).', '.
			      $this->db_add_param($this->stufe).');';
		}
		else
		{
			//Pruefen ob id eine gueltige Zahl ist
			if(!is_numeric($this->kompetenzniveaustufe_id))
			{
				$this->errormsg = 'kompetenzniveaustufe_id muss eine gueltige Zahl sein';
				return false;
			}
			$qry='UPDATE addon.tbl_kompetenzniveaustufe SET'.
				' kompetenztyp_id='.$this->db_add_param($this->kompetenztyp_id).', '.
		      	' stufe='.$this->db_add_param($this->stufe).' '.
		      	'WHERE kompetenzniveaustufe_id='.$this->db_add_param($this->kompetenzniveaustufe_id, FHC_INTEGER, false).';';
		}
		
		if($this->db_query($qry))
		{
			if($this->new)
			{
				//naechste ID aus der Sequence holen
				$qry="SELECT currval('addon.seq_kompetenzniveaustufe_kompetenzniveaustufe_id') as id;";
				if($this->db_query($qry))
				{
					if($row = $this->db_fetch_object())
					{
						$this->kompetenzniveaustufe_id = $row->id;
						$this->db_query('COMMIT');
						return true;
					}
					else
					{
						$this->db_query('ROLLBACK');
						$this->errormsg = "Fehler beim Auslesen der Sequence";
						return false;
					}
				}
				else
				{
					$this->db_query('ROLLBACK');
					$this->errormsg = 'Fehler beim Auslesen der Sequence';
					return false;
				}
			}
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern der Niveaustufe';
			return false;
		}
	}
	
	/**
	 * Loescht eine Niveaustufe
	 * @param $kompetenzniveaustufe_id
	 */
	public function deleteNiveaustufe($kompetenzniveaustufe_id)
	{
		$qry = "SELECT * FROM addon.tbl_kompetenz WHERE kompetenzniveaustufe_id=".$this->db_add_param($kompetenzniveaustufe_id, FHC_INTEGER);
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
			{
				$this->errormsg = 'Es existieren noch Kompetenzen zu dieser Niveaustufe';
				return false;
			}
		}
			
		$qry = "DELETE FROM addon.tbl_kompetenzniveaustufe WHERE kompetenzniveaustufe_id=".$this->db_add_param($kompetenzniveaustufe_id, FHC_INTEGER);
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}
	
	/**
	 * Entfernt einen Kompetenztyp
	 * @param $kompetenztyp_id
	 */
	public function deleteTyp($kompetenztyp_id)
	{
		$qry = "SELECT * FROM addon.tbl_kompetenz WHERE kompetenztyp_id=".$this->db_add_param($kompetenztyp_id, FHC_INTEGER);
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
			{
				$this->errormsg = 'Es existieren noch Kompetenzen zu diesem Typ';
				return false;
			}
		}
		
		$qry = "SELECT * FROM addon.tbl_kompetenzniveaustufe WHERE kompetenztyp_id=".$this->db_add_param($kompetenztyp_id, FHC_INTEGER);
		if($result = $this->db_query($qry))
		{
			if($this->db_num_rows($result)>0)
			{
				$this->errormsg = 'Es existieren noch Niveaustufen zu diesem Typ';
				return false;
			}
		}
		$qry = "DELETE FROM addon.tbl_kompetenztyp WHERE kompetenztyp_id=".$this->db_add_param($kompetenztyp_id, FHC_INTEGER);
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen der Daten';
			return false;
		}
	}
	
	/**
	 * Laedt einen Erwerbstyp
	 * @param $kompetenzerwerbstyp_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function loadErwerbstyp($kompetenzerwerbstyp_kurzbz)
	{
		$qry = "SELECT * FROM addon.tbl_kompetenzerwerbstyp WHERE kompetenzerwerbstyp_kurzbz=".$this->db_add_param($kompetenzerwerbstyp_kurzbz);
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				$this->kompetenzerwerbstyp_kurzbz=$row->kompetenzerwerbstyp_kurzbz;
				$this->bezeichnung = $row->bezeichnung;
				return true;
			}
			else
			{
				$this->errormsg = 'Eintrag wurde nicht gefunden';
				return false;
			}				
		}
		else
		{
			$this->errormsg = 'Fehler beim Laden des Erwerbstyps';
			return false;
		} 
	}
	
	/**
	 * Speichert Erwerbstypen
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function saveErwerbstyp()
	{

		if($this->new)
		{
			//Neuen Datensatz einfuegen
			$qry='INSERT INTO addon.tbl_kompetenzerwerbstyp (kompetenzerwerbstyp_kurzbz, bezeichnung) VALUES('.
			      $this->db_add_param($this->kompetenzerwerbstyp_kurzbz).', '.
			      $this->db_add_param($this->bezeichnung).');';
		}
		else
		{
			if($this->kompetenzerwerbstyp_kurzbz_original=='')
				$this->kompetenzerwerbstyp_kurzbz_original = $this->kompetenzerwerbstyp_kurzbz;
				
			$qry='UPDATE addon.tbl_kompetenzerwerbstyp SET'.
				' kompetenzerwerbstyp_kurzbz='.$this->db_add_param($this->kompetenzerwerbstyp_kurzbz).', '.
		      	' bezeichnung='.$this->db_add_param($this->bezeichnung).' '.
		      	'WHERE kompetenzerwerbstyp_kurzbz='.$this->db_add_param($this->kompetenzerwerbstyp_kurzbz_original).';';
		}
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Speichern des Erwerbstyps';
			return false;
		}
	}
	
	/**
	 * Loescht einen Kompetenzerwerbstyp
	 * 
	 * @param $kompetenzerwerbstyp_kurzbz
	 * @return true wenn ok, false im Fehlerfall
	 */
	public function deleteErwerbstyp($kompetenzerwerbstyp_kurzbz)
	{
		$qry = 'SELECT COUNT(*) as anzahl FROM addon.tbl_kompetenz WHERE kompetenzerwerbstyp_kurzbz='.$this->db_add_param($kompetenzerwerbstyp_kurzbz);
		
		if($result = $this->db_query($qry))
		{
			if($row = $this->db_fetch_object($result))
			{
				if($row->anzahl>0)
				{
					$this->errormsg = 'Es existieren noch Kompetenzen zu diesem Erwerbstyp';
					return false;
				}
			}
		}
		
		$qry = 'DELETE FROM addon.tbl_kompetenzerwerbstyp WHERe kompetenzerwerbstyp_kurzbz='.$this->db_add_param($kompetenzerwerbstyp_kurzbz);
		
		if($this->db_query($qry))
		{
			return true;
		}
		else
		{
			$this->errormsg = 'Fehler beim Löschen des Eintrags';
			return false;
		}
			
	}
}
?>