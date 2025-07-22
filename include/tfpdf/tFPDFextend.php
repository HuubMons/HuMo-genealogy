<?php
require_once __DIR__ . '/tFPDF.php';

class tFPDFextend extends tFPDF
{
    /**
     * EXTRA FUNCTIONS FOR HUMO-GENEALOGY BY YOSSI BECK:
     * pdfdisplay() , displayrel() , writename(),  pdf_ancestor_name() and adjusted Header()
     * 
     * Updated functions for sources, addresses, and improved name: Huub Mons jan. 2021.
     */

    public $pdf_font = 'DejaVu';

    private $languageDate;

    public function __construct()
    {
        parent::__construct();
        $this->languageDate = new \Genealogy\Include\LanguageDate();
    }

    /*
    function __construct($orientation = 'P', $unit = 'mm', $format = 'A4')
    {
        parent::__construct($orientation, $unit, $format);

        //$this->pdf_font = 'DejaVu';
    }
    */

    // *** June 2025: moved to tfpdfextent.php ***
    function pdf_convert($text)
    {
        //$text=@iconv("UTF-8","cp1252//IGNORE//TRANSLIT",$text);	// Only needed if FPDF is used. We now use TFPDF.
        return html_entity_decode(strip_tags($text), ENT_QUOTES);
    }

    // *** New function may 2021 ***
    // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
    function show_text($text, $emphasis, $font_size): void
    {
        //$font_size=12;
        //if ($person_kind=='child'){
        //  $font_size=11;
        //}
        if (isset($_POST['ancestor_report'])) {
            $font_size = 12;
        }
        if ($font_size == '') {
            $font_size = 12;
        }

        if ($text) {
            $this->SetFont($this->pdf_font, $emphasis, $font_size);
            $this->Write(6, html_entity_decode(strip_tags($text)));
            $this->SetFont($this->pdf_font, '', $font_size);
        }
    }

    /**
     * function pdfdisplay() to display details of person from array returned by personData.php
     * it parses the values and places them as needed, including pictures and their text, links to parents
     * it also places "Born:" "Died:" etc before their text (even though in the array they come after the text)
     */
    public function pdfdisplay($templ_personing, $person_kind): void
    {
        global $language, $data, $gen_lus, $romnr, $romannr, $parentchild, $parlink;
        global $indent, $child_indent, $pdf_footnotes, $pdf_count_notes, $user;
        $largest_height = 0;
        $pic = array();

        $picarray = array();
        $numpics = 0;

        $tallestpic = 0;
        $tallesttext = 0;

        if ($person_kind != "child") {
            $font_size = 12;
            $type = '';
        } else {
            $font_size = 11;
            $type = '';
        }

        $data["source_presentation"] = 'title';
        if (isset($_SESSION['save_source_presentation'])) {
            $data["source_presentation"] = $_SESSION['save_source_presentation'];
        }

        // *** Check if we have first occurance of birth, death etc. data, so we add "Born", "Died", etc. ***
        $own_code = 0;
        $born = 0;
        $birth_declaration = 0;
        $bapt = 0;
        $dead = 0;
        $death_declaration = 0;
        $buri = 0;
        $prof = 0;
        $religion = 0;
        $address = 0;
        $source = 0;

        foreach ($templ_personing as $key => $value) {
            $this->SetFont($this->pdf_font, $type, $font_size);

            if ($person_kind == 'ancestor') {
                $this->SetLeftMargin(38);
            }

            if (strpos($key, "pic_path") !== false) {
                if (
                    strpos($value, ".jpeg") !== false or strpos($value, ".jpg") !== false
                    or strpos($value, ".gif") !== false or strpos($value, ".png") !== false
                ) {
                    if (is_file($value)) {
                        //TODO check all picture items in this script.
                        if ($numpics > 14) {
                            // no more than 15 pics
                            continue;
                        }
                        $presentpic = intval(substr($key, 8));   //get the pic nr to compare later with txt nr
                        $picarray[$numpics][0] = $value;
                        $size = getimagesize($value);
                        $height = $size[1];
                        $width = $size[0];
                        if ($width > 180) {  //narrow and wide thumbs should not get height 120px - they will be far too long
                            $height *= 180 / $width;
                            $width = 180;
                        }
                        //if($height > $tallestpic) { $tallestpic=$height; }
                        $picarray[$numpics][1] = $width / 3.87;  // turn px into mm for pdf
                        $picarray[$numpics][4] = $height / 3.87; // turn px into mm for pdf
                        if ($picarray[$numpics][4] > $tallestpic) {
                            $tallestpic = $picarray[$numpics][4];
                        }
                        $numpics++;
                    }
                }
                continue;
            }

            if (strpos($key, "pic_text") !== false) {
                if (isset($presentpic) && $presentpic === intval(substr($key, 8))) {
                    $picarray[$numpics - 1][2] = $value;
                    if (isset($picarray[$numpics - 1][2])) {
                        $textlines = ceil(strlen($value) / 30);
                        $totalheight = ($textlines * 5) + ($picarray[$numpics - 1][4]);
                        if ($totalheight > $tallestpic) {
                            $tallestpic = $totalheight;
                        }
                    }
                }
                continue;
            }

            if (strpos($key, "own_code_start") !== false) {
                continue;
            }
            if (!$own_code && strpos($key, "own_code") !== false) {
                //if ($person_kind=='ancestor'){
                //  $this->SetLeftMargin(38);
                //}

                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["own_code_start"], 'B', $font_size);
                $own_code = 1;
            }

            if (strpos($key, "born_start") !== false) {
                continue;
            }
            if (!$born && strpos($key, "born") !== false) {
                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["born_start"], 'B', $font_size);
                $born = 1;
            }

