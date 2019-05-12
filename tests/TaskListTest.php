<?php

/*
 * This file is part of the league/commonmark-ext-task-list package.
 *
 * (c) Colin O'Dell <colinodell@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace League\CommonMark\Ext\Autolink\Test;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Ext\TaskList\TaskListExtension;
use PHPUnit\Framework\TestCase;

final class TaskListTest extends TestCase
{
    public function testTaskLists()
    {
        $input = <<<'EOT'
- [x] foo
  - [ ] bar
  - [X] baz
- [ ] bim

This works for ordered lists too:

1. [x] foo
2. [X] bar
3. [ ] baz

Some examples which should not match:

 - Checkbox [x] in the middle
 - Checkbox at the end [ ]
 - **[x] Checkbox inside of emphasis**
 - No text, as shown in these examples:
   - [x]
   - [ ]
   -    [x]
   -           [x]
EOT;

        $expected = <<<'EOT'
<ul>
<li>
<input disabled="" type="checkbox" checked="" /> foo
<ul>
<li>
<input disabled="" type="checkbox" /> bar</li>
<li>
<input disabled="" type="checkbox" checked="" /> baz</li>
</ul>
</li>
<li>
<input disabled="" type="checkbox" /> bim</li>
</ul>
<p>This works for ordered lists too:</p>
<ol>
<li>
<input disabled="" type="checkbox" checked="" /> foo</li>
<li>
<input disabled="" type="checkbox" checked="" /> bar</li>
<li>
<input disabled="" type="checkbox" /> baz</li>
</ol>
<p>Some examples which should not match:</p>
<ul>
<li>Checkbox [x] in the middle</li>
<li>Checkbox at the end [ ]</li>
<li>
<strong>[x] Checkbox inside of emphasis</strong>
</li>
<li>No text, as shown in these examples:
<ul>
<li>[x]</li>
<li>[ ]</li>
<li>[x]</li>
<li>
<pre><code>      [x]
</code></pre>
</li>
</ul>
</li>
</ul>

EOT;

        $environment = Environment::createCommonMarkEnvironment();
        $environment->addExtension(new TaskListExtension());

        $converter = new CommonMarkConverter([], $environment);

        $this->assertEquals($expected, $converter->convertToHtml($input));
    }
}
