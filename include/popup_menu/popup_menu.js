// Original: Copyright 2006-2007 javascript-array.com
// Updates: 2010 Huub Mons.
//          2014 Ian Roxburgh, fix operation on android touch devices, simplify getting mouse position

// x    y    explanation:
// 0    0    standard popup (near mouse click)
// 10   150  show pop-up at position 10,150
// ?    ?    no position calculations.

var timeout	= 500;
var closetimer	= 0;
var ddmenuitem	= 0;
var target = null;

function getTarget(e){
	var tgt;
	var ev=(!e)?window.event:e;
	if (ev.target) {tgt = ev.target;}
	else{
		if (ev.srcElement){
			tgt = ev.srcElement;
		}
		else{
			return null;
		}
	}
	if (tgt.nodeType == 3) // if text node, we want its parent, the element containing the text node
		tgt = tgt.parentNode;
	return tgt;
}


function clickFunc(e){
	var ev=(!e)?window.event:e;
	//Prevent default action ie going to link etc
	if (ev.stopPropagation)    ev.stopPropagation();
	if (ev.cancelBubble!=null) ev.cancelBubble = true;
	if (ev.preventDefault) ev.preventDefault();
	ev.returnValue = false;
	mcancelclosetime();     //if clicked or touched, don't close after a timeout
	getTarget(e).onclick = null;   /////// !!! uncapture click or Android Chrome acts strangely next time link is clicked
	return false;
}

// open hidden layer Huub: e=for FF mouse position.
function mopen(e,id,x,y){
	// cancel close timer
	mcancelclosetime();
   
	// close old layer
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';

	// get new layer and show it
	ddmenuitem = document.getElementById(id);
	ddmenuitem.style.visibility = 'visible';
	target = getTarget(e);
	target.onclick = clickFunc;   //temporarily capture click

	var ev=(!e)?window.event:e;//OldIE:Moz

	if(x==0  && y==0) {
		var posx = 0;
		var posy = 0;
		if (ev.clientX && ev.clientY) 	{
			posx = ev.clientX;
			posy = ev.clientY;
		}
		// calculate height of the popup menu
		var divHeight;
		if(ddmenuitem.offsetHeight) {divHeight=ddmenuitem.offsetHeight;}
		else if(ddmenuitem.style.pixelHeight){divHeight=ddmenuitem.style.pixelHeight;}
    
		var divWidth;
		if(ddmenuitem.offsetWidth) {divWidth=ddmenuitem.offsetWidth;}
		else if(ddmenuitem.style.pixelWidth){divWidth=ddmenuitem.style.pixelWidth;}
		if(posx != 0 || posy != 0)
		{
			var win_width = (window.innerWidth != null) ? window.innerWidth : document.body.clientWidth;       //clientWidth for old IE
			var win_height = (window.innerHeight != null) ? window.innerHeight : document.body.clientHeight - 20;  //-20, allow for status bar

			posx+=15;
			if(posx>600)    //if towards right of screen, show to left of mouse (600 is a bit arbitrary !!!)
				posx-=(divWidth+30);
                
				if((posx + divWidth) > win_width) {
				//menu would fall off the screen...	
				posx=win_width - divWidth;
				if(posx < 0)
					posx = 0;
			}
			if((posy + divHeight) > win_height) {
				//menu would fall off the screen...	
				posy=win_height - divHeight;
				if(posy < 0)
					posy = 0;
			}
            
			ddmenuitem.style.left=posx + 'px';
			ddmenuitem.style.top=posy + 'px';
		}
		//else don't do anything leave it to css, the position should be not bad
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
	if(ddmenuitem){
		ddmenuitem.style.visibility = 'hidden';
		ddmenuitem = null;
		target = null;
	}
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

function docClick(e)
{
	// close layer when click-out
	var targ = getTarget(e);
	if(targ == target)
		return;   //ignore unwanted docClick on link
	if(targ != ddmenuitem)   //ignore click on the menu outside active elements
		mclose();    //otherwise close the menu
}
//##### Note this ....
document.onclick = docClick;