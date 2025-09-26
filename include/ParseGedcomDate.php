<?php

/**
 * Parse a GEDCOM date string into its components.
 * Supports formats like "4 JAN 1844", "JAN 1844", "1844", "15 MAR 44 BC", "ABT 1844", "BET 1999 AND 2003", etc.
 *
 * @param string $gedcomDate The GEDCOM date string.
 * @return array An associative array with keys 'year', 'month', 'day'. Values are integers or null if unknown.
 */

namespace Genealogy\Include;

class ParseGedcomDate
{
    public function parse($gedcomDate): array
    {
        $result = [
            'year' => null,
            'month' => null,
            'day' => null,
            'end_year' => null,
            'end_month' => null,
            'end_day' => null,
        ];

        if (empty($gedcomDate)) {
            return $result;
        }

        $gedcomDate = strtoupper(trim($gedcomDate));

        // Remove qualifiers (ABT, BEF, AFT, CAL, EST, ABOUT, BEFORE, AFTER, CIRCA)
        $gedcomDate = preg_replace('/^(ABT|AFT|BEF|CAL|EST|ABOUT|BEFORE|AFTER|CIRCA)\s+/i', '', $gedcomDate);

        // Handle "FROM ... TO ..." ranges
        if (preg_match('/^FROM (.+) TO (.+)$/', $gedcomDate, $m)) {
            $start = $this->parseSingleDate(trim($m[1]));
            $end = $this->parseSingleDate(trim($m[2]));
            $result['year'] = $start['year'];
            $result['month'] = $start['month'];
            $result['day'] = $start['day'];
            $result['end_year'] = $end['year'];
            $result['end_month'] = $end['month'];
            $result['end_day'] = $end['day'];
            return $result;
        }

        // Handle "BET ... AND ..." ranges
        if (preg_match('/^BET(?:WEEN)? (.+) AND (.+)$/', $gedcomDate, $m)) {
            $start = $this->parseSingleDate(trim($m[1]));
            $end = $this->parseSingleDate(trim($m[2]));
            $result['year'] = $start['year'];
            $result['month'] = $start['month'];
            $result['day'] = $start['day'];
            $result['end_year'] = $end['year'];
            $result['end_month'] = $end['month'];
            $result['end_day'] = $end['day'];
            return $result;
        }

        // Fallback: single date
        $single = $this->parseSingleDate($gedcomDate);
        $result['year'] = $single['year'];
        $result['month'] = $single['month'];
        $result['day'] = $single['day'];
        return $result;
    }

    // Helper for single date parsing
    private function parseSingleDate($gedcomDate): array
    {
        $result = [
            'year' => null,
            'month' => null,
            'day' => null,
        ];

        $bc = false;
        if (strpos($gedcomDate, 'BC') !== false) {
            $bc = true;
            $gedcomDate = str_replace('BC', '', $gedcomDate);
        }

        // "4 JAN 1844"
        if (preg_match('/^(\d{1,2})\s+([A-Z]{3})\s+(\d{3,4})$/', $gedcomDate, $m)) {
            $months = ['JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6, 'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12];
            $result['day'] = (int)$m[1];
            $result['month'] = $months[$m[2]] ?? null;
            $result['year'] = (int)$m[3];
            if ($bc) $result['year'] = -$result['year'];
            return $result;
        }
        // "JAN 1844"
        if (preg_match('/^([A-Z]{3})\s+(\d{3,4})$/', $gedcomDate, $m)) {
            $months = ['JAN' => 1, 'FEB' => 2, 'MAR' => 3, 'APR' => 4, 'MAY' => 5, 'JUN' => 6, 'JUL' => 7, 'AUG' => 8, 'SEP' => 9, 'OCT' => 10, 'NOV' => 11, 'DEC' => 12];
            $result['month'] = $months[$m[1]] ?? null;
            $result['year'] = (int)$m[2];
            if ($bc) $result['year'] = -$result['year'];
            return $result;
        }
        // "1844"
        if (preg_match('/^(\d{3,4})$/', $gedcomDate, $m)) {
            $result['year'] = (int)$m[1];
            if ($bc) $result['year'] = -$result['year'];
            return $result;
        }
        return $result;
    }
}
