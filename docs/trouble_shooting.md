---
layout: page
title: Trouble shooting
---
#### Website problems
Serious problems at website (without showing an error)

- First try go the the page in HuMo-genealogy that has a problem (to create an error line in the log file)  
- Log into the providers control panel, and select "DirectAdmin".  
- Select your domainname.  
- Select "Site Summary / Statistics / Logs"  
- Line "Web Error Log" click "100 lines".  
  
A log of errors should be seen. Check if the last error is the error from HuMo-genealogy.  
If we have the error message, we can try to solve this problem.
#### Use of pictures
From the HuMo-genealogy manual:  
* It is advisable to save the names of pictures in small (non capital) letters. That way most problems on Linux servers can be avoided! This is because on Linux servers "Picture.jpg" is a different picture than "picture.jpg".  
* Don't use spaces in filenames and folder names. Example: john_snow.jpg  
* Don't use accent characters (é and è for exampe) in filenames and folder names.  
Also see: [https://www.mtu.edu/umc/services/websit ... ers-avoid/](https://www.mtu.edu/umc/services/websites/writing/characters-avoid/)
#### Access to website is blocked
There is a security item in HuMo-genealogy. If there was a wrong login from a IP address 10x, then this IP address will be blocked. Removal of history or cookies won't help, this is a HuMo-genealogy security item.  
  
There are 2 possibilities to solve this:

1) To change number of logins fails, open file include/db_functions.php and find this line:

```
if ($check_fails > 10) $allowed=false;
```

Just change 10 into 20 or something like that!  
  
2) Wrong logins can be found in table: humo_user_log  

The problem will be solved is this table is made empty.
#### Large family tree  
If there is a large family tree (several thousands of persons) it's possible the screen freezes for a moment. The cache (memory) is then filled with data. If cache would be disabled the page would be slow every time.
#### Slow website  
Slow website because of search engine indexing.
Open log files in control panel of provider to check for searchengines.  
To block certain searchmachines (like Facebook), open htaccess file and enable block lines for searchengines.

Slow website because of large statistics tables.
In some cases the website could be slow because of very large statistics tables.  
Go to admin and remove (part of) the old statistics.
#### Process large gedcom files  
It's possible a standard hosting provider won't process your large gedcom file (small gedcom files with a few thousand persons should be no problem).  
Maybe you need a virtual server or your own webserver so it's possible to control the websites settings (timing and memory) yourself.  
Other possible solutions to process a large gedcom file:

- Split the large gedcom file and read the parts in HuMo-genealogy. It's possible to add a gedcom file to a family tree.  
- Install HuMo-genealogy at a PC or laptop, read the gedcom file. Export the database using PHPMyAdmin. Go to your provider -> PHPMyAdmin, and read the database.  
  
If you have one or multiple very large family trees, and you are using the gedcom import regularly, it's better to change the database id's into BIGINT.

**Before using these queries, do back-up your database!**

```
ALTER TABLE humo_persons CHANGE pers_id pers_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT  
ALTER TABLE humo_unprocessed_tags CHANGE tag_pers_id tag_pers_id BIGINT(20) UNSIGNED NULL DEFAULT NULL  
  
ALTER TABLE humo_families CHANGE fam_id fam_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;  
ALTER TABLE humo_unprocessed_tags CHANGE tag_rel_id tag_rel_id BIGINT(20) UNSIGNED NULL DEFAULT NULL;  
  
ALTER TABLE humo_addresses CHANGE address_id address_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;  
ALTER TABLE humo_unprocessed_tags CHANGE tag_address_id tag_address_id BIGINT(20) UNSIGNED NULL DEFAULT NULL;  
  
ALTER TABLE humo_connections CHANGE connect_id connect_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;  
ALTER TABLE humo_unprocessed_tags CHANGE tag_connect_id tag_connect_id BIGINT(20) UNSIGNED NULL DEFAULT NULL;  
  
ALTER TABLE humo_events CHANGE event_id event_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;  
ALTER TABLE humo_unprocessed_tags CHANGE tag_event_id tag_event_id BIGINT(20) UNSIGNED NULL DEFAULT NULL;  
  
ALTER TABLE humo_location CHANGE location_id location_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;  
ALTER TABLE humo_unprocessed_tags CHANGE tag_place_id tag_place_id BIGINT(20) UNSIGNED NULL DEFAULT NULL;  
  
ALTER TABLE humo_sources CHANGE source_id source_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;  
ALTER TABLE humo_unprocessed_tags CHANGE tag_source_id tag_source_id BIGINT(20) UNSIGNED NULL DEFAULT NULL;  
  
ALTER TABLE humo_texts CHANGE text_id text_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;  
ALTER TABLE humo_unprocessed_tags CHANGE tag_text_id tag_text_id BIGINT(20) UNSIGNED NULL DEFAULT NULL;  
  
ALTER TABLE humo_unprocessed_tags CHANGE tag_id tag_id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT;
```

