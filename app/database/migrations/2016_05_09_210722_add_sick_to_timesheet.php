<?php

use Illuminate\Database\Migrations\Migration;

class AddSickToTimesheet extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table( 'timesheets', function( $table )
		{
			$table->boolean( 'sick' )->default( '0' )->after( 'day' );
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table( 'timesheets', function( $table )
		{
			$table->dropColumn( 'sick' );
		});
	}

}