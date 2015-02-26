<?php


class MainMethodTest extends PHPUnit_Framework_TestCase {

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Model must contain field `key` with fields array!
     */
    public function testEmptyFieldsExceptions() {
        ArrayValidator::validate([], []);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Model must contain field `rules` with list of rules!
     */
    public function testEmptyRulesExceptions() {
        ArrayValidator::validate(['fields' => []], []);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage No fields to validate. Please check model and input data fields!
     */
    public function testNoFieldsToHandleExceptions() {
        ArrayValidator::validate(['fields' => [], 'rules' => []], []);
    }

    public function testOnScenarioValidate() {
        $this->assertNotTrue(ArrayValidator::validate([
            'fields' => ['field1'],
            'rules' => [
                ['field1', function() { return false;}, 'on' => 'test'],
                ['field1', function() { return false;}, 'on' => 'bad_test']
            ]
        ], ['field1' => 123], 'test'));

        $this->assertNotTrue(ArrayValidator::validate([
            'fields' => ['field1'],
            'rules' => [
                ['field1', function() { return false;}, 'on' => 'test'],
                ['field1', function() { return false;}, 'on' => 'bad_test']
            ]
        ], ['field1' => 123], 'bad_test'));

        $this->assertTrue(ArrayValidator::validate([
            'fields' => ['field1'],
            'rules' => [
                ['field1', function() { return false;}, 'on' => 'test'],
                ['field1', function() { return false;}, 'on' => 'bad_test']
            ]
        ], ['field1' => 123], 'any_scenario'));
    }

    public function testWhenCase() {
        $this->assertNotTrue(ArrayValidator::validate([
            'fields' => ['field1', 'field2'],
            'rules' => [
                ['field1', function() { return true;}, 'on' => 'test'],
                ['field2', function() { return false;}, 'when' => '$data[\'field1\'] === 123']
            ]
        ], ['field1' => 123, 'field2' => 234], 'test'));

        $this->assertTrue(ArrayValidator::validate([
            'fields' => ['field1', 'field2'],
            'rules' => [
                ['field1', function() { return true;}, 'on' => 'test'],
                ['field2', function() { return false;}, 'when' => '$data[\'field1\'] === 1234']
            ]
        ], ['field1' => 123, 'field2' => 234], 'test'));

        $this->assertNotTrue(ArrayValidator::validate([
            'fields' => ['field1', 'field2'],
            'rules' => [
                ['field1', function() { return true;}, 'on' => 'test'],
                ['field2', function() { return false;}, 'when' => function($data, $parent) {
                    return $data['field1'] === 123;
                }]
            ]
        ], ['field1' => 123, 'field2' => 234], 'test'));

        $this->assertTrue(ArrayValidator::validate([
            'fields' => ['field1', 'field2'],
            'rules' => [
                ['field1', function() { return true;}, 'on' => 'test'],
                ['field2', function() { return false;}, 'when' => function($data, $parent) {
                    return $data['field1'] === 1235;
                }]
            ]
        ], ['field1' => 123, 'field2' => 234], 'test'));


    }

    public function testOptionsCase() {
        // here we check that options passed to cb correctly
        $this->assertTrue(ArrayValidator::validate([
            'fields' => ['field1', 'field2'],
            'rules' => [
                ['field1', function($a, $b, $options) {
                    return $options === [
                        'option1' => 123
                    ];
                }, 'options' => [
                    'option1' => 123
                ]],
            ]
        ], ['field1' => 123, 'field2' => 234]));

        $this->assertNotTrue(ArrayValidator::validate([
            'fields' => ['field1', 'field2'],
            'rules' => [
                ['field1', function($a, $b, $options) {
                    return $options === [
                        'option2' => 125
                    ];
                }, 'options' => [
                    'option1' => 123
                ]],
            ]
        ], ['field1' => 123, 'field2' => 234]));
    }
}