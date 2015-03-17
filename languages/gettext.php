<?php
// Global variable
$l10n = array();

/*
Script from: http://www.phphulp.nl/php/tutorial/overig/php-meertalige-applicatie/574/

Copyright (c) 2003, 2005 Danilo Segan <danilo@kvota.net>.

This file is part of PHP-gettext.

PHP-gettext is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

PHP-gettext is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with PHP-gettext; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Simple class to wrap file streams, string streams, etc.
// seek is essential, and it should be byte stream

class StringReader {
	protected $_pos;
	protected $_str;

	function __construct($str='') {
		$this->_str = $str;
		$this->_pos = 0;
	}

	function read($bytes) {
		$data = substr($this->_str, $this->_pos, $bytes);
		$this->_pos += $bytes;
		if (strlen($this->_str)<$this->_pos)
		$this->_pos = strlen($this->_str);

		return $data;
	}

	function seekto($pos) {
		$this->_pos = $pos;
		if (strlen($this->_str)<$this->_pos)
		$this->_pos = strlen($this->_str);
		return $this->_pos;
	}

	function currentpos() {
		return $this->_pos;
	}

	function length() {
		return strlen($this->_str);
	}
}

class FileReader {
	protected $_pos;
	protected $_fd;
	protected $_length;

	function __construct($filename) {
		if (file_exists($filename)) {
			$this->_length=filesize($filename);
			$this->_pos = 0;
			$this->_fd = fopen($filename,'rb');
			if (!$this->_fd) {
				$this->error = 3; // Cannot read file, probably permissions
				return false;
			}
		} else {
			$this->error = 2; // File doesn't exist
			return false;
		}
	}

	function read($bytes) {
		$data = '';
		if ($bytes) {
			fseek($this->_fd, $this->_pos);

			// PHP 5.1.1 does not read more than 8192 bytes in one fread()
			// the discussions at PHP Bugs suggest it's the intended behaviour
			while ($bytes > 0) {
				$chunk  = fread($this->_fd, $bytes);
				$data  .= $chunk;
				$bytes -= strlen($chunk);
			}
			$this->_pos = ftell($this->_fd);

			return $data;
		} else return '';
	}

	function seekto($pos) {
		fseek($this->_fd, $pos);
		$this->_pos = ftell($this->_fd);
		return $this->_pos;
	}

	function currentpos() {
		return $this->_pos;
	}

	function length() {
		return $this->_length;
	}

	function close() {
		fclose($this->_fd);
	}
}

// Preloads entire file in memory first, then creates a StringReader
// over it (it assumes knowledge of StringReader internals)

class CachedFileReader extends StringReader {
	function CachedFileReader($filename) {
		if (file_exists($filename)) {
			$length=filesize($filename);
			$fd = fopen($filename,'rb');

			if (!$fd) {
				$this->error = 3; // Cannot read file, probably permissions
				return false;
			}
			$this->_str = fread($fd, $length);
			fclose($fd);
		} else {
			$this->error = 2; // File doesn't exist
			return false;
		}
	}
}

/**
* Provides a simple gettext replacement that works independently from
* the system's gettext abilities.
* It can read MO files and use them for translating strings.
* The files are passed to gettext_reader as a Stream (see streams.php)
*
* This version has the ability to cache all strings and translations to
* speed up the string lookup.
* While the cache is enabled by default, it can be switched off with the
* second parameter in the constructor (e.g. whenusing very large MO files
* that you don't want to keep in memory)
*/

class gettext_reader {

	//public:
	public $error = 0; // public variable that holds error code (0 if no error)

	//private:
	private $BYTEORDER = 0;        // 0: low endian, 1: big endian
	private $STREAM = NULL;
	private $short_circuit = false;
	private $enable_cache = false;
	private $originals = NULL;      // offset of original table
	private $translations = NULL;    // offset of translation table
	private $pluralheader = NULL;    // cache header field for plural forms
	private $select_string_function = NULL; // cache function, which chooses plural forms
	private $total = 0;          // total string count
	private $table_originals = NULL;  // table for original strings (offsets)
	private $table_translations = NULL;  // table for translated strings (offsets)
	private $cache_translations = NULL;  // original -> translation mapping