            if (strpos($key, "birth_declaration_start") !== false) {
                continue;
            }
            if (!$birth_declaration && strpos($key, "birth_declaration") !== false) {
                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["birth_declaration_start"], 'B', $font_size);
                $birth_declaration = 1;
            }

            if (strpos($key, "bapt_start") !== false) {
                continue;
            }
            if (!$bapt && strpos($key, "bapt") !== false) {
                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["bapt_start"], 'B', $font_size);
                $bapt = 1;
            }

            if (strpos($key, "dead_start") !== false) {
                continue;
            }
            if (!$dead && strpos($key, "dead") !== false) {
                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["dead_start"], 'B', $font_size);
                $dead = 1;
            }

            if (strpos($key, "death_declaration_start") !== false) {
                continue;
            }
            if (!$death_declaration && strpos($key, "death_declaration") !== false) {
                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["death_declaration_start"], 'B', $font_size);
                $death_declaration = 1;
            }

            if (strpos($key, "buri_start") !== false) {
                continue;
            }
            if (!$buri && strpos($key, "buri") !== false) {
                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                //if ($person_kind=='ancestor') $this->SetLeftMargin(38);
                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["buri_start"], 'B', $font_size);
                $buri = 1;
            }

            if (strpos($key, "prof_start") !== false) {
                continue;
            }
            if (!$prof && strpos($key, "prof") !== false) {
                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                //if ($person_kind=='ancestor') $this->SetLeftMargin(38);
                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["prof_start"], 'B', $font_size);
                $prof = 1;
            }

            if (strpos($key, "religion_start") !== false) {
                continue;
            }
            if (!$religion && strpos($key, "religion") !== false) {
                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                //if ($person_kind=='ancestor') $this->SetLeftMargin(38);
                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["religion_start"], 'B', $font_size);
                $religion = 1;
            }

            if (strpos($key, "address_start") !== false) {
                continue;
            }
            if (!$address && strpos($key, "address") !== false) {
                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                //if ($person_kind=='ancestor') $this->SetLeftMargin(38);
                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["address_start"], 'B', $font_size);
                $address = 1;
            }
            if (strpos($key, "event_ged") !== false) {
                $this->SetFont($this->pdf_font, 'B', $font_size);
            }

            if (strpos($key, "marr_more") !== false) {
                $temp = explode(":", $value);
                $this->SetFont($this->pdf_font, 'B', $font_size);
                $this->show_text($temp[0] . ":", 'B', $font_size);
                $this->SetFont($this->pdf_font, '', $font_size);
                $this->show_text($temp[1], '', $font_size);
                continue;
            }

            // *** Only needed for source by person and family ***
            //if(!$source AND strpos($key,"source")!==false) {		// Don't use this line.
            if (strpos($key, "source_start") !== false) {
                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                //if ($person_kind=='ancestor') $this->SetLeftMargin(38);
                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_personing["source_start"], 'B', $font_size);
                $source = 1;
                continue; // *** Skip rest of loop, otherwise wrong items are shown ***
            }

            $value = html_entity_decode($value);

            if (strpos($key, "text") !== false) {
                $this->SetFont($this->pdf_font, 'I', $font_size - 1);
            }

            if (strpos($key, "source") !== false || strpos($key, "witn") !== false) {
                $this->SetFont($this->pdf_font, '', $font_size);  // was Times
            }

            if (strpos($key, "parent") !== false) {
                if ($person_kind == "parent1" && $key == "parents" && $gen_lus != 0) {
                    $this->SetTextColor(28, 28, 255);
                    $this->SetFont($this->pdf_font, 'B', $font_size); // was 'U'
                    $temp = $parentchild[$romannr];
                    $this->Write(6, $value, $parlink[$temp]);
                    $this->SetTextColor(0);
                } elseif ($key == "parents") {
                    $this->SetFont($this->pdf_font, 'B', $font_size);
                    $this->Write(6, $value);
                } else {
                    $this->SetFont($this->pdf_font, '', $font_size);
                    $this->Write(6, $value);
                }
            } else {
                if ($person_kind == "child") {

                    // pic with child
                    if (strpos($key, "got_pics") !== false) {
                        $keepY = $this->GetY() + 7;
                        if (($keepY + $tallestpic + 5) > 280) {
                            $this->AddPage();
                            $keepY = 20;
                        }
                        $keepX = $this->GetX();
                        if (isset($picarray[0][0])) {  // we got at least 1 pic
                            $pic_indent = 34;
                            $pictext_indent = 34;
                            $maxw = 180 / 3.87;
                            for ($i = 0; $i < 15; $i++) {
                                if (isset($picarray[$i][0])) {
                                    if ($i > 0 and $i % 3 == 0) {
                                        $pic_indent = 28;
                                        $pictext_indent = 28;
                                        $keepY += ($tallestpic + 1);
                                        if (($keepY + $tallestpic + 5) > 280) {
                                            $this->AddPage();
                                            $keepY = 20;
                                        }
                                    }
                                    $pic_indent += (($maxw - $picarray[$i][1]) / 2);
                                    $this->Image($picarray[$i][0], $pic_indent, $keepY, $picarray[$i][1]);
                                    $pic_indent = $pictext_indent + $maxw + 5;
                                    if (isset($picarray[$i][2])) {
                                        $this->SetFont($this->pdf_font, '', 8);
                                        $this->SetXY($pictext_indent, $keepY + $picarray[$i][4] + 1);
                                        $this->MultiCell($maxw, 4, $picarray[$i][2], 0, 'C');
                                    }
                                    $pictext_indent += $maxw + 5;
                                }
                            }
                            $this->SetXY($keepX, $keepY + $tallestpic - 3);
                        }
                    }
                    // source link with child
                    elseif (strpos($key, "source") !== false and $value != '') {   // make source link to end of document
                        $this->SetLeftMargin($child_indent);
                        $this->SetFont($this->pdf_font, '', $font_size);  // was Times
                        $this->SetTextColor(28, 28, 255);

                        /*
                        $this->SetLeftMargin($child_indent);
                        $this->SetFont('Times','',$font_size);
                        $this->SetTextColor(28,28,255);
                        */
                        $this->PDFShowSources($value);
                        $this->SetLeftMargin($indent);
                    } else {
                        $this->SetLeftMargin($child_indent);
                        $this->Write(6, $value);
                        $this->SetLeftMargin($indent);
                    }
                } elseif ($person_kind == "ancestor") {
                    if (strpos($key, "got_pics") !== false) {
                        $keepY = $this->GetY() + 7;
                        if (($keepY + $tallestpic + 5) > 280) {
                            $this->AddPage();
                            $keepY = 20;
                        }
                        $keepX = $this->GetX();
                        if (isset($picarray[0][0])) {  // we got at least 1 pic
                            $pic_indent = 35;
                            $pictext_indent = 35;
                            $maxw = 180 / 3.87;
                            for ($i = 0; $i < 15; $i++) {
                                if (isset($picarray[$i][0])) {
                                    if ($i > 0 and $i % 3 == 0) {
                                        $pic_indent = 28;
                                        $pictext_indent = 28;
                                        $keepY += ($tallestpic + 1);
                                        if (($keepY + $tallestpic + 5) > 280) {
                                            $this->AddPage();
                                            $keepY = 20;
                                        }
                                    }
                                    $pic_indent += (($maxw - $picarray[$i][1]) / 2);
                                    $this->Image($picarray[$i][0], $pic_indent, $keepY, $picarray[$i][1]);
                                    $pic_indent = $pictext_indent + $maxw + 5;
                                    if (isset($picarray[$i][2])) {
                                        $this->SetFont($this->pdf_font, '', 8);
                                        $this->SetXY($pictext_indent, $keepY + $picarray[$i][4] + 1);
                                        $this->MultiCell($maxw, 4, $picarray[$i][2], 0, 'C');
                                    }
                                    $pictext_indent += $maxw + 5;
                                }
                            }
                            $this->SetXY($keepX, $keepY + $tallestpic - 3);
                        }
                    }
                    // source link with ancestor
                    elseif (strpos($key, "source") !== false and $value != '') {   // make source link to end of document
                        /*
                        $this->SetLeftMargin(38);
                        $this->SetFont('Times','',$font_size);
                        $this->SetTextColor(28,28,255);
                        */
                        $this->PDFShowSources($value);

                        //$this->SetTextColor(0);
                        //$this->SetLeftMargin(10);
                    } else {
                        $this->SetLeftMargin(38);
                        $this->Write(6, $value);
                        $this->SetLeftMargin(10);
                    }
                } elseif (strpos($key, "got_pics") !== false) {
                    $keepY = $this->GetY() + 7;
                    if (($keepY + $tallestpic + 5) > 280) {
                        $this->AddPage();
                        $keepY = 20;
                    }
                    $keepX = $this->GetX();
                    if (isset($picarray[0][0])) {  // we got at least 1 pic
                        $pic_indent = 28;
                        $pictext_indent = 28;
                        $maxw = 180 / 3.87;
                        for ($i = 0; $i < 15; $i++) {
                            if (isset($picarray[$i][0])) {
                                if ($i > 0 && $i % 3 == 0) {
                                    $pic_indent = 28;
                                    $pictext_indent = 28;
                                    $keepY += ($tallestpic + 1);
                                    if (($keepY + $tallestpic + 5) > 280) {
                                        $this->AddPage();
                                        $keepY = 20;
                                    }
                                }
                                $pic_indent += (($maxw - $picarray[$i][1]) / 2);
                                $this->Image($picarray[$i][0], $pic_indent, $keepY, $picarray[$i][1]);
                                $pic_indent = $pictext_indent + $maxw + 5;
                                if (isset($picarray[$i][2])) {
                                    $this->SetFont($this->pdf_font, '', 8);
                                    $this->SetXY($pictext_indent, $keepY + $picarray[$i][4] + 1);
                                    $this->MultiCell($maxw, 4, $picarray[$i][2], 0, 'C');
                                }
                                $pictext_indent += $maxw + 5;
                            }
                        }
                        $this->SetXY($keepX, $keepY + $tallestpic - 3);
                    }
                } elseif (strpos($key, "source") !== false and $value != '') {   // make source link to end of document
                    //$this->SetFont('Times','',$font_size);
                    //$this->SetTextColor(28,28,255);
                    $this->PDFShowSources($value);
                    //$this->SetTextColor(0);
                } else {
                    $this->Write(6, $value);
                }
            }
            $this->SetFont($this->pdf_font, $type, $font_size);
        }
        if ($person_kind != "child") {
            $this->Write(8, "\n");
        } else {
            $this->Write(6, "\n");
        }
    }

    /**
     * function displayrel()  to display wedding/relation details from marriageCls.php
     */
    public function displayrel($templ_relation, $ancestor_report): void
    {
        global $language, $user, $pdf_footnotes, $pdf_count_notes;
        $font_size = 12;
        $samw = 0;
        $prew = 0;
        $wedd = 0;
        $prec = 0;
        $chur = 0;
        $devr = 0;
        $more = 0;
        $address = 0;
        $sour = 0;

        $largest_height = 0;
        $pic = array();

        $picarray = array();
        $numpics = 0;

        $tallestpic = 0;
        $tallesttext = 0;

        $data["source_presentation"] = 'title';
        if (isset($_SESSION['save_source_presentation'])) {
            $data["source_presentation"] = $_SESSION['save_source_presentation'];
        }

        foreach ($templ_relation as $key => $value) {
            $value = html_entity_decode($value);
            $this->SetFont($this->pdf_font, '', $font_size);

            if (strpos($key, "exist") !== false) {
                continue;
            }

            if ($ancestor_report == "ancestor") {
                $this->SetLeftMargin(38);
            }

            if (!$samw && strpos($key, "cohabit") !== false) {            // Living together
                // Example: $this->show_text($text,'B',$font_size);
                if (isset($templ_relation["cohabit_exist"])) {
                    $this->show_text($templ_relation["cohabit_exist"], 'B', '');
                }
                $samw = 1;
            }
            if (!$prew && strpos($key, "prew") !== false) {
                // Example: $this->show_text($text,'B',$font_size);
                if (isset($templ_relation["prew_exist"])) {
                    $this->show_text($templ_relation["prew_exist"], 'B', '');
                }
                $prew = 1;
            }
            if (!$wedd && strpos($key, "wedd") !== false) {
                // Example: $this->show_text($text,'B',$font_size);
                $this->show_text($templ_relation["wedd_exist"], 'B', '');
                $wedd = 1;
            }
            if (!$prec && strpos($key, "prec") !== false) {
                // Example: $this->show_text($text,'B',$font_size);
                $this->show_text($templ_relation["prec_exist"], 'B', '');
                $prec = 1;
            }
            if (!$chur && strpos($key, "chur") !== false) {
                // Example: $this->show_text($text,'B',$font_size);
                $this->show_text($templ_relation["chur_exist"], 'B', '');
                $chur = 1;
            }
            if (!$devr and strpos($key, "devr") !== false) {
                if (isset($templ_relation["devr_exist"])) {
                    // Example: $this->show_text($text,'B',$font_size);
                    $this->show_text($templ_relation["devr_exist"], 'B', '');
                    $devr = 1;
                }
            }

            // *** NEW 03-01-2021 added family address in PDF function ***
            // *** Show text: "Residences (family): " ***
            //if(strpos($key,"address_start")!==false) {
            if (!$address && strpos($key, "address") !== false) {
                // For now: just add newline.
                $this->Ln(4);

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_relation["address_start"], 'B', '');
                $address = 1;
                continue; // *** Skip rest of loop, otherwise wrong items are shown ***
            }

            // ** Source by family ***
            if (strpos($key, "source_start") !== false) {
                //if(!$sour AND strpos($key,"source")!==false) {  // Don't use this line. 
                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_relation["source_start"], 'B', '');
                $source = 1;
                continue; // *** Skip rest of loop, otherwise wrong items are shown ***
            }

            if (strpos($key, "text") !== false) {
                $this->SetFont($this->pdf_font, 'I', $font_size - 1);
            }
            if (strpos($key, "witn") !== false) {
                $this->SetFont($this->pdf_font, '', $font_size);
            }
            if (strpos($key, "source") !== false) {
                $this->SetFont($this->pdf_font, '', $font_size);
            }

            if (strpos($key, "pic_path") !== false) {
                if (
                    strpos($value, ".jpeg") !== false or strpos($value, ".jpg") !== false
                    or strpos($value, ".gif") !== false or strpos($value, ".png") !== false
                ) {
                    if (is_file($value)) {
                        if ($numpics > 14) {
                            continue;
                        }  // no more than 15 pics
                        $presentpic = intval(substr($key, 8));   //get the pic nr to compare later with txt nr
                        $picarray[$numpics][0] = $value;
                        $size = getimagesize($value);
                        $height = $size[1];
                        $width = $size[0];
                        if ($width > 180) {  //narrow and wide thumbs should not get height 120px - they will be far too long
                            $height *= 180 / $width;
                            $width = 180;
                        }
                        //if($height > $tallestpic) { $tallestpic=$height; }
                        $picarray[$numpics][1] = $width / 3.87;  // turn px into mm for pdf
                        $picarray[$numpics][4] = $height / 3.87; // turn px into mm for pdf
                        if ($picarray[$numpics][4] > $tallestpic) {
                            $tallestpic = $picarray[$numpics][4];
                        }
                        $numpics++;
                    }
                }
                continue;
            }

            if (strpos($key, "pic_text") !== false) {
                if (isset($presentpic) && $presentpic === intval(substr($key, 8))) {
                    $picarray[$numpics - 1][2] = $value;
                    if (isset($picarray[$numpics - 1][2])) {
                        $textlines = ceil(strlen($value) / 30);
                        $totalheight = ($textlines * 5) + ($picarray[$numpics - 1][4]);
                        if ($totalheight > $tallestpic) {
                            $tallestpic = $totalheight;
                        }
                    }
                }
                continue;
            }

            if (strpos($key, "source") !== false) {
                $this->PDFShowSources($value);
            } elseif (strpos($key, "got_pics") !== false) {
                $keepY = $this->GetY() + 7;
                if (($keepY + $tallestpic + 5) > 280) {
                    $this->AddPage();
                    $keepY = 20;
                }
                $keepX = $this->GetX();
                if (isset($picarray[0][0])) {  // we got at least 1 pic
                    $pic_indent = 28;
                    $pictext_indent = 28;
                    $maxw = 180 / 3.87;
                    for ($i = 0; $i < 15; $i++) {
                        if (isset($picarray[$i][0])) {
                            if ($i > 0 && $i % 3 == 0) {
                                $pic_indent = 28;
                                $pictext_indent = 28;
                                $keepY += ($tallestpic + 1);
                                if (($keepY + $tallestpic + 5) > 280) {
                                    $this->AddPage();
                                    $keepY = 20;
                                }
                            }
                            $pic_indent += (($maxw - $picarray[$i][1]) / 2);
                            $this->Image($picarray[$i][0], $pic_indent, $keepY, $picarray[$i][1]);
                            $pic_indent = $pictext_indent + $maxw + 5;
                            if (isset($picarray[$i][2])) {
                                $this->SetFont($this->pdf_font, '', 8);
                                $this->SetXY($pictext_indent, $keepY + $picarray[$i][4] + 1);
                                $this->MultiCell($maxw, 4, $picarray[$i][2], 0, 'C');
                            }
                            $pictext_indent += $maxw + 5;
                        }
                    }
                    $this->SetXY($keepX, $keepY + $tallestpic - 3);
                }
            } else {
                $this->Write(6, $value);
            }
            if ($ancestor_report == "ancestor") {
                $this->SetLeftMargin(10);
            }
        }
        //$pdf_count_notes=0;
        $this->Write(8, "\n");
    }

    /**
     * 09-01-2021 RENEWED function write_name() to place the name of a person
     */
    public function write_name($templ_name, $indentation, $length): void
    {
        global $data, $ident, $language, $user, $pdf_footnotes, $pdf_count_notes;
        $sexe = 0;
        $name = 0;
        $name_text = 0;
        $name_parents = 0;
        $name_partner = 0;
        $name_wedd_age = 0;

        $font_size = 12;
        if ($length == 'child') {
            $font_size = 11;
        }

        $data["source_presentation"] = 'title';
        if (isset($_SESSION['save_source_presentation'])) {
            $data["source_presentation"] = $_SESSION['save_source_presentation'];
        }

        foreach ($templ_name as $key => $value) {
            //$value = '';
            if (isset($value)) $value = html_entity_decode($value);

            if ($sexe == 0 && strpos($key, "name_sexe") !== false) {
                if ($templ_name["name_sexe"] == "M") {
                    $pic = __DIR__ . '/../../images/man.gif';
                } elseif ($templ_name["name_sexe"] == 'F') {
                    $pic = __DIR__ . '/../../images/woman.gif';
                } else {
                    $pic = __DIR__ . '/../../images/unknown.gif';
                }

                if ($length != 'child') {
                    $this->Image($pic, $indentation - 4, $this->GetY() + 2, 3.5, 3.5);
                    $sexe = 1;
                } else {
                    $this->Image($pic, $indentation - 4, $this->GetY() + 1, 3.5, 3.5);
                    $sexe = 1;
                    $this->SetX($this->GetX() + 5);
                }

                $this->SetX($indentation);
                if ($length == "long") {
                    $indent = $this->GetX();
                }
            }

            //$this->SetX($indentation);

            //$indent=$this->GetX();
            //$this->SetLeftMargin($indent);

            //$indent=$this->GetX();
            //$this->SetX($indentation);

            if ($name == 0 && strpos($key, "name_name") !== false) {
                //$indentation=$this->GetX();
                //$this->SetX($indentation);
                //$this->SetFont($this->pdf_font,'B',$font_size);
                //$this->Write(6,html_entity_decode($templ_name["name_name"]));
                //$this->MultiCell(0,8,$templ_name["name_name"],0,"L");
                //$this->SetFont($this->pdf_font,'',$font_size);

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $person_kind = '';
                if ($length == 'child') {
                    $person_kind = 'child';
                }
                $this->show_text($templ_name["name_name"], 'B', $font_size);
                $name = 1;
            }

            if ($name_text == 0 && strpos($key, "name_text") !== false) {
                //$indentation=$this->GetX();
                //$this->SetX($indentation);
                //$this->SetFont($this->pdf_font,'I',$font_size);
                //$this->Write(6,strip_tags(html_entity_decode($templ_name["name_text"])));
                //$this->MultiCell(0,8,$templ_name["name_text"],0,"L");
                //$this->SetFont($this->pdf_font,'',$font_size);

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_name["name_text"], 'I', $font_size);
                $name_text = 1;
            }

            if ($name_wedd_age == 0 && strpos($key, "name_wedd_age") !== false) {
                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_name["name_wedd_age"], '', $font_size);
                $name_wedd_age = 1;
            }

            if ($name_parents == 0 && strpos($key, "name_parents") !== false) {
                //$indentation=$this->GetX();
                //$this->SetX($indentation);
                //$this->SetFont($this->pdf_font,'',$font_size);
                //$this->Write(6,strip_tags(html_entity_decode($templ_name["name_parents"])));
                //$this->MultiCell(0,8,$templ_name["name_parents"],0,"L");
                //$this->SetFont($this->pdf_font,'',$font_size);

                if ($data["family_expanded"] != 'compact') {
                    $this->Ln(6);
                }

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_name["name_parents"], '', $font_size);

                $name_parents = 1;
            }

            // *** Sources ***
            //$font_size=12; $type='';
            $font_size = 11;
            $type = '';

            if (strpos($key, "source") !== false && $value !== '') {   // make source link to end of document
                //$this->SetX($indentation);
                //$this->SetFont('Times','',$font_size);
                //$this->SetTextColor(28,28,255);

                $this->PDFShowSources($value);
                // *** If name-sexe-source is used, add a space ***
                $this->Write(8, ' ');
            }

            // *** Show partner by child ***
            if ($name_partner == 0 && strpos($key, "name_partner") !== false) {
                //$indentation=$this->GetX();
                //$this->SetX($indentation);
                //$this->SetFont($this->pdf_font,'',$font_size);
                //$this->Write(6,strip_tags(html_entity_decode($templ_name["name_partner"])));
                //$this->MultiCell(0,8,$templ_name["name_text"],0,"L");
                //$this->SetFont($this->pdf_font,'',$font_size);

                // Example: $this->show_text($text,'B',$font_size);  // B=Bold, I=Italic
                $this->show_text($templ_name["name_partner"], '', $font_size);
                $name_partner = 1;
            }
        }
        //$indentation=$this->GetX();
        //$this->SetX($indentation);
        //$this->SetLeftMargin($indent);
        //$this->SetLeftMargin(10);

        // *** Resets line ***
        //	if ($length!='child')
        //		$this->MultiCell(0,8,'',0,"L");

        // NEW may 2021
        if (isset($_POST['ancestor_report'])) {
            $this->MultiCell(0, 8, '', 0, "L");
        }

        //unset($pdf_footnotes);
    }

    /**
     * function pdf_ancestor_name()   writes names in ancestor report
     */
    public function pdf_ancestor_name($ancestor_reportnr, $sexe, $name): void
    {
        global $language;

        $this->SetFont($this->pdf_font, 'B', 12);
        // (num) will be placed under num
        if ($ancestor_reportnr > 9999 && $this->GetY() + 7 > 270) {
            //(num) would drop off bottom of page
            $this->AddPage();
            // move num already to new page so they stay together...
        }

        $this->Write(8, $ancestor_reportnr);
        $this->SetFont($this->pdf_font, '', 12);
        if ($ancestor_reportnr > 9999) { // num(num) becomes too long. (num) is placed 1 line down
            $this->Ln(7);
            $this->Write(8, '(' . floor($ancestor_reportnr / 2) . ')  ');
            $this->SetY($this->GetY() - 7); //get back to first line to place name
        } else {
            $this->Write(8, ' (' . floor($ancestor_reportnr / 2) . ')  ');
        }

        $this->SetFont($this->pdf_font, 'B', 12);
        //$this->SetX(35);
        $this->SetLeftMargin(38);
        /*
        if($sexe=="M") {
            $this->Image("images/man.gif",$this->GetX(),$this->GetY()+2,3.5,3.5);
        }
        elseif($sexe=="F") {
            $this->Image("images/woman.gif",$this->GetX(),$this->GetY()+2,3.5,3.5);
        }
        else{
            $this->Image("images/unknown.gif",$this->GetX(),$this->GetY()+2,3.5,3.5);
        }
        */
        // SOURCE by M/F/? icon.

        //$this->SetX($this->GetX()+3);
        //$this->MultiCell(0,8,$name,0,"L");
        //$this->SetFont('Arial','',12);
    }

    /**
     * 10 jan 2021 NEW FUNCTION
     * REMARK: footnotes are shown in family script
     */
    private function PDFShowSources($value): void
    {
        global $data, $font_size, $user, $pdf_footnotes;

        // *** May 2021: moved these lines into this function ***
        $data["source_presentation"] = 'title';
        if (isset($_SESSION['save_source_presentation'])) {
            $data["source_presentation"] = $_SESSION['save_source_presentation'];
        }

        //$this->SetX($indentation);
        $this->SetFont($this->pdf_font, '', $font_size);  // was Times
        $this->SetTextColor(28, 28, 255);

        if ($data["source_presentation"] == 'footnote') {  // "1)" as link to list at end of doc
            $footnote_nr_array = explode('~', $value);
            //TEST
            //$this->Write(6,$value);
            //$this->Write(6,'SOURCE_1_TEST');
            foreach ($footnote_nr_array as $footnote_nr) {
                $ofs = 0;
                if (is_numeric($footnote_nr)) {
                    $ofs = $footnote_nr - 1;
                } // offset starts with 0
                if ($ofs >= 0 && isset($pdf_footnotes[$ofs])) {  // is footnote to source from global source list
                    $this->SetTextColor(28, 28, 255);
                    $this->subWrite(6, ' ' . $footnote_nr . ')', $pdf_footnotes[$ofs], 9, 4);
                } else { // "manual" source list as regular non-clickable text
                    $this->SetTextColor(0);
                    $this->Write(6, $footnote_nr);
                }
            }
        } elseif ($user['group_sources'] != 'n') {  // source title as link to list at end of doc
            //TEST
            //$this->Write(6,$value);
            //$this->Write(6,'SOURCE TEST');
            // with multiple sources pdf string looks like:  ,firstsource!!12~,secondsource!!4~,thirdsource!!6
            $multitext = explode('~', $value); // each key looks like: ,somesource!!34
            foreach ($multitext as $i => $value) {
                $pos = strpos($multitext[$i], '!!');
                //if($user['group_sources']=='j' AND $pos) {
                if ($pos) { //source title as link to list at bottom

                    // *** Multiple sources, show a comma ***
                    if ($i > 0) {
                        $this->SetTextColor(0);
                        $this->Write(6, ', ');
                    }

                    if ($user['group_sources'] == 'j') {
                        $this->SetTextColor(28, 28, 255);
                    } else {
                        $this->SetTextColor(0);
                    }
                    //$num = $multitext[$i];
                    //$ofs=0; if (is_numeric($num)) $ofs = $num - 1; // offset starts with 0
                    $ofs = substr($multitext[$i], $pos + 2) - 1;
                    $txt = substr($multitext[$i], 0, $pos); // take off the !!2 source number at end
                    $this->Write(6, $txt, $pdf_footnotes[$ofs]);
                    //$this->subWrite(6,' '.$multitext[$i].')',$pdf_footnotes[$ofs],9,4);
                } else {  // source title as plain text
                    $this->SetTextColor(0);
                    $this->Write(6, $multitext[$i]);
                }
            }
            if ($pos) {
                $this->Write(6, '. ');
            }
        }
        /*
        else {  // manual source title as plain text
            $this->SetTextColor(0);
            $this->Write(6,$value.'RED');
        }
        */
        $this->SetTextColor(0);
    }



    // TODO check variable.
    var $angle = 0;

    private function Rotate($angle, $x = -1, $y = -1): void
    {
        if ($x == -1) {
            $x = $this->x;
        }
        if ($y == -1) {
            $y = $this->y;
        }
        if ($this->angle != 0) {
            $this->_out('Q');
        }
        $this->angle = $angle;
        if ($angle != 0) {
            $angle *= M_PI / 180;
            $c = cos($angle);
            $s = sin($angle);
            $cx = $x * $this->k;
            $cy = ($this->h - $y) * $this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
        }
    }

    function _endpage(): void
    {
        if ($this->angle != 0) {
            $this->angle = 0;
            $this->_out('Q');
        }
        parent::_endpage();
    }

    function Header(): void
    {
        global $title, $humo_option;

        if ($this->PageNo() != 1) {
            //Position at 1.0 cm from top;
            $this->SetY(10);
            //DejaVu italic 8
            $this->SetFont($this->pdf_font, 'I', 8);
            //Text color in gray
            $this->SetTextColor(128);
            //Page description
            $this->Cell(0, 5, $title, 0, 1, 'C');
            $this->Ln(5);
        }

        //Put watermark
        $this->SetFont('Arial', 'B', 50);
        $this->SetTextColor($humo_option["watermark_color_r"], $humo_option["watermark_color_g"], $humo_option["watermark_color_b"]);  // original was 255 192 203, changed default to 229 229 229
        //$this->RotatedText(30, 190, $humo_option["watermark_text"], 45);
        //$watermark=htmlentities($humo_option["watermark_text"]);

        //$watermark = utf8_decode($humo_option["watermark_text"]);
        $watermark = mb_convert_encoding($humo_option["watermark_text"], 'ISO-8859-2', 'UTF-8');
        $this->RotatedText(30, 190, $watermark, 45);
    }

    private function RotatedText($x, $y, $txt, $angle): void
    {
        //Text rotated around its origin
        $this->Rotate($angle, $x, $y);
        $this->Text($x, $y, $txt);
        $this->Rotate(0);
    }

    /*   // original fpdf version - if ever needed
    function Header(){
        global $title;

        //Arial bold 15
        $this->SetFont('Arial','B',15);
        //Calculate width of title and position
        $w=$this->GetStringWidth($title)+6;
        $this->SetX((210-$w)/2);
        //Colors of frame, background and text
        $this->SetDrawColor(0,80,180);
        $this->SetFillColor(230,230,0);
        $this->SetTextColor(220,50,50);
        //Thickness of frame (1 mm)
        $this->SetLineWidth(1);
        //Title
        $this->Cell($w,9,$title,1,1,'C',true);
        //Line break
        $this->Ln(10);
    }
    */

    function Footer(): void
    {
        //Position at 1.5 cm from bottom
        $this->SetY(-15);
        //Arial italic 8
        $this->SetFont($this->pdf_font, 'I', 8);
        //Text color in gray
        $this->SetTextColor(128);
        //Page number
        //$this->Cell(0,10,'PDF Created with HuMo-genealogy    Page '.$this->PageNo(),0,0,'C');

        $text = sprintf(__('PDF Created with %s on'), 'HuMo-genealogy') . ' ';
        $date_part1 = $this->languageDate->language_date(date("j M Y")); // *** Translate first part of date (05 JUL 2022) using HuMo-genealogy language_date script ***
        //$this->Cell(0, 10, $text . $date_part1 . ' ' . date("g:i a") . '. ' . __('Page') . ' ' . $this->PageNo(), 0, 0, 'C');
        $this->AliasNbPages();
        $this->Cell(0, 10, $text . $date_part1 . ' ' . date("g:i a") . '. ' . __('Page') . ' ' . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }

    /**
     * function to make super- or subscript
     */
    private function subWrite($h, $txt, $link = '', $subFontSize = 12, $subOffset = 0): void
    {
        // resize font
        $subFontSizeold = $this->FontSizePt;
        $this->SetFontSize($subFontSize);

        // reposition y
        $subOffset = ((($subFontSize - $subFontSizeold) / $this->k) * 0.3) + ($subOffset / $this->k);
        $subX        = $this->x;
        $subY        = $this->y;
        $this->SetXY($subX, $subY - $subOffset);

        //Output text
        $this->Write($h, $txt, $link);

        // restore y position
        $subX        = $this->x;
        $subY        = $this->y;
        $this->SetXY($subX,   $subY + $subOffset);

        // restore font size
        $this->SetFontSize($subFontSizeold);
    }
}
