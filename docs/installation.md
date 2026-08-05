---
layout: page
title: Installation
---
## Quick installation guide

HuMo-genealogy is a web-based genealogy program. HuMo-genealogy can be installed at a (free or paid) web host that supports PHP and MySQL, Windows PC, Linux PC, etc. It is designed for use on the internet. You need a web server (on the internet or at your pc) to use this program.

Quick installation guide (extended guide can be found in this manual):

* Download the HuMo-genealogy zip file

    Github: [github.com/HuubMons/HuMo-genealogy/releases](https://github.com/HuubMons/HuMo-genealogy/releases)

    Sourceforge: [sourceforge.net/projects/humo-gen/files](https://sourceforge.net/projects/humo-gen/files)

* Unpack HuMo-gen zip package

* Upload HuMo-gen files to your provider using FTP, we recommend using a sub folder like: humo-gen or genealogy
   
* Open your browser and go to: www.yourwebsite/humo-gen
   
* Further installation instructions (connect to a MySQL database) will be shown.

- [Start HuMo-genealogy](start_humo-genealogy) More information about first start of HuMo-genealogy.
   

## Full installation guide

HuMo-genealogy can be installed in several ways. The most common use is installation with a (free or paid) web host (provider) that supports PHP and MySQL. It is also possible to install HuMo-gen on a local PC (mainly for testing purposes).

Important notes for installation

    • HuMo-genealogy works best under PHP 8.0 or more recent PHP version.

If you use a lower version, some functions will not work, and errors will be possible.

Default usernames and passwords in recent HuMogen-versions:

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

The basic steps for installation:

* Download HuMo-genealogy
    Github: [github.com/HuubMons/HuMo-genealogy/releases](https:..github.com/HuubMons/HuMo-genealogy/releases)
    Sourceforge: [sourceforge.net/projects/humo-gen/files](https://sourceforge.net/projects/humo-gen/files)

* Unzip the package in a new folder that you may name HuMo-genealogy or genealogy or any other name you fancy (in this manual we will use HuMo-genealogy for the folder name. If you choose another name change the paths below accordingly).

* If you install with a webhost, copy the folder you just created with its contents to the www folder of your server (sometimes called public_html). Now open your browser, go to: www.your_website.com/humo-gen/admin and follow instructions.

* If you install on your own PC with XAMPP, copy your humo-gen folder to the "htdocs" folder of XAMPP. Now open your browser, go to: localhost/humo-gen/admin
or maybe you have to use: localhost:8080/humo-gen/admin and follow instructions.



Installation of HuMo-genealogy using an internet provider

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


[Start HuMo-genealogy](start_humo-genealogy) More information about first start of HuMo-genealogy.