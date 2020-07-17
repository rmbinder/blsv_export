<?php
/**
 ***********************************************************************************************
 * BLSV_Export
 *
 * Version 2.0.0-Beta1
 * 
 * Stand 17.07.2020
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
 * Compatible with Admidio version 4
 *
 * @copyright 2004-2020 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 *   
 ***********************************************************************************************
 */

require_once(__DIR__ . '/../../adm_program/system/common.php');
require_once(__DIR__ . '/common_function.php');
require_once(__DIR__ . '/config.php');

//$scriptName ist der Name wie er im Menue eingetragen werden muss, also ohne evtl. vorgelagerte Ordner wie z.B. /playground/adm_plugins/blsv_export...
$scriptName = substr($_SERVER['SCRIPT_NAME'], strpos($_SERVER['SCRIPT_NAME'], FOLDER_PLUGINS));

// only authorized user are allowed to start this module
if (!isUserAuthorized($scriptName))
{
	$gMessage->show($gL10n->get('SYS_NO_RIGHTS'));
}

$headline = $gL10n->get('PLG_BLSV_EXPORT_BLSV_EXPORT');

// create html page object
$page = new HtmlPage($headline);

// add current url to navigation stack
$gNavigation->addUrl(CURRENT_URL, $headline);

$page->addHtml($gL10n->get('PLG_BLSV_EXPORT_DESC'));
$page->addHtml('<br><br>');
$page->addHtml($gL10n->get('PLG_BLSV_EXPORT_DESC2'));
$page->addHtml('<br><br>');
$page->addHtml($gL10n->get('PLG_BLSV_EXPORT_DESC3'));
$page->addHtml('<br><br>');

// show form
$form = new HtmlForm('blsv_export_form', SecurityUtils::encodeUrl(ADMIDIO_URL . FOLDER_PLUGINS . PLUGIN_FOLDER .'/export.php'), $page);

$radioButtonEntries = array('xlsx' => $gL10n->get('LST_MICROSOFT_EXCEL').' (XLSX)', 'csv-ms' => $gL10n->get('LST_MICROSOFT_EXCEL').' (CSV)', 'csv-oo' => $gL10n->get('SYS_CSV').' ('.$gL10n->get('SYS_UTF8').')' );
$form->addRadioButton('export_mode',$gL10n->get('PLG_BLSV_EXPORT_SELECT_EXPORTFORMAT'), $radioButtonEntries, array('defaultValue' => 'xlsx'));
$form->addSubmitButton('btn_export', $gL10n->get('PLG_BLSV_EXPORT_CREATE_FILE'), array('icon' => 'fa-file-export', 'class' => ' col-sm-offset-3'));

// add form to html page and show page
$page->addHtml($form->show(false));
$page->show();