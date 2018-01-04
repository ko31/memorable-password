<?php
/**
 * Plugin Name: Memorable Password
 * Plugin URI:  https://github.com/ko31/memorable-password
 * Description: This plugin generates a memorable and strong password.
 * Version:     1.0.0
 * Author:      Ko Takagi
 * Author URI:  https://go-sign.info/
 * License:     GPLv2
 * Text Domain: memorable-password
 * Domain Path: /languages
 */

/*  Copyright (c) 2017 Ko Takagi (https://go-sign.info/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY sor FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$memorablePassword = new memorable_password();
$memorablePassword->register();

class memorable_password {

    private $version = '';
    private $langs   = '';

    function __construct()
    {
        $data = get_file_data(
            __FILE__,
            array('ver' => 'Version', 'langs' => 'Domain Path')
        );
        $this->version = $data['ver'];
        $this->langs   = $data['langs'];
        $this->plugin_name = 'memorable-password';
    }

    public function register()
    {
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
    }

    public function plugins_loaded()
    {
        load_plugin_textdomain(
            'memorable-password',
            false,
            dirname( plugin_basename( __FILE__ ) ) . $this->langs
        );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
        add_filter( 'random_password', array( $this, 'random_password' ), 10, 1 );
    }

    public function admin_menu()
    {
        add_options_page(
            __( 'Memorable Password', 'memorable-password' ),
            __( 'Memorable Password', 'memorable-password' ),
            'manage_options',
            'memorable-password',
            array( $this, 'options_page' )
        );
    }

    public function admin_init()
    {
        if ( isset($_POST['memorable-password-nonce']) && $_POST['memorable-password-nonce'] ) {
            if ( check_admin_referer( 'memorable-password', 'memorable-password-nonce' ) ) {
                global $wpdb;
                $e = new WP_Error();
                if ( isset( $_POST['kind'] ) && $_POST['kind'] ) {
                    if ( !is_array( $_POST['kind'] ) ) {
                        $e->add( 'error', esc_html__( 'Invalid kind of words', 'memorable-password' ) );
                    } else {
                        $kind = array();
                        foreach ( array( 'animal', 'country', 'food' ) as $_kind ) {
                            if ( in_array( $_kind , $_POST['kind'] ) ) {
                                $kind[] = $_kind;
                            }
                        }
                        if ( empty( $kind ) ) {
                            $e->add( 'error', esc_html__( 'Please select at least one kind of words', 'memorable-password' ) );
                        }
                    }
                } else {
                    $e->add( 'error', esc_html__( 'Please select at least one kind of words', 'memorable-password' ) );
                }
                if ( isset( $_POST['uppercase'] ) && $_POST['uppercase'] ) {
                    $uppercase = 1;
                } else {
                    $uppercase = '';
                }
                if ( isset( $_POST['uppercase'] ) && $_POST['uppercase'] ) {
                    $uppercase = 1;
                } else {
                    $uppercase = '';
                }
                if ( isset( $_POST['symbol'] ) && $_POST['symbol'] ) {
                    $symbol = 1;
                } else {
                    $symbol = '';
                }

                if ( $e->get_error_code() ) {
                    set_transient( 'memorable-password-errors', $e->get_error_messages(), 5 );
                } else {
                    $option = get_option( $this->plugin_name );
                    $option['kind'] = $kind;
                    $option['uppercase'] = $uppercase;
                    $option['symbol'] = $symbol;
                    update_option( $this->plugin_name, $option );
                    set_transient( 'memorable-password-updated', true, 5 );
                }
                
                wp_redirect( 'options-general.php?page=memorable-password' );
            }
        }
    }

    public function admin_notices()
    {
?>
        <?php if ( $messages = get_transient( 'memorable-password-errors' ) ): ?>
            <div class="error">
            <ul>
            <?php foreach ( $messages as $message ): ?>
                <li><?php echo esc_html( $message );?></li>
            <?php endforeach; ?>
            </ul>
            </div>
        <?php endif; ?>
        <?php if ( $messages = get_transient( 'memorable-password-updated' ) ): ?>
            <div class="updated">
            <ul>
                <li><?php esc_html_e( 'Password has been updated.', 'memorable-password' );?></li>
            </ul>
            </div>
        <?php endif; ?>
<?php
    }

    public function options_page()
    {
        $option = get_option( $this->plugin_name );
        $kind = isset( $option['kind'] ) ? $option['kind'] : array();
        $uppercase = isset( $option['uppercase'] ) ? $option['uppercase'] : '';
        $symbol = isset( $option['symbol'] ) ? $option['symbol'] : '';
?>
<div id="memorable-password" class="wrap">
<h2>Memorable Password</h2>

<form method="post" action="<?php echo esc_attr($_SERVER['REQUEST_URI']); ?>">
<?php wp_nonce_field( 'memorable-password', 'memorable-password-nonce' ); ?>

<table class="form-table">
<tbody>
<tr>
<th scope="row"><label for="kind"><?php esc_html_e( 'Kind of words', 'memorable-password' );?></label></th>
<td>
<fieldset>
<legend class="screen-reader-text"><span><?php esc_html_e( 'Kind of words', 'memorable-password' );?></span></legend>
<label for="kind_animal"><input name="kind[]" type="checkbox" id="kind_animal" value="animal" <?php if ( in_array( 'animal' , $kind ) ) { echo "checked";} ?>/><?php esc_html_e( 'Animal', 'memorable-password' );?></label>&nbsp;
<label for="kind_country"><input name="kind[]" type="checkbox" id="kind_country" value="country" <?php if ( in_array( 'country' , $kind ) ) { echo "checked";} ?>/><?php esc_html_e( 'country', 'memorable-password' );?></label>&nbsp;
<label for="kind_food"><input name="kind[]" type="checkbox" id="kind_food" value="food" <?php if ( in_array( 'food' , $kind ) ) { echo "checked";} ?>/><?php esc_html_e( 'food', 'memorable-password' );?></label>&nbsp;
</fieldset>
</td>
</tr>
<tr>
<th scope="row"><label for="uppercase"><?php esc_html_e( 'Uppercase', 'memorable-password' );?></label></th>
<td>
<fieldset>
<legend class="screen-reader-text"><span><?php esc_html_e( 'Uppercase', 'memorable-password' );?></span></legend>
<label for="include_uppercase"><input name="uppercase" type="checkbox" id="include_uppercase" value="1" <?php if ( $uppercase ) { echo "checked";} ?>/><?php esc_html_e( 'Include Uppercase characters', 'memorable-password' );?></label>&nbsp;
</fieldset>
</td>
</tr>
<tr>
<th scope="row"><label for="delimiter"><?php esc_html_e( 'Delimiter', 'memorable-password' );?></label></th>
<td>
<fieldset>
<legend class="screen-reader-text"><span><?php esc_html_e( 'Delimiter', 'memorable-password' );?></span></legend>
<label for="symbol"><input name="symbol" type="checkbox" id="symbol" value="1" <?php if ( $symbol ) { echo "checked";} ?>/><?php esc_html_e( 'Use special symbols for delimiter', 'memorable-password' );?></label>&nbsp;
</fieldset>
</td>
</tr>
</tbody>
</table>

<p class="submit">
<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php esc_html_e( 'Update', 'memorable-password' );?>">
</p>
</form>
</div><!-- #memorable-password -->
<?php
    }

    public function random_password( $password )
    {
        $option = get_option( $this->plugin_name );
        $kind = isset( $option['kind'] ) ? $option['kind'] : array();
        $uppercase = isset( $option['uppercase'] ) ? $option['uppercase'] : '';
        $symbol = isset( $option['symbol'] ) ? $option['symbol'] : '';

        $words = array();
        if ( in_array( 'animal' , $kind ) ) {
            $animals = $this->get_animal_words();
            $words[] = $animals[mt_rand( 0, count( $animals ) )];
        }
        if ( in_array( 'country' , $kind ) ) {
            $countries = $this->get_country_words();
            $words[] = $countries[mt_rand( 0, count( $countries ) )];
        }
        if ( in_array( 'food' , $kind ) ) {
            $foods = $this->get_food_words();
            $words[] = $foods[mt_rand( 0, count( $foods ) )];
        }
        if ( empty( $words ) ) {
            return $password;
        }

        shuffle( $words );

        if ( !empty( $uppercase ) ) {
            foreach ( $words as $k => $v ) {
                $words[$k] = $this->uppercase_one_letter( $v );
            }
        }

        if ( !empty( $symbol ) ) {
            $chars = $this->get_symbol_character();
            $password = implode( substr( $chars, wp_rand( 0, strlen( $chars ) - 1 ), 1 ), $words );
        } else {
            $password = implode( '-', $words );
        }

        return $password;
    }

    public function uppercase_one_letter( $word )
    {
        $chars = preg_split( '//', $word, -1, PREG_SPLIT_NO_EMPTY );
        $position = mt_rand( 0, count( $chars ) - 1 );
        $chars[$position] = strtoupper( $chars[$position] );
        $word = implode( '', $chars );

        return $word;
    }

    public function get_symbol_character()
    {
        $special_chars = '!@#$%^&*()';
        $extra_special_chars = '-_ []{}<>~`+=,.;:/?|';

        return $special_chars . $extra_special_chars;
    }

    public function get_animal_words()
    {
        return array('aardvark', 'albatross', 'alligator', 'alpaca', 'ant', 'anteater', 'antelope', 'ape', 'armadillo', 'baboon', 'badger', 'barracuda', 'bat', 'bear', 'beaver', 'bee', 'bird', 'bison', 'boar', 'butterfly', 'camel', 'caribou', 'cassowary', 'cat', 'caterpillar', 'cattle', 'chamois', 'cheetah', 'chicken', 'chimpanzee', 'chinchilla', 'chough', 'coati', 'cobra', 'cockroach', 'cod', 'cormorant', 'coyote', 'crab', 'crocodile', 'crow', 'curlew', 'deer', 'dinosaur', 'dog', 'dolphin', 'donkey', 'dotterel', 'dove', 'dragonfly', 'duck', 'dugong', 'dunlin', 'eagle', 'echidna', 'eel', 'elephant', 'elk', 'emu', 'falcon', 'ferret', 'finch', 'fish', 'flamingo', 'fly', 'fox', 'frog', 'gaur', 'gazelle', 'gerbil', 'giraffe', 'gnat', 'goat', 'goose', 'gorilla', 'goshawk', 'grasshopper', 'grouse', 'guanaco', 'gull', 'hamster', 'hare', 'hawk', 'hedgehog', 'heron', 'herring', 'hippopotamus', 'hornet', 'horse', 'hummingbird', 'hyena', 'ibex', 'ibis', 'jackal', 'jaguar', 'jay', 'jellyfish', 'kangaroo', 'kinkajou', 'koala', 'kouprey', 'kudu', 'lapwing', 'lark', 'lemur', 'leopard', 'lion', 'llama', 'lobster', 'locust', 'loris', 'louse', 'lyrebird', 'magpie', 'mallard', 'manatee', 'mandrill', 'mink', 'mongoose', 'monkey', 'moose', 'mouse', 'mosquito', 'narwhal', 'newt', 'nightingale', 'octopus', 'okapi', 'opossum', 'ostrich', 'otter', 'owl', 'oyster', 'parrot', 'panda', 'partridge', 'peafowl', 'pelican', 'penguin', 'pheasant', 'pork', 'pigeon', 'pony', 'porcupine', 'porpoise', 'quail', 'quelea', 'quetzal', 'rabbit', 'raccoon', 'rat', 'raven', 'reindeer', 'rhinoceros', 'salamander', 'salmon', 'sandpiper', 'sardine', 'seahorse', 'shark', 'sheep', 'shrew', 'skunk', 'sloth', 'snail', 'snake', 'spider', 'squirrel', 'starling', 'swan', 'tapir', 'tarsier', 'termite', 'tiger', 'toad', 'turtle', 'wallaby', 'walrus', 'wasp', 'weasel', 'whale', 'wolf', 'wolverine', 'wombat', 'wren', 'yak', 'zebra');
    }

    public function get_country_words()
    {
        return array('afghanistan', 'albania', 'algeria', 'andorra', 'angola', 'antiguaanddeps', 'argentina', 'armenia', 'australia', 'austria', 'azerbaijan', 'bahamas', 'bahrain', 'bangladesh', 'barbados', 'belarus', 'belgium', 'belize', 'benin', 'bhutan', 'bolivia', 'bosniaherzegovina', 'botswana', 'brazil', 'brunei', 'bulgaria', 'burkina', 'burundi', 'cambodia', 'cameroon', 'canada', 'capeverde', 'centralafricanrep', 'chad', 'chile', 'china', 'colombia', 'comoros', 'congo', 'congodemocraticrep', 'costarica', 'croatia', 'cuba', 'cyprus', 'czechrepublic', 'denmark', 'djibouti', 'dominica', 'dominicanrepublic', 'easttimor', 'ecuador', 'egypt', 'elsalvador', 'equatorialguinea', 'eritrea', 'estonia', 'ethiopia', 'fiji', 'finland', 'france', 'gabon', 'gambia', 'georgia', 'germany', 'ghana', 'greece', 'grenada', 'guatemala', 'guinea', 'guineabissau', 'guyana', 'haiti', 'honduras', 'hungary', 'iceland', 'india', 'indonesia', 'iran', 'iraq', 'ireland', 'israel', 'italy', 'ivorycoast', 'jamaica', 'japan', 'jordan', 'kazakhstan', 'kenya', 'kiribati', 'koreanorth', 'koreasouth', 'kosovo', 'kuwait', 'kyrgyzstan', 'laos', 'latvia', 'lebanon', 'lesotho', 'liberia', 'libya', 'liechtenstein', 'lithuania', 'luxembourg', 'macedonia', 'madagascar', 'malawi', 'malaysia', 'maldives', 'mali', 'malta', 'marshallislands', 'mauritania', 'mauritius', 'mexico', 'micronesia', 'moldova', 'monaco', 'mongolia', 'montenegro', 'morocco', 'mozambique', 'myanmar', 'namibia', 'nauru', 'nepal', 'netherlands', 'newzealand', 'nicaragua', 'niger', 'nigeria', 'norway', 'oman', 'pakistan', 'palau', 'panama', 'papuanewguinea', 'paraguay', 'peru', 'philippines', 'poland', 'portugal', 'qatar', 'romania', 'russianfederation', 'rwanda', 'saintvincentandthegrenadines', 'samoa', 'sanmarino', 'saotomeandprincipe', 'saudiarabia', 'senegal', 'serbia', 'seychelles', 'sierraleone', 'singapore', 'slovakia', 'slovenia', 'solomonislands', 'somalia', 'southafrica', 'southsudan', 'spain', 'srilanka', 'stkittsandnevis', 'stlucia', 'sudan', 'suriname', 'swaziland', 'sweden', 'switzerland', 'syria', 'taiwan', 'tajikistan', 'tanzania', 'thailand', 'togo', 'tonga', 'trinidadandtobago', 'tunisia', 'turkey', 'turkmenistan', 'tuvalu', 'uganda', 'ukraine', 'unitedarabemirates', 'unitedkingdom', 'unitedstates', 'uruguay', 'uzbekistan', 'vanuatu', 'vaticancity', 'venezuela', 'vietnam', 'yemen', 'zambia', 'zimbabwe');
    }

    public function get_food_words()
    {
        return array('almonds', 'anchovies', 'apple', 'applecider', 'artichoke', 'arugula', 'asparagus', 'avocado', 'basil', 'beets', 'belgianendive', 'bellpepper', 'blackpepper', 'blackraspberries', 'blackrice', 'blacktea', 'blackberries', 'blueberries', 'bokchoy', 'broadbean', 'broccoli', 'brownrice', 'brusselsprouts', 'cabbage', 'cactuspear', 'cantaloupe', 'capers', 'cardamom', 'carrotjuice', 'carrots', 'cashews', 'cauliflower', 'celery', 'chard', 'cherries', 'chestnut', 'chickendarkmeat', 'chickpeas', 'chicory', 'chinesechives', 'chocolate', 'cilantro', 'cinnamon', 'clementines', 'cloves', 'cocoapowder', 'coconut', 'coffee', 'collardgreens', 'cranberries', 'currants', 'cuttlefish', 'edamame', 'eggplant', 'escarole', 'favabeans', 'fennel', 'fennelseed', 'flaxseed', 'flounder', 'galangal', 'garlic', 'ginger', 'ginseng', 'goose', 'grapefruit', 'greenbeans', 'greentea', 'haddock', 'halibut', 'hardcheese', 'hazelnuts', 'herring', 'honey', 'kale', 'kiwi', 'kohlrabi', 'kumquat', 'lavender', 'lemon', 'lentils', 'lettuce', 'licoriceroot', 'limabeans', 'lime', 'lingonberry', 'mackerel', 'mandarinoranges', 'mango', 'maplesyrup', 'mint', 'miso', 'mussels', 'mustardgreens', 'natto', 'nectarines', 'nutmeg', 'oats', 'oliveoil', 'olivepaste', 'olives', 'onion', 'orange', 'orangejuice', 'oregano', 'oysters', 'papaya', 'parsley', 'parsnips', 'peach', 'peanuts', 'pears', 'peas', 'pecans', 'peppermint', 'persimmon', 'pinenuts', 'pistachios', 'plums', 'pomegranate', 'poppyseed', 'pumpkin', 'pumpkinseed', 'quinoa', 'radishes', 'raspberries', 'redgrapes', 'redwine', 'redwinevinegar', 'rosemary', 'sage', 'salmon', 'salsify', 'sardines', 'scallions', 'seacucumber', 'seaweed', 'sesameoil', 'sesameseeds', 'shallots', 'shrimpandprawn', 'soymilk', 'soysauce', 'soybeansprouts', 'spinach', 'squid', 'squidink', 'strawberries', 'stringbeans', 'sunflowerseed', 'sweetpotato', 'swordjackbean', 'tangelos', 'tangerines', 'tarragon', 'thistle', 'thyme', 'tofu', 'tomato', 'tomatosauce', 'trout', 'tuna', 'turkeydarkmeat', 'turmeric', 'turnip', 'vanillaextract', 'walnuts', 'watercress', 'wheat', 'whitewine', 'wholegrains', 'wintersquash', 'yoghurt', 'zucchini');
    }

} // end class memorable_password

// EOF
