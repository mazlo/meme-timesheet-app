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

}