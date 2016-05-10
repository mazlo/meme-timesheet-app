<?php

use Illuminate\Database\Migrations\Migration;

class AddPositionToColumnItem extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table( 'column_items', function( $table )
		{
			$table->integer( 'position' )->default( 0 )->after( 'label' );
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
			$table->dropColumn( 'position' );
		});
	}

}