<?php
/**
 * @copyright 2021 - SMI
 * @author    Vinicius Alves <vinicius_o.a@live.com>
 * @category  Markup Interpreter
 * @license   MIT
 * @since     2021-10-07
 * @version   1.0.0
 */

namespace Markup;

use DOMDocument;
use Mpdf\HTMLParserMode;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class Document
{
    protected DOMDocument $dom;
    protected Mpdf $mpdf;
    protected string $root;
    protected array $config;

    public function __construct(string $root)
    {
        $this->root = $root;
        $this->mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'margin_header' => 5,
            'margin_footer' => 5,
            'margin_top' => 35,
            'margin_bottom' => 16,
            'margin_left' => 15,
            'margin_right' => 15,
            'orientation' => 'P',
        ]);
    }

    public function write(array $template): void
    {
        $this->config = $template['config'];
        $text = $template['html'];

        $header = $this->parseTags($text, 'header');
        $footer = $this->parseTags($text, 'footer');
        $section = $this->parseTags($text, 'main');

        $this->printHeader($header);
        $this->printFooter($footer);
        $this->printSection($section);
        $this->printStylesheet();

        $this->save();
    }

    private function parseTags(string $text, string $tag): mixed
    {
        preg_match("/(<{$tag}>([\s\S]*)<\/{$tag}>)/", $text, $matches);
        return $matches[0] ?? '';
    }

    private function printHeader(string $header): void
    {
        $this->mpdf->SetHTMLHeader($header, write: true);
    }

    private function printFooter(string $footer): void
    {
        $this->mpdf->SetHTMLFooter($footer);
    }

    private function printSection(string $section): void
    {
        $this->mpdf->WriteHTML($section, HTMLParserMode::HTML_BODY);
    }

    private function printStylesheet(): void
    {
        $stylesheet = file_get_contents("{$this->root}/markup/themes/{$this->config['theme']}/style.css");
        $this->mpdf->WriteHTML($stylesheet, HTMLParserMode::HEADER_CSS);
    }

    private function save(): void
    {
        $this->mpdf->Output(
            $this->config['output_dir'] . $this->config['filename'],
            Destination::FILE
        );
    }
}
