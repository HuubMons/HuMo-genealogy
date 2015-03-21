<?php
//********************************************************************************************//
//   this is a free script                                                                 ***//
//   for the origin of this script see: http://www.phpf1.com/product/php-chart-script.html ***//
//   this script has been revised for Humogen by Yossi Beck                                ***//
//********************************************************************************************//
class maxChart {
	var $data;         // The data array to display
	var $type = 1;     // Vertical:1 or Horizontal:0 chart
	var $title;        // The title of the chart
	var $width = 300;  // The chart box width
	var $height = 200; // The chart box height
	var $metaSpaceHorizontal = 60; // Total space needed for chart title + bar title + bar value
	var $metaSpaceVertical = 60; // Total space needed for chart title + bar title + bar value
	var $variousColors = false;

	function maxChart($data){
			$this->data = $data;
	}

	function displayChart($title='', $type, $width=300, $height=200, $variousColor=false, $thismonth=false){
		$this->type   = $type;
		$this->title  = $title;
		$this->width  = $width;
		$this->height = $height;
		$this->variousColors = $variousColor;
		$this->thismonth = $thismonth;
		//echo '<div class="chartbox" style="width:'.$this->width.'px; height:'.$this->height.'px;"> <h2>'.$this->title.'</h2>'."\r\n";
		//echo '<div class="chartbox" style="width:100%; height:'.$this->height.'px;"> <h2>'.$this->title.'</h2>'."\r\n";
		if(CMS_SPECIFIC == "Joomla") {
			$chartwidth = "1000px";
		}
		else {
			// $chartwidth = "100%";
			$chartwidth = "80%";
		}
		echo '<div class="chartbox" style="width:'.$chartwidth.'; height:'.$this->height.'px;"> <h2>'.$this->title.'</h2>'."\r\n";
		if ($this->type == 1)  $this->drawVertical();
		else $this->drawHorizontal();
		echo '    </div>';
	}

	function getMaxDataValue(){
		$max = 0;
		foreach ($this->data as $key=>$value) {
			if ($value > $max) $max = $value;
		}
		return $max;
	}

	function getElementNumber(){
		return sizeof($this->data);
	}

	function drawVertical(){
		// Huub: @ toegevoegd, anders komt er een error als er geen waarden zijn
		@$multi = ($this->height -$this->metaSpaceHorizontal) / $this->getMaxDataValue();
		$max   = $multi * $this->getMaxDataValue();
		$barw  = floor($this->width / $this->getElementNumber()) - 5;
		$valuetextclass="barvvalue";
		if($this->getMaxDataValue() > 99 AND $this->getMaxDataValue() < 1000)  { $valuetextclass="barvvalue11"; }
		if($this->getMaxDataValue() > 999 AND  $this->getMaxDataValue() < 10000) { $valuetextclass="barvvalue11"; $barw += 4; }
		if($this->getMaxDataValue() > 9999 AND $this->getMaxDataValue() < 100000) { $valuetextclass="barvvalue10"; $barw +=10 ;}
		if($this->getMaxDataValue() > 99999) { $valuetextclass="barvvalue9";  $barw += 12;}
		$i = 1;
		foreach ($this->data as $key=>$value) {
			$b = floor($max - ($value*$multi));
			$a = $max - $b;
			if ($this->variousColors) $color = ($i % 4) + 1;
			else $color = 3;
			$i++;
			echo '  <div class="barv">'."\r\n";
			//echo '    <div class="barvvalue" style="margin-top:'.$b.'px; width:'.$barw.'px;">'.$value.'</div>'."\r\n";
			echo '    <div class="'.$valuetextclass.'" style="margin-top:'.$b.'px; width:'.$barw.'px;">'.$value.'</div>'."\r\n";
													echo '    <div><img src="'.CMS_ROOTPATH_ADMIN.'statistics/images/bar'.$color.'.png" style="width:'.$barw.'px; height:'.$a.'px;" alt="bar"> </div>'."\r\n";
			$today=date("d"); $dezedag=$key; if($key<10) {$dezedag="0".$key;}
			if($today==$dezedag AND $this->thismonth==true) {
				echo '    <div class="barvvaluevandaag" style="width:'.$barw.'px;">'.$key.'</div>'."\r\n";
			}
			else {
				echo '    <div class="barvvalue" style="width:'.$barw.'px;">'.$key.'</div>'."\r\n";
			}
			echo '  </div>'."\r\n";
		}
	}

	function drawHorizontal(){
		$multi = ($this->width-170) / $this->getMaxDataValue();
		$max   = $multi * $this->getMaxDataValue();
		$barh  = floor(($this->height - 35) / $this->getElementNumber());
		$i = 1;
		foreach ($this->data as $key=>$value) {
			$b = floor($value*$multi);
			if ($this->variousColors) $color = ($i % 5) + 1;
			else $color = 1;
			$i++;
			echo '  <div class="barh" style="height:'.$barh.'px;">'."\r\n";
			echo '    <div class="barhcaption" style="line-height:'.$barh.'px; width:90px;">'.$key.'</div>'."\r\n";
			echo '    <div class="barhimage"><img src="'.CMS_ROOTPATH_ADMIN.'statistics/images/barh'.$color.'.png" style="width:'.$b.'px; height:'.$barh.'px;" alt="barh"></div>'."\r\n";
			echo '    <div class="barhvalue" style="line-height:'.$barh.'px; width:30px;">'.$value.'</div>'."\r\n";
			echo '  </div>';
		}
	}

}
?>