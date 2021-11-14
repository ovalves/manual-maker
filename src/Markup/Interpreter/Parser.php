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

class Parser
{
    protected string $root;
    protected array $config;

    public function __construct(string $root)
    {
        $this->root = $root;
    }

    public function parse(string $markup): array
    {
        $markup = $this->crTag($markup);
        $markup = $this->appendLines($markup);
        $markup = $this->newLine($markup);
        $markup = $this->pTag($markup);
        $markup = $this->h1Tag($markup);
        $markup = $this->h2Tag($markup);
        $markup = $this->aTag($markup);
        $markup = $this->imgTag($markup);
        $markup = $this->figureTag($markup);
        $markup = $this->underlineTag($markup);
        $markup = $this->emphasisTag($markup);
        $markup = $this->blockquoteTag($markup);
        $markup = $this->codeTag($markup);
        $markup = $this->specialTags($markup);
        $markup = $this->unorderedListTag($markup);
        $markup = $this->orderedListTag($markup);
        $markup = $this->hrTag($markup);
        $markup = $this->mdashTag($markup);
        $markup = $this->ndashTag($markup);
        $markup = $this->headerTag($markup);
        $markup = $this->footerTag($markup);
        $markup = $this->mainTag($markup);
        $markup = $this->brTag($markup);
        $markup = $this->closeDomDocument($markup);

        return [
            'config' => $this->config,
            'html' => $markup
        ];
    }

    private function brTag(string $markup): string
    {
        return preg_replace(Token::BR_TOKEN, Token::BR_VALUE, $markup);
    }

    private function crTag(string $markup): string
    {
        return str_replace(Token::CR_TOKEN, Token::CR_VALUE, $markup);
    }

    private function appendLines(string $markup): string
    {
        return "\n\n" . $markup;
    }

    private function newLine(string $markup): string
    {
        return preg_replace("/\n\n+/", "\n\n<p>\n", $markup);
    }

    private function pTag(string $markup): string
    {
        $markup = preg_replace("/\(\s*p\s+([^\s\)]+)\s*\)/", '(p $1)', $markup);
        $markup = preg_replace("/\(\s*p\s+([^\s\)]+)\s*([^\)]+)\)/", '<p>$1 $2</p>', $markup);

        return $markup;
    }

    private function h1Tag(string $markup): string
    {
        return preg_replace("/\n([^\s\n][^\n]+)\n={5,}\s*\n/", "\n<h1>$1</h1>\n", $markup);
    }

    private function h2Tag(string $markup): string
    {
        return preg_replace("/\n([^\s\n][^\n]+)\n-{5,}\s*\n/", "\n<h2>$1</h2>\n", $markup);
    }

    private function aTag(string $markup): string
    {
        $markup = preg_replace("/\(\s*link\s+([^\s\)]+)\s*\)/", '(link $1 $1)', $markup);
        $markup = preg_replace("/\(\s*link\s+([^\s\)]+)\s*([^\)]+)\)/", '<a href="$1">$2</a>', $markup);

        $markup = preg_replace("/\(\s*xlink\s+([^\s\)]+)\s*\)/", '(xlink $1 $1)', $markup);
        $markup = preg_replace("/\(\s*xlink\s+([^\s\)]+)\s*([^\)]+)\)/", '<a target=_blank href="$1">$2</a>', $markup);

        return $markup;
    }

    private function imgTag(string $markup): string
    {
        $markup = preg_replace("/\(\s*image\s+([^\s\)]+)\s*\)/", '(image $1 $1)', $markup);
        $markup = preg_replace_callback(
            "/\(\s*image\s+([^\s\)]+)\s*([^\)]+)\)/",
            [$this, 'makeBase64ImageTag'],
            $markup
        );

        return $markup;
    }

    private function figureTag(string $markup): string
    {
        $markup = preg_replace("/\(\s*figure\s+([^\s\)]+)\s*\)/", '(figure $1 $1)', $markup);
        $markup = preg_replace_callback(
            "/\(\s*figure\s+([^\s\)]+)\s*([^\)]+)\)/",
            [$this, 'makeBase64FigureTag'],
            $markup
        );

        return $markup;
    }

    private function underlineTag(string $markup): string
    {
        return preg_replace('/__(([^_]|_[^_])*)__/', '<u>$1</u>', $markup);
    }

    private function emphasisTag(string $markup): string
    {
        return preg_replace("/\*\*(([^\*]|\*[^\*])*)\*\*/", '<em>$1</em>', $markup);
    }

    private function blockquoteTag(string $markup): string
    {
        $markup = preg_replace('/\n\s*"\s*\n([^"]+)"\s*\n/', "\n<blockquote>$1</blockquote>\n", $markup);
        $markup = preg_replace('/\n\s*{\s*\n([^"]+)}\s*\n/', "\n<blockquote><code>$1</code></blockquote>\n", $markup);

        return $markup;
    }

    private function codeTag(string $markup): string
    {
        return preg_replace('/{([^}]+)}/', '<code>$1</code>', $markup);
    }

