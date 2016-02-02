<?php

use Illuminate\Database\Migrations\Migration;

class AddShowStoryToUser extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table( 'users', function( $table )
		{
			$table->boolean( 'showStory' )->default( '1' )->after( 'email' );
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table( 'users', function( $table )
		{
			$table->dropColumn( 'showStory' );
		});
	}

}