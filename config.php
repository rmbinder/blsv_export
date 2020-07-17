<?php
/**
 ***********************************************************************************************
 * Konfigurationsdatei fuer das Admidio-Plugin blsv_export
 *
 * @copyright 2004-2020 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

// Dateiname
$filename    = 'BLSV-Export'.'_'.date('Y-m-d');

// Hier werden die Spalten der Exportdatei definiert. 
// Die Struktur ist vom BLSV (Bayrischer Landes-Sportverband) vorgegebenen und muss folgendermaßen aussehen:
//
// Titel | Name | Vorname | Namenszusatz | Geschlecht | Geburtsdatum | Spartennummer
//
// z.B.
// Zeile 1: Titel | Name   | Vorname | Namenszusatz | Geschlecht | Geburtsdatum | Spartennummer
// Zeile 2:       | Muster | Max     |              |      m     |  10.02.1961  |      09
// Zeile 3:       | Muster | Max     |              |      m     |  10.02.1961  |      34
// Zeile 4:  Dr.  | Muster | Claudia |              |      w     |  15.05.1958  |      34
//
// Die Anzahl der Spalten kann fuer Meldungen an andere Dachverbände (nicht BLSV) beliebig verkleinert oder vergroessert werden.
//
// Jede auszugebende Spalte wird über ein untergeordnetes Array des $columns-Array definiert. 
// Folgende Voraussetzungen muss ein untergeordnetes Array dabei mindestens aufweisen:
//   - der Schluesselwert 'headline' ( (=Ueberschrift) muss die Überschrift der 1. Zeile enthalten
//   - der Schluesselwert 'rols_blsv' muss genau einmal definiert sein (Ueber diesen Schluesselwert wird die Zuordnung Rolle zu Sparte durchgefuehrt).
//
// Folgende Schluesselwerte sind möglich:
// 'headline'  = 'Text'                                      * Pflichtfeld; Spaltenueberschrift
// 'usf_id'    = Zahl                                        * Optionsfeld; soll in dieser Spalte der Inhalt eines Profilfeldes dargestellt weden, so ist die usf_id des Profilfeldes einzutragen
// 'subst'     = array('m' => 'Männlich', 'w' => 'Weiblich') * Optionsfeld, falls Ersetzungen durchgeführt werden sollen
// 'rols_blsv' = array(Rol-ID => Spartennummer im BLSV)      * Pflichtfeld; der Schluesselwert 'rols_blsv' muss einmal vorhanden sein
//
// Wichtig: Alle zu meldenden Mitglieder muessen sich in Rollen befinden. Es bietet sich hier an, mit abhaengigen Rollen zu arbeiten.
// z.B. von der Rolle Fußballabteilung abgaengige Rollen: 1. Mannschaft, 2. Mannschaft, A-Junioren usw.
// array('135' => '09',.... => in der Admidio-Installation des Autors besitzt die Fußballabteilung die Rollen-ID 135, beim BLSV die Spartennummer 09

$columns = array(); 
$columns[] = array('headline' => 'Titel');
$columns[] = array('headline' => 'Name', 'usf_id' => 1);
$columns[] = array('headline' => 'Vorname', 'usf_id' => 2);
$columns[] = array('headline' => 'Namenszusatz');
$columns[] = array('headline' => 'Geschlecht', 'usf_id' => 11, 'subst' => array('m' => 'Männlich', 'w' => 'Weiblich'));
$columns[] = array('headline' => 'Geburtsdatum', 'usf_id' => 10);
$columns[] = array('headline' => 'Spartennummer', 'rols_blsv' => array('135' => '09', '134' => '34'));




