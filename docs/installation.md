---
layout: page
title: Installation
---
## Quick installation guide

HuMo-genealogy is a web-based genealogy program. HuMo-genealogy can be installed at a (free or paid) web host that supports PHP and MySQL, Windows PC, Linux PC, etc. It is designed for use on the internet. You need a web server (on the internet or at your pc) to use this program.

Quick installation guide (extended guide can be found in this manual):
1) Download HuMo-gen zip file
    Github: [github.com/HuubMons/HuMo-genealogy/releases](https:..github.com/HuubMons/HuMo-genealogy/releases)
    Sourceforge: [sourceforge.net/projects/humo-gen/files](https://sourceforge.net/projects/humo-gen/files)
2) Unpack HuMo-gen zip package
3) Upload HuMo-gen files to your provider using FTP, we recommend using a sub folder like: humo-gen or genealogy
4) Open your browser and go to: www.yourwebsite/humo-gen
5) Further installation instructions (connect to a MySQL database) will be shown.
Go to page: Installation for full installation instructions, and installation at PC etc.


## Installation

HuMo-genealogy can be installed in several ways. The most common use is installation with a (free or paid) web host (provider) that supports PHP and MySQL. It is also possible to install HuMo-gen on a local PC (mainly for testing purposes).

Important notes for installation
    • HuMo-genealogy works best under PHP 8.0 or more recent PHP version.
If you use a lower version, some functions will not work, and errors will be possible.
    • Default usernames and passwords in recent HuMogen-versions:

| Username | Password                   |
| -------- | -------------------------- |
| admin    | humogen                    |
| family   | humogen                    |
| guest    | guest (no password needed) |

Default usernames and passwords in older versions (older than HuMo-genealogy 4.6.4):

| Username                         | Password                   |
| -------------------------------- | -------------------------- |
| beheer (dutch for administrator) | humogen                    |
| familie (dutch for family)       | humogen                    |
| gast (dutch for guest)           | guest (no password needed) |

Installation options

The basic steps for installation are as follows:
1) Download HuMo-genealogy
    Github: [github.com/HuubMons/HuMo-genealogy/releases](https:..github.com/HuubMons/HuMo-genealogy/releases)
    Sourceforge: [sourceforge.net/projects/humo-gen/files](https://sourceforge.net/projects/humo-gen/files)
2) Unzip the package in a new folder that you may name HuMo-genealogy or genealogy or any other name you fancy (in this manual we will use HuMo-genealogy for the folder name. If you choose another name change the paths below accordingly).

3a) If you install with a webhost, copy the folder you just created with its contents to the www folder of your server (sometimes called public_html). Now open your browser, go to: www.your_website.com/humo-gen/admin and follow instructions.

3b) If you install on your own PC with XAMPP, copy your humo-gen folder to the "htdocs" folder of XAMPP. Now open your browser, go to: localhost/humo-gen/admin
or maybe you have to use: localhost:8080/humo-gen/admin and follow instructions.



Installation of HuMo-genealogy with an internet provider
1) Unzip the HuMo-gen.zip file into a new directory on your PC. We suggest you name this new folder "humo-gen" or “genealogy”. We will use “humo-gen” for the examples in the manual.
2) Now use a FTP program (such as Filezilla) to upload the folder humo-gen with its content to your server into the www folder (sometimes called public_html).
3) In your browser (such as: Internet Explorer, Chrome or Firefox) open: your-website/humo-gen/admin/
Now follow further instruction of: Start HuMo-genealogy and first settings

With some American hosting providers (such as Bluehost.com) the values for DATABASE_USERNAME and DATABASE_NAME have to be preceded by the username of your control panel. For example if the values of these two entries above are: "myusername" and "mydatabase" and the username of your control panel is "mycontrol", then the values that have to be entered are: "mycontrol_myusername" and "mycontrol_mydatabase".

With some providers it's needed to change the .htacces file to activate the newest PHP version:

For provider Hosting2go, add this line to the .htaccess file. See: [hosting2go.nl/php5info](http://www.hosting2go.nl/php5info)
`AddHandler x-httpd-php5 .php`

For provider STRATO, add this line to the .htaccess file:
`AddType application/x-httpd-php5 .php`

For provider Tsohost, add this line to php.ini:
`session.save_path = /tmp/php_sessions`



XAMPP bladzijden nog niet overgenomen


## Start HuMo-genealogy and first settings

This is the second step to install HuMo-genealogy. If the HuMo-genealogy files are copied to the server, you can open your web browser (such as: Internet Explorer, Google Chrome or Firefox), and open HuMo-genealogy.
Normally you need these links:
If you are using a hosting provider: www.your_website/humo-gen/admin
If your are using Xampp at a local pc:  localhost/humo-gen/admin or localhost:8080/humo-gen/admin
You will see this screen:

DIT DEEL NOG UITWERKEN MET NIEUWE SCREENSHOTS


# Settings administration menu

After install, check the settings in the administration menu!

NIEUW SCREENSHOT


TIPS:

- Administration > Settings: Check the default settings, if you want, fill in the name of your website, e-mail address etc.
    
- Administration > Settings: _Here you can also set the default language for display and for the administration menu (each separately)._
    
- Gedcom > Family Trees : Here you find the settings for each tree, a.o. the name of the tree, the path to the pictures, etc.
    
- Users > Users : Here you can create new users, and connect the user to a user group.
    
- Users > Groups : Here you can change several settings for EACH user group, a.o. privacy, showing pictures and texts.