	/* Methods */

	/**
	* Constructor
	*
	* @param object Reader the StreamReader object
	* @param boolean enable_cache Enable or disable caching of strings (default on)
	*/

	function __construct($Reader, $enable_cache = true) {
		// If there isn't a StreamReader, turn on short circuit mode.
		if (! $Reader || isset($Reader->error) ) {
			$this->short_circuit = true;
			return;
		}

		// Caching can be turned off
		$this->enable_cache = $enable_cache;

		// $MAGIC1 = (int)0x950412de; //bug in PHP 5.0.2, see https://savannah.nongnu.org/bugs/?func=detailitem&item_id=10565
		$MAGIC1 = (int) - 1794895138;
		// $MAGIC2 = (int)0xde120495; //bug
		$MAGIC2 = (int) - 569244523;
		// 64-bit fix
		$MAGIC3 = (int) 2500072158;

		$this->STREAM = $Reader;
		$magic = $this->readint();
		if ($magic == ($MAGIC1 & 0xFFFFFFFF) || $magic == ($MAGIC3 & 0xFFFFFFFF)) { // to make sure it works for 64-bit platforms
			$this->BYTEORDER = 0;
		} elseif ($magic == ($MAGIC2 & 0xFFFFFFFF)) {
			$this->BYTEORDER = 1;
		} else {
			$this->error = 1; // not MO file
			return false;
		}

		// FIXME: Do we care about revision? We should.
		$revision = $this->readint();

		$this->total = $this->readint();
		$this->originals = $this->readint();
		$this->translations = $this->readint();
	}

	/**
	* Reads a 32bit Integer from the Stream
	*
	* @access private
	* @return Integer from the Stream
	*/

	function readint() {
		if ($this->BYTEORDER == 0) {
			// low endian
			$low_end = unpack('V', $this->STREAM->read(4));
			return array_shift($low_end);
		} else {
			// big endian
			$big_end = unpack('N', $this->STREAM->read(4));
			return array_shift($big_end);
		}
	}

	/**
	* Reads an array of Integers from the Stream
	*
	* @param int count How many elements should be read
	* @return Array of Integers
	*/

	function readintarray($count) {
		if ($this->BYTEORDER == 0) {
			// low endian
			return unpack('V'.$count, $this->STREAM->read(4 * $count));
		} else {
			// big endian
			return unpack('N'.$count, $this->STREAM->read(4 * $count));
		}
	}

	/**
	* Loads the translation tables from the MO file into the cache
	* If caching is enabled, also loads all strings into a cache
	* to speed up translation lookups
	*
	* @access private
	*/

	function load_tables() {
		if (is_array($this->cache_translations) &&
		is_array($this->table_originals) &&
		is_array($this->table_translations))
		return;

		/* get original and translations tables */
 		$this->STREAM->seekto($this->originals);
		$this->table_originals = $this->readintarray($this->total * 2);
		$this->STREAM->seekto($this->translations);
		$this->table_translations = $this->readintarray($this->total * 2);

		if ($this->enable_cache) {
			$this->cache_translations = array ();

			/* read all strings in the cache */
 			for ($i = 0; $i < $this->total; $i++) {
				$this->STREAM->seekto($this->table_originals[$i * 2 + 2]);
				$original = $this->STREAM->read($this->table_originals[$i * 2 + 1]);
				$this->STREAM->seekto($this->table_translations[$i * 2 + 2]);
				$translation = $this->STREAM->read($this->table_translations[$i * 2 + 1]);
				$this->cache_translations[$original] = $translation;
			}
		}
	}

	/**
	* Returns a string from the "originals" table
	*
	* @access private
	* @param int num Offset number of original string
	* @return string Requested string if found, otherwise ''
	*/

