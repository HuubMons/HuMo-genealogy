<?php
echo '<div class="help_box">';
echo '
<p class="help_header">Icons</p>

<p class="help_text">-Erklärung:<br>
<span class="help_explanation">Links der Namen von Personen in Listen oder Berichten ist das Icon <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports"> zu sehen. Wenn der Mauszeiger über dieses Icon geführt wird, klappt ein Menü auf. In diesem Menü sind mehrere Icons mit Namen von Berichten oder Grafiken die über diese Person erstellt werden können (die Anzahl der Icons ist abhängig vom Vorhandensein von Vorfahren und/oder Nachkommen). Nachfolgend ist eine Liste dieser Icons und deren Bedeutung.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>Ahnentafel (Vorfahren Bericht/Grafik)</b>: Eine Ahnentafel ist ein genealogischer Bericht einer Person (die Basis-Person) mit ihren Vorfahren. Bei einer Ahnentafel wird eine spez. Methode zur Nummerierung benutzt: Die Basis-Person ist Nummer 1, ihr Vater Nummer 2 und ihre Mutter Nummer 3. Die Nummer eines Vaters ist die doppelte des Sohnes, die der Mutter ist dann eine Nummer höher. Also, Nummer 40 ist der Vater von Nummer 20 und 41 ist die Mutter von Nummer 20.<br>Über die Icons in dem Pop-up Meü kann eine graphische Anzeige des Ahnen-Berichts ausgewählt werden.<br>Mehr zu Ahnentafel / Stammbaum Diagramme gibt es <a href="http://de.wikipedia.org/wiki/Stammbaum" target="blank"><b>hier</b></a> und <a href="http://de.wikipedia.org/wiki/Ahnentafel" target="blank"><b>hier</b></a> auf Deutsch und <a href="http://en.wikipedia.org/wiki/Pedigree_chart" target="blank"><b>hier</b></a> und <a href="http://en.wikipedia.org/wiki/Ahnentafel" target="blank"><b>hier</b></a> in Englisch.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="Parenteel">&nbsp;<b>Stammbaum (Nachfahren Bericht)</b>: Ein Stammbaum Graph/Diagramm ist ein genealogischer Bericht eines eines einzelnen Paares oder eines Stammvaters (Generation I) mit ihren Kindern (Generation II) und allen weiteren Nachkommen, entlang der männlichen und weiblichen Linien. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp; <b>Outline Bericht</b>: Ein Outline Bericht ist eine kurzgefasste Zusammenstellung aller Nachkommen 
einer Person (und seines/ihres Partners), wobei jede Generation ihre eigene Nummer (aufsteigend) erhält. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b>Vorfahren Blatt</b>: Ein Vorfahren Blatt zeigt 5 Generationen in Tabellenform, mit der Basis-Person unten und darüber seine/ihre Vorfahren in kleiner werdenden Boxen.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>Fanchart (Kreisdiagram)</b>: Ein Fanchart ist eine kreisförmige Darstellung, bei der die Vorfahren, in kreisförmig in um die Basis-Person gelegten Bänder, angezeigt werden. Das ermöglicht eine gute Darstellung der Ahnen / Vorfahren einer bestimmten Person. Das Feld jeder Person in der Grafik ist klickbar um den schnellen Zugriff auf das Familien-Blatt dieser Person zu erlauben.<br>Größe des Fancharts und einige andere Parameter können im Menü, links vom Diagram, eingestellt werden. </span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">Top Menü</p>
<p class="help_text">-Items im Top Menü:<br>
- Die A+ A- Reset Schaltflächen<br>
<span class="help_explanation">Mit diesen Schaltflächen kann die Schriftgröße des Textes auf den meisten Seiten eingestellt werden. Mit "Reset" kehrt man schnell wieder zur Standardanzeige zurück.<br>(Achtung: "Reset" ist nur bei Browsern, die diese Funktion unterstützen, sichtbar).</span><br>
-Das orange RSS-Icon (nur sichtbar wenn vom Seiten-Eigner aktiviert)<br>
<span class="help_explanation">Wenn dieser Feed (so ist das genannt) zu einem benutzten RSS-Reader hinzugefügt wird, kann man auf einen Blick aktuelle Geburtstage sehen!<br>(Im "Tool" Pull-down Menü ist auch eine "Geburtstags-Liste" Option. Bei dieser Option wird eine Liste aller Geburtstage des aktuellen Monats angezeigt).</span><br>
-Sprachen<br><span class="help_explanation">Im Menüband, rechts der Menütasten, sind mehrere Länderflaggen, über die man die Seitensprache wechseln kann.</span>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menütaste "Hauptindex"</p>
<p class="help_text">-Stammbaum Eigentümer:<br>
<span class="help_explanation">Durch einen Klick wird ein e-mail Formular geöffnet, über das man dem Stammbaum Eigentümer eine Mail schicken kann. Bitte geben Sie Ihren Namen und Ihre e-mail Adresse vollständig an, damit Sie die Antwort auf diese e-mail auch erhalten können. Um dem Eigentümer einen Anhang (wie ein Photo oder Dokument) zu schicken, erfragen Sie bitte über dieses Formular die e-mail Adresse des Eigentümers. Danach kann der Anhang über ein normales Mail-Programm an diese e-mail Adresse erfolgen. (Aus Spamm-Gründen ist die e-mail Adresse nicht öffentlich zugängig.)</span><br>
-Such Felder<br><span class="help_explanation">In die Such-Felder können Vor- und/oder Nachnamen zur Suche eingegeben werden. Folgende drei Optionen: "enthält", "gleich" and "beginnt mit" sind wählbar.<br>Achtung: Mit &quot;Erweiterte Suche!&quot; stehen zusätzliche Optionen bereit</span><br>
-Mehr<br><span class="help_explanation">Die nächsten Zeilen sind selbsterklärend: Link anklicken um zur gewünschten Information zu gelangen.</span><br>

