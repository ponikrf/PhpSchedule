<?php
class Util
{
    /**
     * Convert array to string
     *
     * @param array $array
     * @param string $separator
     * @param string $subSeparator
     * @return null|string
     */
    public static function ArrayToString(array $array, $separator = ',', $subSeparator = ',')
    {
        $returnString = NULL;
        $i = 0;
        foreach ($array as $value) {
            if (is_array($value)) $value = static::ArrayToString($value, $subSeparator, $subSeparator);
            if ($i > 0) $value = $separator . $value;
            $returnString .= $value;
            $i++;
        }
        return $returnString;
    }

}