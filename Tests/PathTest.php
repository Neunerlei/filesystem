<?php
declare(strict_types=1);


namespace Neunerlei\FileSystem\Tests;


use Neunerlei\FileSystem\Path;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Path::class)]
class PathTest extends TestCase
{
    public function testUnifySlashes(): void
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals($ds . 'foo' . $ds . 'bar' . $ds . 'baz', Path::unifySlashes("\\foo/bar\\baz"));
        $this->assertEquals($ds . 'foo' . $ds . 'bar', Path::unifySlashes($ds . 'foo' . $ds . 'bar'));
        $this->assertEquals($ds . 'foo' . $ds . 'bar', Path::unifySlashes('/foo/bar'));
        $this->assertEquals($ds . 'foo' . $ds . 'bar', Path::unifySlashes('/foo/../foo/bar'));
        $this->assertEquals($ds . 'bar', Path::unifySlashes('/foo/../bar'));
        $this->assertEquals('bar', Path::unifySlashes('bar'));
        $this->assertEquals('-bar', Path::unifySlashes('/foo/../bar', '-'));
        $this->assertEquals('-foo-bar', Path::unifySlashes('/foo/bar', '-'));
    }
    
    public function testUnifyPath(): void
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->assertEquals($ds . 'foo' . $ds . 'bar' . $ds . 'baz' . $ds, Path::unifyPath("\\foo/bar\\baz"));
        $this->assertEquals($ds . 'foo' . $ds . 'bar' . $ds, Path::unifyPath($ds . 'foo' . $ds . 'bar'));
        $this->assertEquals($ds . 'foo' . $ds . 'bar' . $ds, Path::unifyPath('/foo/bar'));
    }
    
    public function testClassBaseName(): void
    {
        $this->assertEquals('Path', Path::classBasename(Path::class));
        $this->assertEquals('Exception', Path::classBasename(\Exception::class));
        $this->assertEquals('', Path::classBasename(''));
    }
    
    public function testClassNamespace(): void
    {
        $this->assertEquals("Neunerlei\\FileSystem\\Tests", Path::classNamespace(__CLASS__));
        $this->assertEquals("Neunerlei\\FileSystem", Path::classNamespace(Path::class));
        $this->assertEquals('', Path::classNamespace(\Exception::class));
        $this->assertEquals('', Path::classNamespace(''));
    }
    
    
    public static function provideGetFilenameTests(): array
    {
        return [
            ['/webmozart/puli/style.css', 'style.css'],
            ['/webmozart/puli/STYLE.CSS', 'STYLE.CSS'],
            ['/webmozart/puli/style.css/', 'style.css'],
            ['/webmozart/puli/', 'puli'],
            ['/webmozart/puli', 'puli'],
            ['/', ''],
            ['', ''],
        ];
    }

    #[DataProvider('provideGetFilenameTests')]
    public function testGetFilename($path, $filename): void
    {
        $this->assertSame($filename, Path::getFilename($path));
    }
    
    public function testGetFilenameFailsIfInvalidPath(): void
    {
        $this->expectException(\TypeError::class);
        Path::getFilename([]);
    }
}
