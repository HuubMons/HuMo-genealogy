<?php
echo '<div class="help_box">';
echo '
<p class="help_header">Ikonforklaringer</p>
<p class="help_text">- Forklaringer: <br>
<span class="help_explanation">Til venstre for en persons navn i rapporter ser du et ikon <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports">. Når du beveger musen over dette ikonet dukker det opp en meny. I denne menyen vises de forskjellige tavler og rapporter som kan lages for denne personen (antall muligheter avhenger av om personen har aner/etterkommere). Under er en beskrivelse av disse menyvalgene.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>Anetavle</b>: En anetavle er en genealogisk rapport for en person (startpersonen) med dennes aner. I en anetavle benyttes et spesielt nummereringssystem: Startpersonen er nr. 1, hans far er nr. 2 og hans mor er nr. 3. Farens tall fortsetter for hver generasjon å være det doble av sønnens, mens moren ligger ett tall høyere. Nummer 40 er altså far til nummer 20, og nummer 41 er hans mor. <br>Anetavlen kan vises som tekst eller som en grafisk presentasjon (slektstre).<br>Les mer om anetavler <a href="http://en.wikipedia.org/wiki/Pedigree_chart" target="blank"><b>her</b></a> og <a href="http://en.wikipedia.org/wiki/Ahnentafel" target="blank"><b>her</b></a> (engelsk).</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="Parenteel">&nbsp;<b>Etterkommerrapport</b>: En etterkommertavle er en genealogisk rapport med utgangspunkt i et par eller en stamfar (generasjon I) med barn (generasjon II) og alle påfølgende etterkommere langs både mannlige og kvinnelige linjer. <br>Blant menyvalgene med samme ikon kan du også velge en grafisk presentasjon av etterkommerrapporten.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp;<b>Etterkommerrapport</b>: En etterkommerrapport er en oppsummering av etterkommerne til en person, der hver generasjon nummereres i stigende rekkefølge. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b>Anetabell</b>: En anetabell viser 5 generasjoner i tabellformat, med startpersonen på bunnen og anene i gradvis mindre felt over. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>Viftetavle</b>: En viftetavle er en rund anetavle med anene i sirkler rundt startpersonen. Dette gir lettforståelig oversikt over anene til en person. Hver person i viftetavlen er lenket til familiesiden til personen. <br>Menyen til venstre gir tilgang til innstillinger for størrelse samt enkelte andre valg.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">Hovedmeny</p>
<p class="help_text">- Diverse elementer i hovedmenyen:<br>
- A+ A- Reset knapper<br>
<span class="help_explanation">Med disse knappene kan du tilpasse størrelsen på teksten på de fleste sider. Reset gjenoppretter standardstørrelsen på teksten. (Merk at Reset-funksjonen bare er tilgjengelig i nettlesere som støtter funksjonen). </span><br><br>
- Et orange RSS-ikon (vises bare hvis funksjonen er aktivert av administrator)<br>
<span class="help_explanation">Hvis du legger denne feed\'en (som det heter) i din RSS-leser vil du kunne se med et øyekast hvem som har fødselsdag!<br>(I verktøy-menyen vil du finne en fødselsdagsliste. Her vises fødselsdager i inneværende måned).</span><br>
<p class="help_text">- Språk<br>
<span class="help_explanation">Med menyknappen som viser et flagg kan du endre standardspråk for hele siden.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">Menyvalget "Hovedindeks"</p>
<p class="help_text">- Sideeier<br>
<span class="help_explanation">Klikker du navnet på sidens eier kommer det opp et epost-skjema. Vennligst oppgi ditt navn og epostadresse, så du kan få svar. Ønsker du å sende et bilde eller et dokument kan du spørre eieren om hans epostadresse. Bruk så ditt vanlige epostprogram for å sende vedlegg (eierens adresse er ikke synlig her, for å hindre søppelpost).</span><br><br>
- Søkefelt<br>
<span class="help_explanation">I søkefeltene kan du søke på for- og/eller etternavn. I tillegg kan du benytte valgene "Inneholder", "Er lik" eller "Begynner med". Merk: under søkefeltet er et valg for &quot;Avansert søk!&quot;</span><br><br>
- Mer<br>
<span class="help_explanation">De påfølgende linjene trenger ingen forklaring. Klikk lenken du ønsker å gå til.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menyvalget "Personer"</p>
<p class="help_text">- Denne siden viser en liste med personer<br>
<span class="help_explanation">Listen viser alle personer i familietreet, sortert alfabetisk. Maksimalt 150 personer vises samtidig. Klikk på sidenummerne for å bla i listen.</span><br><br>
- Kompakt eller detaljert visning<br>
<span class="help_explanation">Du kan velge mellom &quot;Kompakt visning&quot; eller &quot;Detaljert visning&quot;. I detaljert visning vises også (eks)partnere, disse er ikke synlige i kompakt visning.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menyvalget "Navn"</p>
<p class="help_text">- Forklaring:<br>
<span class="help_explanation">Her vises alle familienavn, etterfulgt av de personer som har navnet.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menvalget "Steder"</p>
<p class="help_text">- Forklaring: (denne knappen er bare synlig hvis den er aktivert av sidens eier)<br>
<span class="help_explanation">Her kan du søke på fødested, adresse og steder for død eller begravelse. <br>
Du kan søke med alternativene "inneholder", "er lik" og "starter med". <br>
Også her kan du velge mellom kompakt eller detaljert visning. <br>
Resultatene vises alfabetisk sortert.</span><br>
</p>';
echo '</div>';
echo '<p><div class="help_box">';
echo '<p class="help_header">Menyvalget "Verktøy"</p>
<p class="help_text">Innhold i verktøymenyen:<br>
<p class="help_text">- Kilder: (knappen vises bare hvis sideeieren har aktivert den)<br>
<span class="help_explanation">Her vises en liste med alle kilder som er benyttet i slektsgranskingen.</span><br>
<p class="help_text">- Fødselsdagsliste:<br>
<span class="help_explanation">Viser en liste med alle personer som har fødselsdag i inneværende måned. Du kan også velge en annen måned enn den inneværende.</span><br>
<p class="help_text">- Statistikk:<br>
<span class="help_explanation">Statistikkinformasjonen er selvforklarende.</span><br>
<p class="help_text">- Relasjonskalkulator:<br>
<span class="help_explanation">Med relasjonskalkulatoren kan du finne blods- eller ekteskapelige relasjoner mellom personer. I søkefeltene "Fornavn" og "Etternavn" kan du skrive inn hele navn, deler av navn eller ingenting. Klikk deretter "Søk" og velg et navn fra listen. Når to navn er valgt klikker du "Beregn relasjon". Hvis en relasjon blir funnet vil den vises som tekst og med en grafisk forklaring. Bruk vendeknappen for å bytte om personene.</span><br>
<p class="help_text">- Kontakt:<br>
<span class="help_explanation">Dette åpner epostskjemaet, lik det som er beskrevet over ("Hovedindeks -> Sideeier").</span><br>
</p>';
echo '</div>';
echo '<p><div class="help_box">';
echo '<p class="help_header">Menyvalget "Fotobok"</p>
<p class="help_text">- Forklaring: (denne knappen vises bare hvis sideeieren har aktivert den)<br>
<span class="help_explanation">Her vil du se alle fotos i databasen. Klikk på et foto for en forstørret versjon, eller klikk på navnet for å gå til personens familieside.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menvalget "Logg inn"</p>
<p class="help_text">- Forklaring:<br>
<span class="help_explanation">Hvis du har mottatt et brukernavn og passord kan du logge inn her for å se informasjon som ikke er allment tilgjengelig (som detaljer om levende, eller "skjulte" familietrær).<br>
</p>';
echo '</div>';
?>
