<?php
echo '<div class="help_box">';
echo '
<p class="help_header">Ikonok</p>
<p class="help_text">- Magyarázat: <br>
<span class="help_explanation">A listákban vagy jelentésekben szereplő személyek neveinek baloldalán látni fogja ezt az ikont <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports">. Mikor az egerével a kurzort az ikon fölé viszi, egy felugró ablak fog megjelenni. A felugró listában több kis ikont fog látni különböző jelentések és diagramok neveivel, amelyeket létrehozhat az illető személyről. (az ikonok pontos száma változó, az ősök és / vagy leszármazottak meglétének függvényében). Egy lista következik ezekkel az ikonokkal és jelentéseikkel.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>Ősfa jelentés (Pedigré)</b>: A pedigré lap egy származástani jelentés  egy személy őseiről. A pedigré különleges számozási módszert alkalmaz: az alap személy az 1-es számot viseli, apja a 2-est és anyja a 3-ast. Az apa száma fia számának kétszerese és az anyáé ennél eggyel magasabb.  Ilyenformán  a 40-es szám a 20-as apja és a 41-es a 20-as anyja. <br>A felügró menü jelképei közül választhatja az ősfa jelentés grafikus ábrázolását is. <br>Pedigréről bővebben olvashat <a href="http://en.wikipedia.org/wiki/Pedigree_chart" target="blank"><b>itt</b></a> és <a href="http://en.wikipedia.org/wiki/Ahnentafel" target="blank"><b>itt.</b></a></span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="Parenteel">&nbsp;<b>Leszármazási jelentés / diagram </b>: A leszármazási jelentés egy család vagy egyetlen nemzetségfő (I. generáció), ezek gyermekeinek (II. generáció) és további leszármazottainak származástani jelentése, mind férfi- mind női ágon. <br>A felugró menü jelképei közül választhatja a leszármazási jelentés grafikus megjelenítését is. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp;<b>Vázlatos jelentés</b>: A vázlatos jelentés egy világos összefoglalása egy adott személy (és házastársai) összes leszármazottjának, ahol minden generáció saját (növekvő) számot kap. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b>Ősök lapja</b>: Az ősök lapja 5 generációt jelenít meg táblázatos formában, az alap személlyel legalul és fölötte, egyre csökkenő méretű  dobozokban az őseivel.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>Legyező diagram</b>: A legyező diagram egy kör alakú ábra amely az alap személy körüli körben jeleníti meg az ősöket. Ez lehetővé teszi egy adott személy őseinek letisztult áttekintését. Minden személy doboza az ábrán kattintható és így az adott személy családi lapjához enged gyors hozzáférést. <br> A legyező diagram mérete és néhány más beállítás személyre szabható a diagram baloldali menüjéből. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/timeline.gif" alt="Timeline chart">&nbsp;<b>Idővonal diagram</b>: Az idővonal diagram történelmi eseményeket jelenít meg az adott személy élettörténetének eseményei mellett, hogy rávilágítson arra a korra amelyben az illető személy élt.<br> Ennek a diagramnak saját súgója van, amelyet megtekinthet, ha az egérkurzort a képernyő baloldalán elhelyezett Súgó szó fölé viszi.</span><br><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">Felső menüszalag</p>
<p class="help_text">- “Keresés” mező és gomb<br>
<span class="help_explanation">Ez a doboz a HuMo-gen minden oldalán megjelenik és azt a kényelmet biztosítja önnek, hogy az adatbázis bármely személyére rákeressen, bármelyik oldalról. Ha beír egy nevet és a keresésre kattint, az összes olyan személy listáját kapja az adatbázisból, amely a keresőmezőbe beírt nevet viseli.</span><br><br>
- “Válasszon témát” legördülő menü:<br>
<span class="help_explanation">Alapértelmezetten, a HuMo-gen számos színsémával van ellátva, és amennyiben az adminisztrátor nem kapcsolta ki, egy legördülő menüben jelennek meg. Őn kiválaszthat egy kedvére való témát, amely olyan elemeket változtat meg, mint az oldal színe, háttérkép, stb. Ezek a témák csak a HuMo-gen oldalon való navigálás élményére vannak hatással és nem módosítják böngészőjét vagy számítógépét.</span><br><br>
- Az A+ A- visszaállítás gombok<br>
<span class="help_explanation">Ezek az eszközök megengedik a szöveg méretének állítását a képernyőn amíg a HuMo-gen-t használja. Ezek a gombok csak a HuMo-gen oldalon való navigálás élményére vannak hatással és nem módosítják böngészőjét vagy számítógépét. (Megjegyzés: A Visszaállítás csak azon böngészők esetében látható, amelyek támogatják ezt a funkciót). </span><br><br>
- A narancsszínű RSS ikon (csak akkor látszik, ha az oldal tulajdonosa ezt bekapcsolta)<br>
<span class="help_explanation">Ha hozzáadja ezt a hírfolyamot (ahogy ezt nevezik) az RSS olvasójához, képes lesz egy pillantással áttekinteni, hogy kinek van születésnapja!<br>(Az "Eszközök" legördülő menüben láthat egy "Évforduló lista" opciót. Ez az opció a jelenlegi hónap születésnapjait jeleníti meg.).</span><br>

