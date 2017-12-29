<?php
/**
 * Class MemorablePasswordTest
 *
 * @package Memorable_Password
 */

class MemorablePasswordTest extends WP_UnitTestCase {

    public $mp;
    public $plugin_name;

    public function setUp() {
        parent::setUp();  

        $this->plugin_name = 'memorable-password';

        $this->mp = new memorable_password();
        $this->mp->register();
    }
        
    public function tearDown() {
        parent::tearDown();  
    }

    function test_kind_is_animal() {
        $option = array(
            'kind' => array( 'animal' ),
            'uppercase' => false,
            'symbol' => false,
        );
        update_option( $this->plugin_name, $option );

        $password = wp_generate_password();

        $words = $this->mp->get_animal_words();

        $this->assertContains( $password, $words );
    }

    function test_kind_is_country() {
        $option = array(
            'kind' => array( 'country' ),
            'uppercase' => false,
            'symbol' => false,
        );
        update_option( $this->plugin_name, $option );

        $password = wp_generate_password();

        $words = $this->mp->get_country_words();

        $this->assertContains( $password, $words );
    }

    function test_kind_is_food() {
        $option = array(
            'kind' => array( 'food' ),
            'uppercase' => false,
            'symbol' => false,
        );
        update_option( $this->plugin_name, $option );

        $password = wp_generate_password();

        $words = $this->mp->get_food_words();

        $this->assertContains( $password, $words );
    }

    function test_kind_is_all() {
        $option = array(
            'kind' => array( 'animal', 'country', 'food' ),
            'uppercase' => false,
            'symbol' => false,
        );
        update_option( $this->plugin_name, $option );

        $password = wp_generate_password();

        $words_animal = $this->mp->get_animal_words();
        $words_country = $this->mp->get_country_words();
        $words_food = $this->mp->get_food_words();

        $keywords = explode( '-', $password );

        $this->assertCount( 3, $keywords );
        $this->assertTrue(
            in_array( $keywords[0], $words_animal ) ||
            in_array( $keywords[1], $words_animal ) ||
            in_array( $keywords[2], $words_animal )
        );
        $this->assertTrue(
            in_array( $keywords[0], $words_country ) ||
            in_array( $keywords[1], $words_country ) ||
            in_array( $keywords[2], $words_country )
        );
        $this->assertTrue(
            in_array( $keywords[0], $words_food ) ||
            in_array( $keywords[1], $words_food ) ||
            in_array( $keywords[2], $words_food )
        );
    }

    function test_uppercase_is_enabled() {
        $option = array(
            'kind' => array( 'animal' ),
            'uppercase' => true,
            'symbol' => false,
        );
        update_option( $this->plugin_name, $option );

        $password = wp_generate_password();

        $words = $this->mp->get_animal_words();

        $this->assertRegExp( '/[A-Z]{1}/', $password );
        $this->assertContains( strtolower( $password ), $words );
    }

    function test_symbol_is_enabled() {
        $option = array(
            'kind' => array( 'animal', 'country' ),
            'uppercase' => false,
            'symbol' => true,
        );
        update_option( $this->plugin_name, $option );

        $password = wp_generate_password();

        $words = $this->mp->get_animal_words();

        $this->assertRegExp( '/[!@#$%^&*()-_ \[\]{}<>~`+=,.;:\/\?|]/', $password );
    }
}