</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menütaste "Personen"</p>
<p class="help_text">-Diese Seite zeigt eine Liste mit Personen<br>
<span class="help_explanation">In dieser Liste sind alle Personen des Family Trees, alphabetisch sortiert, aufgeführt. Es werden maximal 150 Personen angezeigt. Durch anklicken der Seitennummer kann die jeweilige Seite angezeigt werden.</span><br>
-Verkürzte oder Ausfürliche Anzeige<br><span class="help_explanation">Mit "verkürzt anzeigen" oder "ausfürlich anzeigen" kann der Anzeigemodus geändert werden. In der ausführlichen Anzeige werden auch die (Ex)Partner aufgeführt, die in der der verkürzten Anzeige nicht enthalten sind.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menütaste "Namen"</p>
<p class="help_text">-Erklärung:<br>
<span class="help_explanation">Diese Seite zeigt eine Liste aller Familiennamen, inklusive der Personenzahl die diesen Namen tragen.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menütaste "Orte"</p>
<p class="help_text">-Erklärung: (die Taste ist nur sichtbar wenn vom Seiten-Eigner aktiviert)<br>
<span class="help_explanation">Hier kann eine Suche nach Geburtsort, der Adresse und Sterbe- oder Begräbnissort durchgeführt werden. Als Optionen sind: "enthält", "gleich" and "beginnt mit" einstellbar.<br>Auch hier kann zwischen "ausfürlich anzeigen" oder "verkürzt anzeigen" gewählt werden.<br>Das Ergebnis wird nach alphabetisch sortierten Ortsnamen ausgegeben.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menütaste "Tools"</p>
<p class="help_text">Items im Tools Men&uuml<br>
-Quellen: (nur sichtbar wenn vom Seiten-Eigner aktiviert)<br>
<span class="help_explanation">Hier werden alle Quellen zum Erstellen der Daten in dieses Stammbaumes aufgelistet.</span><br>
-Geburtstagsliste:<br>
<span class="help_explanation">Öffnet eine Liste mit allen Personen in dem "Family Tree", die in diesem Monat Geburtstag haben oder hatten. Bei angezeigter Liste kann ein anderer Monat gewählt werden.</span><br>
-Statistiken:<br>
<span class="help_explanation">Durch die Informationen die es zu bzw. in den einzelnen Felder gibt, bedarf es keiner weiteren Erklärung zu dieser Seite.</span><br>
-Verwandtschaftsgrad Rechner:<br>
<span class="help_explanation">Mit dem Verwandtschaftsgrad Rechner lässt sich die verwandschaftliche Beziehung zwischen zwei Personen ermitteln.<br>In die Felder "Vorname" und "Nachmame" können die Namen und Teile von Namen eingegeben oder aber auch das jeweilge Feld leer gelassen werden. Aus der Ergebnisliste wird dann der gewünschte Namen ausgewält. Nachdem die beiden Personen namentlich festgelegt sind, wird über die Schaltfläche "Verwandtschaftsgrad ermitteln" das Ergebnis textlich und graphisch dargestellt. Mit dem "Wechsel-Symbol" lassen sich die Personen tauschen.</span><br>
-Kontakt:<br>
<span class="help_explanation">Öffnet ein e-mail Formular ähnlich dem oben beschriebenen ("Hauptindex -> Seiten-Eigner")</span><br>

</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menütaste "Photoalbum"</p>
<p class="help_text">-Erklärung: (nur sichtbar wenn vom Seiten-Eigner aktiviert)<br>
<span class="help_explanation">Hier werden alle Photos, die in der Datenbank sind, angezeigt.<br>Klick auf ein Bild vergrößert die Anzeige.<br>Klick auf den Namen ruft die entsprechende Familien-Seite der Person auf.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menütaste "Login"</p>
<p class="help_text">-Erklärung:<br>
<span class="help_explanation">Wenn Ihnen der Seiten-Eigner einen Benutzernamen und Passwort gegeben hat, können Sie sich "einloggen" um Informationen zu erhalten, die nicht öffentlich zugängig sind (Details über lebende Personen oder "versteckte" Stammbäume).</span><br>
</p>';
echo '</div>';
?>
