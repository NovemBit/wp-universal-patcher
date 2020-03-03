<?php


namespace NovemBit\wp\plugins\UniversalPatcher;


use diazoxide\wp\lib\option\Option;
use NovemBit\wp\plugins\UniversalPatcher\patcher\Patch;

class Main
{

    private static $instance;

    public $plugin_file;

    public $plugin_name = 'wp-universal-patcher';

    public $plugin_title = 'Universal Patcher';

    public static function instance($plugin_file = null)
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($plugin_file);
        }
        return self::$instance;
    }


    private function __construct($plugin_file)
    {

        $this->plugin_file = $plugin_file;

        add_action('admin_menu', [$this, 'admin_menu']);

//        $patch = new Patch(
//            [
//                'path' => 'wp-includes/l10n.php',
//                'replace_map' => [
//                    '/function translate\(.*((.*\n){15})/' => 'function translate($text, $domain = "default"){return $text;}'
//                ]
//            ]
//        );
//
//        $patch->restore();
//
//        $patch->restore();
//
    }

    public function get_settings()
    {
        return [
            'patches' => new Option(
                'request_source_type_map',
                [],
                [
                    'parent' => $this->plugin_name,
                    'type' => Option::TYPE_GROUP,
                    'method' => Option::METHOD_MULTIPLE,
                    'template' => [
                        'path' => ['type' => Option::TYPE_TEXT, 'label' => 'path'],
                        'replace_map' => [
                            'type' => Option::TYPE_OBJECT,
                            'field' => ['type' => Option::TYPE_TEXT]
                        ],
                    ],
                    'label' => 'Patches'
                ]
            )
        ];
    }

    public function admin_menu()
    {

        add_menu_page(
            $this->plugin_title . ' Settings',
            $this->plugin_title,
            'manage_options',
            $this->plugin_name,
            [$this, 'admin_page']
        );

    }

    public function admin_page_patches()
    {

    }

    public function admin_page()
    {
        Option::printForm($this->plugin_name, $this->get_settings());
    }
}