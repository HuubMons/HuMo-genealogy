<?php
function language_event($text_event)
{
    $text_event = str_replace("ADOP", __('Adopted by both'), $text_event);
    $text_event = str_replace("_ADPF", __('Adopted by father'), $text_event);
    $text_event = str_replace("_ADPM", __('Adopted by mother'), $text_event);
    $text_event = str_replace("AFN", __('AFN number'), $text_event);
    $text_event = str_replace("ANCI", __('Ancestor interest'), $text_event);
    $text_event = str_replace("ANUL", __('Annulled'), $text_event);
    $text_event = str_replace("ARVL", __('arrive'), $text_event);
    $text_event = str_replace("BAPL", __('Baptized LDS'), $text_event);
    $text_event = str_replace("BAPM", __('baptized as child'), $text_event);
    $text_event = str_replace("BARM", __('Bar Mitzvah'), $text_event);
    $text_event = str_replace("BASM", __('Bat Mitzvah'), $text_event);
    $text_event = str_replace("BLES", __('Blessing'), $text_event);
    $text_event = str_replace("_BRTM", __('Brit Mila'), $text_event);
    $text_event = str_replace("CAST", __('Caste'), $text_event);
    $text_event = str_replace("CENS", __('Census'), $text_event);
    $text_event = str_replace("CHRA", __('Christened as adult'), $text_event);
    $text_event = str_replace("CONF", __('Confirmation'), $text_event);
    $text_event = str_replace("CONL", __('Confirmation LDS'), $text_event);
    $text_event = str_replace("_COML", __('Common law'), $text_event);
    $text_event = str_replace("DESI", __('Descendant interest'), $text_event);
    $text_event = str_replace("DIVF", __('Divorce filed'), $text_event);
    $text_event = str_replace("DPRT", __('depart'), $text_event);
    $text_event = str_replace("DSCR", __('Description'), $text_event);
    $text_event = str_replace("EDUC", __('Education'), $text_event);
    $text_event = str_replace("EMIG", __('Emigrated'), $text_event);
    $text_event = str_replace("ENDL", __('Endowment LDS'), $text_event);
    $text_event = str_replace("ENGA", __('Engaged'), $text_event);
    $text_event = str_replace("EVEN", __('event'), $text_event);
    $text_event = str_replace("_EYEC", __('Eye colour'), $text_event);
    $text_event = str_replace("FCOM", __('First Communion'), $text_event);
    $text_event = str_replace("_FNRL", __('Funeral'), $text_event);
    $text_event = str_replace("GRAD", __('Graduated'), $text_event);
    $text_event = str_replace("_HAIR", __('Hair colour'), $text_event);
    $text_event = str_replace("_HEIG", __('Height'), $text_event);
    $text_event = str_replace("IMMI", __('Immigrated'), $text_event);
    $text_event = str_replace("IDNO", __('ID number'), $text_event);
    $text_event = str_replace("_INTE", __('Interred'), $text_event);
    $text_event = str_replace("LEGI", __('justified'), $text_event);
    $text_event = str_replace("MARC", __('Marriage contract'), $text_event);
    $text_event = str_replace("MARL", __('Marriage license'), $text_event);
    $text_event = str_replace("MARS", __('Marriage settlement'), $text_event);
    $text_event = str_replace("_MBON", __('Marriage bond'), $text_event);
    $text_event = str_replace("_MEDC", __('Medical condition'), $text_event);
    $text_event = str_replace("MILI", __('Military service'), $text_event);
    $text_event = str_replace("NATI", __('Nationality'), $text_event);
    $text_event = str_replace("NATU", __('Naturalised'), $text_event);
    $text_event = str_replace("NCHI0", __('No children'), $text_event); // Extra event...
    $text_event = str_replace("NCHI", __('Nr. of children'), $text_event);
    $text_event = str_replace("_NLIV", __('Not living'), $text_event);
    $text_event = str_replace("_NMAR", __('Never married'), $text_event);
    $text_event = str_replace("_NMR", __('BK NOT MARRIED'), $text_event);
    $text_event = str_replace("ORDN", __('Ordination'), $text_event);
    $text_event = str_replace("_PRMN", __('Permanent number'), $text_event);
    $text_event = str_replace("PROB", __('Probate'), $text_event);
    $text_event = str_replace("PROP", __('Property'), $text_event);
    $text_event = str_replace("RFN", __('Marr ID number'), $text_event);
    $text_event = str_replace("REFN", __('Ref. number'), $text_event);
    //$text_event=str_replace("RELI", __('Religious'), $text_event);
    $text_event = str_replace("RETI", __('Retirement'), $text_event);
    $text_event = str_replace("SLGC", __('Sealed child LDS'), $text_event);
    $text_event = str_replace("SLGL", __('sealing to parents LDS'), $text_event);
    $text_event = str_replace("SLGS", __('Sealed to spouse LDS'), $text_event);
    $text_event = str_replace("SSN", __('Social Security Number'), $text_event);
    $text_event = str_replace("TXPY", __('taxpayer'), $text_event);
    $text_event = str_replace("_WEIG", __('Weight'), $text_event);
    $text_event = str_replace("WILL", __('Will signed'), $text_event);
    $text_event = str_replace("_YART", __('Yartzeit'), $text_event);

    $text_event = ucfirst($text_event);
    return $text_event;
}

function language_name($text)
{
    $return_text='';
    if ($text == '_ALIA') $return_text = __('alias name') . ': ';	// For Pro-Gen
    if ($text == '_SHON') $return_text = __('Short name (for reports)') . ': ';
    if ($text == '_ADPN') $return_text = __('Adopted name') . ': ';
    if ($text == '_HEBN') $return_text = __('Hebrew name') . ': ';
    if ($text == '_CENN') $return_text = __('Census name') . ': ';
    if ($text == '_MARN') $return_text = __('Married name') . ': ';
    if ($text == '_GERN') $return_text = __('Given name') . ': ';
    if ($text == '_FARN') $return_text = __('Farm name') . ': ';
    if ($text == '_BIRN') $return_text = __('Birth name') . ': ';
    if ($text == '_INDN') $return_text = __('Indian name') . ': ';
    if ($text == '_FKAN') $return_text = __('Formal name') . ': ';
    if ($text == '_CURN') $return_text = __('Current name') . ': ';
    if ($text == '_SLDN') $return_text = __('Soldier name') . ': ';
    if ($text == '_FRKA') $return_text = __('Formerly known as') . ': ';
    if ($text == '_RELN') $return_text = __('Religious name') . ': ';
    if ($text == '_OTHN') $return_text = __('Other name') . ': ';
    return $return_text;
}
