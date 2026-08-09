---
layout: page
title: Usernames and passwords
---
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

## Reset admin password

Best way to reset the admin password at this moment if password is lost:  

* Open PHPMyAdmin  
* Open table humo_users  
* Go to the admin user line (or the user name with admin rights)  
* If there is a field "user_password_salted", empty this field.  
* Change user_password into: 712697cdade1e78580bf26e564a891f5  

This will reset the password to the default password: humogen