</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">A Főmenü "Főoldal" gombja</p><br>
<span class="help_explanation">Ez a központi Személyek indexhez viszi önt.
Néhány vezérlő ezen az oldalon, amely magyarázatra szorul:</span><br>
<p class="help_text">- Tulajdonos családfája<br>
<span class="help_explanation">Az oldal tulajdonosának nevére kattintva egy e-mail űrlap jelenik meg amely lehetővé teszi, hogy rövid üzenetet küldjön az oldal tulajdonosának. Kérjük, írja be nevét és e-mail címét, hogy válaszolhassunk. Ha csatolmányt szeretne küldeni az oldal tulajdonosának (mint például fénykép vagy dokumentum), ezt az űrlapot használhatja arra, hogy elkéri az oldal tulajdonosának e-mail címét. Ezután bármely megszokott e-mail programmal elküldheti a csatolmányokat. (Az oldaltulajdonos e-mail címét nem közöljük a levélszemét kiszűrésének érdekében).</span><br><br>
<p class="help_text">- Keresőmezők<br>
<span class="help_explanation">A keresőmezőkben kereszt- és / vagy vezetéknévre kereshet. Ugyanakkor használhatja a három opció egyikét: "tartalmazza", "egyenlő" és "ezzel kezdődik". Megjegyzés: a keresőgomb mellett van egy &quot;Haladó keresés!&quot; opció</span><br><br>
- Több<br>
<span class="help_explanation">A következő néhány sor nyilvánvaló: kattintson a hiperhivatkozásra, ahova navigálni szeretne.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">A főmenü "Családfa" gombja</p><br>
<p class="help_text">- Családfa index<br>
<span class="help_explanation">Ez a központi Személyek indexhez vezeti önt (Lásd fentebb).</span><br><br>
<p class="help_text">- Személyek<br>
<span class="help_explanation">A központi személyi indexhez vezeti önt. Ugyanaz, mint a "Főoldal" gomb.
A lista a családfában szereplő összes személyt megjeleníti, betűrendbe szedve. Legtöbb 150 személyt jelenít meg. Az oldalszámokra kattinthat a továbbiak megtekintéséhez. Választhat a "Tömör nézet" és a "Kiterjesztett nézet" között. Kiterjesztett nézetben a (volt) házastársakat is megjeleníti, amelyek nem jelennek meg a tömör nézetben.</span><br><br>
<p class="help_text">- Nevek<br>
<span class="help_explanation">Itt az összes vezetéknév listáját találja, ezt követi az illető vezetéknév vislőinek száma. </span><br><br>
<p class="help_text">- Helységek (ez a gomb csak akkor látható, ha az oldal tulajdonosa ezt bekapcsolta)<br>
<span class="help_explanation">Itt születési-, keresztelési-, elhalálozási- vagy temetési hely illetve lakcím szerint kereshet. 
A "tartalmazza", "egyenlő" és "ezzel kezdődik" opciókkal kereshet. 
Itt is választhat tömör- illetve kiterjesztett nézet között. 
A találatokat helységnevek szerint, betűrenbe szedve jeleníti meg. 
</span><br><br>
</p>';
echo '</div>';



