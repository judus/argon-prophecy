<?php

namespace App\Http\Controllers;

class TestClass implements TestClassInterface
{
    public function test(): string
    {
        return 'test string';
    }
}