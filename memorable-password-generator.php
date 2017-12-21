<?php
/**
 * Plugin Name: Memorable Password Generator
 * Plugin URI:  https://github.com/ko31/memorable-password-generator
 * Description: This plugin generates memorable, strong passwords.
 * Version:     1.0.0
 * Author:      Ko Takagi
 * Author URI:  http://go-sign.info/
 * License:     GPLv2
 * Text Domain: memorable-password-generator
 * Domain Path: /languages
 */

/*  Copyright (c) 2016 Ko Takagi (http://go-sign.info/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$memorablePasswordGenerator = new memorable_password_generator();
$memorablePasswordGenerator->register();

class memorable_password_generator {

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
    }

    public function register()
    {
        add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
    }

    public function plugins_loaded()
    {
        load_plugin_textdomain(
            'memorable-password-generator',
            false,
            dirname( plugin_basename( __FILE__ ) ) . $this->langs
        );

        add_filter( 'random_password', array( $this, 'random_password' ), 10, 1 );
    }

    public function random_password( $password )
    {
        $chars = '!@#$%^&*()';
        $animals = $this->get_animal_words();
        $countries = $this->get_country_words();
        $foods = $this->get_food_words();

        $words = array();
        $words[] = $this->capitalise_one_letter( $animals[mt_rand(0, count($animals))] );
        $words[] = $this->capitalise_one_letter( $countries[mt_rand(0, count($countries))] );
        $words[] = $this->capitalise_one_letter( $foods[mt_rand(0, count($foods))] );
        shuffle( $words );

        $password = implode( substr($chars, wp_rand(0, strlen($chars) - 1), 1), $words );

        return $password;
    }

    protected function capitalise_one_letter( $word )
    {
        $chars = preg_split( '//', $word, -1, PREG_SPLIT_NO_EMPTY );
        $position = mt_rand( 0, count( $chars ) - 1 );
        $chars[ $position ] = strtoupper( $chars[ $position ] );
        $word = implode( '', $chars );
        return $word;
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

} // end class memorable_password_generator

// EOF
