<?php

use Illuminate\Database\Migrations\Migration;

class AddImportantToColumnItems extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table( 'column_items', function( $table )
		{
			$table->boolean( 'important' )->default( 0 )->after( 'position' );
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table( 'column_items', function( $table )
		{
			$table->dropColumn( 'important' );
		});
	}

}