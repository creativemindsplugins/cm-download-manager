<?php

class CMDM_WPHelper {
	
	public static function splitDescriptionByWordsCount($text, $splitLength=0)
    {
        
        $wordArray = explode(' ', $text);
        if( sizeof($wordArray) > $splitLength )
        {
            $firstWordArray = array_slice($wordArray, 0, $splitLength);
            $lastWordArray = array_slice($wordArray, $splitLength, sizeof($wordArray));

            $firstString = implode(' ', $firstWordArray);
            $lastString = implode(' ', $lastWordArray);
            return array($firstString, $lastString);
        }

        return array($text);
    }
}