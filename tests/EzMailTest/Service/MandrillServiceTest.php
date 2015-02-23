<?php
/**
 * Copyright (c) 2012-2015 Keshav Mishra.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the names of the copyright holders nor the names of the
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @author      Keshav Mishra <keshav.kr.mishra@gmail.com>
 * @copyright   2012-2015 Keshav Mishra.
 * @license     http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link        http://www.keshavmishra.com
 */

namespace EzMailTest\Service;

use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use EzMail\Service\MandrillService;
use EzMailTest\Util\ServiceManagerFactory;
use Zend\Http\Response as HttpResponse;

class MandrillServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MandrillService
     */
    protected $service;

    public function setUp()
    {
        $this->service = new MandrillService('my-secret-key');
    }

    public function testCreateFromFactory()
    {
        $service = ServiceManagerFactory::getServiceManager()->get('EzMail\Service\MandrillService');
        $this->assertInstanceOf('EzMail\Service\MandrillService', $service);
    }

    public function exceptionDataProvider()
    {
        return array(
            array(200, null, null),
            array(401, '{"name":"InvalidKey","message":"Invalid credentials", "code":4}', 'EzMail\Service\Exception\InvalidCredentialsException'),
            array(400, '{"name":"ValidationError","message":"Validation failed", "code":4}', 'EzMail\Service\Exception\ValidationErrorException'),
            array(400, '{"name":"Unknown_Template","message":"Unknown template", "code":4}', 'EzMail\Service\Exception\UnknownTemplateException'),
            array(500, '{"name":"GeneralError","message":"Failed", "code":4}', 'EzMail\Service\Exception\RuntimeException'),
        );
    }

    /**
     * @dataProvider exceptionDataProvider
     */
    public function testExceptionsAreThrownOnErrors($statusCode, $content, $expectedException)
    {
        $method = new ReflectionMethod('EzMail\Service\MandrillService', 'parseResponse');
        $method->setAccessible(true);

        $response = new HttpResponse();
        $response->setStatusCode($statusCode);
        $response->setContent($content);

        $this->setExpectedException($expectedException);

        $method->invoke($this->service, $response);
    }
}
