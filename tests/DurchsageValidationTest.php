<?php

declare(strict_types=1);

include_once __DIR__ . '/stubs/Validator.php';

class DurchsageValidationTest extends TestCaseSymconValidation
{
    public function testValidateLibrary(): void
    {
        $this->validateLibrary(__DIR__ . '/..');
    }

    public function testValidateModule_Bose(): void
    {
        $this->validateModule(__DIR__ . '/../Bose');
    }
}