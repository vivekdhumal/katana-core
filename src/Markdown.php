<?php

namespace Katana;

class Markdown
{
    /**
     * Parse markdown
     *
     * @param $text
     *
     * @return string
     */
    static function parse($text)
    {
        $parser = new \Parsedown();

        $text = static::cleanLeadingSpace($text);

        return $parser->text($text);
    }

    /**
     * Parse markdown with YAML headers
     *
     * @param string $getContents
     *
     * @return string
     */
    public static function parseWithYAML($getContents)
    {
    }

    /**
     * Remove initial leading space from each line
     *
     * Since @markdown can be placed inside any HTML element, there might
     * be leading space due to code editor indentation, here we trim it
     * to avoid compiling the whole markdown block as a code block.
     *
     * @param $text
     *
     * @return string
     */
    private static function cleanLeadingSpace($text)
    {
        $i = 0;

        while (! $firstLine = explode("\n", $text)[$i]) {
            $i ++;
        }

        preg_match('/^( *)/', $firstLine, $matches);

        return preg_replace('/^[ ]{'.strlen($matches[1]).'}/m', '', $text);
    }
}