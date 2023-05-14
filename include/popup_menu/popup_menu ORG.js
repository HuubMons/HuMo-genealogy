// Original: Copyright 2006-2007 javascript-array.com
// Updates: 2010 Huub Mons.

// x    y    explanation:
// 0    0    standard popup
// 10   150  show pop-up at position 10,150
// ?    ?    no position calculations.

var posx;var posy; // Mouse coordinates
var timeout	= 500;
var closetimer	= 0;
var ddmenuitem	= 0;
 
// open hidden layer Huub: e=for FF mouse position.
function mopen(e,id,x,y){
	// cancel close timer
	mcancelclosetime();

	// close old layer
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';

	// get new layer and show it
	ddmenuitem = document.getElementById(id);
	ddmenuitem.style.visibility = 'visible';
	if(x==0  && y==0) {
		//function getMouse(e){
		posx=0;posy=0;
		var ev=(!e)?window.event:e;//IE:Moz
		if (ev.screenX && ev.screenY){

			// calculate height of the popup menu
			var divHeight;
			if(ddmenuitem.offsetHeight) {divHeight=ddmenuitem.offsetHeight;}
			else if(ddmenuitem.style.pixelHeight){divHeight=ddmenuitem.style.pixelHeight;}
		
			var divWidth;
			if(ddmenuitem.offsetWidth) {divWidth=ddmenuitem.offsetWidth;}
			else if(ddmenuitem.style.pixelWidth){divWidth=ddmenuitem.style.pixelWidth;}

			var ua = navigator.userAgent.toLowerCase(); //detect browser

			// calculate X/Y position relative to browser window on the screen
			if(window.location == window.parent.location) { // not in iframe
				posx=ev.screenX;
				//posx=ev.pageX;
				if(ua.indexOf( "msie" ) == -1) { // NOT IE
					posx-=window.screenX;
				}
				else { //IE
					posx-=window.screenLeft;
				}
			}
			else { // in iframe
				//posx = window.event.clientX + document.body.scrollLeft;
				posx = window.event.clientX;				
			}
			posx+=15;		
			if(posx>600) {
				posx-=(divWidth+30);
			}
			posx+='px';

			if(window.location == window.parent.location) { // not in iframe
				posy=ev.screenY;
				//posy=ev.pageY;
				if(ua.indexOf( "msie" ) == -1) { //NOT IE
					var diff; 			 
					diff=window.outerHeight-window.innerHeight;	// diff = the menu bar area above (and below!) the viewport	
		
					if(diff>5) { // we are not in fullscreen mode			     
						posy-=(window.screenY+diff); //window.screenY (top of total browser window)  +  menubar area = top of viewport
						if(ua.indexOf( "chrome" ) == -1) { posy+=30} // if not chrome, make up for bottom bar (chrome doesn't have one...)
					}  // else - we're in fullscreen mode so posy doen't have to change
					if((posy + divHeight) > window.innerHeight) {
						//menu would fall off the screen...	
						posy=window.innerHeight - divHeight;
						if(ua.indexOf( "chrome" ) != -1) { posy-=20;}  //chrome's popup url bar blocks view....
					}
				}
				else { //IE			
					posy-=window.screenTop; // screenTop = top of viewport			
					if((posy + divHeight) > (document.documentElement.clientHeight)) { 
						//menu would fall off the screen...				
						posy=document.documentElement.clientHeight - divHeight;
					}
				}                 
			}
			else { // in iframe
				posy = window.event.clientY;
				if(ua.indexOf( "msie" ) == -1) { //NOT IE
					if((posy + divHeight) > window.innerHeight) {
						//menu would fall off the screen...	
						posy=window.innerHeight - divHeight;
						if(ua.indexOf( "chrome" ) != -1) { posy-=20;}  //chrome's popup url bar blocks view....
					}
				}
				else { // IE
					if((posy + divHeight) > (document.documentElement.clientHeight)) { 
						//menu would fall off the screen...				
						posy=document.documentElement.clientHeight - divHeight;
					}
				}
			}
			posy+='px';
		}
	
		ddmenuitem.style.left=posx;
		ddmenuitem.style.top=posy; 
		if(ua.indexOf( "opera" ) != -1) { // Opera doesn't handle screenX/Y so it misses out...
			ddmenuitem.style.left="400px";
			ddmenuitem.style.top="200px";
		}
	}
	else if (x=='?' && y=='?') {
		// don't do anything CSS will take care...
	}
	else { // x and y are not 0 this means: absolute positioning
		ddmenuitem.style.left=x + 'px';
		ddmenuitem.style.top=y + 'px';
	}

	ddmenuitem.style.textIndent="0em";
}

// close showed layer
function mclose(){
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
}

// go close timer
function mclosetime(){
	closetimer = window.setTimeout(mclose, timeout);
}

// cancel close timer
function mcancelclosetime(){
	if(closetimer){
		window.clearTimeout(closetimer);
		closetimer = null;
	}
}

// close layer when click-out
document.onclick = mclose;