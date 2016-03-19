<?php
echo '<div class="help_box">';
echo '
<p class="help_header">Ikoner</p>
<p class="help_text">- Forklaring: <br>
<span class="help_explanation">Til venstre for personnavnet i lister og rapporter,vil du se ikonet for Rapporter <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports">. Når du flytter musen over ikonet, vises en popup menu. I denne popup menu finder du flere ikoner med navne på rapporter og kort, som du kan danne for denne person (det eksakte nummer af ikoner på listen varierer alt efter tildtedeværelsen af forfædre og/ eller efterkommere). Følgende er en liste over disse ikoner over deres betydning.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>Anetavle rapport</b>: En anetavle er en genealogisk rapport over en person\'s forfædre. En sådan anetavle benytter en speciel metode til nummerering: basepersonen (probanden) er nummer 1, hans far nummer 2 og hans mor nummer 3. Faderens nummer er dobbelt op i forhold til sønnens og moderens et nummer højere. Således er nummer 40 en far til nummer 20 og nummer 41 er moderen til nummer 20. <br>Blandt ikonerne i pop-up menuen kan du også vælge en grafisk visning af forfader rapporten.<br>Du kan læse mere om anetavler <a href="http://da.wikipedia.org/wiki/Anetavle" target="blank"><b>her</b></a></b></a></span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="Parenteel">&nbsp;<b>Efterkommer rapport/Kort</b>: En efterkommer rapport er en genealogisk rapport af et patriarkalsk par eller en patriark (generation I) med deres børn (generation II) og alle yderligere efterkommere både langs mandlig og kvindelig linje. <br>Blandt ikonerne i popup menuen kan du også vælge en grafisk visning af efterkommer rapporten.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp;<b>Oversigts rapport</b>: En oversigts rapport er en klar oversigt over alle en persons efterkommere (og hans/hendes partnere), hvor hver generation får sit eget (stigende) nummer. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b>Anetavle skema</b>: Et anetavle skema lister 5 generationer i et tabel layout med base personen nederst og forfædrene over ham/hende i stadig mindre kasser. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>Anehjul</b>: Et anehjul er et cirkulært kort, der viser forfædrene i cirkler omkring basepersonen. Dette giver mulighed for et klart billede af forfædrene for enhver specifik person. Boksen for hver person på kortet er klikbar for at give hurtig adgang til denne person\'s familie ark. <br> Størrelsen af anehjulet og nogle andre indstillinger kan justeres i menuen til venstre i diagrammet. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/timeline.gif" alt="Timeline chart">&nbsp;<b>Tidslinje kort</b>: Tidslinje kortet viser historiske begivenheder, ved siden af en person\'s livsbegivenheder, for at give en fornemmelse af den tid hvori personen levede.<br> Dette diagram har sin egen selvstændige hjælp, som du kan se ved at holde musen over ordet "Hjælp" til venstre på skærmen.</span><br><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">Øverste bånd</p>
<p class="help_text">- “Søge” feltet og knapper<br>
<span class="help_explanation">Denne boks optræder på alle sider i HuMo-gen og giver dig den bekvemmelighed at søge efter enhver person i databasen på alle sider. At skrive et navn og klikke på søgning vil vise en liste over personer fra databasen, der bærer et navn, som matcher søgeordet.</span><br><br>
- “Vælg et tema” dropdown menu:<br>
<span class="help_explanation">Som standard er HuMo-gen forsynet med adskillige farve skemaer og så længe administratoren ikke har deaktiveret dem, vil de blive vist i denne dropdown menu. Du kan vælge det tema, som du foretrækker, det vil ændre elementer såsom side farve, baggrunds billede etc. Disse temaer vil kun påvirke din oplevelse af HuMo-gen og vil IKKE skabe ændringer i din internet browser eller computer.</span><br><br>
- A+ og A- tasterne<br>
<span class="help_explanation">Disse taster giver dig mulighed for at kontrollere tekst størrelsen på skærmen, mens du bruger HuMo-gen. Tasterne vil kun påvirke din oplevelse af HuMo-gen og vil IKKE skabe nogen ændringer i din internet browser eller computer. (Bemærk: Brugen af disse taster er kun mulig/synlig i browsere, der understøtter denne funktion). </span><br><br>
- Orange RSS-ikon (vises kun, hvis det er aktiveret af webstedets ejer)<br>
<span class="help_explanation">Hvis du tilføjer dette feed (som det hedder) til din RSS-læser, vil du med et blik blive i stand til at se hvem, der har fødselsdag!<br>(I "Værktøj" pulldown menuen kan du se en "Årsdags liste" valgmulighed. Denne mulighed vil vise en liste over fødselsdage i dette måned).</span><br>

