<?php
/*
 * Copyright 2020 by Heiko Schäfer <heiko@rangun.de>
 *
 * This file is part of webvirus.
 *
 * webvirus is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * webvirus is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with webvirus.  If not, see <http://www.gnu.org/licenses/>.
 */

trait LevenshteinTraits {

    private function titleNormalizer($title) {
        return trim(preg_replace('~[^\x00-\xFF]~u', "", $title));
    }

    private function toUnicodeCharArray($str) {
        return preg_split('//u', $str, -1, PREG_SPLIT_NO_EMPTY);
    }

    private function damerauLevenshteinDistance($source, $target) {

        $sourceLength = count($source);
        $targetLength = count($target);

        if($sourceLength == 0) return $targetLength;
        if($targetLength == 0) return $sourceLength;

        $dist = array(array());

        for($i = 0; $i <= $sourceLength; $i++) $dist[$i][0] = $i;
        for($j = 0; $j <= $targetLength; $j++) $dist[0][$j] = $j;

        for($i = 1; $i <= $sourceLength; $i++) {

        $sca = $source[$i - 1];

        for($j = 1; $j <= $targetLength; $j++) {

            $tca = $target[$j - 1];
            $cost = $sca == $tca ? 0 : 1;

            $dist[$i][$j] = min(min($dist[$i - 1][$j] + 1, $dist[$i][$j - 1] + 1), $dist[$i - 1][$j - 1] + $cost);

            if($j > 1 && $i > 1 && $sca == $target[$j - 2] && $source[$i - 2] == $tca) {
            $dist[$i][$j] = min($dist[$i][$j], $dist[$i - 2][$j - 2] + $cost);
            }
        }
        }

        return $dist[$sourceLength][$targetLength];
    }
}

// indent-mode: cstyle; indent-width: 4; keep-extra-spaces: false; replace-tabs-save: false; replace-tabs: false; word-wrap: false; remove-trailing-space: true;
?>
