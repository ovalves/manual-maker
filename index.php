<?php
/**
 * @copyright 2021 - SMI
 * @author    Vinicius Alves <vinicius_o.a@live.com>
 * @category  Markup Interpreter
 * @license   MIT
 * @since     2021-10-07
 * @version   1.0.0
 */
require 'vendor/autoload.php';

set_time_limit(0);

use Markup\Document;
use Markup\Interpreter\Parser;

(new Document(__DIR__))->write(
    (new Parser(__DIR__))->parse(
        file_get_contents('./markup/markup.txt')
    )
);
