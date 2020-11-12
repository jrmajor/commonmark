<?php

declare(strict_types=1);

/*
 * This file is part of the league/commonmark package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Tests\Functional\Extension\Mention;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Exception\InvalidConfigurationException;
use League\CommonMark\Extension\Mention\Generator\MentionGeneratorInterface;
use League\CommonMark\Extension\Mention\Mention;
use League\CommonMark\Extension\Mention\MentionExtension;
use League\CommonMark\Node\Inline\AbstractInline;
use PHPUnit\Framework\TestCase;

class MentionExtensionTest extends TestCase
{
    public function testNoConfig(): void
    {
        $input = <<<'EOT'
You can follow the author of this library on Github - he's @colinodell!
EOT;

        $expected = <<<'EOT'
<p>You can follow the author of this library on Github - he's @colinodell!</p>

EOT;

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new MentionExtension());

        $converter = new CommonMarkConverter([], $environment);

        $this->assertEquals($expected, $converter->convertToHtml($input));
    }

    public function testConfigStringGenerator(): void
    {
        $input = <<<'EOT'
You can follow the author of this library on Github - he's @colinodell!
EOT;

        $expected = <<<'EOT'
<p>You can follow the author of this library on Github - he's <a href="https://github.com/colinodell">@colinodell</a>!</p>

EOT;

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new MentionExtension());
        $environment->mergeConfig([
            'mentions' => [
                'github_handle' => [
                    'prefix'    => '@',
                    'pattern'   => '[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}(?!\w)',
                    'generator' => 'https://github.com/%s',
                ],
            ],
        ]);

        $converter = new CommonMarkConverter([], $environment);

        $this->assertEquals($expected, $converter->convertToHtml($input));
    }

    public function testConfigCallableGenerator(): void
    {
        $input = <<<'EOT'
You can follow the author of this library on Github - he's @colinodell!
EOT;

        $expected = <<<'EOT'
<p>You can follow the author of this library on Github - he's <a href="https://github.com/colinodell">@colinodell</a>!</p>

EOT;

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new MentionExtension());
        $environment->mergeConfig([
            'mentions' => [
                'github_handle' => [
                    'prefix'    => '@',
                    'pattern'   => '[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}(?!\w)',
                    'generator' => static function (Mention $mention) {
                        $mention->setUrl(\sprintf('https://github.com/%s', $mention->getIdentifier()));

                        return $mention;
                    },
                ],
            ],
        ]);

        $converter = new CommonMarkConverter([], $environment);

        $this->assertEquals($expected, $converter->convertToHtml($input));
    }

    public function testConfigObjectImplementingMentionGeneratorInterface(): void
    {
        $input = <<<'EOT'
You can follow the author of this library on Github - he's @colinodell!
EOT;

        $expected = <<<'EOT'
<p>You can follow the author of this library on Github - he's <a href="https://github.com/colinodell">@colinodell</a>!</p>

EOT;

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new MentionExtension());
        $environment->mergeConfig([
            'mentions' => [
                'github_handle' => [
                    'prefix'    => '@',
                    'pattern'   => '[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}(?!\w)',
                    'generator' => new class () implements MentionGeneratorInterface {
                        public function generateMention(Mention $mention): ?AbstractInline
                        {
                            $mention->setUrl(\sprintf('https://github.com/%s', $mention->getIdentifier()));

                            return $mention;
                        }
                    },
                ],
            ],
        ]);

        $converter = new CommonMarkConverter([], $environment);

        $this->assertEquals($expected, $converter->convertToHtml($input));
    }

    public function testConfigUnknownGenerator(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new MentionExtension());
        $environment->mergeConfig([
            'mentions' => [
                'github_handle' => [
                    'prefix'    => '@',
                    'pattern'   => '[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}(?!\w)',
                    'generator' => new \stdClass(),
                ],
            ],
        ]);

        $converter = new CommonMarkConverter([], $environment);

        $converter->convertToHtml('');
    }

    public function testLegacySymbolOption(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new MentionExtension());
        $environment->mergeConfig([
            'mentions' => [
                'github_handle' => [
                    'symbol'    => '@',
                    'pattern'   => '[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}(?!\w)',
                    'generator' => 'https://github.com/%s',
                ],
            ],
        ]);

        $converter = new CommonMarkConverter([], $environment);

        $converter->convertToHtml('foo');
    }

    public function testWithFullRegexOption(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new MentionExtension());
        $environment->mergeConfig([
            'mentions' => [
                'github_handle' => [
                    'prefix'    => '@',
                    'pattern'   => '/[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}(?!\w)/i',
                    'generator' => 'https://github.com/%s',
                ],
            ],
        ]);

        $converter = new CommonMarkConverter([], $environment);

        $converter->convertToHtml('foo');
    }
}
