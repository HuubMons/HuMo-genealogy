<!--

/*********************************/
/*         FONTSIZE.JS           */
/*     Yossi Beck - May 2009     */
/*     for use with HuMo-gen     */
/*********************************/

function getCookie(NameOfCookie){
   if (document.cookie.length > 0){
      begin = document.cookie.indexOf(NameOfCookie+"="); 
      if (begin != -1) {
         begin += NameOfCookie.length+1; 
         end = document.cookie.indexOf(";", begin);
         if (end == -1){
           end = document.cookie.length;
         }
         return unescape(document.cookie.substring(begin, end)); 
      } 
   }
   return null; 
}
 
function setCookie(direction){
    factor=getCookie('yobemagnitude');
    if (factor==null){
      factor="0";
    }
    multi=parseInt(factor); 
    if(direction=='plus') { multi+=1; }
    else {multi-=1; }
  
    var exp = new Date();      
    exp.setTime(exp.getTime() + (1000 * 60 * 60 * 24 * 365));      
    /* cookie is set to expire in a year */
      
    document.cookie='yobemagnitude='+escape(multi)+';expires='+exp.toGMTString()+';path=/';
    /* alert("TEST: cookievalue= "+multi); */
}

function checkCookie(){
  magnitude=getCookie('yobemagnitude');
  if (magnitude!=null)  {
    multi=parseInt(magnitude);  
    if (multi>0)   {
      increaseFontSize(multi);
    }
    else if (multi<0)  {
      decreaseFontSize(multi);
    }
  } 
}

function delCookie() {
document.cookie = "yobemagnitude=; expires=Thu, 01 Jan 1970 00:00:01 GMT;path=/";     
location.reload(true);
} 

document.getElementsByClassName = function(cl) {
var retnode = [];
var myclass = new RegExp('\\b'+cl+'\\b');
var elem = this.getElementsByTagName('*');
for (var i = 0; i < elem.length; i++) {
var classes = elem[i].className;
if (myclass.test(classes)) retnode.push(elem[i]);
}
return retnode;
}; 

function increaseFontSize(magnitude)  {   
  var p = document.getElementsByClassName('fonts');
  var inc;
  if(magnitude==0) { inc=1;}
  else { inc=magnitude; }
   for(i=0;i<p.length;i++) {   
      // FOR FIREFOX AND ALL NORMAL BROWSERS
      if (document.defaultView && document.defaultView.getComputedStyle) { 
       var sty=document.defaultView.getComputedStyle(p[i], "");  
       var s = parseInt(sty.fontSize.replace("px","")); s+=inc; p[i].style.fontSize = s+"px"; 
      } 
      // FOR INTERNET EXPLORER
      else if(p[i].currentStyle.fontSize) {  
       if(p[i].currentStyle.fontSize=="xx-small") {p[i].style.fontSize = 10+inc+"px"}
       else if(p[i].currentStyle.fontSize=="x-small") {p[i].style.fontSize = 12+inc+"px"}
       else if(p[i].currentStyle.fontSize=="small") { p[i].style.fontSize = 14+inc+"px"}
       else if(p[i].currentStyle.fontSize=="medium") {p[i].style.fontSize = 16+inc+"px"}
       else if(p[i].currentStyle.fontSize=="large") {p[i].style.fontSize = 18+inc+"px"}
       else if(p[i].currentStyle.fontSize=="x-large") {p[i].style.fontSize = 21+inc+"px"}
       else if(p[i].currentStyle.fontSize=="xx-large") {p[i].style.fontSize = 24+inc+"px"}
       else if(p[i].currentStyle.fontSize.search("%")!=-1) { var s = parseInt(p[i].currentStyle.fontSize.replace("%","")); s+=(inc*10); p[i].style.fontSize = s+"%" }      
       else if(p[i].currentStyle.fontSize.search("px")!=-1) { var s = parseInt(p[i].currentStyle.fontSize.replace("px","")); s+=inc; p[i].style.fontSize = s+"px" } 

      } // END if currentstyle
            

    } // END for loop

      // if button is used (no page load) - increase cookie:
      if (magnitude=="0") { setCookie('plus'); }
} // END function

function decreaseFontSize(magnitude) {
   var p = document.getElementsByClassName('fonts');
   var inc;
  if(magnitude==0) { inc= -1;}
  else { inc=magnitude; }

   for(i=0;i<p.length;i++) {

      /* FOR FIREFOX AND ALL NORMAL BROWSERS */
      if (document.defaultView && document.defaultView.getComputedStyle) { 
       var sty=document.defaultView.getComputedStyle(p[i], "");  
       var s = parseInt(sty.fontSize.replace("px","")); s+=inc; p[i].style.fontSize = s+"px"; 
      } 

       /* FOR INTERNET EXPLORER */
      else if(p[i].currentStyle.fontSize) {
       if(p[i].currentStyle.fontSize=="xx-small") {p[i].style.fontSize = 5+inc+"px"}
       else if(p[i].currentStyle.fontSize=="x-small") {p[i].style.fontSize = 7+inc+"px"}
       else if(p[i].currentStyle.fontSize=="small") { p[i].style.fontSize = 9+inc+"px"}
       else if(p[i].currentStyle.fontSize=="medium") {p[i].style.fontSize = 11+inc+"px"}
       else if(p[i].currentStyle.fontSize=="large") {p[i].style.fontSize = 13+inc+"px"}
       else if(p[i].currentStyle.fontSize=="x-large") {p[i].style.fontSize = 16+inc+"px"}
       else if(p[i].currentStyle.fontSize=="xx-large") {p[i].style.fontSize = 19+inc+"px"}
       else if(p[i].currentStyle.fontSize.search("%")!=-1) { var s = parseInt(p[i].currentStyle.fontSize.replace("%","")); s+=(inc*10); p[i].style.fontSize = s+"%" }      
       else if(p[i].currentStyle.fontSize.search("px")!=-1) { var s = parseInt(p[i].currentStyle.fontSize.replace("px","")); s+=inc; p[i].style.fontSize = s+"px" } 

      }
   }   
   /* if button is used (no page load) - decrease cookie: */
      if (magnitude=="0") { setCookie('min'); }  
}   

//-->
