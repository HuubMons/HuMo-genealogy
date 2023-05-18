<?php


// TEMPLATE
include_once __DIR__ . '/header.php';
include_once __DIR__ . '/menu.php';


?>
<div class="help_div">
	<div class="help_box">
		<!-- *** Icons *** -->
		<p class="help_header"><?= __('Icons'); ?></p><br>
		<span class="help_explanation"><img src="/theme/images/reports.gif" alt="Reports">&nbsp;<b><?= __('Reports'); ?></b><br>
			<?= __('This icon is shown to the left of names of persons in lists or reports. When you hover over this icon with your mouse, a pop-up will be displayed. In the pop-up list you will find several icons with names of reports and charts that you can create from this person (the exact number of icons on the list varies according to the presence of ancestors and/or descendants). Following is a list of those icons and their meaning.'); ?>
		</span><br><br>
		<span class="help_explanation"><img src="/theme/images/ancestor_report.gif" alt="Pedigree">&nbsp;<b><?= __('Ancestor report'); ?></b>
			<br><?= __('A pedigree is a genealogical report of a person\'s ancestors. A pedigree uses a special method of numbering: the base person is number 1, his father number 2 and his mother number 3. The number of a father is ways twice that of his son and the mother is one number higher. Thus, number 40 is the father of number 20 and 41 is the mother of number 20. <br>From amongst the icons in the pop-up menu you can also choose a graphical display of the ancestor report.'); ?></span><br><br>
		<!-- REMOVED: <br>You can read more about pedigrees <a href="http://en.wikipedia.org/wiki/Pedigree_chart" target="blank"><b>here</b></a> and <a href="http://en.wikipedia.org/wiki/Ahnentafel" target="blank"><b>here.</b></a> -->
		<span class="help_explanation"><img src="/theme/images/descendant.gif" alt="Parenteel">&nbsp;<b><?= __('Descendant report'); ?></b><br><?= __('A descendant report is a genealogical report of a patriarchal couple or of one patriarch (generation I) with their children (generation II) and all further descendants, both along male and female lines. <br>From amongst the icons in the pop-up menu you can also choose a graphical display of the descendant report.'); ?></span><br><br>
		<span class="help_explanation"><img src="/theme/images/outline.gif" alt="Outline report">&nbsp;<b><?= __('Outline report'); ?></b>
			<br><?= __('An outline report is a clear summary of all descendants of one person (and his/her partners), where each generation gets its own (ascending) number.'); ?></span><br><br>
		<span class="help_explanation"><img src="/theme/images/ancestor_chart.gif" alt="ancestor sheet">&nbsp;<b><?= __('Ancestor sheet'); ?></b>
			<br><?= __('An ancestor sheet lists 5 generations in table layout, with the base person at the bottom and the ancestors above him/her in increasingly smaller boxes.'); ?></span><br><br>
		<span class="help_explanation"><img src="/theme/images/fanchart.gif" alt="Fanchart">&nbsp;<b><?= __('Fanchart'); ?></b>
			<br><?= __('A fanchart is a circular chart that shows the ancestors in circles around the base person. This allows for a very clear view of the ancestry of any specific person. The box for each person on the chart is clickable to enable fast access to that person\'s family sheet. <br> The size of the fanchart and some other settings may be adjusted from the menu to the left of the chart.'); ?></span><br><br>
		<span class="help_explanation"><img src="/theme/images/timeline.gif" alt="Timeline chart">&nbsp;<b><?= __('Timeline'); ?></b><br><?= __('The timeline chart displays historic events alongside a person\'s life events to give a context of the time in which the person lived.<br>This chart has its own dedicated help which you can view by hovering the cursor over the word "Help" to the left of screen.'); ?></span><br><br></p>
	</div>
	<!-- *** Top ribbon *** -->
	<p>
	<div class="help_box">
		<p class="help_header"><?= __('Top Ribbon'); ?></p>
		<p class="help_text"><?= __('“Search” field and button'); ?><br>
			<span class="help_explanation">
				<?= sprintf(__('This box appears on all pages of %s and allows you the convenience to search for any persons from the database from any page. Typing in a name and clicking search will display a list of people from the database bearing a name matching the search term.'), 'HuMo-genealogy'); ?>
			</span><br><br>
			<?= __('The orange RSS-icon') . ' ' . __('(only displayed if activated by the site owner and enabled in webbrowser)'); ?><br>
			<span class="help_explanation"><?= __('If you add this feed (as it\'s called) to your RSS-reader, you will be able to see at one glance who has a birthday!<br>(In the "Tools" pull-down menu you may see an "Anniversary List" option. That option will display a list of birthdays in the present month).'); ?></span><br>
		</p>
	</div>
	<!-- *** Menu items *** -->
	<p>
	<div class="help_box">
		<p class="help_header"><?= __('Menu items'); ?></p><br>
		<p class="help_text"><?= __('Home') . '/' . __('Family tree index'); ?><br>
			<span class="help_explanation"><?= __('This takes you to the main Persons Index. The "Home" page can be replaced by a custom page made by the website owner.'); ?></span><br>
		<p class="help_text"><?= __('Family tree') . ' - ' . __('Persons'); ?><br>
			<span class="help_explanation"><?= __('Takes you to the main persons index. Same as the ‘Home’ button. The list shows all persons in the family tree, sorted alphabetically. A maximum number of 150 persons is displayed. You can press the page numbers to see more. You can chose between "Concise view" or "Expanded view".  In the expanded view (ex)partners are also displayed, that are not shown in concise view.'); ?></span><br>
		<p class="help_text"><?= __('Family tree') . ' - ' . __('Names'); ?><br>
			<span class="help_explanation"><?= __('Here you will find a list of all family names, followed by the number of persons who carry that name.'); ?></span><br>
		<p class="help_text"><?= __('Family tree') . ' - ' . __('Places') . ' ' . __('(only displayed if activated by the site owner)'); ?><br>
			<span class="help_explanation"><?= __('Here you can search by place of birth, baptism, by address, place of death or burial. You can search with the options: "contains", "equals" and "starts with". Here too, you can choose between expanded or concise view. The results will be sorted alphabetically by place name.'); ?></span>
		</p>
		<p class="help_text"><?= __('Family tree') . ' - ' . __('Photobook') . ' ' . __('(only displayed if activated by the site owner)'); ?><br>
			<span class="help_explanation"><?= __('Here you will see a display of all photos in the database.<br>Click on a photo for an enlarged version or click on the name to move to the family page of that person.'); ?><br>
		</p>
		<p class="help_text"><?= __('Tools') . ' - ' . __('Sources') . ' ' . __('(only displayed if activated by the site owner)'); ?><br>
			<span class="help_explanation"><?= __('Here you will find a list of all sources used in the genealogical research.'); ?></span><br>
		<p class="help_text"><?= __('Tools') . ' - ' . __('Anniversary list'); ?><br>
			<span class="help_explanation"><?= __('This opens a list with all persons in the selected tree who have a birthday in the current month. You can also choose a different month than the current one.'); ?></span><br>
		<p class="help_text"><?= __('Tools') . ' - ' . __('Statistics'); ?><br>
			<span class="help_explanation"><?= __('The information given in the statistics table doesnt warrant further explanation.'); ?></span><br>
		<p class="help_text"><?= __('Tools') . ' - ' . __('Relationship calculator'); ?><br>
			<span class="help_explanation"><?= __('With the relationship calculator you can establish blood and/or marital relations between two people. In the search field "Name" you can enter a name. For example first name, last name, call name, part of name or leave a field empty. Then you can click "search" and consequently pick a name from the result list. Once two names have been selected, you can click "Calculate relationship" and if a relationship is found it will be listed together with a graphical representation. You can press the change symbol to switch between the persons.'); ?></span><br>
		<p class="help_text"><?= __('Tools') . ' - ' . __('World map'); ?><br>
			<span class="help_explanation"><?= __('This will display a world map relating to the people present in the database, with ability to map by births or deaths.'); ?></span><br>
			<!-- REMOVED: Instructions on the use of these googlemap features can be found <a href="http://humogen.com/index.php?option=com_wrapper&view=wrapper&Itemid=58" target="_blank">here</a> in the online manual, under "Advanced Options" -->
		<p class="help_text"><?= __('Tools') . ' - ' . __('Contact'); ?><br>
			<span class="help_explanation"><?= __('This will open an email form, similar to the one explained above (see “owner family tree”).'); ?></span><br>
		<p class="help_text"><?= __('Tools') . ' - ' . __('Latest changes'); ?><br>
			<span class="help_explanation"><?= __('This will display a list of new and recently changed people in the database. A fully scrollable list is displayed in reverse chronological date order, with the most recent items displayed first. There is a search field that allows you to narrow the list of results down. It accepts partial names, e.g. searching for “Sa” will return all people with SA in their name, such as Sam, Sarah, Susanne, or even people with the surname Samson for example.'); ?></span><br>
		</p>
		<p class="help_text"><?= __('Login'); ?><br>
			<span class="help_explanation"><?= __('If the site owner gave you a username and password, you can log in here to see data that is not shown to the general public (such as details of living people or "hidden" family trees)'); ?><br>
		</p>
		<p class="help_text"><img src="languages/en/flag.gif"> <?= __('Language Flags'); ?><br>
			<span class="help_explanation"><?= __('On the menu bar, to the right of the menu buttons, you will notice several national flags, that allow you to change the display language.'); ?><br>
		</p>
		<p class="help_text"><img src="styles/images/settings.png"> <?= __('User settings'); ?><br>
			<span class="help_explanation"><?= __('This page contains several user settings: selecting a theme and changing password (if allowed).'); ?><br>
		</p>
	</div>
	<!-- *** Settings page *** -->
	<p>
	<div class="help_box">
		<p class="help_header"><?= __('User settings'); ?></p><br>
		<p class="help_text"><?= __('”Select a theme” dropdown'); ?><br>
			<span class="help_explanation">
				<?php sprintf(__('By default, %s is provided with several color schemes, and so long as the site administrator has not disabled them, they will appear in this dropdown list. You can select a theme to your preference, that will change elements such as page color, background images, etc. These themes will only affect your experience on %s and will make no changes to your internet browser or computer.'), 'HuMo-genealogy', 'HuMo-genealogy'); ?>
			</span><br><br>
	</div>
	</p>
	<!-- *** Family tree index *** -->
	<p>
	<div class="help_box">
		<p class="help_header"><?= __('Family tree index'); ?></p><br>
		<!-- <span class="help_explanation"> ?= __('This takes you to the main Persons Index. Some panels on this page that require explanation:'); ?></span><br> -->
		<p class="help_text"><?= __('Owner family tree'); ?><br>
			<span class="help_explanation"><?= __('Clicking the name of the site owner will open an email form that allows you to send the site owner a short notice. Please enter your name and email address, so you can be answered. If you wish to send the site owner an attachment (such as a photo or a document) you can use this form to ask the site owner for his email. Then you can use any regular email program to send those attachments. (The email address of the site owner is not published on the site to prevent spamming).'); ?></span><br>
		<p class="help_text"><?= __('Search fields'); ?><br>
			<span class="help_explanation"><?= __('In the search fields you can search by first and/or last name. You can also choose from three options: "contains", "equals" and "starts with". Note: next to the search button there is an option for  &quot;Advanced Search!&quot;'); ?></span>
		</p>
	</div>
</div>

<?php include_once __DIR__ . '/footer.php';
