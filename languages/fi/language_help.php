<?php
echo '<div class="help_box">';
echo '
<p class="help_header">Icons</p>
<p class="help_text">- Kuvaus: <br>
<span class="help_explanation">Henkilölistoissa ja raporteissa näet henkilöiden nimien vasemmalla puolella kuvakkeen <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports">. Kun viet kursorin tämän kuvakkeen päälle, avautuu pop-up ikkuna. Ikkunassa on kuvakkeita sekä tästä henkilöstä tulostettavissa olevien kaavioiden ja raporttien nimet. Kuvakkeiden määrä vaihtelee sen mukaan, onko henkilöllä esipolvia ja/tai jälkipolvia. Seuraavassa on luettelo kuvakkeista ja niiden tarkoituksesta.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>Sukutaulu (Perigree, Esipolviraportti/-kaavio)</b>: Sukutaulu on yhden henkilön (lähtöhenkilö) esipolvet kuvaava taulu. Sukutaulussa on määrätty numerointitapa: Lähtöhenkilö on numero 1, hänen isänsä numero 2 ja äitinsä numero 3. Isän numero on aina kaksinkertainen hänen lapseensa verrattuna ja äidin numero yhtä suurempi. Esimerkiksi numero 40 on numeron 20 isä ja numero 41 on numeron 20 äiti.<br>Kuvakkeista löytyy myös sukutaulun graafinen esitys.<br>Voit lukea lisää sukutauluista <a href="http://en.wikipedia.org/wiki/Pedigree_chart" target="blank"><b>täältä</b></a> ja <a href="http://en.wikipedia.org/wiki/Ahnentafel" target="blank"><b>täältä.</b></a></span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="Parenteel">&nbsp;<b>Jälkipolviraportti/-kaavio (Descendant Report/Chart)</b>: Jälkipolvikaavio esittää jälkipolvet alkaen vanhemmista tai vanhemmasta (sukupolvi I) ja heidän lapsistaan (sukupolvi II) ja kaikki edelleen kaikki jälkeläiset, mies- ja naislinjat. <br>Kuvakkeista voi valita myös jälkipolvien graafisen esityksen.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp;<b>Jälkeläisraportti</b>: Jälkeläisraportti on yhteenveto yhden henkilön (ja hänen puolisoidensa/kumppaneidensa) jälkeläisestä. Jokainen sukupolvi saa oman nousevan järjestysnumeron.  </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b>Esipolvitaulu</b>: Esipolvitaulu kuvaa 5 sukupolvea taulukkomuodossa. Lähtöhenkilö on alinna ja esipolvet hänen yläpuolellaan kapenevissa kehyksissä. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>Sektorikaavio</b>: Sektorikaavio on ympyrämäinen kuvio, joka esittää esipolvet lähtöhenkilön ympärillä. Näin saadaan selkeämpi esitys esipolvista. Kaaviossa esitetyistä henkilöistä voi klikkaamalla avata kyseisen henkilön perhetaulun. <br> Sektorikaavion koko ja joitakin muita asetuksia voi säätää valikosta joka on kaavion vasemmalla puolella. </span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">Päävalikko</p>
<p class="help_text">- Päävalikon kohdat:<br>
- Painikkeet A+ A- Reset<br>
<span class="help_explanation">Näillä painikkeilla voit säätää kirjasinkokoa useimmilla sivuille. Klikkaamalla Reset voit nopeasti palata takaisin alkuperäisiin asetuksiin. Huomioi, että kaikki selaimet eivät välttämättä tue tätä toimintoa ja silloin painikkeet eivät ole näkyvissä. </span><br><br>
- Oranssi RSS-kuvake (näkyy vain, jos ylläpitäjä on sen aktivoinut)<br>
<span class="help_explanation">Jos lisäät tämän syötteen RSS-lukijaasi, voit yhdellä vilkaisulla nähdä kenellä on syntymäpäivä!<br>(Valikossa "Työkalut" on alasvetovalikossa kohta "Merkkipäivälista". Tästä voit katsoa kuluvan kuukauden syntymäpäivät.</span><br>
<p class="help_text">- Kielet<br>
<span class="help_explanation">Menuvalikossa näkyy muutamia valtioiden lippuja. Niitä klikkaamalla voita vaihtaa näytön kielen.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">Etusivun kohta "Päävalikko"</p>
<p class="help_text">- Sukupuun omistaja<br>
<span class="help_explanation">Klikkaamalla sukupuun omistajan nimeä, avautuu sähköpostilomake, jolla voit lähettää sukupuun omistajalle lyhyen viestin. Muista kirjoittaa nimesi ja sähköpostisoitteesi, jotta sinulle voidaan vastata. Jos haluat lähettää liitetiedostoja (esimerkiksi kuvia tai muita dokumentteja), kysy ensin tällä lomakkeella sivun omistajan sähköpostiosoite. Siihen osoitteeseen voit sitten lähettää liitetiedostoja. Sähköpostisosoitetta ei julkaista tässä roskapostin välttämiseksi.</span><br><br>
- Etsintäkentät<br>
<span class="help_explanation">Hakukenttien avulla voit etsiä henkilöitä etu- ja/tai sukunimen avulla. Voit myös käyttää jotain optioista "Sisältää", "Täsmälleen" tai "Alkaa". Huomaa myös hakupainikkeen vieressä oleva optio &quot;Laajennettu haku!&quot;</span><br><br>
- Muut<br>
<span class="help_explanation">Seuraavat muutamat rivit eivät kaipaa tarkempaa esittelyä. Klikkaa vain linkkiä, johon haluat siirtyä.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Valintapainike "Henkilöt"</p>
<p class="help_text">- Tästä näet listan kaikista henkilöistä<br>
<span class="help_explanation">Listassa on kaikki sukupuun henkilöt aakkosjärjestyksessä. Yhdellä sivulla on korkeintaan 150 henkilöä. Klikkaa sivunumeroa siirtyäksesi muille sivuille. </span><br><br>
- Suppea ja Laajennettu näkymä<br>
<span class="help_explanation">Voit vaihdella näkymien &quot;Suppea näkymä&quot; ja &quot;Laajennettu näkymä &quot; välillä. Laajennetyssa näkymässä esitetään myös kunkin henkilön puolisot ja muut kumppanit.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Valintapainike "Nimet"</p>
<p class="help_text">- Kuvaus:<br>
<span class="help_explanation">Tästä löydät luettelon kaikista sukunimistä ja niiden jälkeen sukunimen haltijoiden lukumäärän. Luettelossa ei huomioida henkilöiden myöhempiä sukunimiä.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Valintapainike "Paikat"</p>
<p class="help_text">- Kuvaus: (tämä painike näkyy vain jo sivuston ylläpitäjä on sen aktivoinut)<br>
<span class="help_explanation">Tästä voit etsiä henkilöitä syntymäpaikan, asuinpaikan, kuolinpaikan ja hautauspaikan mukaan. <br>
Haussa voit käyttöö optioita: "Sisältää", "Täsmälleen" tai "Alkaa". <br>
Näytöksi voit valita suppean tai laajennetun. Tulokset esitetään paikan nimen mukaisesti aakkosjärjestyksessä.</span><br>
</p>';
echo '</div>';
echo '<p><div class="help_box">';
echo '<p class="help_header">Valintapainike "Työkalut"</p>
<p class="help_text">Työkaluvalikko:<br>
<p class="help_text">- Lähteet: (näkyy vain jo sivuston ylläpitäjä on tämän aktivoinut)<br>
<span class="help_explanation">Tässä on luettelo kaikista sukupuun tulosten yhteyteen merkitystä lähteistä.</span><br>
<p class="help_text">- Merkkipäivälista:<br>
<span class="help_explanation">Merkkipäivälista on luettelo henkilöistä, joilla on syntymäpäivä kuluvan kuukauden aikana. Voit myös vaihtaa kuukauden jolla haet henkilöitä.</span><br>
<p class="help_text">- Tilastot:<br>
<span class="help_explanation">Tilastosivulla on tilastotietoa sukupuusta.</span><br>
<p class="help_text">- Sukulaisuuden laskuri:<br>
<span class="help_explanation">Sukulaisuuden laskurin avulla voit selvittää kahden valitsemasi henkilön verisukulaisuuden tai sukulaisuuden avioliiton kautta. Hakukenttiin "Etunimi" ja "Sukunimi" voit kirjoittaa nimen, sen osan tai jättää kentät tyhjäksi. Klikkaamalla "haku" saat listan ehdon täyttävistä nimistä ja nimeä klikkaamalla voit poimia nimen hakuun. Kun kaksi nimeä on haettu, voit klikata "Laske sukulaisuus". Laskuri selvittää sukulaisuuden ja esittää sen sanallisesti ja kaaviomuodossa. Vaihtokuvakkeella voit vaihtaa henkilöiden paikkaa keskenään.</span><br>
<p class="help_text">- Yhteystiedot:<br>
<span class="help_explanation">Tästä avautuu sähköpostilomake, josta voit lähettää viestin. (Katso kuvaus kohdasta "Päävalikko -> Sivuston omistaja").</span><br>
</p>';
echo '</div>';
echo '<p><div class="help_box">';
echo '<p class="help_header">Valintapainike "Kuvakirja"</p>
<p class="help_text">- Kuvaus: (Tämä painike näkyy vain, jos sivuston ylläpitäjä on sen aktivoinut)<br>
<span class="help_explanation">Tässä näet luettelon kaikista tietokannassa olevista valokuvista.<br>Klikkaamalla kuvaa se avautuu täysikokoisena tai klikkaamalla henkilön nimeä avautuu henkilön perhesivu.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Valintapainike "Kirjaudu"</p>
<p class="help_text">- Kuvaus:<br>
<span class="help_explanation">Jos ylläpitäjä on antanut sinulle käyttäjätunnuksen ja salasanan, voit kirjautua ja saada tiedot näkyviin tunnuksellesi sallitun mukaisesti.<br>
</p>';
echo '</div>';
?>