</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">Hoved menu knappen "Home"</p><br>
<span class="help_explanation">Dette bringer dig til hovedpersonens index.
Nogle paneler på denne side kræver en forklaring:</span><br>
<p class="help_text">- Stamtræets ejer<br>
<span class="help_explanation">Hvis du klikker på navnet på webstedets ejer, vil en email formular åbne sig, der tillader dig at sende ejeren af webstedet en kort besked. Indtast venligst dit navn og email adresse så du kan modtage et svar. Hvis du ønsker at sende ejeren af webstedet en vedhæftet fil (som et foto eller dokument) kan du bruge denne formular til at anmode ejeren af webstedet om hans email. Derefter kan du bruge et hvilket som helst almindeligt email program til at sende disse vedhæftede filer. (Email adressen på ejeren for webstedet er ikke offentliggjort på webstedet for at undgå spam).</span><br><br>
<p class="help_text">- Søge feltet<br>
<span class="help_explanation">I søgefeltet kan du søge ved at bruge fornavn og/eller efternavn. Du kan også vælge mellem tre muligheder: "indeholder", "lig med" og "starter med". Bemærk: lige under søge knappen er der en mulighed for &quot;Avanceret søgning&quot.</span><br><br>
- Mere<br>
<span class="help_explanation">De næste par linjer er indlysende: klik på linket som du vil flytte til.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">Hoved menu knappen "Stamtræ"</p><br>
<p class="help_text">- Stamtræ index<br>
<span class="help_explanation">Dette bringer dig til den vigtigste persons index (Se ovenfor).</span><br><br>
<p class="help_text">- Personer<br>
<span class="help_explanation">Tager dig til den vigtigste persons index. Det samme som ‘Home’ knappen.
Listen viser alle personer i stamtræet, sorteret alfabetisk. Et maksimalt antal på 150 personer vises. Du kan trykke på sidetal for at se mere. Du kan vælge mellem "Kortfattet visning" eller "Udvidet visning". I den udvidede visning bliver (ex)partnere også vist, de vises ikke i den kortfattede visning.</span><br><br>
<p class="help_text">- Navne<br>
<span class="help_explanation">Her finder du en liste over alle familienavne, efterfulgt af det antal personer, der bærer det navn.</span><br><br>
<p class="help_text">- Steder (denne knap vises kun, hvis den er aktiveret af ejeren af webstedet)<br>
<span class="help_explanation">Her kan du søge på fødested, dåb, på adresse, sted for død eller begravelse. 
Du kan søge med mulighederne:: "indeholder", "lig med" og "starter med". 
Også her kan du vælge mellem udvidet eller kortfattet visning. 
Resultaterne vil blive sorteret alfabetisk efter stednavn.
</span><br><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Hoved menu knappen "Værktøj"</p><br>
<span class="help_explanation">Adskillige undermenuer vises under Værktøj menuen:</span><br>
<p class="help_text">- Kilder: (vises kun, hvis det er aktiveret af webstedets ejer)<br>
<span class="help_explanation">Her finder du en liste over alle de kilder, der anvendes i slægtsforskningen.</span><br><br>
<p class="help_text">- Årsdags liste<br>
<span class="help_explanation">Dette åbner en liste over alle personer i den valgte anetavle, der har fødselsdag i det aktuelle måned. Du kan også vælge et andet måned end det nuværende.</span><br><br>
<p class="help_text">- Statistik<br>
<span class="help_explanation">Oplysninger i statistiktabellen garanterer ikke yderligere forklaring.</span><br><br>
<p class="help_text">- Relations beregner<br>
<span class="help_explanation">Med Relations beregner kan du oprette blod og/eller ægteskabelig relationer mellem to personer. I søge feltet "Fornavn" og "Efternavn" kan du indtaste navne, del af navn eller lade et felt være tomt. Derefter kan du klikke på "søg" og dermed vælge et navn fra resultatlisten. Når først to navne er valgt, kan du klikke "Beregn relation" og hvis et forhold er fundet, vil det blive vist sammen med en grafisk gengivelse. Du kan trykke ændrings symbolet for at skifte mellem personer.</span><br><br>
<p class="help_text">- Google kort<br>
<span class="help_explanation">Dette vil vise et Google kort relateret til personerne tilstede i databasen med mulighed for kortlægning af fødsel og død. Vejledning i brugen af disse Google korts muligheder kan findes <a href="http://humogen.com/index.php?option=com_wrapper&view=wrapper&Itemid=58" target="_blank">here</a> i online manualen under "Advanced Options"</span><br><br>
<p class="help_text">- Kontakt<br>
<span class="help_explanation">Dette vil åbne en email formular magen til den beskrevet ovenfor (se under “Stamtræets ejer”).</span><br>
<p class="help_text">- Seneste ændringer<br>
<span class="help_explanation">Dette vil vise en liste over nye og nyligt ændrede personer i databasen. En fuld rulleliste vises i omvendt kronologisk dato rækkefølge, med de seneste emner, der vises først.
Der er et søgefelt, der giver dig mulighed for at indsnævre listen over resultater ned. Det accepterer delvise navne, f.eks vil søgning efter “Sa” returnere alle personer med SA i deres navn, såsom Sam, Sarah, Susanne, eller selv personer med efternavnet Samson for eksempel.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Hoved menu knappen "Flag"</p><br>
<span class="help_explanation">På menulinjen, til højre for menu knapperne, vil du bemærke en række nationale flag, der tillader dig at ændre det viste sprog.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Hoved menu knappen "Fotobog"</p>
<p class="help_text">Bemærk: Denne knap vises kun hvis aktiveret af webstedets ejer.<br><br>
<span class="help_explanation">Her vil du se en udstilling af alle fotos i databasen.<br>Klik på et billed for en udvidet version eller klik på navnet for at gå til denne persons familie side.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Hoved menu knappen "Login"</p><br>
<span class="help_explanation">Hvis webstedets ejer gav dig et brugernavn og password, kan du logge ind her og se data, der ikke er vist til offentligheden (f.eks oplysninger om levende personer eller "skjulte" anetavler).<br>
</p>';
echo '</div>';
?>
