<?php
echo '<div class="help_box">';
echo '
<p class="help_header">Icones</p>
<p class="help_text">- Signification:<br>
<span class="help_explanation"> Vous verrez l\'icône <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports"> à gauche des noms des personnes, dans des listes ou des rapports. Lorsque le curseur de la souris passe au-dessus de cette icône, une fenêtre contextuelle s\'affichera. Dans la liste, vous trouverez plusieurs icônes avec les noms de rapports et de graphiques que vous pouvez créer à partir de cette personne (le nombre exact d\'icônes sur la liste varie selon la présence d\'ancêtres ou de descendants). Voici la liste de ces icônes et leur signification.</span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>Rapport des ancêtres (Pédigree)</b> Un pedigree est un rapport généalogique des ancêtres d\'une personne. Un pédigree utilise une méthode spéciale de numérotation: la personne de base est le numéro 1 et le numéro de son père est 2, le numéro de sa mère est 3. Le numéro d\'un père est toujours deux fois celui de son fils et la mère est un chiffre plus élevé. Ainsi, le numéro 40 est le père du numéro 20 et 41 est la mère du numéro 20. <br> Parmi les icônes dans le menu contextuel vous pouvez également choisir un affichage graphique du rapport ancêtre.<br> Vous pouvez en savoir plus sur les pedigrees <a href="http://en.wikipedia.org/wiki/Pedigree_chart" target="blank"><b>here</b></a> et <a href="http://en.wikipedia.org/wiki/Ahnentafel" target="blank"><b>here.</b></a></span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="Parenteel">&nbsp;<b>Descendant Rapport/Graphique</b>: Un rapport des descendants est un rapport généalogique d\'un couple patriarcal ou d\'un patriarche (génération I) avec leurs enfants (génération II) et tous leurs descendants le long de lignes mâles et femelles.<br> Parmi les icônes dans le menu contextuel vous pouvez également choisir un affichage graphique du rapport des descendants </span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp;<b>Rapport sommaire</b>: Un rapport sommaire est un résumé clair de tous les descendants d\'une personne (et leurs partenaires), où chaque génération obtient son propre numéro (ascendant). </span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b>Rapport des Ancêtres</b>: Un rapport d\'ancêtre énumère 5 générations dans la présentation tabulaire, avec la personne de base au fond et les ancêtres au-dessus de lui/elle dans des zones de plus en plus petites.</span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>Éventail</b>: Un éventail est un graphique circulaire qui montre les ancêtres dans les cercles autour de la personne de base. Cela permet une vision très claire de l\'ascendance d\'une personne spécifique. La boîte de chaque personne sur la carte est cliquable pour permettre un accès rapide à page de famille de cette person\. <br> La taille de l\'éventail et certains autres paramètres peuvent être réglées dans le menu à gauche du graphique. </span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/timeline.gif" alt="Timeline chart">&nbsp;<b>Graphique chronologique</b>: La chronologie graphique affiche des événements historiques aux côtés des événements de la vie d\'une person pour donner un contexte de l\'époque dans laquelle la personne a vécu.<br> Ce tableau a sa propre aide dédié que vous pouvez consulter en pointant le curseur sur le mot « Help » à gauche de l\'écran.</span><br><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">Barre d\'outils supérieure</p>
<p class="help_text">- “Recherche” case et bouton<br>
<span class="help_explanation">Cette boîte apparaît sur ​​toutes les pages de HuMo-gen pour vous permettre de rechercher des personnes de la base de données à partir de n\'importe quelle page. En inscrivant un nom et en cliquant \"Recherche\" une liste de personnes provenant de la base de données portant un nom correspondant au terme de la recherche sera affiché.</span><br><br>

- “Choisir un thème” liste déroulante:<br>
<span class="help_explanation">HuMo-gen est fourni avec plusieurs combinaisons de couleurs par défaut, et tant que l\'administrateur du site le permet, ils apparaîtront dans la liste déroulante. Vous pouvez choisir un thème de votre choix, qui va changer des éléments tels que la couleur de la page, les images de fond, etc Ces thèmes se rapportent qu\'à votre expérience sur HuMo-gen et feront aucun changement à votre navigateur Internet ou votre ordinateur.  </span><br><br>

