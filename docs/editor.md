---
layout: page
title: Editor
---
#### Editor usergroup option
Since version 4.4 there is a new group option (admin panel > Users > Groups).

Under the green caption "Group" you will notice the option "Editor" that can be set to "YES".

Users who are connected to a group with "Editor" set to "YES" can log into the administration menu. They will not see the entire admin menu but only the HuMo-genealogy editor tab.

This way it is possible to allow certain family members of your choice to edit the family tree, without giving them additional admin privileges.

If you use a .htaccess file to log in to the administration menu (instead of the default PHP-MySQL login), then you have add a new username to the access file, called: 'editor'.

This username can be used to log in to the admin panel, but there will be only one option: Editor.
#### Text processing
If links are added in text fields, these will be changed into clickable links. You have to precede these links with: http:// or https://.

