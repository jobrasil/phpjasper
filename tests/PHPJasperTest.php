<?php

/*
 * This file is part of the PHPJasper.
 *
 * (c) Daniel Rodrigues (geekcom)
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PHPJasper\Test;

use PHPUnit\Framework\TestCase;
use PHPJasper\PHPJasper;
use PHPJasper\Exception;

/**
 * @author Rafael Queiroz <rafaelfqf@gmail.com>
 */
final class PHPJasperTest extends TestCase
{
    private $instance;

    public function setUp()
    {
        $this->instance = new PHPJasper();
    }

    public function tearDown()
    {
        unset($this->instance);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(PHPJasper::class, new PHPJasper());
    }

    public function testCompile()
    {
        $result = $this->instance->compile('examples/hello_world.jrxml', '{output_file}');

        $expected = '.*jasperstarter compile ".*hello_world.jrxml" -o "{output_file}"';

        $this->expectOutputRegex('/'.$expected.'/', $result->output());
    }

    public function testProcess()
    {
        $result = $this->instance->process('examples/hello_world.jrxml', '{output_file}');

        $expected = '.*jasperstarter process ".*hello_world.jrxml" -o "{output_file}"';

        $this->expectOutputRegex('/'.$expected.'/', $result->output());
    }

    public function testProcessWithOptions()
    {
        $options = [
            'locale' => 'en_US',
            'params' => [
                'param_1' => 'value_1',
                'param_2' => 'value_2',
            ],
            'db_connection' => [
                'driver' => 'driver',
                'username' => 'user',
                'password' => '12345678',
                'database' => 'db'
            ],
            'resources' => 'foo',
        ];

        $result = $this->instance->process('examples/hello_world.jrxml', '{output_file}', $options);

        $expected = '.*jasperstarter --locale en_US process ".*hello_world.jrxml" -o "{output_file}" ';
        $expected .= '-f pdf -P  param_1="value_1"   param_2="value_2"   -t driver -u user -p 12345678 -n db -r foo';

        $this->expectOutputRegex(
            '/'.$expected.'/',
            $result->output()
        );
    }

    public function testListParameters()
    {
        $result = $this->instance->listParameters('examples/hello_world.jrxml');

        $this->expectOutputRegex(
            '/.*jasperstarter list_parameters ".*hello_world.jrxml"/',
            $result->output()
        );
    }

    public function testCompileWithWrongInput()
    {
        $this->expectException(Exception\InvalidInputFile::class);

        $this->instance->compile('');
    }

    public function testCompileHelloWorld()
    {
        $result = $this->instance->compile('examples/hello_world.jrxml');

        $this->expectOutputRegex('/.*jasperstarter compile ".*hello_world.jrxml"/', $result->output());
    }

    public function testOutputWithUserOnExecute()
    {
        $this->expectException(Exception\ErrorCommandExecutable::class);

        $this->instance->compile(__DIR__ . '/test.jrxml', __DIR__ . '/test')->execute('phpjasper');

        $expected = 'su -u 1000 -c "./jasperstarter compile "/var/www/app/tests/test.jrxml" -o "/var/www/app/tests/test""';

        $this->expectOutputRegex('/' . $expected . '/', $this->instance->output());
    }

    public function testExecuteWithoutCompile()
    {
        $this->expectException(Exception\InvalidCommandExecutable::class);

        $this->instance->execute();
    }

    public function testInvalidInputFile()
    {
        $this->expectException(Exception\InvalidInputFile::class);

        $this->instance->compile('{invalid}')->execute();
    }

    public function testExecute()
    {
        $actual = $this->instance->compile(__DIR__ . '/test.jrxml')->execute();

        $this->assertInternalType('array', $actual);
    }

    public function testExecuteWithOutput()
    {
        $actual = $this->instance->compile(__DIR__ . '/test.jrxml', __DIR__ . '/test')->execute();

        $this->assertInternalType('array', $actual);
    }

    public function testExecuteThrowsInvalidResourceDirectory()
    {
        $reflectionObject = new \ReflectionObject($this->instance);
        $reflectionProperty = $reflectionObject->getProperty('pathExecutable');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->instance, '');

        $this->expectException(Exception\InvalidResourceDirectory::class);

        $this->instance->compile(__DIR__ . '/test.jrxml', __DIR__ . '/test')->execute();
    }

    public function testListParametersWithWrongInput()
    {
        $this->expectException(Exception\InvalidInputFile::class);

        $this->instance->listParameters('');
    }

    public function testProcessWithWrongInput()
    {
        $this->expectException(Exception\InvalidInputFile::class);

        $this->instance->process('', '', [
            'format' => 'mp3'
        ]);
    }

    public function testProcessWithWrongFormat()
    {
        $this->expectException(Exception\InvalidFormat::class);

        $this->instance->process('hello_world.jrxml', '', [
            'format' => 'mp3'
        ]);
    }
}
