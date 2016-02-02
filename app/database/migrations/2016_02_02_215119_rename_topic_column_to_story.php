<?php

use Illuminate\Database\Migrations\Migration;

class RenameTopicColumnToStory extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table( 'timesheets', function( $table )
		{
			$table->renameColumn( 'topic', 'story' );
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table( 'timesheets', function ( $table )
		{
			$table->renameColumn( 'story', 'topic' );
		});
	}

}