    private function specialTags(string $markup): string
    {
        $markup = preg_replace('/\(tm\)/', '&trade;', $markup);
        $markup = preg_replace('/\(r\)/', '&reg;', $markup);
        $markup = preg_replace('/\(c\)/', '&copy;', $markup);
        $markup = preg_replace('/\(cy\)/', '&copy;&nbsp;' . date('Y'), $markup);
        $markup = preg_replace('/\(cm\s([^)]+)\)/', '&copy;&nbsp;' . date('Y') . '&nbsp;$1&nbsp;&ndash;&nbsp;All&nbsp;Rights&nbsp;Reserved', $markup);

        return $markup;
    }

    private function unorderedListTag(string $markup): string
    {
        $markup = preg_replace('/\n((\s*-\s+[^\n]+\n)+)/', "\n<ul>\n$1\n</ul>", $markup);
        $markup = preg_replace('/\n\s*-\s+/', "\n<li>", $markup);

        return $markup;
    }

    private function orderedListTag(string $markup): string
    {
        $markup = preg_replace('/\n((\s*(\d+\.|#)\s+[^\n]+\n)+)/', "\n<ol>\n$1\n</ol>", $markup);
        $markup = preg_replace('/\n\s*(\d+\.|#)\s+/', "\n<li>", $markup);

        return $markup;
    }

    private function hrTag(string $markup): string
    {
        return preg_replace('/\n\s*-{4,}\s*\n/', "\n<hr>\n", $markup);
    }

    private function mdashTag(string $markup): string
    {
        return preg_replace('/-{3}/', '&mdash;', $markup);
    }

    private function ndashTag(string $markup): string
    {
        return preg_replace('/-{2}/', '&ndash;', $markup);
    }

    private function headerTag(string $markup): string
    {
        $markup = preg_replace("/\(\s*header\s+([^\s\)]+)\s*\)/", '(header $1 $1)', $markup);
        $markup = preg_replace("/\(\s*header\s+([^\s\)]+)\s*([^\)]+)\)/", '<header>$1 $2</header>', $markup);

        return $markup;
    }

    private function footerTag(string $markup): string
    {
        $markup = preg_replace("/\(\s*footer\s+([^\s\)]+)\s*\)/", '(footer $1 $1)', $markup);
        $markup = preg_replace("/\(\s*footer\s+([^\s\)]+)\s*([^\)]+)\)/", '<footer>$1 $2</footer>', $markup);

        return $markup;
    }

    private function mainTag(string $markup): string
    {
        $markup = preg_replace("/\(\s*main\s+([^\s\)]+)\s*\)/", '(main $1 $1)', $markup);
        $markup = preg_replace("/\(\s*main\s+([^\s\)]+)\s*([^\)]+)\)/", '<main>$1 $2</main>', $markup);

        return $markup;
    }

    private function closeDomDocument(string $markup): string
    {
        preg_match('/\(page:title\s([^)]+)\)/', $markup, $title);
        preg_match('/\(page:lang\s([^)]+)\)/', $markup, $lang);
        preg_match('/\(page:theme\s([^)]+)\)/', $markup, $theme);
        preg_match('/\(page:output_dir\s([^)]+)\)/', $markup, $outputDir);
        preg_match('/\(page:filename\s([^)]+)\)/', $markup, $filename);

        $this->config['title'] = $title[1] ?? 'Título da Página';
        $this->config['lang'] = $lang[1] ?? 'pt-br';
        $this->config['theme'] = $theme[1] ?? 'simple';
        $this->config['output_dir'] = $outputDir[1] ?? $this->root.'/markup/output/';
        $this->config['filename'] = $filename[1] ?? 'sample.pdf';

        $markup = preg_replace('/\(page:title\s([^)]+)\)/', '', $markup);
        $markup = preg_replace('/\(page:lang\s([^)]+)\)/', '', $markup);
        $markup = preg_replace('/\(page:theme\s([^)]+)\)/', '', $markup);
        $markup = preg_replace('/\(page:output_dir\s([^)]+)\)/', '', $markup);
        $markup = preg_replace('/\(page:filename\s([^)]+)\)/', '', $markup);

        $markup = <<<HTML
                <html lang="{$this->config['lang']}">
                    <head>
                        <title>{$this->config['title']}</title>
                    </head>
                    <body>
HTML . $markup;

        $markup .= '</body></html>';

        return $markup;
    }

    private function makeBase64Source(mixed $param): string
    {
        $path = "{$this->root}/markup/images/{$param[1]}";
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        return 'data:image/' . $type . ';base64,' . base64_encode($data);
    }

    protected function makeBase64FigureTag(mixed $param): string
    {
        $base64 = $this->makeBase64Source($param);

        return "<figure><img src='{$base64}' title='{$param[2]}' alt='{$param[2]}'><figcaption>$param[2]</figcaption></figure>";
    }

    protected function makeBase64ImageTag(mixed $param): string
    {
        $base64 = $this->makeBase64Source($param);

        return "<img src='{$base64}' title='{$param[2]}' alt='{$param[2]}'>";
    }
}
