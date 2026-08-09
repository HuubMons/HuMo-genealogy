---
layout: page
title: Timelines
---
A HuMo-genealogy download (from version 4.6) includes the timeline option (available from the pop-up menu next to each person). The timeline report shows the years in which the person lived, with on the left side personal events in this person's life, and on the right side historic events in this same period.

By default, HuMo-genealogy includes 7 timeline history files that can be chosen from the timeline menu by the end-users: These files contain historic events on these topics: Africa, America, Asia, Europe, Oceania, Netherlands and Jewish history.
## Removing certain or all timeline history files

You may not be interested in showing all these options on your web site. To remove one or more of these options from the timeline menu, go to the humo-gen/languages/en/timelines folder and remove the .txt file(s) you do not want to be displayed. Repeat the same for each display language (i.e. humogen/languages/**fr**/timelines, humogen/languages/**nl**/timelines).

## Adding your own timeline files

It is quite easy to add your own timeline files (for example with the history of your own country). Take these steps:

1. Create a new file with the name you want to appear in the menu and with .txt as suffix (i.e. belgium.txt)

2. Each line in the file should have one of the following entries:

a. **1857 Battle of Nothingland** (4-digit year, single space, free text)  
b. **1834-1842 War of Nogood** (4-digit year, dash, 4-digit year, single space, free text.  

3. Note: the year **has** to be listed as the first item. Therefore, if you want to give a full date for an event, place the month and day as the first part of the text section after the space:

1857 January 5, Nomination of President Whatshisname.  
  

4. In the file a period has to be entered as 1857-1861. On display this will be rendered as: 1857 (till 1861)

5. Place the lines in chronological order. You can have multiple lines starting with the same year. These will be grouped automatically by the program. This is preferable and easier to read for the user, than placing multiple events on the same line after a specific year.

6. That's all. Save your file and place it in each language's timelines folder. Remember that if you place it only in one language folder, people using another language display for HuMo-genealogy will not be able to see the historic events at all. I suggest that for languages you don't know or don't want to translate to, you just put an English file so people will at least see something there. (The existing timeline files in HuMo-genealogy come in Dutch for /nl and in English for all other languages)

7. Anyone who wants to translate existing timeline files to HuMo-genealogy languages other than Dutch or English is welcome to send them to us (zipped) on the HuMo-genealogy forum: [http://www.humo-gen.com/genforum/viewforum.php?f=25](http://www.humo-gen.com/genforum/viewforum.php?f=25)

## Adding links to a timeline file

When you make your own timeline file, you may want to add links to related pictures or even external websites. This can be done exactly the way it is done in any html document. For example, let's say your timeline text looks like this:

1840 January 10, the new Northchester Town-hall was inaugurated.  
  

and you would want "Northchester Town-hall" to be a link to a photo of that building, called townhall.jpg. If the photo would be located within a "pictures" folder on your server, parallel to your humogen folder, you would use this in the text of your timeline file:

1840 January 10, the new <a href="../pictures/townhall.jpg"> Northchester Town-hall </a> was inaugurated.  
  

You may also use an absolute path to the photo on your server, or even to a photo on another site:

1840 January 10, the new <a href="www.somedomain.com/pics/townhall.jpg"> Northchester Town-hall </a> was inaugurated.

## Changing default steps (periods)

By default the timeline report displays periods of 5 years, and the end-user can choose to display 1 or 10 years instead. If you have many timeline items, you may want to change the default to 1 year, and if you have very few you might prefer 10 as the default. To do this, look for line nr. 369 in the timelines.php file:

$step=5; // default step - user can choose 1 or 10 instead

Change the number from 5 to 1 or 10 to set a different default ($step=1; $step=10;)

## Changing default timeline

By default "europe" is set as the default timeline. You may wish to change that, and set a different timeline as default (america, asia etc) or to set your self-made timeline file as the default. Also, if you remove one or more timeline files, the default may change to a different timeline than you want. Here we'll show you how to set the default timeline yourself.

On line 371 in the timelines.php file you will see this line:

$tml=3; // default timeline file

Since programming languages start counting at 0 (rather than 1) this means the fourth item on the timeline menu is set as default (0, 1, 2, 3). With a default installation of HuMo-genealogy this is "europe". To set a different default, simply count the menu items as they look on your website and deduct 1. For example, if the timeline you want is number 6 on the list, you want to set the variable to 5. So change line 371 to read:

$tml=5; // default timeline file  
  

## Changing the looks of the timeline report (for more advanced users)

The timeline report automatically has the same looks as any other table in humogen, according to the theme (skin) you use. If you want to change the looks of this report, you will have to add your own CSS class (let's call it "mytimeline").

First, in the file timelines.php on line 457 change:

`print "<table class='humo' style='border:1px'>";`

to:

`print "<table class='humo mytimeline' style='border:1px'>";`  
  

Now you can create the "mytimeline" class and add it to the gedcom.css file. For example, one might add the following code:

```
table.mytimeline {  
font-size:18px; /* font size will be set to 18 pixel */  
}  
table.mytimeline td {  
border:1px solid blue; /* border lines will be blue */  
color:green; /* text color will be green */  
font-style:italic; /* text will be slanted */  
font-family:Comic Sans MS; /* default font will change to Comic Sans */  
}  
```
  

Of course you can change this to anything you like.

**IMPORTANT**
_The above CSS that you place in gedcom.css will only influence the "Elegant" themes (skins). To  
influence the looks of the timelines report in the other skins, you will have to place the code in their specific CSS  
files that you can find in the folder styles/timelines (such as: Silverline.css)_