	function get_original_string($num) {
		$length = $this->table_originals[$num * 2 + 1];
		$offset = $this->table_originals[$num * 2 + 2];
		if (! $length)
		return '';
		$this->STREAM->seekto($offset);
		$data = $this->STREAM->read($length);
		return (string)$data;
	}

	/**
	* Returns a string from the "translations" table
	*
	* @access private
	* @param int num Offset number of original string
	* @return string Requested string if found, otherwise ''
	*/

	function get_translation_string($num) {
		$length = $this->table_translations[$num * 2 + 1];
		$offset = $this->table_translations[$num * 2 + 2];
		if (! $length)
		return '';
		$this->STREAM->seekto($offset);
		$data = $this->STREAM->read($length);
		return (string)$data;
	}

	/**
	* Binary search for string
	*
	* @access private
	* @param string string
	* @param int start (internally used in recursive function)
	* @param int end (internally used in recursive function)
	* @return int string number (offset in originals table)
	*/

	function find_string($string, $start = -1, $end = -1) {
		if (($start == -1) or ($end == -1)) {
			// find_string is called with only one parameter, set start end end
			$start = 0;
			$end = $this->total;
		}
		if (abs($start - $end) <= 1) {
			// We're done, now we either found the string, or it doesn't exist
			$txt = $this->get_original_string($start);
			if ($string == $txt)
			return $start;
			else
			return -1;
		} else if ($start > $end) {
			// start > end -> turn around and start over
			return $this->find_string($string, $end, $start);
		} else {
			// Divide table in two parts
			$half = (int)(($start + $end) / 2);
			$cmp = strcmp($string, $this->get_original_string($half));
			if ($cmp == 0)
			// string is exactly in the middle => return it
			return $half;
			else if ($cmp < 0)
			// The string is in the upper half
			return $this->find_string($string, $start, $half);
			else
			// The string is in the lower half
			return $this->find_string($string, $half, $end);
		}
	}

	/**
	* Translates a string
	*
	* @access public
	* @param string string to be translated
	* @return string translated string (or original, if not found)
	*/

	function translate($string) {
		if ($this->short_circuit){
			return $string;
		}
		$this->load_tables();

		if ($this->enable_cache) {
			// Caching enabled, get translated string from cache
			if (array_key_exists($string, $this->cache_translations))
			return $this->cache_translations[$string];
			else
			return $string;
		} else {
			// Caching not enabled, try to find string
			$num = $this->find_string($string);
			if ($num == -1)
			return $string;
			else
			return $this->get_translation_string($num);
		}
	}

	/**
	* Get possible plural forms from MO header
	*
	* @access private
	* @return string plural form header
	*/

	function get_plural_forms() {
		// lets assume message number 0 is header
		// this is true, right?
		$this->load_tables();

		// cache header field for plural forms
		if (! is_string($this->pluralheader)) {
			if ($this->enable_cache) {
				$header = $this->cache_translations[""];
			} else {
				$header = $this->get_translation_string(0);
			}
			$header .= "\n"; //make sure our regex matches
			if (eregi("plural-forms: ([^\n]*)\n", $header, $regs))
			$expr = $regs[1];
			else
			$expr = "nplurals=2; plural=n == 1 ? 0 : 1;";

			// add parentheses
			// important since PHP's ternary evaluates from left to right
			$expr.= ';';
			$res= '';
			$p= 0;
			for ($i= 0; $i < strlen($expr); $i++) {
				$ch= $expr[$i];
				switch ($ch) {
					case '?':
						$res.= ' ? (';
						$p++;
						break;
					case ':':
						$res.= ') : (';
						break;
					case ';':
						$res.= str_repeat( ')', $p) . ';';
						$p= 0;
						break;
					default:
						$res.= $ch;
				}
			}
			$this->pluralheader = $res;
		}

		return $this->pluralheader;
	}

	/**
	* Detects which plural form to take
	*
	* @access private
	* @param n count
	* @return int array index of the right plural form
	*/

