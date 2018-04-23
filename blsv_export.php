<?php
/**
 ***********************************************************************************************
 * BLSV_Export
 *
 * Version 1.1.0
 *
 * Dieses Plugin erzeugt eine Exportliste zur Mitgliedermeldung beim BLSV (Bayrischer-Landessportverband).
 * 
 * Author: rmb
 *
 * Compatible with Admidio version 3.3
 *
 * @copyright 2004-2018 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 *   
 ***********************************************************************************************
 */

require_once(__DIR__ . '/../../adm_program/system/common.php');
require_once(__DIR__ . '/config.php');

//$scriptName ist der Name wie er im Menue eingetragen werden muss, also ohne evtl. vorgelagerte Ordner wie z.B. /playground/adm_plugins/blsv_export...
$scriptName = substr($_SERVER['SCRIPT_NAME'], strpos($_SERVER['SCRIPT_NAME'], FOLDER_PLUGINS));

// only authorized user are allowed to start this module
if (!isUserAuthorized($scriptName))
{
	$gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}

// initialize some special mode parameters
$separator   = '';
$valueQuotes = '';
$charset     = '';
$str_csv     = '';   // enthaelt die komplette CSV-Datei als String

switch ($exportMode)
{
	case 'csv-ms':
		$separator   = ';';  // Microsoft Excel 2007 or new needs a semicolon
		$valueQuotes = '"';  // all values should be set with quotes
		$getMode     = 'csv';
		$charset     = 'iso-8859-1';
		break;
	case 'csv-oo':
		$separator   = ',';   // a CSV file should have a comma
		$valueQuotes = '"';   // all values should be set with quotes
		$getMode     = 'csv';
		$charset     = 'utf-8';
		break;
}

$rols_blsv = array();
$rols_count = array();
$sum_count = array();

// die erste Zeile (Kopf) zusammensetzen
foreach ($columns as $data)
{
	$str_csv .= $valueQuotes. $data['headline']. $valueQuotes. $separator;
	
	if (isset($data['rols_blsv']) && is_array($data['rols_blsv'])) 
	{
		$rols_blsv = $data['rols_blsv'];
		
		foreach ($data['rols_blsv'] as $roldata)
		{
			$rols_count[$roldata] = 0;
		}
	}
}
$str_csv = substr($str_csv, 0, -1);
$str_csv .= "\n";

// jetzt die Mitgliederdaten zusammensetzen
$user = new User($gDb, $gProfileFields);

$sql = ' SELECT DISTINCT mem_usr_id
             	    FROM '.TBL_MEMBERS.', '.TBL_ROLES.', '.TBL_CATEGORIES. '
             	   WHERE mem_rol_id = rol_id
             	     AND rol_valid  = 1
             	     AND rol_cat_id = cat_id
             	     AND ( cat_org_id = '.$gCurrentOrganization->getValue('org_id').'
               		  OR cat_org_id IS NULL )
             	     AND mem_begin <= \''.DATE_NOW.'\'
           		     AND mem_end    > \''.DATE_NOW.'\' ';

$statement = $gDb->query($sql);

