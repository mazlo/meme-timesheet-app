<?php

use Illuminate\Database\Migrations\Migration;

class AddTrashedColumnToColumns extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table( 'columns', function( $table )
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
		Schema::table( 'columns', function( $table )
		{
			$table->dropColumn( 'trashed' );
		});
	}

}