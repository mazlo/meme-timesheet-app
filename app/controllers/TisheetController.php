<?php

class TisheetController extends BaseController 
{

    const stopwords = array( "a", "a’s", "able", "about", "above", "according", "accordingly", "across", "actually", "after", "afterwards", "again", "against", "ain’t", "all", "allow", "allows", "almost", "alone", "along", "already", "also", "although", "always", "am", "among", "amongst", "an", "and", "another", "any", "anybody", "anyhow", "anyone", "anything", "anyway", "anyways", "anywhere", "apart", "appear", "appreciate", "appropriate", "are", "aren’t", "around", "as", "aside", "ask", "asking", "associated", "at", "available", "away", "awfully", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "behind", "being", "believe", "below", "beside", "besides", "best", "better", "between", "beyond", "both", "brief", "but", "by", "c’mon", "c’s", "came", "can", "can’t", "cannot", "cant", "cause", "causes", "certain", "certainly", "changes", "clearly", "co", "com", "come", "comes", "concerning", "consequently", "consider", "considering", "contain", "containing", "contains", "corresponding", "could", "couldn’t", "course", "currently", "definitely", "described", "despite", "did", "didn’t", "different", "do", "does", "doesn’t", "doing", "don’t", "done", "down", "downwards", "during", "each", "edu", "eg", "eight", "either", "else", "elsewhere", "enough", "entirely", "especially", "et", "etc", "even", "ever", "every", "everybody", "everyone", "everything", "everywhere", "ex", "exactly", "example", "except", "far", "few", "fifth", "first", "five", "followed", "following", "follows", "for", "former", "formerly", "forth", "four", "from", "further", "furthermore", "get", "gets", "getting", "given", "gives", "go", "goes", "going", "gone", "got", "gotten", "greetings", "had", "hadn’t", "happens", "hardly", "has", "hasn’t", "have", "haven’t", "having", "he", "he’s", "hello", "help", "hence", "her", "here", "here’s", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "hi", "him", "himself", "his", "hither", "hopefully", "how", "howbeit", "however", "i’d", "i’ll", "i’m", "i’ve", "ie", "if", "ignored", "immediate", "in", "inasmuch", "inc", "indeed", "indicate", "indicated", "indicates", "inner", "insofar", "instead", "into", "inward", "is", "isn’t", "it", "it’d", "it’ll", "it’s", "its", "itself", "just", "keep", "keeps", "kept", "know", "knows", "known", "last", "lately", "later", "latter", "latterly", "least", "less", "lest", "let", "let’s", "like", "liked", "likely", "little", "look", "looking", "looks", "ltd", "mainly", "many", "may", "maybe", "me", "mean", "meanwhile", "merely", "might", "more", "moreover", "most", "mostly", "much", "must", "my", "myself", "name", "namely", "nd", "near", "nearly", "necessary", "need", "needs", "neither", "never", "nevertheless", "new", "next", "nine", "no", "nobody", "non", "none", "noone", "nor", "normally", "not", "nothing", "novel", "now", "nowhere", "obviously", "of", "off", "often", "oh", "ok", "okay", "old", "on", "once", "one", "ones", "only", "onto", "or", "other", "others", "otherwise", "ought", "our", "ours", "ourselves", "out", "outside", "over", "overall", "own", "particular", "particularly", "per", "perhaps", "placed", "please", "plus", "possible", "presumably", "probably", "provides", "que", "quite", "qv", "rather", "rd", "re", "really", "reasonably", "regarding", "regardless", "regards", "relatively", "respectively", "right", "said", "same", "saw", "say", "saying", "says", "second", "secondly", "see", "seeing", "seem", "seemed", "seeming", "seems", "seen", "self", "selves", "sensible", "sent", "serious", "seriously", "seven", "several", "shall", "she", "should", "shouldn’t", "since", "six", "so", "some", "somebody", "somehow", "someone", "something", "sometime", "sometimes", "somewhat", "somewhere", "soon", "sorry", "specified", "specify", "specifying", "still", "sub", "such", "sup", "sure", "t’s", "take", "taken", "tell", "tends", "th", "than", "thank", "thanks", "thanx", "that", "that’s", "thats", "the", "their", "theirs", "them", "themselves", "then", "thence", "there", "there’s", "thereafter", "thereby", "therefore", "therein", "theres", "thereupon", "these", "they", "they’d", "they’ll", "they’re", "they’ve", "think", "third", "this", "thorough", "thoroughly", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "took", "toward", "towards", "tried", "tries", "truly", "try", "trying", "twice", "two", "un", "under", "unfortunately", "unless", "unlikely", "until", "unto", "up", "upon", "us", "use", "used", "useful", "uses", "using", "usually", "value", "various", "very", "via", "viz", "vs", "want", "wants", "was", "wasn’t", "way", "we", "we’d", "we’ll", "we’re", "we’ve", "welcome", "well", "went", "were", "weren’t", "what", "what’s", "whatever", "when", "whence", "whenever", "where", "where’s", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "who’s", "whoever", "whole", "whom", "whose", "why", "will", "willing", "wish", "with", "within", "without", "won’t", "wonder", "would", "would", "wouldn’t", "yes", "yet", "you", "you’d", "you’ll", "you’re", "you’ve", "your", "yours", "yourself", "yourselves", "zero" );

