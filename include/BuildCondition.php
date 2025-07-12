<?php

/**
 * Builds a condition for a SQL query based on the provided data.
 */

namespace Genealogy\Include;

use Genealogy\Include\SafeTextDb;

class BuildCondition
{
    public function build($search_name, $search_part): string
    {
        $safeTextDb = new SafeTextDb();

        $text = "LIKE '%" . $safeTextDb->safe_text_db($search_name) . "%'"; // *** Default value: "contains" ***
        if ($search_part == 'equals') {
            $text = "='" . $safeTextDb->safe_text_db($search_name) . "'";
        }
        if ($search_part == 'starts_with') {
            $text = "LIKE '" . $safeTextDb->safe_text_db($search_name) . "%'";
        }
        return $text;
    }
}
