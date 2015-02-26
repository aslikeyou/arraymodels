<?php

class ValidatorsTest extends PHPUnit_Framework_TestCase
{

    public function testRequiredValidator()
    {
        $this->assertTrue(ArrayValidator::required(['field1' => '123'],
            'field1', []));
        $this->assertTrue(ArrayValidator::required(['field1' => true], 'field1',
            []));

        $this->assertStringStartsWith(
            'Field field1',
            ArrayValidator::required(['field1' => ''], 'field1', [])
        );
        $this->assertStringStartsWith(
            'Field field1',
            ArrayValidator::required(['field1' => null], 'field1', [])
        );
        $this->assertStringStartsWith(
            'Field field1',
            ArrayValidator::required(['field1' => false], 'field1', [])
        );

        $this->assertEquals(
            'some new text field1',
            ArrayValidator::required(['field1' => false], 'field1', [
                'message' => 'some new text %attribute%'
            ])
        );
    }

    public function testRegexpValidator()
    {
        $this->assertTrue(ArrayValidator::regexp([
            'field1' => '123'
        ], 'field1', [
                'pattern' => '/^\d+$/'
            ]
        ));

        $this->assertStringStartsWith(
            'Field field1',
            ArrayValidator::regexp(['field1' => '1ss23'], 'field1', [
                    'pattern' => '/^\d+$/'
                ]
            ));
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You must specify pattern to check with!
     */
    public function testRegexpParamException()
    {
        ArrayValidator::regexp(['field1' => '1ss23'], 'field1', []);
    }

    public function testInValidator()
    {
        $this->assertTrue(ArrayValidator::in([
            'field1' => '123'
        ], 'field1', [
                'range' => ['123', '345']
            ]
        ));

        $this->assertTrue(ArrayValidator::in([
            'field1' => '345'
        ], 'field1', [
                'range' => ['123', '345']
            ]
        ));

        $this->assertTrue(ArrayValidator::in([
            'field1' => '345'
        ], 'field1', [
                'range' => ['123', 345]
            ]
        ));

        $this->assertStringStartsWith(
            'Field field1',
            ArrayValidator::in(['field1' => '1ss23'], 'field1', [
                    'range' => ['123', 345]
                ]
            )
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage You must specify data range to check in!
     */
    public function testInParamException()
    {
        $this->assertTrue(ArrayValidator::in([
            'field1' => '123'
        ], 'field1', []
        ));
    }

    public function testIntegerValidator()
    {
        $this->assertTrue(ArrayValidator::integer([
            'field1' => 123
        ], 'field1', [
                'min_range' => 10
            ]
        ));

        $this->assertEquals(
            'Field field1 not a valid integer',
            ArrayValidator::integer([
                'field1' => 5
            ], 'field1', [
                    'min_range' => 10
                ]
            )
        );

        $this->assertTrue(ArrayValidator::integer([
            'field1' => 15
        ], 'field1', [
                'max_range' => 20
            ]
        ));

        $this->assertEquals(
            'Field field1 not a valid integer',
            ArrayValidator::integer([
                'field1' => 25
            ], 'field1', [
                    'max_range' => 20
                ]
            ));

        $this->assertTrue(ArrayValidator::integer([
            'field1' => 17
        ], 'field1', [
                'min_range' => 10,
                'max_range' => 20
            ]
        ));
        $this->assertEquals(
            'Field field1 not a valid integer', ArrayValidator::integer([
            'field1' => 4
        ], 'field1', [
                'min_range' => 10,
                'max_range' => 20
            ]
        ));
        $this->assertEquals(
            'Field field1 not a valid integer', ArrayValidator::integer([
            'field1' => 48
        ], 'field1', [
                'min_range' => 10,
                'max_range' => 20
            ]
        ));

        $this->assertTrue(ArrayValidator::integer([
            'field1' => '17'
        ], 'field1', [
                'min_range' => 10,
                'max_range' => 20
            ]
        ));

        $this->assertEquals(
            'Field field1 not a valid integer', ArrayValidator::integer([
            'field1' => 'a'
        ], 'field1', [
            ]
        ));
        $this->assertTrue(ArrayValidator::integer([
            'field1' => 10
        ], 'field1', [
            ]
        ));
    }

    public function testBoolValidator() {
        $this->assertTrue(ArrayValidator::bool(['field1' => '1'], 'field1'));
        $this->assertTrue(ArrayValidator::bool(['field1' => 'true'], 'field1'));
        $this->assertTrue(ArrayValidator::bool(['field1' => 'on'], 'field1'));
        $this->assertTrue(ArrayValidator::bool(['field1' => 'yes'], 'field1'));

        $this->assertTrue(ArrayValidator::bool(['field1' => '0'], 'field1'));
        $this->assertTrue(ArrayValidator::bool(['field1' => 'false'], 'field1'));
        $this->assertTrue(ArrayValidator::bool(['field1' => 'off'], 'field1'));
        $this->assertTrue(ArrayValidator::bool(['field1' => 'no'], 'field1'));
        $this->assertTrue(ArrayValidator::bool(['field1' => ''], 'field1'));

        $this->assertEquals(
            'Field field1 not a valid boolean',ArrayValidator::bool(['field1' => 'aa'], 'field1'));
        $this->assertEquals(
            'Field field1 not a valid boolean',ArrayValidator::bool(['field1' => '1212'], 'field1'));
    }

    public function testEmailValidator() {
        $this->assertTrue(ArrayValidator::email(['field1' => 'example@somedomain.com'], 'field1'));
        $this->assertTrue(ArrayValidator::email(['field1' => 'emple@somedo.com'], 'field1'));
        $this->assertTrue(ArrayValidator::email(['field1' => 'somesva@yandex.ru'], 'field1'));

        $this->assertEquals(
            'Field field1 not a valid email',ArrayValidator::email(['field1' => '@yandex.ru'], 'field1'));
        $this->assertEquals(
            'Field field1 not a valid email',ArrayValidator::email(['field1' => 'ssss@yandex'], 'field1'));
    }

    public function testLengthValidator() {
        $this->assertTrue(ArrayValidator::length(['field1' => 'example@somedomain.com'], 'field1', [
            'min_length' => 5
        ]));

        $this->assertTrue(ArrayValidator::length(['field1' => 'example@somedomain.com'], 'field1', [
            'min_length' => 5,
            'max_length' => 100
        ]));

        $this->assertTrue(ArrayValidator::length(['field1' => 'example@somedomain.com'], 'field1', [
            'max_length' => 100
        ]));

        $this->assertEquals(
            'Field field1 has invalid length', ArrayValidator::length(['field1' => 'example@somedomain.com'], 'field1', [
            'min_length' => 100
        ]));

        $this->assertEquals(
            'Field field1 has invalid length', ArrayValidator::length(['field1' => 'example@somedomain.com'], 'field1', [
            'min_length' => 30,
            'max_length' => 100
        ]));

        $this->assertEquals(
            'Field field1 has invalid length',ArrayValidator::length(['field1' => 'example@somedomain.com'], 'field1', [
            'max_length' => 10
        ]));

        $this->assertTrue(ArrayValidator::length(['field1' => ['i1','i1','i1']], 'field1', [
            'min_length' => 1
        ]));
    }

    public function testNestedHandler() {
        $this->assertTrue(ArrayValidator::nested([
            'users' => [
                ['name' => 'Jack'],
                ['name' => 'Joe'],
            ]
        ], 'users', [
            'model' => [
                'fields' => ['name'],
                'rules' => [
                    ['name', ['ArrayValidator', 'required']]
                ]
            ]
        ]));

        $this->assertArrayHasKey(1, ArrayValidator::nested([
            'users' => [
                ['name' => 'Jack'],
                ['name' => ''],
            ]
        ], 'users', [
            'model' => [
                'fields' => ['name'],
                'rules' => [
                    ['name', ['ArrayValidator', 'required']]
                ]
            ]
        ]));
    }
}