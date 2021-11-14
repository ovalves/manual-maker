<?php
/**
 * @copyright 2021 - SMI
 * @author    Vinicius Alves <vinicius_o.a@live.com>
 * @category  Markup Interpreter
 * @license   MIT
 * @since     2021-10-07
 * @version   1.0.0
 */

namespace Markup\Interpreter;

class Token
{
    // BR
    public const BR_TOKEN = '/\(br\)/';
    public const BR_VALUE = '<br>';

    // CR
    public const CR_TOKEN = '/\r';
    public const CR_VALUE = '\n';
}
