<?php
/*
* Plugin Name: WordPress Universal Patcher
* Plugin URI:
* Description:
* Author: NovemBit LLC
* Text Domain: novembit
* Version: 1.0.1
* License: GPL2+
*/

include_once "vendor/autoload.php";

\NovemBit\wp\plugins\UniversalPatcher\Main::instance(__FILE__);