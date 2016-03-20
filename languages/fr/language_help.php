<?php
echo '<div class="help_box">';
echo '
<p class="help_header">Icônes</p><br>
<p class="help_text">- Signification :<br>
<span class="help_explanation"> Vous verrez l\'icône <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports"> à gauche des noms des personnes, dans les listes, les arbres ou les fiches. Lorsque le curseur de la souris passe au-dessus de cette icône, une fenêtre contextuelle s\'affichera. Dans la liste, vous trouverez plusieurs icônes avec les noms de fiches, de listes et d\'arbres généalogiques que vous pouvez créer à partir de cette personne, le nombre exact d\'icônes sur la liste varie selon la présence d\'ascendants ou de descendants. Voici la liste de ces icônes et leur signification.</span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="liste d\'ascendance">&nbsp;<b>Liste d\'ascendance </b> : Une liste d\'ascendance est une liste généalogique des ascendants d\'une personne. Une liste d\'ascendance utilise une méthode spéciale de numérotation : le numéro de la personne de base est 1 et le numéro de son père est 2, le numéro de sa mère est 3. Le numéro d\'un père est toujours deux fois celui de son fils. Le numéro de la mère est celui du père plus un. Ainsi, le numéro 40 est le père du numéro 20 et 41 est la mère du numéro 20.<br>Parmi les icônes du menu contextuel vous pouvez également choisir un affichage des ascendants sous la forme d\'un arbre.<br>Vous pouvez en savoir plus sur les listes ou les arbres d\'ascendance <a href="https://fr.wikipedia.org/wiki/Portail:Généalogie" target="blank"><b>ici</b></a>, <a href="https://fr.wikipedia.org/wiki/Catégorie:Technique_généalogique" target="blank"><b>ici</b></a>, <a href="https://fr.wikipedia.org/wiki/Numérotation_de_Sosa-Stradonitz" target="blank"><b>ici</b></a> et <a href="https://fr.wikipedia.org/wiki/Liste_d\'ascendance" target="blank"><b>ici.</b></a></span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="liste de descendance">&nbsp;<b>Liste de descendance</b> : Une liste de descendance est une liste généalogique des descendants d\'un couple patriarcal ou d\'un patriarche, génération I, avec leurs enfants, génération II, et tous leurs descendants le long des lignées masculines et feminines.<br>Parmi les icônes du menu contextuel vous pouvez également choisir un affichage des descendants sous la forme d\'un arbre.</span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="liste de descendance simple">&nbsp;<b>Liste de descendance simple</b> : Une liste de descendance simple est un résumé de tous les descendants d\'une personne et de leurs partenaires, où chaque génération, et non chaque personne, a un numéro. </span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="fiche d\'ascendance">&nbsp;<b>Fiche d\'ascendance</b> : Une fiche d\'ascendance énumère 5 générations dans une présentation tabulaire, avec la personne de base en bas et les ascendants au-dessus de lui/elle dans des cadres de plus en plus petits.</span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="éventail d\'ascendance">&nbsp;<b>Éventail d\'ascendance</b> : Un éventail d\'ascendance est un graphique circulaire qui montre les ascendants dans les cercles autour de la personne de base. Cela permet une vision très claire de l\'ascendance d\'une personne spécifique. Le cadre de chaque personne sur la carte est cliquable pour permettre un accès rapide à la fiche de famille de cette personne. <br> La taille de l\'éventail et d\'autres paramètres sont réglables dans le menu à gauche du graphique. </span><br><br>

<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/timeline.gif" alt="chronologie">&nbsp;<b>Chronologie</b> : La chronologie affiche des événements historiques à côté des événements de la vie d\'une personne pour donner le contexte de l\'époque dans laquelle la personne a vécu.<br> Ce tableau a sa propre aide dédiée que vous pouvez consulter en pointant le curseur sur le mot « Aide » à gauche de l\'écran.</span><br><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">Barre d\'outils supérieure</p><br>
<p class="help_text">- «Recherche» boîte et bouton<br>
<span class="help_explanation">Cette boîte apparaît sur ​​toutes les pages de HuMo-gen pour vous permettre de rechercher des personnes de la base de données à partir de n\'importe quelle page. En inscrivant un nom et en cliquant sur «Recherche», une liste de personnes provenant de la base de données portant un nom correspondant au terme de la recherche sera affichée.</span><br><br>

