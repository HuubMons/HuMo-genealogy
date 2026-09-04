---
layout: page
title: Trouble shooting
---
#### PHP version
If possible, use a recent version of PHP. 
Most providers have an upgrade procedure to a newer PHP version (also use a recent version of MySQL).

#### Reset admin password
Best way to reset the admin password at this moment if password is lost:  

* Open PHPMyAdmin  
* Open table humo_users  
* Go to the admin user line (or the user name with admin rights)  
* If there is a field "user_password_salted", empty this field.  
* Change user_password into: 712697cdade1e78580bf26e564a891f5  

This will reset the password to the default password: humogen

#### Security/ login problems
For security, HuMo-genealogy blocks login access if someone tries to login unsuccessfully more than 20 times. A text "Access to website is blocked." will be shown.
To solve this issue, temporarily change the $check_fails variable to a higher value in file \include\DbFunctions.php:
```
if ($check_fails > 20) {
```

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

#### Style/  theme/ layout problems
If there are (CSS) style problems: just reload the HuMo-genealogy page using `[CTRL]-F5`. This will completely reload the webpage and CSS files.
Another option is to empty the cache and/ or cookies in the browser.

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

#### Large family tree  
If there is a large family tree (several thousands of persons) it's possible the screen freezes for a moment. The cache (memory) is then filled with data. If cache would be disabled the page would be slow every time.

#### Slow website  
Slow website because of search engine indexing.
Open log files in control panel of provider to check for searchengines.  
To block certain searchmachines (like Facebook), open htaccess file and enable block lines for searchengines.

Slow website because of large statistics tables.
In some cases the website could be slow because of very large statistics tables.  
Go to admin and remove (part of) the old statistics.

#### Wrong characters
If special characters like é, ö etc. don't show properly, try another export of the GEDCOM file. If you use ANSEL, try exporting to UTF-8 or ANSI

#### GEDCOM import
If a GEDCOM file stucks, also try the setting "Show all numbers when processing GEDCOM (useful when a time-out occurs!)". You will see a long list of GEDCOM items numbers, but now it's possible to see if GEDCOM reading stops at the same item every time (contact the HuMo-genealogy programmer team).

#### Process large gedcom files  
It's possible a standard hosting provider won't process your large gedcom file (small gedcom files with a few thousand persons should be no problem).  

First options (GEDCOM process settings in GEDCOM import page):

* Try the batch processing option, for example: try "10000 records per batch". This will speed up GEDCOM processing (but will cost lots of server memory...).
* Use time-out settings.

Maybe you need a virtual server or your own webserver so it's possible to control the websites settings (timing and memory) yourself.  

If it’s not possible to read the GEDCOM file, you could do the following:
1) Install HuMo-genealogy on a local PC
2) Import the GEDCOM file
3) Export the database (with PhpMyAdmin)
4) Import the database with the provider (with PhpMyAdmin)
If all works OK, now it should not be necessary any more to import the GEDCOM file with the provider!

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