    /**
    *   Retrieves all tisheets from the database.
    */
    public function index( $day = '' )
    {
        if ( empty( $day ) || $day == 'today' )
            $day = date( 'Y-m-d', time() );
        
        $tisheets = Tisheet::where( 'day', $day )
            ->where( 'user_id', Auth::user()->id )
            ->orderBy( 'index_' )
            ->orderBy( 'created_at' )
            ->get();

        $timesheet = Timesheet::where( 'day', $day )
            ->where( 'user_id', Auth::user()->id )
            ->first();

        $timeline = SummaryController::byDayAndPeriodGroupByContext( $day, 'today' )->get();

        $oneDay = 60*60*24;

        return View::make( 'index' )
            ->with( 'tisheets', $tisheets )
            // for yesterday substract 24h of the day given
            ->with( 'yesterday', date( 'Y-m-d', strtotime( $day ) - $oneDay ) )
            ->with( 'today', $day )
            ->with( 'todayForReal', $day === date( 'Y-m-d', time() ) )
            // for tomorrow add 24h of the day given
            ->with( 'tomorrow', date( 'Y-m-d', strtotime( $day ) + $oneDay ) )
            ->with( 'timeline', $timeline )
            ->with( 'timesheet', $timesheet );
    }

    /**
    *
    */
    public function timeline( $day )
    {
        $timeline = SummaryController::byDayAndPeriodGroupByContext( $day, 'today' )->get();

        return View::make( 'ajax.timeline' )->with( 'timeline', $timeline );
    }

    /**
    *
    */
    public function autocomplete( $day )
    {
        $dayAsTime = strtotime( $day );

        // TODO ZL distinct
        $tisheets = Tisheet::where( 'user_id', Auth::user()->id )
            ->where( 'day', '>=', date( 'Y-m-d', strtotime( '-1 month', $dayAsTime ) ) )
            ->where( 'day', '<=', $day )
            ->whereNotNull( 'description' )
            ->orderBy( 'updated_at', 'desc' )
            ->groupBy( 'description' )
            ->get();

        return View::make( 'ajax.tisheets-autocomplete' )
            ->with( 'tisheets', $tisheets );
    }

    /**
    *   Adds a new tisheet to the database.
    */
    public function add( $day )
    {
        $tisheet = new Tisheet();

        $tisheet->user()->associate( Auth::user() );
        $tisheet->day = $day;

		// save tisheet to obtain an id
		$tisheet->save();
		
        if ( Input::has( 'vl' ) )
        {
            $value = Input::get( 'vl' );
            
            TisheetController::syncContexts( $tisheet, $value );
            TisheetController::syncTime( $tisheet, $value );
            TisheetController::syncWords( $tisheet, $value );

            $tisheet->description = $value;
        }
        
        $tisheet->save();

        return Response::json( array( 
            'status' => 'ok', 
            'action' => 'add', 
            'id' => $tisheet->id, 
            'time' => $tisheet->time_start,
            'context' => $tisheet->context ? substr( $tisheet->context->prefLabel, 1 ) : null
        ) );
    }

    /**
    *
    */
    public function update( $day, $id )
    {
        // add 

        if ( $id == 'undefined' )
            return $this->add( $day );

        // update
        
        $tisheet = Tisheet::where( 'id', '=', $id )
            ->where( 'user_id', Auth::user()->id )
            ->first();

        if ( Input::has( 'vl' ) )
        {
            $value = Input::get( 'vl' );
            
			TisheetController::syncContexts( $tisheet, $value );
            TisheetController::syncTime( $tisheet, $value );
            TisheetController::syncWords( $tisheet, $value );

            $tisheet->description = $value;
        }
        
        // update time spent
        else if ( Input::has( 'ts' ) )
        {
            $tisheet->time_spent = Input::get( 'ts' );
            $tisheet->time_start = Input::get( 'tm' );
        }
        
        // update planned flag
        else if ( Input::has( 'pl' ) )
            $tisheet->planned = Input::get( 'pl' ) == 'true' ? true : false;

        // update day of tisheet -> move
        else if ( Input::has( 'mv' ) )
        {
            $dayAsTime = strtotime( $day );
            $tomorrow = date( 'Y-m-d', strtotime( 'tomorrow', $dayAsTime ) );
            
            $tisheet->day = $tomorrow;
        }
        
        // note of tisheet will be updated via NoteController

        $tisheet->save();

        return Response::json( array( 
            'status' => 'ok', 
            'action' => 'update', 
            'id' => $tisheet->id, 
            'time' => $tisheet->time_start,
            'context' => $tisheet->context ? substr( $tisheet->context->prefLabel, 1 ) : null
        ) );
    }

