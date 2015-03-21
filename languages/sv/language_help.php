<?php
echo '<div class="help_box">';
echo '
<p class="help_header">Icons</p>
<p class="help_text">- Explanation: <br>
<span class="help_explanation">To the left of names of persons in lists or reports, you will see the icon <img src="'.CMS_ROOTPATH.'images/reports.gif" alt="Reports">. When you hover over this icon with your mouse, a popup will be displayed. In the popup list you will find several icons with names of reports and charts that you can create from this person (the exact number of icons on the list varies according to the presence of ancestors and/or descendants). Following is a list of those icons and their meaning.</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_report.gif" alt="Pedigree">&nbsp;<b>Ancestor Report (Pedigree)</b>: A pedigree is a genealogical report of a person\'s ancestors. A pedigree uses a special method of numbering: the base person is number 1, his father number 2 and his mother number 3. The number of a father is ways twice that of his son and the mother is one number higher. Thus, number 40 is the father of number 20 and 41 is the mother of number 20. <br>From amongst the icons in the popup menu you can also choose a graphical display of the ancestor report.<br>You can read more about pedigrees <a href="http://en.wikipedia.org/wiki/Pedigree_chart" target="blank"><b>here</b></a> and <a href="http://en.wikipedia.org/wiki/Ahnentafel" target="blank"><b>here.</b></a></span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/descendant.gif" alt="Parenteel">&nbsp;<b>Descendant Report/Chart</b>: A descendant report is a genealogical report of a patriarchal couple or of one patriarch (generation I) with their children (generation II) and all further descendants, both along male and female lines. <br>From amongst the icons in the popup menu you can also choose a graphical display of the descendant report</span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/outline.gif" alt="Outline report">&nbsp;<b>Outline Report</b>: An outline report is a clear summary of all descendants of one person (and his/her partners), where each generation gets its own (ascending) number. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b>Ancestor Sheet</b>: An ancestor sheet lists 5 generations in table layout, with the base person at the bottom and the ancestors above him/her in increasingly smaller boxes. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/fanchart.gif" alt="Fanchart">&nbsp;<b>Fanchart</b>: A fanchart is a circular chart that shows the ancestors in circles around the base person. This allows for a very clear view of the ancestry of any specific person. The box for each person on the chart is clickable to enable fast access to that person\'s family sheet. <br> The size of the fanchart and some other settings may be adjusted from the menu to the left of the chart. </span><br><br>
<span class="help_explanation"><img src="'.CMS_ROOTPATH.'images/timeline.gif" alt="Timeline chart">&nbsp;<b>Timeline Chart</b>: The timeline chart displays historic events alongside a person\'s life events to give a context of the time in which the person lived.<br> This chart has its own dedicated help which you can view by hovering the cursor over the word "Help" to the left of screen.</span><br><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '
<p class="help_header">Top Ribbon</p>
<p class="help_text">- “Search” field and button<br>
<span class="help_explanation">This box appears on all pages of HuMo-gen and allows you the convenience to search for any persons from the database from any page. Typing in a name and clicking search will display a list of people from the database bearing a name matching the search term.</span><br><br>
- “Select a theme” dropdown:<br>
<span class="help_explanation">By default, HuMo-gen is provided with several color schemes, and so long as the site administrator has not disabled them, they will appear in this dropdown list. You can select a theme to your preference, that will change elements such as page color, background images, etc. These themes will only affect your experience on HuMo-gen and will make no changes to your internet browser or computer.</span><br><br>
- The A+ A- Reset buttons<br>
<span class="help_explanation">These controls allow you to control the text size on screen while using HuMo-gen. These controls will only affect your experience on HuMo-gen and will make no changes to your internet browser or computer. (Note: Reset is only visible on browsers that support this function). </span><br><br>
- The orange RSS-icon (only displayed if activated by the site owner)<br>
<span class="help_explanation">If you add this feed (as it\'s called) to your RSS-reader, you will be able to see at one glance who has a birthday!<br>(In the "Tools" pull-down menu you may see an "Anniversary List" option. That option will display a list of birthdays in the present month).</span><br>

</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">Main Menu Button "Home"</p><br>
<span class="help_explanation">This takes you to the main Persons Index.
Some panels on this page that require explanation:</span><br>
<p class="help_text">- Owner family tree<br>
<span class="help_explanation">Clicking the name of the site owner will open an email form that allows you to send the site owner a short notice. Please enter your name and email address, so you can be answered. If you wish to send the site owner an attachment (such as a photo or a document) you can use this form to ask the site owner for his email. Then you can use any regular email program to send those attachments. (The email address of the site owner is not published on the site to prevent spamming).</span><br><br>
<p class="help_text">- Search fields<br>
<span class="help_explanation">In the search fields you can search by first and/or last name. You can also choose from three options: "contains", "equals" and "starts with". Note: next to the search button there is an option for  &quot;Advanced Search!&quot;</span><br><br>
- More<br>
<span class="help_explanation">The next few lines are obvious: click the link that you want to move to.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo 
'<p class="help_header">Main Menu Button "Family Tree"</p><br>
<p class="help_text">- Family tree index<br>
<span class="help_explanation">This takes you to the main Persons Index (See above).</span><br><br>
<p class="help_text">- Persons<br>
<span class="help_explanation">Takes you to the main persons index.  Same as the ‘Home’ button.
The list shows all persons in the family tree, sorted alphabetically. A maximum number of 150 persons is displayed. You can press the page numbers to see more. You can chose between "Concise view" or "Expanded view".  In the expanded view (ex)partners are also displayed, that are not shown in concise view.</span><br><br>
<p class="help_text">- Names<br>
<span class="help_explanation">Here you will find a list of all family names, followed by the number of persons who carry that name.</span><br><br>
<p class="help_text">- Places (this button is only displayed if activated by the site owner)<br>
<span class="help_explanation">Here you can search by place of birth, baptism, by address, place of death or burial. 
You can search with the options: "contains", "equals" and "starts with". 
Here too, you can choose between expanded or concise view. 
The results will be sorted alphabetically by place name.
</span><br><br>
</p>';
echo '</div>';



echo '<p><div class="help_box">';
echo '<p class="help_header">Main Menu Button "Tools"</p><br>
<span class="help_explanation">Several child menus appear under the Tools menu:</span><br>
<p class="help_text">- Sources: (only displayed if activated by the site owner)<br>
<span class="help_explanation">Here you will find a list of all sources used in the genealogical research.</span><br><br>
<p class="help_text">- Anniversary List<br>
<span class="help_explanation">This opens a list with all persons in the selected tree who have a birthday in the current month. You can also choose a different month than the current one.</span><br><br>
<p class="help_text">- Statistics<br>
<span class="help_explanation">The information given in the statistics table doesnt warrant further explanation.</span><br><br>
<p class="help_text">- Relationship calculator<br>
<span class="help_explanation">With the relationship calculator you can establish blood and/or marital relations between two people. In the search fields "First Name" and "Last Name" you can enter names, part of names or leave a field empty. Then you can click "search" and consequently pick a name from the result list. Once two names have been selected, you can click "Calculate relationship" and if a relationship is found it will be listed together with a graphical representation. You can press the change symbol to switch between the persons.</span><br><br>
<p class="help_text">- Google maps<br>
<span class="help_explanation">This will display a googlemap relating to the people present in the database, with ability to map by births or deaths. Instructions on the use of these googlemap features can be found <a href="http://humogen.com/index.php?option=com_wrapper&view=wrapper&Itemid=58" target="_blank">here</a> in the online manual, under "Advanced Options"</span><br><br>
<p class="help_text">- Contact<br>
<span class="help_explanation">This will open an email form, similar to the one explained above (see “owner family tree”).</span><br>
<p class="help_text">- Latest changes<br>
<span class="help_explanation">This will display a list of new and recently changed people in the database. A fully scrollable list is displayed in reverse chronological date order, with the most recent items displayed first.
There is a search field that allows you to narrow the list of results down. It accepts partial names, e.g. searching for “Sa” will return all people with SA in their name, such as Sam, Sarah, Susanne, or even people with the surname Samson for example.</span><br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menu Button "Language Flags"</p><br>
<span class="help_explanation">On the menu bar, to the right of the menu buttons, you will notice several national flags, that allow you to change the display language.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menu Button "Photobook"</p>
<p class="help_text">Note: this button is only displayed if activated by the site owner<br><br>
<span class="help_explanation">Here you will see a display of all photos in the database.<br>Click on a photo for an enlarged version or click on the name to move to the family page of that person.<br>
</p>';
echo '</div>';

echo '<p><div class="help_box">';
echo '<p class="help_header">Menu Button "Login"</p><br>
<span class="help_explanation">If the site owner gave you a username and password, you can log in here to see data that is not shown to the general public (such as details of living people or "hidden" family trees).<br>
</p>';
echo '</div>';
?>
