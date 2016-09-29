<?php

class TisheetUtils 
{

    /**
     * This method filters all controls from the given list of words. E.g. words
     * starting with @, #, or /
     *
     * @param type $words
     */
    public static function filter_controls( $words )
    {
        return array_filter( $words, function( $word )
        {
            // should not happen, however
            if ( empty( $word ) || strlen( $word ) == 1 )
                return false;

            // ignore Contexts and Time and Commands here
            if ( $word{0} == '#' || $word{0} == '@' || $word{0} == '/' )
                return false;

            return true;
        } );
    }

    /**
     * This method substracts all stopwords from the given list of words that were submitted by the user
     *
     * @param type $value
     * @return type
     */
    public static function filter_stopwords( $value )
    {
        return array_diff( array_diff( explode( ' ', $value ), Stopwords::$en ), Stopwords::$de );
    }

    /**
     * Returns true or false, with respect to the operation.
     *
     * @param type $wordsInTisheet
     * @param type $wordsToFilter
     * @param type $andOperator
     * @return boolean
     */
    public static function filter_words( $wordsInTisheet, $wordsToFilter, $andOperator )
    {
        // substract $wordsToFilter from $wordsInTisheet
        $diff = array_diff( $wordsInTisheet, $wordsToFilter );

        if ( $andOperator )
        {
            if ( self::and_criteria_met( $diff, $wordsInTisheet, $wordsToFilter ) )
                return true;

            return false;
        }
        else 
        {
            if ( self::or_criteria_met( $diff, $wordsInTisheet ) )
                return true;

            return false;
        }

        return false;
    }

    /**
     * If diff has same size as substracted sizes of arrays.
     *
     * @param type $diff
     * @param type $wordsInTisheet
     * @param type $wordsToFilter
     * @return type
     */
    static function and_criteria_met( $diff, $wordsInTisheet, $wordsToFilter )
    {
        return count( $diff ) == ( count( $wordsInTisheet ) - count( $wordsToFilter ) );
    }

    /**
     * If diff is at least smaller than original, or criteria is met.
     *
     * @param type $diff
     * @param type $wordsInTisheet
     * @return type
     */
    static function or_criteria_met( $diff, $wordsInTisheet )
    {
        return count( $diff ) < count( $wordsInTisheet );
    }

}