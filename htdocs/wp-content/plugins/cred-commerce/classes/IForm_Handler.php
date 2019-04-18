<?php

/**
 * Class CRED Commerce Form Handler
 */
interface ICRED_Commerce_Form_Handler {

	public function getProducts();

	public function getProduct( $id );

	public function getRelativeProduct( $id );

	public function getAbsoluteProduct( $id2 );

	public function getCredData();

	public function getNewProductLink();

	public function getCustomer( $post_id, $form_id );
}