---
layout: page
title: Cookies
---
According to recent European legislation websites are obliged to notify visitors about the use of cookies by the site.

The legislation was initiated to curb the use of cookies that collect data about the visitor's actions for marketing analysis and similar purposes. The legislation demands that prior to implementation of cookies by a web site, the website has to notify the visitor of the character of the cookies and get the visitor's agreement (by a confirmation click) before placing them.

The legislation makes an exception for cookies that are vital for the proper use of the site (an example is a sales site that uses a cookie to store items that you place in your "shopping cart" so they will be available when you move to the "checkout").

## HuMo-genealogy cookies

HuMo-genealogy uses a few cookies that are only used so that settings that the visitors made can be stored on their computer for their future use.

The cookies that are created by HuMo-genealogy are **not** used for any other purpose, are **not** transferred to others and the information they contain is used by **the visitor alone**.

Therefore no confirmation is asked of the visitor.

However, in order to comply with the obligation to notify visitors of the use of cookies, HuMo-genealogy (from version 4.8.4) does the following:

- The "Help" menu item contains a "HuMo-genealogy cookies" item that explains the character of the HuMo-genealogy cookies.
    
- On the bottom of the main entry page a link was placed to the above Help -> HuMo-genealogy cookies item.
    

HuMo-genealogy cookies are used for these purposes:

- The user chooses a theme (skin) that is different from the default. The theme he chose will be used next time he visits the HuMo-genealogy site.
    
- The user used the star to mark a family as "favorite". This family will appear on his favorite list on future visits as well.
    
- In the photo album the user set the number of photos to be displayed at a different number than the default. This number will be used next time he visits.
    
- The user changed the font size with the A+A- buttons. HuMo-genealogy will be displayed with this font size next time he visits.
    

If a user does not want HuMo-genealogy to create these cookies, he can just refrain from changing the default values for the above features.

## Cookies added to HuMo-genealogy by the webmaster of a site

The above section deals with the cookies that are used by an original version of HuMo-genealogy.

If a webmaster who installed HuMo-genealogy on his website, decides to manually add cookies in addition to those that exist in the original HuMo-genealogy version, **it is the explicit responsibility of that webmaster to change the terminology and the display of the cookie notifications needed to comply with the European legislation**. It is very likely that addition of third party cookies obliges the webmaster to obtain explicit confirmation of the visitor before implementing these cookies.

HuMo-genealogy can in no way be held responsible for legal actions taken against a webmaster who added cookies without taking the proper actions to comply with the European law.

On the web one can find numerous scripts that can be added to existing websites in order to place the necessary cookie notifications and confirmation requests. In most cases these scripts can be added to the HuMo-genealogy code without major changes.

Following is an example of implementing one of those free scripts.

**Please note**: this is an example only. The content and display of these notification scripts differ according to the character of the cookies employed and should be verified by the webmaster to make sure they comply with the relevant legislation.

In the file **header.php**, towards the end, just before the line with:

```
if (!CMS_SPECIFIC){  
print "</head>\n";  
```
  

place the following piece of code:

```
?>  
<style type="text/css">  
<!--  
#eucookielaw { display:none }  
-->  
</style>  
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js" type="text/javascript"></script>  
<?php  
if(!isset($_COOKIE['eucookie']))  
{ ?>  
<script type="text/javascript">  
function SetCookie(c_name,value,expiredays)  
{  
var exdate=new Date()  
exdate.setDate(exdate.getDate()+expiredays)  
document.cookie=c_name+ "=" +escape(value)+";path=/"+((expiredays==null) ? "" : ";expires="+exdate.toGMTString())  
}  
</script>  
<?php }  
```
  

Now, in the file **menu.php,** at the very end just before the last ?> add this:

```
if(!isset($_COOKIE['eucookie']))  
{ ?>

<div id="eucookielaw" >  
<p><b>This site uses cookies. </b>  
<a href="#" id="removecookie"><u>Confirm</u></a>  
<a href="http://****mydomain****/cookienotification.html"><u><i>More information</i></u></a>  
</div>  

<script type="text/javascript">  
if( document.cookie.indexOf("eucookie") ===-1 ){  
$("#eucookielaw").show();  
}  
$("#removecookie").click(function () {  
SetCookie('eucookie','eucookie',365*10)  
$("#eucookielaw").remove();  
});  
</script>  
<?php }  
  
```

In the above piece of code, replace ****mydomain**** with the name of your domain and in the main folder on your domain place a file cookienotification.html with all necessary notifications about the cookies you use on your site.

