<?php
use PHPUnit\Framework\TestCase;
use App\Libs\CodeGenerator;

class CodeGeneratorTest extends TestCase {
    public function testGeneratesCodeWithPrefixAndPattern() {
        $code = CodeGenerator::generate('CM');
        $this->assertMatchesRegularExpression('/^CM-[A-Z0-9]{5}-[0-9]$/', $code);
    }

    public function testGeneratesUniqueCodesMostOfTheTime() {
        $codes = [];
        for ($i = 0; $i < 100; $i++) {
            $codes[] = CodeGenerator::generate('CT');
        }
        $this->assertGreaterThan(95, count(array_unique($codes))); // probabilistic uniqueness
    }
}