- Les boutons A+ A- <br>

<span class="help_explanation">Ces contrôles vous permettent de contrôler la taille du texte à l\'écran lors de l\'utilisation de HuMo-gen. Ces contrôles ne concerneront que votre expérience sur HuMo-gen et feront aucun changement à votre navigateur Internet ou à votre ordinateur. (Note: La réinitialisation est seulement visible sur les navigateurs qui prennent en charge cette fonction). </span><br><br>

- L\'icone orange RSS (s\'affiche uniquement si elle est activée par le propriétaire du site)<br>
<span class="help_explanation">If you add this feed (as it\'s called) to your RSS-reader, you will be able to see at one glance who has a birthday!<br>(In the "Tools" pull-down menu you may see an "Anniversary List" option. That option will display a list of birthdays in the present month)     Si vous ajoutez ce \"feed\" (c\'est appe; ainsi) à votre lecteur RSS, vous serez en mesure de voir d\"un coup d\'œil qui a un anniversaire! <br> (Dans le menu déroulant \"Outils\", vous pouvez voir une option «anniversaire de la liste". Cette option permet d\'afficher la liste des anniversaires dans le mois courant).</span><br>

</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">Menu Principal, bouton "Accuel"</p><br>
<span class="help_explanation">Ceci vous amène à l\'indexe principal des Personnes.
Certains panneaux sur cette page nécessitent une explication:</span><br>

<p class="help_text">- Propriétaire de la base de données<br>
<span class="help_explanation">Cliquer sur le nom du propriétaire du site ouvre un formulaire électronique qui vous permet de lui envoyer un préavis. Veuillez saisir votre nom et votre adresse courriel, afin qu\'on puisse vous répondre. Si vous souhaitez envoyer une pièce jointe (comme une photo ou un document) au propriétaire du site, vous pouvez utiliser ce formulaire pour demander au propriétaire du site pour son courriel. Ensuite, vous pourrez utiliser n\'importe quel programme de messagerie habituel pour envoyer ces pièces jointes. (L\'adresse courriel du propriétaire du site n\'est pas publié sur ce site afin de prévenir le spam)</span><br><br>

<p class="help_text">- Champs de recherche<br>
<span class="help_explanation">Dans les champs de recherche, vous pouvez effectuer une recherche par prénom et/ou nom. Vous pouvez également choisir parmi trois options: "contient", "égale" et "commence par". Note: à côté du bouton de recherche il ya une option pour "Recherche avancée"</span><br><br>

- Plus<br>
<span class="help_explanation">Les quelques lignes qui suivent sont évidentes: cliquez sur le lien auquel vous voulez vous déplacer.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">Menu Principal, bouton "Arbre généalogique"</p><br>
<p class="help_text">- Index de l\'arbre généalogique<br>

<span class="help_explanation">Ceci vous amène à l\'indexe principal des Personnes (voir ci-dessus).</span><br><br>

<p class="help_text">- Personnes<br>

<span class="help_explanation">Vous emmène au principal fichier des personnes. Identique à la touche «Accueil».
La liste affiche toutes les personnes dans l\'arbre généalogique, triée en ordre alphabétique. Un nombre maximum de 150 personnes sont affichées. Vous pouvez cliquer sur les numéros de page pour en voir plus. Vous pouvez choisir entre "vue concise» ou  «vue étendue». Dans la vue étendue, les (ex) partenaires sont également affichés, alors qu\'il ne les sont pas dans la vue concise.</span><br><br>

<p class="help_text">- Noms<br>

<span class="help_explanation">Vous trouverez ici une liste de tous les noms de famille, suivi par le nombre de personnes qui portent ce nom.</span><br><br>

<p class="help_text">- Lieux (ce bouton ne s\'affiche que si elle est activée par le propriétaire du site)<br>

<span class="help_explanation">Vous pouvez rechercher ici par lieu de naissance, de baptême, par adresse, lieu de décès ou de sépulture.
Vous pouvez effectuer une recherche avec les options: "contient", "égale" et "commence par".
Ici aussi, vous pouvez choisir entre une vue étendue ou concise.
Les résultats seront triés par ordre alphabétique par nom de lieu.</span><br><br>
</p>';
echo '</div>';



echo '<p><div class="help_box">';
echo '<p class="help_header">Menu Principal, bouton "Outils"</p><br>

<span class="help_explanation">Plusieurs sous-menus apparaissent dans le menu Outils:</span><br>

<p class="help_text">- Sources: (uniquement affichée si elle est activée par le propriétaire du site)<br>
<span class="help_explanation">Vous trouverez ici une liste de toutes les sources utilisées dans la recherche généalogique.</span><br><br>

<p class="help_text">- Liste d\'anniversaires<br>
<span class="help_explanation">Ceci ouvre une liste de toutes les personnes dans l\'arbre sélectionné qui ont leur anniversaire dans le mois en cours. Vous pouvez également choisir un mois différent du mois actuel.</span><br><br>

<p class="help_text">- Statistiques<br>
<span class="help_explanation">L\'information donnée dans le tableau des statistiques ne nécessite pas de plus amples explications.</span><br><br>

<p class="help_text">- Calculatrice de relation<br>
<span class="help_explanation">Avec la calculatrice de relation que vous pouvez établir la relation de sang et/ou la relation conjugale entre deux personnes. Dans les champs de recherche "Prénom" et "Nom" vous pouvez entrer des noms, une partie des noms ou laisser le champ vide. Ensuite, vous pouvez cliquer sur "Recherche" et par conséquent choisir un nom de la liste de résultats. Une fois que deux noms ont été sélectionnés, vous pouvez cliquer sur "Calcul de parenté» et si une relation est trouvé, elle sera affichée avec une représentation graphique. Vous pouvez appuyer sur le symbole de changement pour basculer entre les personnes.</span><br><br>

<p class="help_text">- Google maps<br>
<span class="help_explanation">Ceci permet d\'afficher une carte google concernant les personnes présentes dans la base de données, avec possibilité de cartographier par naissances ou décès. Les instructions sur l\'utilisation des fonctionnalités googlemap peuvent être trouvés ici <a href="http://humogen.com/index.php?option=com_wrapper&view=wrapper&Itemid=58" target="_blank"></a> dans le manuel en ligne, sous «Options avancées»</span><br><br>


<p class="help_text">- Contact<br>
<span class="help_explanation">Ceci ouvrira un formulaire électronique, semblable à celui expliquée ci-dessus (voir "Propriétaire de l\'arbre généalogique").</span><br>

<p class="help_text">- Changement récents<br>
<span class="help_explanation">Ceci permet d\'afficher une liste des nouvelles personnes et des personnes récemment changées dans la base de données. Une liste déroulante s\'affiche entièrement en ordre de date chronologique inversé, avec les éléments les plus récents affichés en premier.
Il y a un champ de recherche qui vous permet de réduire la liste des résultats. Elle accepte des noms partiels, par exemple, la recherche de "Sa" renverra toutes les personnes avec SA en leur nom, comme Sam, Sarah, Susanne, ou encore les personnes ayant le nom Samson.</span><br>
</p>';
echo '</div>';


echo '<p><div class="help_box">';
echo '<p class="help_header">Menu Principal, bouton "Drapeaux de langue"</p><br>
<span class="help_explanation">Dans la barre de menu, à droite des boutons de menu, vous remarquerez plusieurs drapeaux nationaux, qui vous permettent de changer la langue d\'affichage.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menu Bouton "Livre photo"</p>
<p class="help_text">Remarque: ce bouton s\'affiche uniquement s\'il est activée par le propriétaire du site<br><br>
<span class="help_explanation">Vous verrez ici un affichage de toutes les photos dans la base de données. <br> Cliquez sur une photo pour l\'agrandir ou cliquez sur le nom pour accéder à la page de la famille de cette personne.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menu bouton "S\'identifier"</p><br>
<span class="help_explanation">Si le propriétaire du site vous a donné un nom d\'utilisateur et un mot de passe, vous pouvez vous identifier ici pour voir les données qui ne sont pas présenté au grand public (tels que les coordonnées des personnes vivantes ou des arbres de la famille "cachés")
.<br>
</p>';
echo '</div>';
?>
