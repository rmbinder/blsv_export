<?php
/**
 ***********************************************************************************************
 * BLSV_Export
 *
 * Version 1.1.3
 * 
 * Stand 21.05.2020
 *
 * Seit Anfang 2018 muss eine Mitgliedermeldung an den BLSV (Bayrischer-Landessportverband) 
 * immer als Excel-Liste mit allen Vereinsmitgliedern erfolgen.
 * 
 * Dieses Plugin erstellt diese Exportliste als CSV- oder als XLSX-Datei.
 * 
 * Hinweis: blsv_export verwendet die externe Klasse XLSXWriter (https://github.com/mk-j/PHP_XLSXWriter)
 * 
 * Autor: rmb
 *
 * Compatible with Admidio version 3.3
 *
 * @copyright 2004-2020 The Admidio Team
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
$header      = array();
$rows        = array();

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
	case 'xlsx':
	    include_once(__DIR__ . '/vendor/PHP_XLSXWriter/xlsxwriter.class.php');
	    $getMode     = 'xlsx';
	    break;
}

$rols_blsv = array();
$rols_count = array();
$sum_count = array();

// die erste Zeile (Kopf) zusammensetzen
foreach ($columns as $data)
{
    if ($getMode == 'csv')
    {
        $str_csv .= $valueQuotes. $data['headline']. $valueQuotes. $separator;
    }
    else               //'xlsx'
    {
        $header[$data['headline']] = 'string';
    }
	
	if (isset($data['rols_blsv']) && is_array($data['rols_blsv'])) 
	{
		$rols_blsv = $data['rols_blsv'];
		
		foreach ($data['rols_blsv'] as $roldata)
		{
			$rols_count[$roldata] = 0;
		}
	}
}

if ($getMode == 'csv')
{
    $str_csv = substr($str_csv, 0, -1);
    $str_csv .= "\n";
}

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
		    $row = array();
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
				if ($getMode == 'csv')
				{
				    $str_csv .= $valueQuotes. $content. $valueQuotes. $separator;
				}
				else                  //'xlsx'
				{
				    $row[] = $content;
				}
			}
			
			if ($getMode == 'csv')
			{
			    $str_csv = substr($str_csv, 0, -1);
			    $str_csv .= "\n";
			}
			else                     //'xlsx'
			{
			    $rows[] = $row;
			}
			
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
	
if ($getMode == 'csv')
{
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
}
else                    //'xlsx'
{
    header('Content-disposition: attachment; filename="'.XLSXWriter::sanitize_filename($filename).'"');
    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    $keywords = array('BLSV', $gL10n->get('PLG_BLSV_EXPORT_DATA_COMPARISON'), $gL10n->get('PLG_BLSV_EXPORT_MEMBERSHIP_REPORT'));
    
    $writer = new XLSXWriter();
    $writer->setAuthor($gCurrentUser->getValue('FIRST_NAME').' '.$gCurrentUser->getValue('LAST_NAME'));
    $writer->setTitle($filename);
    $writer->setSubject($gL10n->get('PLG_BLSV_EXPORT_DATA_COMPARISON_WITH_BLSV'));
    $writer->setCompany($gCurrentOrganization->getValue('org_longname'));
    $writer->setKeywords($keywords);
    $writer->setDescription($gL10n->get('PLG_BLSV_EXPORT_CREATED_WITH'));
    $writer->writeSheet($rows,'', $header);
    $writer->writeToStdOut();
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
	global $gDb, $gCurrentUser, $gMessage, $gL10n, $gLogger;
	
	$userIsAuthorized = false;
	$menId = 0;
	
	$sql = 'SELECT men_id
              FROM '.TBL_MENU.'
             WHERE men_url = ? -- $scriptName ';
	
	$menuStatement = $gDb->queryPrepared($sql, array($scriptName));
	
	if ( $menuStatement->rowCount() === 0 || $menuStatement->rowCount() > 1)
	{
		$gLogger->notice('BlsvExport: Error with menu entry: Found rows: '. $menuStatement->rowCount() );
		$gLogger->notice('BlsvExport: Error with menu entry: ScriptName: '. $scriptName);
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


