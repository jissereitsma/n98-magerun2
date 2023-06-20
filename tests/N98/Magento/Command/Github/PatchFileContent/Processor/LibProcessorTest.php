<?php
/**
 * @copyright Copyright (c) netz98 GmbH (https://www.netz98.de)
 *
 * @see PROJECT_LICENSE.txt
 */

declare(strict_types=1);

namespace N98\Magento\Command\Github\PatchFileContent\Processor;

use PHPUnit\Framework\TestCase;

class LibProcessorTest extends TestCase
{
    public function testProcess()
    {
        $diffContent = <<<'DIFF'
                       app/i18n/de_DE/composer.json
                       app/i18n/zh_Hans_CN/language.xml
                       DIFF;

        $expectedResult = <<<'RESULT'
                        vendor/magento/language-de_de/composer.json
                        vendor/magento/language-zh_hans_cn/language.xml
                        RESULT;

        $processor = new I18nProcessor();

        $this->assertEquals($expectedResult, $processor->process($diffContent));
    }
}