echo '<p><div class="help_box">';
echo '<p class="help_header">A főmenü "Eszközök" gombja</p><br>
<span class="help_explanation">Számos almenü jelenik meg az Eszközök menü alatt:</span><br>
<p class="help_text">- Források: (csak akkor látszik, ha az oldal tulajdonosa bekapcsolja)<br>
<span class="help_explanation">itt a genealógiai kutatásában használt összes forrását fogja megtalálni. </span><br><br>
<p class="help_text">- Évforduló lista<br>
<span class="help_explanation">Ez egy listát nyit meg a kiválasztott fában szereplő összes személlyel, akiknek a folyó hónapban születésnapja van. Kiválaszthat egy másik, a folyó hónaptól eltérő hónapot is. </span><br><br>
<p class="help_text">- Statisztika<br>
<span class="help_explanation">A statisztikai táblázatban megjelenő információ nem szorul további magyarázatra.</span><br><br>
<p class="help_text">- Rokonsági kapcsolat számoló<br>
<span class="help_explanation">A rokonsági kapcsolat számolóval megállapíthatja a két személy között fennálló vér- és/vagy házassági rokoni kapcsolatokat. A "Keresztnév" és "Vezetéknév" keresőmezőkbe beírhat  neveket, ezek egy részét vagy üresen is hagyhatja a mezőt. Majd kattintson a "Keresés"-re és válasszon nevet a találati listából. Ha a két nevet kiválasztotta, rákattinthat a "Rokoni kapcsolat számítása" gombra és ha kapcsolatot talál, megjeleníti azt egy grafikus ábrázolással együtt. Kattinthat a váltás jelképre a személyek közötti váltáshoz.</span><br><br>
<p class="help_text">- Google térkép<br>
<span class="help_explanation">Ez egy Google térképet fog megjeleníteni az adatbázisban szereplő személyekkel kapcsolatban, azal a lehetőséggel, hogy születések vagy elhalálozások szerint térképezzen. A Google térkép jellemzők használatára vonatkozó utasítások az <a href="http://humogen.com/index.php?option=com_wrapper&view=wrapper&Itemid=58" target="_blank">itt</a> elérhető online kézikönyvben, a "Haladó beállítások" alatt</span><br><br>
<p class="help_text">- Kapcsolat<br>
<span class="help_explanation">Ez egy e-mail űrlapot fog megnyitni, hasonlót a fentebb leírthoz (lásd “Tulajdonos családfája”).</span><br>
<p class="help_text">- Legutóbbi változások<br>
<span class="help_explanation">Ez egy listát fog megjelenítani az adatbázis új- és nemrégiben módosított személyeivel. Egy teljes, gördíthető lista fog megjelenni, fordított időrendi sorrendben, a legfrissebb elemeket legelől megjelenítve.
Van egy keresőmező, amelyik lehetővé teszi a találati lista szűkítését. Elfogad részleges nevet, pl. "Já" keresése minden személyt betölt, akinek a nevében szerepel a JÁ, mint például a János, Jánosi, Jákób, még akkor is, ha a betűcsoport a vezetéknévben szerepel</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header"> "Nyelvválasztó zászlók" menügomb</p><br>
<span class="help_explanation">A menüsávon, a menü gombjaitól jobbra, megfigyelhet több nemzeti zászlót, amelyek lehetővé teszik a megjelenítés nyelvének módosítását.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">"Fotóalbum" menügomb</p>
<p class="help_text">Megjegyzés: ez a gomb csak akkor látszik, ha az oldal tulajdonosa bekapcsolta<br><br>
<span class="help_explanation">Itt az adatbázisa összes fényképét láthatja. <br>Kattintson egy fényképre annak egy nagyított változatához, vagy a névre, hogy az illető személy családi oldalára ugorjon.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">"Bejelentkezés" menügomb</p><br>
<span class="help_explanation">Ha a lista tulajdonosától felhasználónevet és jelszót kapott, bejelentkezhet, hogy megtekinthesse azokat az adatokat, amelyek nem láthatók mindenki számára(mint például az élő személyek adatai vagy "rejtett" családfák).<br>
</p>';
echo '</div>';
?>