    /**
    *
    */
    public function delete( $day, $id )
    {
        $tisheet = Tisheet::find( $id );
        
        if ( $tisheet->note )
            $tisheet->note->delete();
        
		$tisheet->subContexts()->detach();
        $tisheet->delete();

        return 'true';
    }

    /**
     * Parses the given value for Words. Each Word will be associated with the
	 * given Tisheet and Context then.
     * 
     * @param type $tisheet
     * @param type $value
     * @return type
     */
    public static function syncWords( &$tisheet, $value ) 
    {
        $words = TisheetController::parseWords( $value );

        $wordsToSync = array_map( function( $word ) use ($tisheet)
        {
			$context = empty( $tisheet->context_id ) ? '0' : $tisheet->context_id;
            
			return array( 
				'id' => $tisheet->id . $word,
				'context_id' => $context
            );
        }, $words );
        
        $tisheet->words()->sync( array_combine( $words, $wordsToSync ) );
    }

    /**
     * Parses the given value for Time-statements. Time-statements are identified
	 * by the @ symbol.
	 *
	 * @param type $tisheet
	 * @param type $value
	 * @return type
    */
    public static function syncTime( &$tisheet, $value )
    {
        $time = array_filter( explode( ' ', $value ), function( $word )
        {
            if( empty( $word ) || strlen( $word ) == 1 )
                return false;

            if( $word{0} == '@' )
                return true;

            return false;
        });

        $timeStart = reset( $time );

        if ( $timeStart )
            $tisheet->time_start = substr( $timeStart, 1 );
    }

	/**
	 * Parses the given value for Contexts. Contexts are identified by the # symbol.
	 * Takes the first Context as first level Context.
	 * 
	 * @param type $tisheet
	 * @param type $value
	 * @return type
	 */
	public static function syncContexts( &$tisheet, $value ) 
	{
		$contexts = TisheetController::parseContexts( $value );
		
		// reset first level Concept if it's empty
		if ( count( $contexts ) == 0 ) 
		{
			$tisheet->context_id = null;
			
			return;
		}

		// assign first level Concept to Tisheet
		$mainContext = Context::find( reset( $contexts ) );
		$tisheet->context()->associate( $mainContext );
	}

	/**
	 * the return value of this function is an array of Context-ids
	 * according to the submitted value, in preparation for the association 
	 * of Tisheet to Context
	 * 
	 * @param type $value
	 * @return type
	 */
	public static function parseContexts( $value )
	{
		return array_map( function( $word )
		{
			// return an array of Context-ids
			$context = Context::where( 'prefLabel', $word )
                ->where( 'user_id', Auth::user()->id )
                ->first();

			// create new and associate
			if ( empty( $context ) )
			{                
				$context = new Context();
				$context->prefLabel = $word;
                $context->user()->associate( Auth::user() );
				$context->save();
			}

			// 2nd dimension consists of foreign-key ids
			return $context->id;
		},  
			// form an array of Contexts that was parsed from the text
			array_filter( explode( ' ', $value ), function( $word )
			{
                if( empty( $word ) || strlen( $word ) == 1 )
                    return false;

				if( $word{0} == '#' )
					return true;

				return false;
			})
		);
	}

	/**
	 * the return value of this function is an array of Word-ids
	 * according to the submitted value, in preparation for the association 
	 * of Tisheet to Words
	 * 
	 * @param type $value
	 * @return type
	 */
    public static function parseWords( $value )
    {	
        $filteredWords = TisheetController::filter_stopwords( $value );

        return array_map( function( $value )
        {
            $word = Word::where( 'value', $value )
                ->where( 'user_id', Auth::user()->id )
                ->first();

            if ( empty( $word ) )
            {
                $word = new Word();
                $word->value = $value;
                $word->user()->associate( Auth::user() );
                $word->save();
            }

            return $word->id;
        },  
            array_filter( $filteredWords, function( $word )
            {
                // should not happen, however
                if ( empty( $word ) || strlen( $word ) == 1 )
                    return false;

                // ignore Contexts here
                if ( $word{0} == '#' )
                    return false;

                return true;
            })
        );
    }

    /**
     * This method substracts all stopwords from the given list of words that were submitted by the user
     *
     * @param type $value
     * @return type
     */
    public static function filter_stopwords( $value ) 
    {
        return array_diff( explode( ' ', $value ), TisheetController::stopwords );
    }
}