- «Choisir un thème» liste déroulante<br>
<span class="help_explanation">HuMo-gen est fourni avec plusieurs combinaisons de couleurs par défaut, et si l\'administrateur du site le permet, ils apparaîtront dans la liste déroulante. Vous pouvez choisir un thème de votre choix, qui va changer des éléments tels que la couleur de la page, les images de fond, etc Ces thèmes sont spécifiques à HuMo-gen et ne changeront rien à votre navigateur Internet ou votre ordinateur.  </span><br><br>

- Les boutons A+ A- <br>
<span class="help_explanation">Ces contrôles vous permettent de contrôler la taille du texte à l\'écran lors de l\'utilisation de HuMo-gen. Ces contrôles sont spécifiques à HuMo-gen et changeront rien à votre navigateur Internet ou à votre ordinateur. Note: La réinitialisation est seulement visible sur les navigateurs qui prennent en charge cette fonction. </span><br><br>

- Icône orange RSS, s\'affiche uniquement si elle est activée par le propriétaire du site<br>
<span class="help_explanation">Si vous ajoutez ce flux à votre lecteur RSS, vous verrez d\'un seul coup d\'œil les anniversaires du jour! <br>Dans le menu déroulant «Outils», vous pouvez voir l\'option "Liste d\'anniversaire". Cette option permet d\'afficher la liste des anniversaires du mois courant.</span><br>

</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">Menu Principal, menu «Accueil»</p><br><br>
<span class="help_explanation">Ce menu donne l\'index principal des personnes.
Certaines parties de cette page nécessitent des explications :</span><br>

<p class="help_text">- Propriétaire de la base de données<br>
<span class="help_explanation">Le fait de cliquer sur le nom du propriétaire du site ouvre un formulaire électronique qui vous permet de lui envoyer un message. Veuillez saisir votre nom et votre adresse e-mail, afin qu\'il puisse vous répondre. Si vous souhaitez envoyer une pièce jointe, comme une photo ou un document, au propriétaire du site, vous pouvez utiliser ce formulaire pour lui demander son e-mail. Ensuite, vous pourrez utiliser n\'importe quel logiciel de messagerie pour envoyer ces pièces jointes. L\'adresse e-mail du propriétaire du site n\'est pas publié sur le site afin de prévenir le spam.</span><br><br>

<p class="help_text">- Champs de recherche<br>
<span class="help_explanation">Dans les champs de recherche, vous pouvez effectuer une recherche par prénom et/ou nom. Vous pouvez également choisir parmi trois options: «contient», «égale» et «commence par».<br>Note: à côté du bouton de recherche il y a une option pour «Recherche avancée»</span><br><br>

- Plus<br>
<span class="help_explanation">Les quelques lignes qui suivent sont évidentes : cliquez sur le lien pour l\'ouvrir.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">Menu Principal, menu «Arbre généalogique»</p><br>
<p class="help_text">- Index de l\'arbre généalogique<br>

<span class="help_explanation">Ceci vous amène à l\'index principal des personnes, voir ci-dessus.</span><br><br>

<p class="help_text">- Personnes<br>

<span class="help_explanation">Ceci vous emmène à l\'index principal des personnes. Identique à la touche «Accueil».
La liste affiche toutes les personnes de l\'arbre généalogique, triées par ordre alphabétique. Un nombre maximum de 150 personnes sont affiché. Si l\'affichage se fait sur plusieurs pages, vous pouvez cliquer sur les numéros de page pour voir les suivantes. Vous pouvez choisir entre «Vue simple» ou «Vue détaillée». Dans la vue détaillée, les (ex) partenaires sont également affichés, alors qu\'ils ne le sont pas dans la vue simple.</span><br><br>

<p class="help_text">- Noms<br>

<span class="help_explanation">Vous trouverez ici une liste de tous les noms de famille, suivi par le nombre de personnes qui portent ce nom.</span><br><br>

<p class="help_text">- Lieux, ces menus ne s\'affichent que s\'ils sont activés par le propriétaire du site<br>

<span class="help_explanation">Vous pouvez rechercher ici par lieu de naissance, de baptême, par adresse, lieu de décès ou de sépulture.
Vous pouvez effectuer une recherche avec les options: "Contient", "Égale" et "Commence par".
Ici aussi, vous pouvez choisir entre une vue détaillée ou simple.
Les résultats seront triés par ordre alphabétique par nom de lieu.</span><br><br>

