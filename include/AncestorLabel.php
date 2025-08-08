<?php

/**
 * Returns the ancestor label for a given generation.
 *
 * @param int $generation The generation number (1-50).
 * @return string The label for the specified generation.
 */

namespace Genealogy\Include;

class AncestorLabel
{
    public function getLabel(int $generation): string
    {
        $label = [];
        $label[1] = '';
        if (__('PROBANT') != 'PROBANT') {
            $label[1] .= __('PROBANT');
        }
        $label[2] = __('parents');
        $label[3] = __('grandparents');
        $label[4] = __('great-grandparents');
        $label[5] = __('great great-grandparents');
        $label[6] = __('3rd great-grandparents');
        $label[7] = __('4th great-grandparents');
        $label[8] = __('5th great-grandparents');
        $label[9] = __('6th great-grandparents');
        $label[10] = __('7th great-grandparents');
        $label[11] = __('8th great-grandparents');
        $label[12] = __('9th great-grandparents');
        $label[13] = __('10th great-grandparents');
        $label[14] = __('11th great-grandparents');
        $label[15] = __('12th great-grandparents');
        $label[16] = __('13th great-grandparents');
        $label[17] = __('14th great-grandparents');
        $label[18] = __('15th great-grandparents');
        $label[19] = __('16th great-grandparents');
        $label[20] = __('17th great-grandparents');
        $label[21] = __('18th great-grandparents');
        $label[22] = __('19th great-grandparents');
        $label[23] = __('20th great-grandparents');
        $label[24] = __('21st great-grandparents');
        $label[25] = __('22nd great-grandparents');
        $label[26] = __('23rd great-grandparents');
        $label[27] = __('24th great-grandparents');
        $label[28] = __('25th great-grandparents');
        $label[29] = __('26th great-grandparents');
        $label[30] = __('27th great-grandparents');
        $label[31] = __('28th great-grandparents');
        $label[32] = __('29th great-grandparents');
        $label[33] = __('30th great-grandparents');
        $label[34] = __('31st great-grandparents');
        $label[35] = __('32nd great-grandparents');
        $label[36] = __('33rd great-grandparents');
        $label[37] = __('34th great-grandparents');
        $label[38] = __('35th great-grandparents');
        $label[39] = __('36th great-grandparents');
        $label[40] = __('37th great-grandparents');
        $label[41] = __('38th great-grandparents');
        $label[42] = __('39th great-grandparents');
        $label[43] = __('40th great-grandparents');
        $label[44] = __('41st great-grandparents');
        $label[45] = __('42nd great-grandparents');
        $label[46] = __('43rd great-grandparents');
        $label[47] = __('44th great-grandparents');
        $label[48] = __('45th great-grandparents');
        $label[49] = __('46th great-grandparents');
        $label[50] = __('47th great-grandparents');

        return isset($label[$generation]) ? $label[$generation] : '';
    }
}
