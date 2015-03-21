// --- START these lines will be executed automatically when the js file is loaded.

// this is based on the original autorun code in this script
window.cookieVal = getStyleCookie("mysheet");
window.selectedtitle = (document.getElementById && cookieVal) ? cookieVal : defaultskin;
setStylesheet(window.selectedtitle);

StartOnReady(
  function(){
    // this was the inline onloadscript in sss1.php
    var el = document.getElementById("switchform");
    if (!el) return false;
    indicateSelected(el.switchcontrol,window.selectedtitle)
  }
);

//check if jQuery is loaded. 
try{
  var jQueryOk = jQuery; // If loaded, the jQuery object is set
  jQueryOk = true;
} catch(err) {
  var jQueryOk = false;
}
//check if mootools is loaded. 
try{
  var mooToolsOk = MooTools; // if loaded, the If so, the MooTools object is set
  mooToolsOk = true;
} catch(err) {
  var mooToolsOk = false;
}
// --- END these lines will be executed automatically when the js file is loaded.

function StartOnReady(func) {
  //use mootools if loaded
  if(mooToolsOk) {
    window.addEvent('domready', function() { func(); });
  //else use jquery if loaded
  } else if (jQueryOk) {
    $(function () { func(); });
  //else use w3c event listening if is feature
  } else if (window.addEventListener) { // W3C standard
      window.addEventListener('load', func, false); // NB **not** 'onload'
  //else use microsoft event listening if is feature
  } else if (window.attachEvent) { // Microsoft
      window.attachEvent('onload', func);
  } else {
  //else we're down to window.onload
    window.onload = function() { func(); };
  }
}

function getStyleCookie(Name) {
  var re=new RegExp(Name+"=[^;]+", "i"); //construct RE to search for target name/value pair
  if (document.cookie.match(re)) //if cookie found
    return document.cookie.match(re)[0].split("=")[1] //return its value
  return null
}

function setStyleCookie(name, value, days) {
  var expireDate = new Date()
  //set "expstring" to either future or past date, to set or delete cookie, respectively
  var expstring=(typeof days!="undefined")? expireDate.setDate(expireDate.getDate()+parseInt(days)) : expireDate.setDate(expireDate.getDate()+365)
  document.cookie = name+"="+value+"; expires="+expireDate.toGMTString()+"; path=/";
}

function deleteCookie(name){
  setStyleCookie(name, "moot")
}

function setStylesheet(title) {
  var i, cacheobj
  for(i=0; (cacheobj=document.getElementsByTagName("link")[i]); i++) {
    if(cacheobj.getAttribute("rel").indexOf("style") != -1 && cacheobj.getAttribute("title")) {
      cacheobj.disabled = true
      if(cacheobj.getAttribute("title") == title)
      cacheobj.disabled = false //enable chosen style sheet
    }
  }
}

function chooseStyle(styletitle, days){
  if (document.getElementById){
    setStylesheet(styletitle)
    setStyleCookie("mysheet", styletitle, days)
  }
}

function indicateSelected(element,selectedtitle){ //Optional function that shows which style sheet is currently selected within group of radio buttons or select menu
  if (!selectedtitle) return false;
  if (element.type==undefined | element.type=="select-one"){ //if element is a radio button or select menu
    var element = (element.type=="select-one") ? element.options : element
    for (var i=2; i<element.length; i++){ //tb edit i0=info text i1=default. Skip these
      if (element[i].value==selectedtitle){ //if match found between form element value and cookie value
        element[i].selected = (element[i].tagName=="OPTION") //if this is a select menu
        break
      }
    }
  }
}
