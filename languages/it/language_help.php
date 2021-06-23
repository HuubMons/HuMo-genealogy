<?php
echo '<div class="help_box">';
echo '<br><br>
<p class="help_header">Icone</p>
<p class="help_text">- Spiegazione: <br>
<span class="help_explanation">A sinistra dei nomi delle persone nelle liste o nei rapporti, vedrai l\'icona <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports">. Quando passi su questa icona con il mouse, verrà visualizzata una finestrella nella quale sono elencate diverse icone con i nomi dei report e dei grafici che è possibile creare da questa persona (il numero esatto di icone nell\'elenco varia in base alla presenza di antenati e / o discendenti). Di seguito è riportato l\'elenco di queste icone e il loro significato.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>Report  degli antenati (albero genealogico)</b>: Il Report  degli Antenati è un rapporto genealogico degli antenati di una persona,  utilizzando un metodo speciale di numerazione: la persona di base è il numero 1, il padre numero 2 e sua madre numero 3. Il numero di un padre è due volte maggiore di quello di suo figlio e la madre è il numero successivo. Quindi, il numero 40 è il padre del numero 20 e 41 è la madre del numero 20. <br> Tra le icone nel menù a comparsa puoi anche scegliere una visualizzazione grafica del rapporto antenato. <br> Puoi leggere di più sul Report  degli Antenati <a href="http://en.wikipedia.org/wiki/Pedigree_chart" target="blank"><b>qui</b></a> e <a href="http://en.wikipedia.org/wiki/Ahnentafel" target="blank"><b>qui.</b></a></span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="Parenteel">&nbsp;<b>Report dei discendenti</b>: Un report dei discendenti è un rapporto genealogico di una coppia patriarcale o di un patriarca (Prima Generazione) con i loro figli (Seconda Generazione) e tutti gli altri discendenti, sia lungo le linee maschili che femminili. <br> Tra le icone nel menù a comparsa puoi anche scegliere una visualizzazione grafica del rapporto discendente</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp;<b>Report descrittivo</b>: Un report descrittivo è un dettagliato elenco di tutti i discendenti di una persona (e dei suoi partner), in cui ogni generazione riceve il proprio numero (crescente). </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b>Scheda degli antenati</b>: Un foglio degli antenati elenca 5 generazioni racchiuse in tabelle, con la persona di base nella parte inferiore e gli antenati sopra di lui / lei in boxes sempre più piccoli. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>Grafico a Ventaglio</b>: Un Ventaglio è un grafico circolare che mostra gli antenati nei circoli attorno alla persona di base. Ciò consente una visione molto chiara della discendenza di una persona specifica. La casella per ogni persona sul grafico è selezionabile per consentire l\'accesso rapido alla scheda della famiglia di quella persona. <br> Le dimensioni del Ventaglio e alcune altre impostazioni possono essere regolate dal menù a sinistra del grafico. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/timeline.gif" alt="Timeline chart">&nbsp;<b>Grafico Cronologico</b>: Il grafico cronologico mostra gli eventi storici insieme agli eventi della vita di una persona per fornire un contesto del tempo in cui la persona ha vissuto. <br> Questo grafico ha il suo aiuto dedicato che puoi visualizzare posizionando il cursore sopra la parola "Aiuto "a sinistra dello schermo.</span>
</p><br><br>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<br><br>
<p class="help_header">Top Ribbon / Impostazioni di Base</p>
<p class="help_text">- Campo e pulsante “Cerca”<br>
<span class="help_explanation">Questa finestra appare su tutte le pagine di HuMo-gen e ti consente di cercare qualsiasi persona dal database da qualsiasi pagina. Digitando un nome e facendo clic su Cerca verrà visualizzato un elenco di persone dal database con un nome corrispondente al termine di ricerca.</span><br><br>
- menù "Seleziona un tema":<br>
<span class="help_explanation">Per impostazione predefinita, HuMo-gen viene fornito con diversi schemi di colori, e fintanto che l\'amministratore del sito non li ha disabilitati, appariranno in questa tendina. In base alle tue preferenze, puoi selezionare un tema che cambierà alcuni elementi come il colore della pagina, gli sfondi, ecc. Il tema scelto influenzerà solo la tua ricerca su HuMo-gen e non cambierà nulla nel tuo browser o computer.</span><br><br>
- I pulsanti A+ A- Reset<br>
<span class="help_explanation">Questi controlli ti permettono di controllare le dimensioni del testo sullo schermo mentre usi HuMo-gen. Questi controlli influenzeranno solo la tua ricerca su HuMo-gen e non cambieranno nulla nel tuo browser o computer. (Nota: il tasto "Reset" è visibile solo sui browser che supportano questa funzione). </span><br><br>
- L\'icona RSS arancione (visualizzato solo se attivato dall\'amministratore del sito)<br>
<span class="help_explanation">Se aggiungi questo feed (come viene chiamato) al tuo RSS-Reader, sarai in grado di vedere immediatamente chi ha un compleanno!<br>(Nel menù "Strumenti" è possibile visualizzare la opzione "Elenco anniversari". Quella opzione mostrerà un elenco di compleanni nel mese corrente).</span>
</p><br><br>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<br><br>
<p class="help_header">Pulsante "Home" del menù principale</p><br>
<span class="help_explanation">Questo ti porta al Indice principale delle persone .
Alcuni pannelli su questa pagina che richiedono una spiegazione:</span><br>
<p class="help_text">- Albero genealogico del proprietario<br>
<span class="help_explanation">Facendo clic sul nome del proprietario verrà aperto un modulo di posta elettronica che consente di inviare al proprietario dell\'Albero una breve Nota. Inserisci il tuo nome e indirizzo e-mail, in modo da poter ricevere una risposta.</span><br><br>
<p class="help_text">- Search fields<br>
<span class="help_explanation">È possibile effettuare la ricerca per nome e / o cognome. Puoi anche scegliere tra tre opzioni: "contiene...", "è uguale a..." e "comincia con...". Nota: accanto al pulsante di ricerca vi è una opzione per "Ricerca Avanzata".</span><br><br>
- Inoltre:<br>
<span class="help_explanation">Le prossime righe sono ovvie: fai clic sul link che desideri.</span>
</p><br><br>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<br><br>
<p class="help_header">Pulsante "Albero Genealogico" del menù principale </p><br>
<p class="help_text">- Indice dell\'albero genealogico<br>
<span class="help_explanation">Questo ti porta all Indice principale (vedi sopra).</span><br><br>
<p class="help_text">- Persone<br>
<span class="help_explanation">Vai al indice principale delle persone. Come il pulsante "Home".
L\'elenco mostra tutte le persone nell\'albero genealogico, in ordine alfabetico. Viene visualizzato un numero massimo di 150 persone per pagina. Puoi premere i numeri di pagina per vedere di più. Puoi scegliere tra "Vista Concisa" o "Vista Espansa". Nella vista espansa vengono visualizzati anche i partner, che non sono mostrati nella visualizzazione concisa.</span><br><br>
<p class="help_text">- Nomi<br>
<span class="help_explanation">Qui trovi l\'elenco di tutti i cognomi, seguito dal numero di persone che portano quel cognome.</span><br><br>
<p class="help_text">- Luoghi (questo pulsante viene visualizzato solo se attivato dall\'amministratore del sito)<br>
<span class="help_explanation">Qui puoi cercare per luogo di nascita, battesimo, indirizzo, luogo di morte o sepoltura.
Puoi scegliere tra tre opzioni: "contiene...", "è uguale a..." e "comincia con...".
Anche in questo caso, puoi scegliere tra visualizzazione estesa o concisa. 
I risultati verranno ordinati alfabeticamente in base al nome del luogo.</span>
</p><br><br>';
echo '</div>';



echo '<p><div class="help_box">';
echo '<br><br>
<p class="help_header">Pulsante del menù "Strumenti"</p><br>
<span class="help_explanation">Diversi Sottomenù compaiono sotto il menù Strumenti:</span><br>
<p class="help_text">- Fonti: (visualizzate solo se attivate dal proprietario del sito)<br>
<span class="help_explanation">Qui troverai un elenco di tutte le fonti utilizzate in questa ricerca genealogica.</span><br><br>
<p class="help_text">- Lista degli Anniversari<br>
<span class="help_explanation">Questo apre una lista con tutte le persone dell\'albero selezionato che hanno un compleanno nel mese corrente. Puoi anche scegliere un mese diverso da quello attuale.</span><br><br>
<p class="help_text">- Statistiche<br>
<span class="help_explanation">Le informazioni fornite nella tabella delle statistiche non richiedono ulteriori spiegazioni.</span><br><br>
<p class="help_text">- Calcolo della Parentela<br>
<span class="help_explanation">Con il calcolatore delle parentele puoi stabilire le relazioni di sangue e / o coniugali tra due persone. Nei campi di ricercas "Nome" e "Cognome" puoi inserire il nome, parte del nome o lasciare un campo vuoto. Quindi puoi fare clic su "cerca" e poi scegliere un nome dalla lista dei risultati. Dopo aver selezionato due nomi, puoi fare clic su  "Calcola parentela" e se viene trovata una relazione verrà elencata insieme a una rappresentazione grafica. È possibile premere il simbolo di modifica per passare da una persona all\'altra.</span><br><br>
<p class="help_text">- Google maps<br>
<span class="help_explanation">Questo mostrerà una mappa di Google relativa alle persone presenti nel database, con possibilità di mappare per nascita o morte. È possibile trovare istruzioni sull\'utilizzo di queste funzioni della mappa di google <a href="http://humogen.com/index.php?option=com_wrapper&view=wrapper&Itemid=58" target="_blank">qui</a> nel manuale online, sotto "Advanced Options"</span><br><br>
<p class="help_text">- Contatto<br>
<span class="help_explanation">Verrà aperto un modulo email simile a quanto spiegato sopra (vedi "proprietario dell\'albero genealogico").</span><br>
<p class="help_text">- Ultime modifiche<br>
<span class="help_explanation">Questo mostrerà l\'elenco delle persone nuove o modificate di recente nel database. La <b>lista</b> viene visualizzato in ordine cronologico inverso, con le voci più recenti visualizzate per prime.
Un ulteriore campo di ricerca che ti consente di restringere la lista dei risultati. Accetta nomi parziali, ad es. cercando “Sa” si otterrà tutte le persone con SA nel loro nome, come Sandro, Sarah, Susanna, o anche persone con il cognome Sabbadini per esempio.</span>
</p><br><br>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<br><br>
<p class="help_header">menù "Bandiere delle lingue"</p><br>
<span class="help_explanation">Sulla barra dei menù, a destra dei pulsanti del menù, noterai diverse bandiere nazionali che ti permetteranno di cambiare la lingua del display.</span>
</p><br><br>';
echo '</div>';

echo '<p><div class="help_box">';                                                                                                                                                                                                         
echo '<br><br>
<p class="help_header">menù "Album Fotografico"</p>
<p class="help_text">Nota: questo pulsante viene visualizzato solo se attivato dal proprietario del sito<br><br>
<span class="help_explanation">Qui puoi vedere una galleria di tutte le foto nel database.<br>Clicca su una foto per ingrandirla o clicca sul nome per passare alla pagina di famiglia di quella persona.</span>
</p><br><br>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<br><br>
<p class="help_header">Bottone "Login" del menù principale</p><br>
<span class="help_explanation">Se l\'amministratore del sito ti ha fornito un nome utente e una password, puoi accedere da qui per vedere i dati che non vengono mostrati al pubblico (come i dettagli di persone viventi o alberi genealogici "nascosti").</span>
</p><br><br>';
echo '</div>';
?>