	function select_string($n) {
		if (is_null($this->select_string_function)) {
			$string = $this->get_plural_forms();
			if (preg_match("/nplurals\s*=\s*(\d+)\s*\;\s*plural\s*=\s*(.*?)\;+/", $string, $matches)) {
				$nplurals = $matches[1];
				$expression = $matches[2];
				$expression = str_replace("n", '$n', $expression);
			} else {
				$nplurals = 2;
				$expression = ' $n == 1 ? 0 : 1 ';
			}
			$func_body = "
				\$plural = ($expression);
				return (\$plural <= $nplurals)? \$plural : \$plural - 1;";
			$this->select_string_function = create_function('$n', $func_body);
		}
		return call_user_func($this->select_string_function, $n);
	}

	/**
	* Plural version of gettext
	*
	* @access public
	* @param string single
	* @param string plural
	* @param string number
	* @return translated plural form
	*/

	function ngettext($single, $plural, $number) {
		if ($this->short_circuit) {
			if ($number != 1)
			return $plural;
			else
			return $single;
		}

		// find out the appropriate form
		$select = $this->select_string($number);

		// this should contains all strings separated by NULLs
		$key = $single.chr(0).$plural;


		if ($this->enable_cache) {
			if (! array_key_exists($key, $this->cache_translations)) {
				return ($number != 1) ? $plural : $single;
			} else {
				$result = $this->cache_translations[$key];
				$list = explode(chr(0), $result);
				return $list[$select];
			}
		} else {
			$num = $this->find_string($key);
			if ($num == -1) {
				return ($number != 1) ? $plural : $single;
			} else {
				$result = $this->get_translation_string($num);
				$list = explode(chr(0), $result);
				return $list[$select];
			}
		}
	}
}

function get_locale() {
	global $locale;
	if (isset($locale)){ return $locale; }
	// WPLANG is defined in wp-config.
	if (defined('CONFIG_LANGUAGE')){ $locale = CONFIG_LANGUAGE; }
	if (empty($locale)){ $locale = ''; }
	return $locale;
}

function translate($text, $domain) {
	global $l10n;
	if (isset($l10n[$domain])){
		return $l10n[$domain]->translate($text);
	}else{
		return $text;
	}
}

// Return a translated string.
//function __($text, $domain = 'default') {
function __($text, $domain = 'default') {
	return translate($text, $domain);
}

// Echo a translated string.
function _e($text, $domain = 'default') {
	echo translate($text, $domain);
}

function _c($text, $domain = 'default') {
	$whole = translate($text, $domain);
	$last_bar = strrpos($whole, '|');
	if ( false == $last_bar ) {
		return $whole;
	} else {
		return substr($whole, 0, $last_bar);
	}
}

// Return the plural form.
function __ngettext($single, $plural, $number, $domain = 'default') {
	global $l10n;
	if (isset($l10n[$domain])) {
		return $l10n[$domain]->ngettext($single, $plural, $number);
	} else {
		if ($number != 1)
		return $plural;
		else
		return $single;
	}
}

function load_textdomain($domain, $mofile) {
	global $l10n;
	if (isset($l10n[$domain])){
		return;
	}
	if ( is_readable($mofile)){
		$input = new CachedFileReader($mofile);
	}else{
		return;
	}
	$l10n[$domain] = new gettext_reader($input);
}

function load_default_textdomain() {
	global $l10n;
	$locale = get_locale();
	if ( empty($locale) ){
		//$locale = CONFIG_LANGUAGE;
		$locale='en';
		// *** Extra check if language exists ***
		if (isset($_SESSION["language_selected"]) AND file_exists(CMS_ROOTPATH.'languages/'.$_SESSION["language_selected"].'/'.$_SESSION["language_selected"].'.mo')){
			$locale=$_SESSION["language_selected"];
		}
	}

	//$mofile = CONFIG_FOLDER_LIBRARY . "language/$locale.mo";
	$mofile=CMS_ROOTPATH.'languages/'.$locale.'/'.$locale.'.mo';
	load_textdomain('default', $mofile);
}

?>