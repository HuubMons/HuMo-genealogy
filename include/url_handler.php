<?php 

// *** Check if HuMo-genealogy is in a CMS system ***
//	- Code for all CMS: if (CMS_SPECIFIC) {}
//	- Code for one CMS: if (CMS_SPECIFIC == 'Joomla') {}
//	- Code NOT for CMS: if (!CMS_SPECIFIC) {}
/**
 * Build the 
 */
function path(string $path)
{
    if (!CMS_SPECIFIC) {
        return "$path";
    }

    if (CMS_SPECIFIC == 'Joomla') {
        return "$path";
    }
}