<?php
echo '<div class="help_box">';
echo '
<p class="help_header">Symbole in Listen und Berichten</p><br>

<span class="help_explanation">Links neben den Namen von Personen in Listen oder Berichten ist das Symbol <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports"> zu sehen. Wenn der Mauszeiger über dieses Icon geführt wird, klappt ein Menü auf. Dieses Menü zeigt mehrere Icons mit Namen von Berichten oder Grafiken, die für diese Person erstellt werden können (die Anzahl der Icons ist abhängig vom Vorhandensein von Vorfahren und/oder Nachkommen). Nachfolgend eine Liste dieser Icons und deren Bedeutung.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp; <b>Familienblatt</b>: Ein  Familienblatt ist eine kurzgefasste Zusammenstellung aller Nachkommen einer Person (und seines/ihres Partners), wobei jede Generation ihre eigene Nummer (aufsteigend) erhält. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>Ahnenbericht (Ahnentafel/Grafik)</b>: Ein Ahnenbericht ist ein genealogischer Bericht einer Person (die Basis-Person) mit ihren Vorfahren. Bei einer Ahnentafel wird eine spez. Methode zur Nummerierung benutzt: Die Basis-Person ist Nummer 1, ihr Vater Nummer 2 und ihre Mutter Nummer 3. Die Nummer eines Vaters ist die doppelte des Sohnes, die der Mutter ist dann eine Nummer höher. Also, Nummer 40 ist der Vater von Nummer 20 und 41 ist die Mutter von Nummer 20.<br>Über die Icons in dem Pop-up Meü kann eine graphische Anzeige des Ahnenberichts ausgewählt werden.<br>Mehr zu Ahnentafel / Stammbaumdiagramme gibt es auf Wikipedia unter den Schlagworten <a href="http://de.wikipedia.org/wiki/Stammbaum" target="blank"><b>Stammbaum</b></a> und <a href="http://de.wikipedia.org/wiki/Ahnentafel" target="blank"><b>Ahnentafel</b></a>.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b>Ahnenblatt</b>: Ein Ahnenblatt zeigt 5 Generationen in Tabellenform, mit der Basis-Person unten und darüber seine/ihre Vorfahren in kleiner werdenden Boxen.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>Fächerdiagramm</b>: Ein Fächerdiagramm ist eine kreisförmige Darstellung, bei der die Vorfahren in kreisförmig in um die Basis-Person gelegten Bänder angezeigt werden. Das ermöglicht eine gute Darstellung der Ahnen / Vorfahren einer bestimmten Person. Das Feld jeder Person in der Grafik ist klickbar um den schnellen Zugriff auf das Familienblatt dieser Person zu erlauben.<br>Größe des Fächerdiagramms und einige andere Parameter können im Menü links vom Diagram, eingestellt werden. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/dna.png" alt="DNA Diagram">&nbsp;<b>DNA-Diagramme</b>: Zeigt verschiedene Formen von DNA Diagrammen (Y-DNA und mtDNA) für diese Person an. Detaillierte Hilfe ist in der Funktion selber vorhanden.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/timeline.gif" alt="Historic timeline">&nbsp;<b>Geschichtliche Zeitachse</b>: Zeigt die für die Person relevante geschichtliche Ereignisse für seine erfassten Daten an. Detaillierte Hilfe ist in der Funktion selber vorhanden.</span><br><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">Kopfzeile</p>
<p class="help_text">"Stammbaum" Dropdownliste:
<span class="help_explanation">In der Liste werden die für Sie verfügbaren Stammbäume angezeigt. Hier können Sie schnell durch einfaches Klicken von einem zum anderen Stammbaum wechseln.<br>(Achtung: "Reset" ist nur bei Browsern, die diese Funktion unterstützen, sichtbar).</span><br>
<p class="help_text">"Suchen"-Feld und Schaltfläche: 
<span class="help_explanation">Hier können Sie schnell durch Eingabe eines Namens oder Teil eines Namens und klicken auf die Schaltfläche "Suchen" eine Person suchen und anzeigen lassen.</span><br>
<p class="help_text">"Favoriten" Dropdownliste:
<span class="help_explanation">Wenn Sie in der Personenliste eine Person als Favoriten markiert haben wird sie in dieswer Dropdownliste angezeigt und Sie können durch klicken schnell direkt auf die Person zugreifen.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Hauptmenü "Stammbaum"</p>
<p class="help_text">Stammbaumindex: 
<span class="help_explanation">Listet in der linken Spalte die vorhandenen Stammbäume der Website. In der mittleren Spalte wird eine Liste der häufigsten namen des gewählten Stammbaums angezeigt. In der rechten Spalte können Sie in einem ausführlichen Dialog alle oder den gewählten Stammbaum durchsuchen.</span><br>
<p class="help_text">Personen: 
<span class="help_explanation">In dieser Liste sind alle Personen des Stammbaums aufgeführt. Es werden jeweils 30 Personen auf einmal angezeigt. Durch anklicken der Seitennummer kann die jeweilige Seite angezeigt werden. In der Kopfzeile können verschiedenen Sortierungen gewählt werden. Im oberen teil der Seite kann durch Eingabe im Suchfeld ein beliebiger Name gesucht werden. Die Schaltfläche "Erweiterte Suche" öffnet einen komplexen Suchdialog in dem nach den verschiedensten Kriterien gesucht werden kann.</span><br>
<p class="help_text">Namen: 
<span class="help_explanation">Diese Seite zeigt eine Liste aller Familiennamen, inklusive der Personenzahl die diesen Namen tragen. Durch Anklicken eines Bucstabens können alle mit diesem Buchstaben beginnenden Nachnamen gewählt werden. In den beiden darunter aufgeführten Dropdowns kann die Anzahl der maximal angezeigten Familiennamen sowie die der Spalten verändert werden."</span><br>
<p class="help_text">Orte nach Namen/nach Familien (nur sichtbar wenn vom Seiten-Eigner aktiviert): 
<span class="help_explanation">Hier werden Personen oder Familen nach Orten sortiert aufgelistet. Es kann über das Feld in der linken Spalte eine Suche nach Geburts-, Wohn-, Sterbe- oder Begräbnissort durchgeführt werden. Als Optionen sind: "enthält", "gleich" and "beginnt mit" einstellbar. Auch hier kann zwischen "ausfürlich anzeigen" oder "verkürzt anzeigen" gewählt werden.<br>Das Ergebnis wird nach alphabetisch sortierten Ortsnamen ausgegeben.</span><br>
<p class="help_text">Fotoalbum: (nur sichtbar wenn vom Seiten-Eigner aktiviert)<br>
<span class="help_explanation">Hier werden alle Photos, die in der Datenbank sind, angezeigt. Ein Klick auf ein Bild vergrößert die Anzeige, ein Klick auf den Namen ruft die entsprechende Familienseite der Person auf.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Hauptmenü "Werkzeuge"</p>
<br>Verschiedene Untermenüs erscheinen unter dem Werkzeugmenü:<br>
<p class="help_text">Quellen: (nur sichtbar wenn vom Seiten-Eigner aktiviert): 
<span class="help_explanation">Hier werden alle Quellen aufgelistet, die für die Stammbaumforschung verwendet wurden.</span><br>
<p class="help_text">Geburtstagsliste: 
<span class="help_explanation">Öffnet eine Liste mit allen Personen in dem gewählten Stammbaum, die in diesem Monat Geburtstag haben oder hatten. Sie können auch einen anderen Monat als den gegenwärtigen auswählen.</span><br>
<p class="help_text">Statistiken: 
<span class="help_explanation">Durch die Informationen die es zu bzw. in den einzelnen Felder gibt, bedarf es keiner weiteren Erklärung zu dieser Seite.</span><br>
<p class="help_text">Verwandtschaftsgrad Rechner: 
<span class="help_explanation">Mit dem Verwandtschaftsgradrechner lässt sich die verwandschaftliche Beziehung zwischen zwei Personen ermitteln.<br>In die Felder "Vorname" und "Nachmame" können die Namen und Teile von Namen eingegeben oder aber auch das jeweilge Feld leer gelassen werden. Aus der Ergebnisliste wird dann der gewünschte Namen ausgewählt. Nachdem die beiden Personen namentlich festgelegt sind, wird über die Schaltfläche "Verwandtschaftsgrad ermitteln" das Ergebnis textlich und graphisch dargestellt. Mit dem "Wechsel-Symbol" lassen sich die Personen tauschen.</span><br>
<p class="help_text">Google Maps: 
<span class="help_explanation">Zeigt eine Google Map an, die die Personen in der Datenbank entweder nach Geburts- oder Sterbeorten anzeigt. Erläuterungen für die Benutzung der Google Map Funktionalität können <a href="http://humogen.com/index.php/manual" target="_blank">hier</a> im Onlinehandbuch unter "Additional Manuals" gefunden werden.</span><br>
<p class="help_text">Kontakt: 
<span class="help_explanation">Öffnet ein Emmailformular ähnlich dem oben beschriebenen ("Hauptindex -> Seiten-Eigner")</span><br>
<p class="help_text">Letzte Änderungen 
<span class="help_explanation">Zeigt eine Liste neuer oder kürzlich geänderter Einträge für Personen in der Datenbank an. Eine Liste in absteigender chronologischer Reihenfolge mit den neuesten Änderungen zuerst wird angezeigt. Es gibt ein Suchfeld, das es erlaubt die Ergebnisliste weiter einzuschränken. In das feld können Teile von Namen eingegeben werden, z. B. zeigt die Eingabe von "Sa" alle Personen an, die SA im Namen haben, also Sam, Sarah, Susanne oder auch Personen beispielsweise mit Nachnamen Samson. </span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Hauptmenü "Anmelden"</p>
<br><span class="help_explanation">Wenn Ihnen der Seiten-Eigner einen Benutzernamen und Passwort gegeben hat, können Sie sich anmelden um Informationen zu erhalten, die nicht öffentlich zugänglich sind (z. B. Details über lebende Personen oder "versteckte" Stammbäume).</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Hauptmenü Schaltfläche Sprachflaggen</p>
<br><span class="help_explanation">Im Hauptmenü rechts von den Menüeinträgen sehen Sie eine Liste von Länderflaggen, die es Ihnen erlaubt die Anzeigesprache zu ändern.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Hauptmenü Schaltfläche <img src="'.CMS_ROOTPATH.'images/settings.png" alt="Einstellungen">&nbsp; ("Einstellungen" )</p>
<p class="help_text">Benutzereinstellungen: 
<br><span class="help_explanation">Wenn Sie angemeldet sind können Sie hier Ihr Passwort und Ihre Emailadresse ändern.<br>
<p class="help_text">Vorlagen auswählen: 
<br><span class="help_explanation">In der Dropdownliste wird eine Anzahl von Webdesigns an, aus denen Sie auswählen können. Diese Einstellung wird bis zur nächsten Änderung gespeichert.<br></p>';
echo '</div>';
?>
