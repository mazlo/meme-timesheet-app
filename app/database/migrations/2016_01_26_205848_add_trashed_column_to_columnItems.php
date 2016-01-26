<?php

use Illuminate\Database\Migrations\Migration;

class AddTrashedColumnToColumnItems extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table( 'column_items', function( $table )
		{
			$table->boolean( 'trashed' )->default( '0' )->after( 'id' );
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
			$table->dropColumn( 'trashed' );
		});
	}

}