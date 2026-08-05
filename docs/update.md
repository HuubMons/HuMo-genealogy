---
layout: page
title: Update
---
# HuMo-genealogy update

There are 2 update methods: automatic and manually.

## Automatic update method (HuMo-genealogy version > 4.8.5)

_If you are using a HuMo-genealogy version < 4.8.6 then there is no automatic update build in.  

*The automatic update needs to download new HuMo-genealogy files and unzip them. Unfortunately some providers don’t allow these options due to safety reasons.*

Go to the admin screen.

- In the top of the admin screen there will be a message, of a new version available, OR you can click the "update option" to do a check for the latest HuMo-genealogy beta version.

- Now you can start the update procedure by clicking: "Start automatic update."

- STEP 1) Download and unzip new HuMo-genealogy version (should be safe to do, no changes made yet).

- STEP 2) Check files (should be safe to do, no changes made yet).

- STEP 3) Install files (this will install new files, and overwrite existing files!).

## Manual update method 1

1) Create a new folder, for example: humo-gen2
2) Install the new HuMo-genealogy version in the new folder.
3) Copy the file db_login.php from the old map to the new folder.
4) Now you can test the new version.   
   You can test using this link: [http://www.name_of_your_website/humo-gen2](http://www.name_of_your_website/humo-gen2)
5) If the new version worked: delete or rename the old folder](http://www.name_of_your_website/humo-gen2)
6) Rename the folder humo-gen2 to humo-gen (or the name of the old version)  
  
## Manual update method 2

Normal procedure for a HuMo-genealogy update:
1) First create a backup of the file: db_login.php
2) Replace all files (normally you can leave the file db_login.php)  
## Update problems

Try the next steps one-by-one if HuMo-genealogy does not work properly after an update:
1. Go to the administration menu and select "import gedcom", and re-import the GEDCOM file(s).
2. Go to the administration menu and select "install", and reinstall the tables. Unfortunately all previous settings will be gone.

When you encounter other problems on your site: reload the HuMo-genealogy page or remove the cookies from your PC, that may help!