<?php

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * Additional emphasis processing code based on commonmark-java (https://github.com/atlassian/commonmark-java)
 *  - (c) Atlassian Pty Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Tests\Functional\Delimiter;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use PHPUnit\Framework\TestCase;

final class DelimiterProcessingTest extends TestCase
{
    public function testDelimiterProcessorWithInvalidDelimiterUse()
    {
        $e = Environment::createCommonMarkEnvironment();
        $e->addDelimiterProcessor(new FakeDelimiterProcessor(':', 0));
        $e->addDelimiterProcessor(new FakeDelimiterProcessor(';', -1));

        $c = new CommonMarkConverter([], $e);

        $this->assertEquals("<p>:test:</p>\n", $c->convertToHtml(':test:'));
        $this->assertEquals("<p>;test;</p>\n", $c->convertToHtml(';test;'));
    }

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMultipleDelimiters()
    {
        $e = Environment::createCommonMarkEnvironment();
        $e->addDelimiterProcessor(new TestDelimiterProcessor('@'));
        $e->addDelimiterProcessor(new TestDelimiterProcessor('@'));
    }
}
