<?php
/**
 ***********************************************************************************************
 * Erzeugt ein Modal-Fenster mit Plugininformationen
 *
 * @copyright 2004-2022 The Admidio Team
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

/******************************************************************************
 * Parameters:      none
 *****************************************************************************/

require_once(__DIR__ . '/../../adm_program/system/common.php');
include(__DIR__ . '/version.php');

// set headline of the script
$headline = $gL10n->get('PLG_BLSV_EXPORT_PLUGIN_INFORMATION');

// create html page object
$page = new HtmlPage('plg-blsv_export-info', $headline);

header('Content-type: text/html; charset=utf-8');

$form = new HtmlForm('plugin_informations_form', null, $page);
$form->addHtml('
    <div class="modal-header">
        <h3 class="modal-title">'.$headline.'</h3>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
    </div>
    <div class="modal-body">
    ');
$form->addStaticControl('plg_name', $gL10n->get('PLG_BLSV_EXPORT_PLUGIN_NAME'), $gL10n->get('PLG_BLSV_EXPORT_BLSV_EXPORT'));
$form->addStaticControl('plg_version', $gL10n->get('PLG_BLSV_EXPORT_PLUGIN_VERSION'), $plugin_version);
$form->addStaticControl('plg_date', $gL10n->get('PLG_BLSV_EXPORT_PLUGIN_DATE'), $plugin_stand);

$form->addHtml('</div>');
echo $form->show();


