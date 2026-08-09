---
layout: page
title: Language files
---
It's possible to create new language files or to adjust existing language files.

## Adjust an existing language
Move to the admin section and open the tab "Control -> Language editor".  
Change the translation in the right column and press save.

## Add a new language
HuMo-genealogy uses the Poedit language system for most translations (except the main menu "Help" screen).

All language files can be found in the /languages/ folder in HuMo-genealogy, which is divided in sub-folders according to the common ISO standard 2-letter country codes ("en" for English, "nl" for Dutch, "fr" for French etc.).  
It’s also possible to use country codes like “en_us” or “en_ca”.

The steps to add a new language are as follows.

In this tutorial we will use "pt" for Portuguese. Replace this everywhere with your country code.

Please follow these steps in **chronological order**.

1. Create a new subfolder in the /languages folder: /languages/pt
    
2. From the languages/en folder, copy the following files to the new languages/pt sub-folder:  
    a. Language_data.php  
    b. Language_help.php  
    c. en.po  
    d. (optionally) the timelines folder
    
3. **The country flag**  
    Create a 21x15 px gif file of your flag and place it in the /languages/pt folder with the name "flag.gif".  
    This is important so you can find and identify your language in the admin and end-user screens.
    
4. **The language_data.php file**  
    This file holds only 2 items for you to tend to:  
    **1. $language["name"]**  
    Set the $language["name"] variable to the native name of your language: $language["name"]="Português";  
    **2. $language["dir"]**  
    If your language is a left-to-right language (like Portuguese and English) leave the setting as it is.  
    Should it be a right-to-left language (like Arabic), change the setting to: $language["dir"]="rtl";
    
5. **The en.po file**  
    This is a "poedit" type file.  
    To translate the file into your language you can use the built-in HuMo-genealogy language editor or an external program (like "Poedit") on your PC.  
    Change the name of the file to: **pt.po  
    ****To use the HuMo-genealogy language editor**  
    Open the tab "Control --> Language editor".  
    Among the flags displayed, you should now see your country's flag that you created and placed in step 3. Click it.  
    Make sure the caption of the right column now says "Translation into Português" (or whatever you called the name of your language in step 4).  
    Now you are ready to translate and change the English terms in the right column to your language.  
    When you are ready (or whenever you want to take a break), press "Save" above the right column.  
    
    _**IMPORTANT NOTE** __: the untranslated items always appear on top, so don't get a shock if you translated a lot of items, pressed "Save" and then see empty fields. Your translated items did not disappear, they will show when you scroll down!  
    
    _When you press "Save", a new pt.**mo** file be save to the languages/pt folder. This is the actual compiled file that HuMo-genealogy will use. You don't have to open or handle the **.mo** file at all - it is created/updated automatically each time the **.po** file gets saved.
    
    **To do the translation in a PC editor such as Poedit**  
    Download and install Poedit ( [www.poedit.net](http://www.poedit.net/) ) and install it on your PC  
    Download the pt.po file to your computer and open it with Poedit.  
    Translate the terms (if you leave an item untranslated it will appear in English on your site), and save your work.  
    The Poedit program will have saved the **pt.po** file and has created a **pt.mo** file as well  
    Upload both files to the /languages/pt folder on your server.
    
1. **The language_help.php file**  
    The .po file holds all terms that are used in the HuMo-genealogy program, with the exception of the "Help" screen that is opened with the "Help --> Help" tab on HuMo-genealogy's main top menu.  
    To translate this file, open it in any text editor and translate the English terms.  
    Make sure to leave the HTML tags intact, to ensure proper formatting.
    
2. **The "timelines" folder**  
    This folder holds the text files for the timelines feature in HuMo-genealogy. This is one of the reports, where the genealogical events from a person's life (birth, marriage, birth of children etc) are shown next to historic events of that period.  
    The historic events are subdivided in continents/countries/cultures, so that the end-users can choose the desired historical background for this person.  
    If you do not add this subfolder to your /language/pt folder, the "timelines" icon will not be displayed in the person's pop-up when HuMo-genealogy is viewed in your language.  
    If you place the timelines folder in /languages/pt untranslated, people who view your HuMo-genealogy site in your language will see the historic events displayed in English.  
    If you wish, you can translate all or some of the .txt files into your language. You can also add a new .txt file with historic events of your country, following the guidelines on this topic elsewhere in the manual.
    


**If you have added a new language in the above described way, please post all above files to the forum so we can include them in the next official HuMo-genealogy version!**

Once your language is officially included in HuMo-genealogy, please check periodically on Transifex, where the language files are kept, updated with the latest additions to HuMo-genealogy. This way you can check if there are new items to be translated and if so, you can translate them online. The new translations will then be used in the following HuMo-genealogy version. This way you can help us keeping your language constantly updated!

## Use a link to change the language

It's possible to make a direct link to HuMo-genealogy AND change the language. Use the link: [http://link_to_website/humo-gen/index.php?language=nl](http://link_to_website/humo-gen/index.php?language=nl)

