<?php

namespace Tests\Tecnico\Atleta;

use App\Tecnico\Atleta\Sexo;
use PHPUnit\Framework\TestCase;
use ValueError;

class SexoTest extends TestCase
{
    public function testToStringReturnsMasculino(): void
    {
        $sexo = Sexo::MASCULINO;
        $this->assertSame('Masculino', $sexo->toString());
    }

    public function testToStringReturnsFeminino(): void
    {
        $sexo = Sexo::FEMININO;
        $this->assertSame('Feminino', $sexo->toString());
    }

    public function testFromWithValidString(): void
    {
        $sexoM = Sexo::from('M');
        $this->assertSame(Sexo::MASCULINO, $sexoM);

        $sexoF = Sexo::from('F');
        $this->assertSame(Sexo::FEMININO, $sexoF);
    }

    public function testFromWithInvalidString(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('"Invalid" is not a valid backing value for enum App\Tecnico\Atleta\Sexo');

        Sexo::from("Invalid");
    }
}
