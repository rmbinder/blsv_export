<?php
/**
 ***********************************************************************************************
 * Konfigurationsdatei fuer das Admidio-Plugin blsv_export
 *
 * @copyright 2004-2018 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

// Exportmode: 'csv-ms' = Microsoft Excel oder 'csv-oo' = Standard-CSV
$exportMode       = 'csv-ms';

// Dateiname
$filename    = 'BLSV-Export'.'_'.date('Y-m-d');

// Hier werden die Spalten der Exportdatei definiert. 
// Die Struktur ist vom BLSV vorgegebenen und muss folgendermaßen aussehen:
// Titel | Name | Vorname | Namenszusatz | Geschlecht | Geburtsdatum | Spartennummer
//
// z.B.
// Zeile 1: Titel | Name   | Vorname | Namenszusatz | Geschlecht | Geburtsdatum | Spartennummer
// Zeile 2:       | Muster | Max     |              |      m     |  10.02.1961  |      09
// Zeile 3:       | Muster | Max     |              |      m     |  10.02.1961  |      34
// Zeile 4:  Dr.  | Muster | Claudia |              |      w     |  15.05.1958  |      34
//
// Die Anzahl der Spalten kann fuer andere Meldungen (nicht BLSV) beliebig verkleinert oder vergroessert werden.
// Aber: - Jede Spalte muss den Schluesselwert (=Ueberschrift) 'headline' aufweisen
//       - Es muss einmal der Schluesselwert 'rols_blsv' definiert sein. Ueber diesen Schluesselwert wird die Zuordnung Rolle zu Sparte durchgefuehrt.
//
// Folgende Schluesselwerte sind möglich:
// 'headline' = 'Text'                                      * Pflichtfeld; Spaltenueberschrift
// 'usf_id'   = Zahl                                        * Optionsfeld; soll in dieser Spalte der Inhalt eines Profilfeldes dargestellt weden, so ist die usf_id des Profilfeldes einzutragen
// 'subst'    = array('m' => 'Männlich', 'w' => 'Weiblich') * Optionsfeld, falls Ersetzungen durchgeführt werden sollen
// 'rols_blsv' = array(Rol-ID => Spartennummer im BLSV)     * Pflichtfeld; der Schluesselwert 'rols_blsv' muss einmal vorhanden sein
//
// Wichtig: Alle zu meldenden Mitglieder muessen sich in Rollen befinden. Es bietet sich hier an, mit abhaengigen Rollen zuz arbeiten.
// z.B. von der Rolle Fußballabteilung abgaengige Rollen: 1. Mannschaft, 2. Mannschaft, A-Junioren usw.
// Fußballabteilung besitzt in Admidio die Rollen-ID 140, beim BLSV die Spartennummer 09
$columns = array(); 
$columns[] = array('headline' => 'Titel');
$columns[] = array('headline' => 'Name', 'usf_id' => 1);
$columns[] = array('headline' => 'Vorname', 'usf_id' => 2);
$columns[] = array('headline' => 'Namenszusatz');
$columns[] = array('headline' => 'Geschlecht', 'usf_id' => 11, 'subst' => array('m' => 'Männlich', 'w' => 'Weiblich'));
$columns[] = array('headline' => 'Geburtsdatum', 'usf_id' => 10);
$columns[] = array('headline' => 'Spartennummer', 'rols_blsv' => array('140' => '09', '141' => '34'));




