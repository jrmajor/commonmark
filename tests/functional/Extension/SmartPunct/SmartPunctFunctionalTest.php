<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Original code based on the CommonMark JS reference parser (http://bitly.com/commonmark-js)
 *  - (c) John MacFarlane
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Tests\Functional\Extension\SmartPunct;

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\MarkdownConverter;
use PHPUnit\Framework\TestCase;

/**
 * Tests the parser against the CommonMark spec
 */
class SmartPunctFunctionalTest extends TestCase
{
    /** @var MarkdownConverter */
    protected $converter;

    protected function setUp(): void
    {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new SmartPunctExtension());
        $this->converter = new MarkdownConverter($environment);
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $markdown Markdown to parse
     * @param string $html     Expected result
     * @param string $section  Section of the spec
     * @param int    $number   Example number
     */
    public function testExample(string $markdown, string $html, string $section, int $number): void
    {
        $actualResult = $this->converter->convertToHtml($markdown);

        $failureMessage  = \sprintf('Unexpected result ("%s" section, example #%d)', $section, $number);
        $failureMessage .= "\n=== markdown ===============\n" . $markdown;
        $failureMessage .= "\n=== expected ===============\n" . $html;
        $failureMessage .= "\n=== got ====================\n" . $actualResult;

        $this->assertEquals($html, $actualResult, $failureMessage);
    }

    /**
     * @return array<array<string>>
     */
    public function dataProvider(): array
    {
        $filename = __DIR__ . '/../../../../vendor/commonmark/commonmark.js/test/smart_punct.txt';
        if (($data = \file_get_contents($filename)) === false) {
            $this->fail(\sprintf('Failed to load spec from %s', $filename));
        }

        $matches = [];
        // Normalize newlines for platform independence
        $data = \preg_replace('/\r\n?/', "\n", $data);
        $data = \preg_replace('/<!-- END TESTS -->.*$/', '', $data);
        \preg_match_all('/^`{32} example\n([\s\S]*?)^\.\n([\s\S]*?)^`{32}$|^#{1,6} *(.*)$/m', $data, $matches, PREG_SET_ORDER);

        $examples       = [];
        $currentSection = '';
        $exampleNumber  = 0;

        foreach ($matches as $match) {
            if (isset($match[3])) {
                $currentSection = $match[3];
            } else {
                $exampleNumber++;

                $markdown = $match[1];
                $markdown = \str_replace('→', "\t", $markdown);

                $examples[] = [
                    'markdown' => $markdown,
                    'html'     => $match[2],
                    'section'  => $currentSection,
                    'number'   => $exampleNumber,
                ];
            }
        }

        return $examples;
    }
}
