<?php
/**
 * This file is part of Moodle - https://moodle.org/
 *
 * Moodle is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Moodle is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Moodle. If not, see <https://www.gnu.org/licenses/>.
 *
 */
// Copyright (C) 2026 Andreas Giesen. See LICENSE.md.
/**
 * @package certificateelement_coursefieldtext
 * @copyright 2026 Andreas Giesen <andreas@108design.com>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['pluginname'] = 'Certificate Multitool – Cert. Element';
$string['elementname'] = 'Certificate Multitool – Cert. Element';
$string['choosefield'] = 'Wähle ein Kursfeld vom Typ Certificate Multitool – Custom Field';
$string['missingfield'] = 'Fehlendes Kursfeld: {$a}';
$string['coursefield'] = 'Kursfeld';
$string['coursefield_help'] = 'Wähle ein Kursfeld vom Typ Certificate Multitool – Custom Field. Den Text pflegst Du in den Kurseinstellungen. Dieses Element verwendet Schrift, Größe, Farbe, Breite und Ausrichtung der Zertifikatvorlage für alle Zeilen.';
$string['previewcourse'] = 'Vorschaukurs';
$string['previewcourse_help'] = 'Optionaler Kurs ausschließlich für den Designer und das Vorschau-PDF. Du musst den Kurs bearbeiten dürfen. Ausgestellte Zertifikate verwenden ihre gespeicherten Kurswerte unabhängig von dieser Vorschauauswahl.';
$string['issuance'] = 'Ausgestellter Text';
$string['issuancesummary'] = 'Ausgestellte Zertifikate verwenden den bei der Ausstellung gespeicherten Kurstext. ';
$string['issuance_help'] = 'Spätere Kursänderungen verändern den gespeicherten Text bestehender Zertifikate nicht. Fehlende gespeicherte Werte bleiben leer. ';
$string['privacy:metadata'] = 'Zertifikatwerte und Empfängerdaten werden durch den Certificate Manager und Moodles System für benutzerdefinierte Felder gespeichert und verarbeitet.';
$string['adminhelp'] = 'Hilfe: Certificate Multitool – Cert. Element';
$string['adminhelptopics'] = 'Hilfethemen';
$string['adminhelpmore'] = 'Mehr dazu im Hilfepanel';
$string['overview'] = 'Text aus Kursfeld';
$string['overview_help'] = 'Wähle ein mehrzeiliges Kursfeld und gestalte dessen Ausgabe in der Zertifikatvorlage.';
$string['adminhelp_overview'] = 'Textblock einrichten';
$string['adminhelp_overview_article'] = '<p>Lege ein Kursfeld vom Typ Certificate Multitool – Custom Field an und trage die gewünschten Zeilen in den Kurseinstellungen ein. Füge der Zertifikatvorlage Certificate Multitool – Cert. Element hinzu und wähle dieses Feld.</p><p>Lege im Element Schrift, Größe, Farbe, Zeilenhöhe, Textausrichtung und Breite fest. Bei zentrierter Ausrichtung bezeichnet Position X die Mitte des Textblocks. Die Breite begrenzt den Text; lange Zeilen können zusätzlich umbrechen. Die Zeilenhöhe ist ein Faktor der Schriftgröße; ein leeres Feld behält den bisherigen Standard. Textmodus und eine optionale Textausrichtung wählst Du pro Feld in den Kurseinstellungen. Aus Vorlage übernehmen behält die Vorlagenausrichtung; eine explizite Kursausrichtung gilt innerhalb desselben Blocks. Breite und Position bleiben in der Vorlage. Listen und Hervorhebungen bleiben erhalten, Schriftstile aus HTML-Attributen werden entfernt. Mit Bereichshöhe ist Position Y die Bereichsoberkante; Oben oder Unten richtet den Text innerhalb dieses Bereichs aus. Ohne Bereichshöhe bleibt Position Y der bisherige obere oder untere Textanker. Ein ausdrücklicher Kurswert überschreibt diese Voreinstellung. Ein Vorschaukurs zeigt Werte eines Kurses, den Du bearbeiten darfst. Der gestrichelte Kasten im Zertifikatseditor zeigt Breite und Bereichshöhe auch bei leerem Text; er wird nicht ins PDF übernommen. Mitte zentriert den gesamten Textblock vertikal im festgelegten Bereich. Dafür muss im Zertifikatselement eine Bereichshöhe gesetzt sein.</p><p>Zusatztext-Größe (%) steuert ausschließlich mit small markierten Text. Bei 60 % und Schriftgröße 45 wird daraus 27. Die Reihenfolge im Kursfeld bestimmt, ob der kleinere Zusatz über oder unter der Hauptzeile steht. Bereichshöhe und Mitte richten den gesamten Block aus. Speichere das Kursfeld erneut im Modus Begrenztes HTML, um small zu verwenden.</p>';
$string['adminhelp_issuance'] = 'Gespeicherter Zertifikattext';
$string['adminhelp_issuance_article'] = '<p>Die Aktivität Kurszertifikat übergibt Kurswerte an neue Ausstellungen. Andere ausstellende Integrationen müssen dieselben exportierten Kursfelddaten liefern. Fehlt der gewählte gespeicherte Wert, bleibt das Element leer; der aktuelle Kurswert wird niemals ersatzweise verwendet.</p><p>Änderungen am Kurstext gelten für neue Ausstellungen. Erneute PDF-Erzeugung verwendet den gespeicherten Text; Layout und Feldauswahl folgen weiterhin der aktuellen Vorlage. Nach Änderung des Feldkurznamens musst Du das Feld für künftige Ausstellungen erneut auswählen. Vorhandene PDFs bleiben bis zur erneuten Erzeugung unverändert.</p>';

$string['lineheight'] = 'Zeilenhöhe';
$string['lineheight_help'] = 'Faktor der Schriftgröße von 0,5 bis 3, z. B. 1,0 für kompakte Zeilen oder 1,25 für mehr Abstand. Punkt und Komma sind möglich. Leer lässt den Standard des PDF-Renderers unverändert. Werte unter 1 können je nach Schrift zu Überlappungen führen. Gilt auch für automatische Umbrüche und Listen.';
$string['invalidlineheight'] = 'Gib einen Faktor zwischen 0,5 und 3 ein oder lasse das Feld für den Standard leer.';

$string['verticalanchor'] = 'Vertikale Ausrichtung';
$string['anchortop'] = 'Oben';
$string['anchormiddle'] = 'Mitte';
$string['anchorbottom'] = 'Unten';
$string['verticalanchor_help'] = 'Voreinstellung für Kurse mit Aus Vorlage übernehmen. Mit Bereichshöhe richtet Oben den Text am oberen Rand bei Position Y aus; Unten am unteren Rand bei Position Y plus Bereichshöhe. Ohne Bereichshöhe setzt Oben die Textoberkante direkt an Position Y und Unten die Textunterkante direkt an Position Y. Eine ausdrückliche Kursauswahl überschreibt diese Voreinstellung. Die Vorlage bleibt für erneut erzeugte PDFs maßgeblich. Mitte zentriert den gesamten Textblock vertikal im festgelegten Bereich. Dafür muss im Zertifikatselement eine Bereichshöhe gesetzt sein.';
$string['invalidanchor'] = 'Wähle Oben, Mitte oder Unten.';
$string['textaboveedge'] = 'Der Textblock passt nicht oberhalb des unteren Ankers auf die Seite. Erhöhe Position Y, vergrößere die Breite oder verkleinere Schrift bzw. Zeilenhöhe.';

$string['areaheight'] = 'Bereichshöhe (mm)';
$string['areaheight_help'] = 'Optionaler Textbereich in Millimetern. Position Y ist dann immer die Oberkante des Bereichs. Oben richtet den Text am oberen Rand aus; Unten am unteren Rand bei Position Y plus Bereichshöhe. Die Kursauswahl überschreibt die Voreinstellung des Elements. Leer behält das bisherige Verhalten: Oben oder Unten bezieht sich direkt auf Position Y. Zu hoher Text wird im PDF mit einer Fehlermeldung abgewiesen; vergrößere den Bereich oder passe Text, Breite, Schrift bzw. Zeilenhöhe an. Im Zertifikatseditor zeigt ein gestrichelter Kasten die eingestellte Breite und Bereichshöhe, auch bei leerem Text. Er erscheint nicht im PDF. Mitte verteilt den freien Platz gleichmäßig oberhalb und unterhalb des Textblocks.';
$string['invalidareaheight'] = 'Gib eine positive Bereichshöhe in Millimetern ein oder lasse das Feld für die bisherige Ankerfunktion leer.';
$string['areaoutsidepage'] = 'Der Textbereich muss vollständig auf der Zertifikatsseite liegen. Passe Position Y oder die Bereichshöhe an.';
$string['textoutsidearea'] = 'Der Text passt nicht in den festgelegten Bereich. Vergrößere die Bereichshöhe oder passe Text, Breite, Schrift bzw. Zeilenhöhe an.';

$string['coursepreview'] = 'Zertifikatsvorschau';
$string['invalidpreviewactivity'] = 'Wähle eine verfügbare Kurszertifikat-Aktivität aus diesem Kurs.';
$string['previewsummary'] = 'HTML-Layoutvorschau mit gespeicherten Kurswerten. Schrift und Umbrüche können vom PDF abweichen. Empfänger, Datum, Code und sonstige Ausstellungsdaten sind Platzhalter. Punktierte Linien markieren Textbereiche.';
$string['previewactivity'] = 'Kurszertifikat-Aktivität';
$string['previewactivity_help'] = 'Du brauchst Kursbearbeitungsrechte und Zugriff auf die Aktivität. Wähle eine gespeicherte Kurszertifikat-Aktivität dieses Kurses. Die Vorschau verwendet deren zugeordnetes Template. Speichere Änderungen an Kurswerten oder an der Template-Auswahl zuerst und lade danach die Vorschau neu.';
$string['choosepreviewactivity'] = 'Aktivität auswählen';
$string['showpreview'] = 'Vorschau anzeigen';
$string['refreshpreview'] = 'Vorschau aktualisieren';
$string['editpreviewcourse'] = 'Kurswerte bearbeiten';
$string['previewnoactivities'] = 'In diesem Kurs ist keine verfügbare Kurszertifikat-Aktivität vorhanden. Prüfe Sichtbarkeit und Zugriffsrechte oder lege eine Aktivität mit Zertifikatstemplate an.';
$string['previewperson'] = '[Name der teilnehmenden Person]';
$string['previewdate'] = '[Ausstellungsdatum]';
$string['previewcode'] = '[Zertifikatcode]';
$string['previewdynamic'] = '[Ausstellungswert]';
$string['previewmissingimage'] = '[Bild in der Vorschau nicht verfügbar]';
$string['previewunsupported'] = '[{$a}]';
$string['preview'] = 'Hinweise zur HTML-Vorschau';
$string['preview_help'] = 'Die Vorschau zeigt das aktuelle Kurslayout und stellt kein Zertifikat aus. Gespeicherte Zertifikate werden nicht verändert.';
$string['adminhelp_preview'] = 'Kurszertifikat prüfen';
$string['adminhelp_preview_article'] = '<p>Öffne im Kurs unter Mehr die Zertifikatsvorschau. Wähle bei mehreren Kurszertifikat-Aktivitäten die gewünschte Aktivität und dann Vorschau anzeigen. Die Seite verwendet ausschließlich deren gespeicherte Template-Zuordnung. Bei einer einzigen Aktivität erscheint die Vorschau direkt.</p><p>Kurswerte bearbeiten führt zu den Kurseinstellungen. Speichere dort den Text, Textmodus und die Ausrichtung; kehre anschließend zurück und wähle Vorschau aktualisieren. Änderungen im Zertifikatstemplate oder in der Aktivität müssen ebenfalls zuerst gespeichert werden.</p><p>Die Ansicht ist schreibgeschützt und erzeugt weder Ausstellungen noch PDFs. Kursname und Kursfelder verwenden aktuelle Werte, auch absichtlich verborgene Zertifikatfelder. Die im Designer gespeicherte Vorschaukurs-Auswahl wird nicht verändert. Empfängerdaten, Datum, Code und andere Ausstellungswerte bleiben Platzhalter; fremde oder nicht verfügbare Elemente sind gekennzeichnet.</p><p>Punktierte Linien markieren reservierte Textbereiche. Die komplette Zertifikatsseite wird proportional auf höchstens 90 % der Containerbreite und der nutzbaren Fensterhöhe verkleinert. Die zuerst erreichte Grenze bestimmt die Größe; Textumbrüche und Abstände bleiben dabei erhalten. Der Browser verwendet verfügbare Schriftarten; genaue Umbrüche, Abstände und die endgültige Ausgabe prüfst Du weiterhin im PDF. Unterstützt werden feste Texte, Kursdaten, mehrzeilige Kursfelder, Rahmen, native Rasterbilder und das optionale Kursbild-Element. Eingebettete Bilder in Textfeldern und andere Medien werden durch Platzhalter ersetzt.</p>';

$string['middleneedsarea'] = 'Setze im Zertifikatselement eine Bereichshöhe, um den Text vertikal mittig auszurichten.';

$string['smalltextsize'] = 'Zusatztext-Größe (%)';
$string['smalltextsize_help'] = 'Größe für mit small markierten Zusatztext im HTML-Modus: 10 bis 100 Prozent der Schriftgröße dieses Elements, Standard 60. Beispiel: 45 mit 60 % ergibt 27. Schreibe im Kursfeld &lt;small&gt;Zusatztext&lt;/small&gt;&lt;br&gt;Certificate oder umgekehrt und speichere im Modus Begrenztes HTML. Verschachteltes small verkleinert nicht weiter. Position, Bereichshöhe und vertikale Ausrichtung gelten weiterhin für den gesamten Block. Bisherige HTML-Werte unterstützen small erst nach erneutem Speichern im HTML-Modus; bestehende Ausstellungen behalten ihre Interpretation.';
$string['invalidsmalltextsize'] = 'Gib einen Prozentwert von 10 bis 100 ein oder lasse das Feld für 60 % leer.';