while ($row = $statement->fetch())
{
	$userId = (int) $row['mem_usr_id'];
	$user->readDataById($userId);
	$user->getRoleMemberships();
	
	foreach ($rols_blsv as $roleId => $spartennummer)
	{
		if ($user->isMemberOfRole((int) $roleId))
		{
			foreach ($columns as $data)
			{
				$content = '';
				if (isset($data['usf_id']) )
				{
					if ( ($gProfileFields->getPropertyById( $data['usf_id'], 'usf_type') == 'DROPDOWN'
							|| $gProfileFields->getPropertyById($data['usf_id'], 'usf_type') == 'RADIO_BUTTON') )
					{
						$content =  $user->getValue($gProfileFields->getPropertyById($data['usf_id'], 'usf_name_intern'), 'database');
						
						// show selected text of optionfield or combobox
						$arrListValues = $gProfileFields->getPropertyById($data['usf_id'], 'usf_value_list', 'text');
						$content       = $arrListValues[$content];
					}
					else 
					{
						$content = $user->getValue($gProfileFields->getPropertyById($data['usf_id'], 'usf_name_intern'));
					}
					
					if (isset($data['subst']) )
					{
						$content = array_search($content, $data['subst']);
					}
				}
				elseif (isset($data['rols_blsv']) )
				{
					$content = $spartennummer;
					$rols_count[$spartennummer]++;
				}
				$str_csv .= $valueQuotes. $content. $valueQuotes. $separator;
			}
			$str_csv = substr($str_csv, 0, -1);
			$str_csv .= "\n";
			
			$sum_count[$userId] = 1;
		}
	}
}

$filename .= '_SUM-'.sizeof($sum_count);

foreach ($rols_count as $sparte => $count)
{
	$filename .= '_SPARTE'.$sparte.'-'.$count;
}

$filename .= '.'.$getMode;
	
// for IE the filename must have special chars in hexadecimal
if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT']))
{
	$filename = urlencode($filename);
}
	
header('Content-Disposition: attachment; filename="'.$filename.'"');
	
// neccessary for IE6 to 8, because without it the download with SSL has problems
header('Cache-Control: private');
header('Pragma: public');
	
// nun die erstellte CSV-Datei an den User schicken
header('Content-Type: text/comma-separated-values; charset='.$charset);
	
if ($charset == 'iso-8859-1')
{
	echo utf8_decode($str_csv);
}
else
{
	echo $str_csv;
}

exit;

/**
 * Funktion prueft, ob der Nutzer berechtigt ist das Plugin aufzurufen.
 * Zur Prüfung werden die Einstellungen von 'Modulrechte' und 'Sichtbar für' 
 * verwendet, die im Modul Menü für dieses Plugin gesetzt wurden.
 * @return  bool    true, wenn der User berechtigt ist
 */
function isUserAuthorized($scriptName)
{
	global $gDb, $gCurrentUser, $gMessage, $gL10n;
	
	$userIsAuthorized = false;
	$menId = 0;
	
	$sql = 'SELECT men_id
              FROM '.TBL_MENU.'
             WHERE men_url = ? -- $scriptName ';
	
	$menuStatement = $gDb->queryPrepared($sql, array($scriptName));
	
	if ( $menuStatement->rowCount() === 0 || $menuStatement->rowCount() > 1)
	{
		$gMessage->show($gL10n->get('PLG_BLSV_EXPORT_MENU_URL_ERROR', array($scriptName)), $gL10n->get('SYS_ERROR'));
	}
	else
	{
		while ($row = $menuStatement->fetch())
		{
			$menId = (int) $row['men_id'];
		}
	}
	
	$sql = 'SELECT men_id, men_com_id, men_name_intern, men_name, men_description, men_url, men_icon, com_name_intern
                  FROM '.TBL_MENU.'
             LEFT JOIN '.TBL_COMPONENTS.'
                    ON com_id = men_com_id
                 WHERE men_id = ? -- $menId
              ORDER BY men_men_id_parent DESC, men_order';
	
	$menuStatement = $gDb->queryPrepared($sql, array($menId));
	while ($row = $menuStatement->fetch())
	{
		if ((int) $row['men_com_id'] === 0 || Component::isVisible($row['com_name_intern']))
		{
			// Read current roles rights of the menu
			$displayMenu = new RolesRights($gDb, 'menu_view', $row['men_id']);
			$rolesDisplayRight = $displayMenu->getRolesIds();
			
			// check for right to show the menu
			if (count($rolesDisplayRight) === 0 || $displayMenu->hasRight($gCurrentUser->getRoleMemberships()))
			{
				$userIsAuthorized = true;
			}
		}
	}
	return $userIsAuthorized;
}


