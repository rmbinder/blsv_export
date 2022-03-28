<?php
/**
 ***********************************************************************************************
 * Konfigurationsdatei fuer das Admidio-Plugin blsv_export
 *
 * @copyright 2004-2022 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

// Dateiname
$filename    = 'BLSV-Export'.'_'.date('Y-m-d');

// Hier werden die Spalten der Exportdatei definiert. 
// Die Struktur ist vom BLSV (Bayerischer Landes-Sportverband) vorgegebenen und muss folgendermaßen aussehen:
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
// 'usf_uuid'  = 'String'                                    * Optionsfeld; soll in dieser Spalte der Inhalt eines Profilfeldes dargestellt werden, so ist die usf_uuid des Profilfeldes einzutragen
//                                                             1: Möglichkeit: 'usf_uuid' => $gProfileFields->getProperty('<interner_Name>', 'usf_uuid')
//                                                             2: Möglichkeit: 'usf_uuid' => 'cddbc1ba-bc3c-4f30-b68b-d8f85f6b9954'
// 'subst'     = array('m' => 'Männlich', 'w' => 'Weiblich') * Optionsfeld, falls Ersetzungen durchgeführt werden sollen
// 'rols_blsv' = array(Rol-ID => Spartennummer im BLSV)      * Pflichtfeld; der Schluesselwert 'rols_blsv' muss einmal vorhanden sein
//
// Wichtig: Alle zu meldenden Mitglieder muessen sich in Rollen befinden (Es bietet sich hier an, mit abhaengigen Rollen zu arbeiten).
//
// Beispiel:
// Alle Mitglieder der Rollen "Fußballabteilung" (Rollen-ID in Admidio 135) und "Gymnastikabteilung" (Rollen-ID in Admidio 125) sollen an den BLSV gemeldet werden.
// Beim BLSV besitzt Fußball die Spartennummer 09, Gymnastik (Turnen) die Spartennummer 34
// Folgende Konfiguration ist anzugeben:
// $columns[] = array('headline' => 'Spartennummer', 'rols_blsv' => array('135' => '09', '125' => '34'));

$columns = array(); 
$columns[] = array('headline' => 'Titel');
$columns[] = array('headline' => 'Name',         'usf_uuid' => $gProfileFields->getProperty('LAST_NAME', 'usf_uuid'));
$columns[] = array('headline' => 'Vorname',      'usf_uuid' => $gProfileFields->getProperty('FIRST_NAME', 'usf_uuid'));
$columns[] = array('headline' => 'Namenszusatz');
$columns[] = array('headline' => 'Geschlecht',   'usf_uuid' => $gProfileFields->getProperty('GENDER', 'usf_uuid'), 'subst' => array('m' => 'Männlich', 'w' => 'Weiblich'));
$columns[] = array('headline' => 'Geburtsdatum', 'usf_uuid' => $gProfileFields->getProperty('BIRTHDAY', 'usf_uuid'));
$columns[] = array('headline' => 'Spartennummer', 'rols_blsv' => array('135' => '09', '134' => '34'));


//**************************************************************************************************
// Option BSBnet kompatible Austauschdatei
//
//   *  *  *  ACHTUNG  *  *  *
// Wird als Exportdatei "BSBnet (XML)" gewählt, darf die Anzahl und die Reihenfolge der Spalten (wie weiter oben erwähnt) NICHT verändert werden.
//
// Im Schluesselwert 'rols_blsv' ist anstelle der Spartennummer des BLSV die Nummer des BSB-Fachverbandes einzugeben.
// 
// Beispiel:
// In Admidio sollen gegeben sein eine Rolle "Fußballabteilung" mit der Rollen-ID 135 und eine Rolle "Volleyballabteilung" mit der Rollen-ID 134
// Im BSB Nord besitzt Fußball die Fachverbandsnummer 30 und Volleyball die Nummer 88
// Folgende Konfiguration ist anzugeben:
// $columns[] = array('headline' => 'Spartennummer', 'rols_blsv' => array('135' => '30', '134' => '88'));
//
// Zusätzlich ist in der BSBnet kompatiblen Austauschdatei die Angabe einer Vereinsnummer erforderlich:
// (Alle weiteren notwendigen Variablen sind in der export.php fest kodiert.)
$verein_nummer = 12345;





