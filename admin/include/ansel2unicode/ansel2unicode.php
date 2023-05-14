<?php
/**
* A class that converts strings in ANSEL character set into Unicode (UTF-8).
*
* ANSEL is known as the American Library Association character set, and is until now
* (2009) still the main character set used in GEDCOM files, the standard format for
* exchanging genealogical data.
*
* The conversion is based on the work of Heiner Eichmann documented at his web page:
*      http://www.heiner-eichmann.de/gedcom/charintr.htm
* The conversion uses the mapping file that can be downloaded from here:
*      http://www.heiner-eichmann.de/gedcom/ans2uni.con.zip
*
* You are free to use this code and edit it to suit your needs, but let my parts of the
* commenting be intact. It is not legal to sell this piece of software.
* This class was published on my blog at http://www.gammelsaeter.com/
*
* @author Pål Gjerde Gammelsæter
* @license Free to use, illegal to sell.
* @version 2nd October 2009
*/
class Ansel2Unicode {
	// Huub: Commented this line because of errors in PHP 4, this line is not used???
	// private $_mapping;   // The character mappings

	/**
	* Constructor of the object.
	* Generates the table for ANSEL to Unicode mapping.
	* @param String $conversionFile  The name of the mapping file made by Heiner Eichmann.
	*                                 The file can be downloaded from this URL:
	*                                 http://www.heiner-eichmann.de/gedcom/ans2uni.con.zip
	*/
	public function __construct($conversionFile = 'include/ansel2unicode/ans2uni.con') {
		$temp_ini_file = 'mappings.ini';    // Name of temporary ini file

		/*
		if (file_exists($conversionFile)) {
			// Load file contents, convert into well-formed ini file for later parsing.
			// This is done because the original mapping file cannot be parsed by the
			// PHP function parse_ini_file.
			//print "<br>bef make temp ".date('h:i:s:u')."<br>";
			$file_contents = file_get_contents($conversionFile, 'FILE_BINARY'); // Load contents
			$file_contents = $this->stripComments($file_contents, '#');         // Strip comments
			file_put_contents($temp_ini_file, $file_contents);                  // Save contents
			//print "<br>after make temp ".date('h:i:s:u')."<br>";
		*/

		// Get ini contents
		$map = parse_ini_file($temp_ini_file);            // Parse ini file

		// Go through map to split up the mappings that contain more characters in the key,
		// so that mappings with one character goes into $this->mapping[1], those with two
		// characters goes to $this->mapping[2] etc.
		foreach ($map as $key => $value) {
			$characters = explode('+', $key);             // Split string where '+' occurrs
			$num_chars = count($characters);              // Count number of characters
			$this->_mapping[$num_chars][strtolower($key)] = $value;// Put mapping in right place
		}
		/*
		//print "<br>after mapsplit ".date('h:i:s:u')."<br>";
		// Delete temporary ini file efterwards if exists
		if (file_exists($temp_ini_file)) { unlink($temp_ini_file); }
		}
		else {
			echo '<p>No mapping file with name '.$conversionFile.' exists. Download this file '.
			'from <a href="http://www.heiner-eichmann.de/gedcom/ans2uni.con.zip">here</a>.';
		}
		*/
	}


	/**
	* Loops through string to look for matches in the mapping table and replaces the
	* characters that has a mapping. This will convert the given string to Unicode/UTF-8.
	*
	* @param String $string    String to convert to Unicode (ÙTF-8)
	* @return String
	*/
	public function convert($string) {
		$i          = 0;                                // Initialize counter in loop
		$output     = '';                               // String to output

		// Go through string, fetch next character, next two characters and next three characters
		// to check against mapping table if mapping exist.

		$convertit=0; // default - don't use the ANSEL conversion

		for($a=224; $a<=255; $a++) {  // look for special ANSEL diacritics in range chr(224) till chr (255)
			if(strpos($string,chr($a)) != false ) {
				$convertit=1; // there is at least one special diacritic - we need to do ANSEL conversion
				break;  // no need to look for further diacritics - we have to do ANSEL conversion anyway
			}
		}

		if($convertit==0) { $output=$string; }  // no diacritic in the string no need for ANSEL conversion

		while ($i <= (strlen($string) - 1) AND $convertit==1) { // do special ANSEL conversion for diacritics
			$remains = strlen($string) - $i;            // Characters that remains in string
			$key = array();                             // Initialize array

			// Get next, next two and next three characters (if number of remaing
			// characters allow it)
			if ($remains >= 3) { $key[3] = $this->getKeyMap($string, $i, 3); }
			if ($remains >= 2) { $key[2] = $this->getKeyMap($string, $i, 2); }
			if ($remains >= 1) { $key[1] = $this->getKeyMap($string, $i, 1); }

			// Check if next three characters exist in mapping, and replace them if they do
			if (count($key) == 3 && array_key_exists($key[3], $this->_mapping[3])) {
				$output .= chr(hexdec($this->_mapping[3][$key[3]]));
				$i += 3; // We mapped three bytes into one char, jump three forward for next loop
			}
			// Check if next two characters exist in mapping, and replace them if they do
			elseif (count($key) >= 2 && array_key_exists($key[2], $this->_mapping[2])) {
				$output .= chr(hexdec($this->_mapping[2][$key[2]]));
				$i += 2;
			}
			// Check if next character exist in mapping, and replace it if it does
			elseif (count($key) >= 1 && array_key_exists($key[1], $this->_mapping[1])) {
				$output .= chr(hexdec($this->_mapping[1][$key[1]]));
				$i++;
			}
			// No mapping found, just return the character we have
			else {
				$output .= chr(hexdec($key[1]));
				$i++;
			}
		}

		return utf8_encode($output); // Return the string with replacements
	}


	/**
	* Format a string to same format as the keys in mapping table. The characters are
	* formatted as hexadecimal and separated with a plus sign. Example:
	* "Gon" -> "47+6f+6e"
	*
	* @param String  $string  The string witch contains the characters to convert
	* @param int     $start   Start position for the characters to convert
	* @param int     $length  Number of characters to convert
	* @return String
	*/
	private function getKeyMap($string, $start, $length = 1) {
		$array = str_split(substr($string, $start, $length));
		foreach ($array as $key => $value) { $array[$key] = dechex(ord($value)); }
		return implode('+', $array);
	}


	/**
	* Strips comments off the end of each line in given string.
	*
	* @param String    $data           String where comments are being removed
	* @param String    $comment_char   The character indicating start of a comment
	* @return String[] The array of strings where comments are removed
	*/
	private function stripComments($string, $comment_char = '#') {
		$array = explode("\n", $string);                            // Split each line into array
		$return = array();
		foreach ($array as $i => $line) {
			$pos = strpos($line, $comment_char);                   // Get postition of first '#'
			if ($pos > 0) { $return[$i] = substr($line, 0, $pos); }// Get part before '#'
			else { $return[$i] = $line; }                          // No '#' found
		}
		return implode("\n", $return);                               // Merge together as a string
	}
}

?>