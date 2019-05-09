<?php
/**
 * Plugin Name: Memorable Password
 * Plugin URI:  https://github.com/ko31/memorable-password
 * Description: This plugin generates a memorable and strong password.
 * Version:     1.0.1
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
    private $text_domain = '';
    private $langs = '';
    private $plugin_slug = '';
    private $option_name = '';
    private $options;

    function __construct()
    {
        $data = get_file_data(
            __FILE__,
            array(
                'ver' => 'Version',
                'langs' => 'Domain Path',
                'text_domain' => 'Text Domain'
            )
        );
        $this->version = $data['ver'];
        $this->text_domain = $data['text_domain'];
        $this->langs = $data['langs'];
        $this->plugin_slug = basename( dirname( __FILE__ ) );
        $this->option_name = basename( dirname( __FILE__ ) );
    }

    public function register()
    {
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
        register_activation_hook( __FILE__ , array( $this, 'register_activation_hook' ) );
    }

    public function register_activation_hook()
    {
        $options = get_option( $this->option_name );
        if ( empty( $options ) ) {
            add_option( $this->option_name, $this->get_default_option_value() );
        }
    }

    public function plugins_loaded()
    {
        load_plugin_textdomain(
            $this->text_domain,
            false,
            dirname( plugin_basename( __FILE__ ) ) . $this->langs
        );

        add_action( 'admin_menu', array( $this, 'admin_menu' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_filter( 'random_password', array( $this, 'random_password' ), 10, 1 );
    }

    public function admin_menu()
    {
        add_options_page(
            __( 'Memorable Password', $this->text_domain ),
            __( 'Memorable Password', $this->text_domain ),
            'manage_options',
            $this->plugin_slug,
            array( $this, 'options_page' )
        );
    }

    public function admin_init()
    {
        register_setting(
            $this->plugin_slug,
            $this->option_name,
            array( $this, 'sanitize_callback' )
        );

        add_settings_section(
            $this->plugin_slug,
            __( 'Basic settings', $this->text_domain ),
            array( $this, 'section_callback' ),
            $this->plugin_slug
        );

        add_settings_field( 
            'word', 
            __( 'Word', $this->text_domain ),
            array( $this, 'kind_callback' ),
            $this->plugin_slug,
            $this->plugin_slug
        );

        add_settings_field( 
            'uppercase', 
            __( 'Uppercase', $this->text_domain ),
            array( $this, 'uppercase_callback' ),
            $this->plugin_slug,
            $this->plugin_slug
        );

        add_settings_field( 
            'delimiter', 
            __( 'Delimiter', $this->text_domain ),
            array( $this, 'symbol_callback' ),
            $this->plugin_slug,
            $this->plugin_slug
        );
    }

    public function sanitize_callback( $input ) { 

        if ( !is_array( $input ) ) {
            $input = (array)$input;
        }

        $is_selected = false;
        foreach ( array( 'animal', 'country', 'food' ) as $_kind ) {
            if ( isset( $input[$_kind] ) ) {
                $is_selected = true;
                break;
            }
        }
        if ( !$is_selected ) {
            add_settings_error( $this->plugin_slug, 'unselected_word', __( 'Please select at least one kind of words', $this->text_domain ) );
            $options = get_option( $this->option_name );
            foreach ( $this->get_default_kinds() as $val ) {
                if ( isset( $options[$val] ) ) {
                    $input[$val] = 1;
                }
            }
        }

        return $input;
    }

    public function section_callback() { 
        return;
    }

    public function kind_callback() { 
        foreach ( $this->get_default_kinds() as $val ) :
            $check = isset( $this->options[$val] ) ? 1 : '';
?>
    <label for="<?php echo $val;?>"><input type="checkbox" id="<?php echo $val;?>" name="<?php echo $this->option_name;?>[<?php echo $val;?>]" <?php checked( $check, 1 ); ?> value="1" /><?php esc_html_e( ucfirst( $val ), $this->text_domain );?></label>&nbsp;
<?php
        endforeach;
?>
    <p class="description"><?php esc_html_e( 'Please select words to enable', $this->text_domain );?></p>
<?php
    }

    public function uppercase_callback() { 
        $check = isset( $this->options['uppercase'] ) ? 1 : '';
?>
    <label for="uppercase"><input type="checkbox" id="uppercase" name="<?php echo $this->option_name;?>[uppercase]" <?php checked( $check, 1 ); ?> value="1" /><?php esc_html_e( 'Include Uppercase characters', $this->text_domain );?></label>
<?php
    }

    public function symbol_callback() { 
        $check = isset( $this->options['symbol'] ) ? 1 : '';
?>
    <label for="symbol"><input type="checkbox" id="symbol" name="<?php echo $this->option_name;?>[symbol]" <?php checked( $check, 1 ); ?> value="1" /><?php esc_html_e( 'Use special symbols for delimiter', $this->text_domain );?></label>
<?php
    }

    public function options_page()
    {
        $this->options = get_option( $this->option_name );
?>
    <form action='options.php' method='post'>
        <h1><?php echo __( 'Memorable Password', $this->text_domain );?></h1>
<?php
        settings_fields( $this->plugin_slug );
        do_settings_sections( $this->plugin_slug );
        submit_button();
?>
    </form>
<?php
    }

    public function get_default_kinds()
    {
        return array(
            'animal',
            'country',
            'food'
        );
    }

    public function get_default_option_value()
    {
        $options = array();
        foreach ( $this->get_default_kinds() as $val ) {
            $options[$val] = 1;
        }
        return $options;
    }

    public function random_password( $password )
    {
        $options = get_option( $this->plugin_slug );

        $words = array();
        if ( isset( $options['animal'] ) ) {
            $animals = $this->get_animal_words();
            $words[] = $animals[mt_rand( 0, count( $animals ) - 1 )];
        }
        if ( isset( $options['country'] ) ) {
            $countries = $this->get_country_words();
            $words[] = $countries[mt_rand( 0, count( $countries ) - 1 )];
        }
        if ( isset( $options['food'] ) ) {
            $foods = $this->get_food_words();
            $words[] = $foods[mt_rand( 0, count( $foods ) - 1 )];
        }
        if ( empty( $words ) ) {
            return $password;
        }

        shuffle( $words );

        if ( isset( $options['uppercase'] ) ) {
            foreach ( $words as $k => $v ) {
                $words[$k] = $this->uppercase_one_letter( $v );
            }
        }

        if ( isset( $options['symbol'] ) ) {
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