<p class="help_text">- Sources, ce menu ne s\'affiche que s\'il est activé par le propriétaire du site<br>
<span class="help_explanation">Vous trouverez ici une liste de toutes les sources utilisées dans la recherche généalogique.</span><br><br>

</p>';
echo '</div>';



echo '<p><div class="help_box">';
echo '<p class="help_header">Menu Principal, menu «Outils»</p><br><br>

<span class="help_explanation">Plusieurs sous-menus apparaissent dans le menu Outils :</span><br>


<p class="help_text">- Liste d\'anniversaires<br>
<span class="help_explanation">Ceci ouvre une liste de toutes les personnes dans l\'arbre sélectionné qui ont leur anniversaire dans le mois en cours. Vous pouvez également choisir un mois différent du mois actuel.</span><br><br>

<p class="help_text">- Statistiques<br>
<span class="help_explanation">L\'information donnée dans le tableau des statistiques ne nécessite pas de plus amples explications.</span><br><br>

<p class="help_text">- Calcul de parenté<br>
<span class="help_explanation">Avec la calculatrice de parenté que vous pouvez établir la parenté de sang et/ou la parenté conjugale entre deux personnes. Dans les champs de recherche «Prénom» et «Nom» vous pouvez entrer des noms, une partie des noms ou laisser le champ vide. Ensuite, vous pouvez cliquer sur «Recherche» et choisir un nom de la liste de résultats. Une fois deux noms sélectionnés, vous pouvez cliquer sur «Calcul de parenté» et si une parenté est trouvée, elle sera affichée avec une représentation graphique. Vous pouvez appuyer sur le symbole de changement pour basculer entre les personnes.</span><br><br>

<p class="help_text">- Google maps<br>
<span class="help_explanation">Vous pouvez afficher une carte Google concernant les personnes présentes dans la base de données, avec possibilité de cartographier par naissances ou décès. Les instructions sur l\'utilisation des fonctionnalités Google maps peuvent être trouvées ici <a href="http://www.humo-gen.com/genwiki/index.php?title=Google_Maps" target="_blank"><b>Google Maps dans Humo-gen</b></a> dans le manuel en ligne.</span><br><br>


<p class="help_text">- Contact<br>
<span class="help_explanation">Ceci ouvrira un formulaire électronique, semblable à celui expliqué ci-dessus, voir «Propriétaire de l\'arbre généalogique».</span><br>

<p class="help_text">- Changement récents<br>
<span class="help_explanation">Vous pouvez afficher une liste des nouvelles personnes et des personnes récemment modifiées dans la base de données. Une liste déroulante s\'affiche par ordre chronologique inverse, les éléments les plus récents affichés en premier.
Il y a un champ de recherche qui vous permet de réduire la liste des résultats. Elle accepte des noms partiels, par exemple, la recherche de "Sa" renverra toutes les personnes avec SA en leur nom, comme Sam, Sarah, Susanne, ou encore les personnes ayant le nom Samson.</span><br>
</p>';
echo '</div>';


echo '<p><div class="help_box">';
echo '<p class="help_header">Menu Principal, menu «Drapeaux de langue»</p><br><br>
<span class="help_explanation">Dans la barre de menu, à droite des boutons de menu, vous remarquerez plusieurs drapeaux nationaux, ils vous permettent de changer la langue d\'affichage.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menu Principal, menu «Livre photo«</p><br>
<p class="help_explanation">Remarque: ce menu s\'affiche uniquement s\'il est activé par le propriétaire du site<br><br>
<span class="help_explanation">Vous verrez ici un affichage de toutes les photos de l\'arbre généalogique. <br> Cliquez sur une photo pour l\'agrandir ou cliquez sur le nom pour accéder à la page de famille de cette personne.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menu Principal, bouton «Connexion»</p><br><br>
<span class="help_explanation">Si le propriétaire du site vous a donné un nom d\'utilisateur et un mot de passe, vous pouvez vous connecter ici pour voir les données qui sont privées, telles que les coordonnées des personnes vivantes ou des arbres généalogiques «cachés».
.<br>
</p>';
echo '</div>';
?>
