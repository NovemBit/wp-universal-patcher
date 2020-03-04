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

        if (apply_filters($this->plugin_file . '-enable-admin-ui', true)) {
            add_action('admin_menu', [$this, 'admin_menu']);
        }

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

    public function get_options()
    {
        return Option::expandOptions($this->get_settings());
    }

    /**
     * @return Patch[]
     */
    private function get_patches()
    {
        $patches_configs = $this->get_options()['patches'] ?? [];
        $patches = [];
        foreach ($patches_configs as $config) {
            $patches[] = new Patch([
                'path' => $config['path'] ?? null,
                'replace_map' => $config['replace_map'] ?? []
            ]);
        }
        return $patches;
    }

    private function apply_patches()
    {
        $patches = $this->get_patches();

        foreach ($patches as $patch) {
            $patch->apply();
        }
    }

    private function restore_patches()
    {
        $patches = $this->get_patches();
        foreach ($patches as $patch) {
            $patch->restore();
        }
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
                        'path' => [
                            'type' => Option::TYPE_TEXT,
                            'label' => 'File Path'
                        ],
                        'replace_map' => [
                            'type' => Option::TYPE_GROUP,
                            'method' => Option::METHOD_MULTIPLE,
                            'template' => [
                                'pattern' => [
                                    'type' => Option::TYPE_TEXT,
                                    'label' => 'Pattern/Regex'
                                ],
                                'replace' => [
                                    'markup' => Option::MARKUP_TEXTAREA,
                                    'type' => Option::TYPE_TEXT,
                                    'label' => 'Replace'
                                ]
                            ],
                            'label' => 'Replace Patterns'
                        ],
                    ],
                    'template_params' => [
                        'description' => function ($key, $value) {

                            $patch = new Patch(
                                [
                                    'path' => $value['path'] ?? null,
                                    'replace_map' => $value['replace_map'] ?? []
                                ]
                            );

                            if ($patch->isApplied()) {
                                return "Applied!";
                            }

                            return "Not applied.";
                        }
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

    public function admin_page()
    {
        if (isset($_POST['patch'], $_POST['action']) && wp_verify_nonce($_POST['patch'], $this->plugin_name)) {
            switch ($_POST['action']) {
                case "1":
                    $this->apply_patches();
                    echo "done";
                    break;
                case "0":
                    $this->restore_patches();
                    break;
            }
        }
        ?>
        <div class="wrapper">
            <h1><?php echo $this->plugin_title; ?></h1>
            <form method="post">
                <button name="action" class="button button-primary" type="submit" value="1">Apply all</button>
                <button name="action" class="button button-secondary" type="submit" value="0">Restore all</button>
                <?php wp_nonce_field($this->plugin_name, 'patch'); ?>
            </form>
        </div>

        <?php

        Option::printForm($this->plugin_name, $this->get_settings());
    }
}