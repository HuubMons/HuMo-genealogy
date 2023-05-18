<?php 

// *** Check if HuMo-genealogy is in a CMS system ***
//	- Code for all CMS: if (CMS_SPECIFIC) {}
//	- Code for one CMS: if (CMS_SPECIFIC == 'Joomla') {}
//	- Code NOT for CMS: if (!CMS_SPECIFIC) {}
/**
 * Build the path of link
 * 
 * ex: path('/dir/of/myfile.jpg')
 * if joomla        return '/joomla/base/to/dir/of/myfile.jpg'
 * if cmsspecific   return '/specific/base/to/dir/of/myfile.jpg'
 * else             return '/dir/of/myfile.jpg'
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