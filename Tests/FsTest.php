<?php
/**
 * Copyright 2020 Martin Neundorfer (Neunerlei)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * Last modified: 2020.03.12 at 09:45
 */

declare(strict_types=1);

namespace Neunerlei\FileSystem\Tests;

include __DIR__ . "/FileSystemTestCase.php";

use Neunerlei\FileSystem\Fs;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Tests\FilesystemTestCase;

class FsTest extends FilesystemTestCase {
	
	public function testGetFs(): void {
		$this->assertInstanceOf(Filesystem::class, Fs::getFs());
		$this->assertSame(Fs::getFs(), Fs::getFs());
	}
	
	public function testCopyForFilesAndFolders(): void {
		
		// Copy a file
		$sourceFile = $this->workspace . "/foo.txt";
		$targetFile = $this->workspace . "/bar.txt";
		$this->assertFalse(Fs::exists($sourceFile));
		$this->assertFalse(Fs::exists($targetFile));
		Fs::touch($sourceFile);
		$this->assertTrue(Fs::exists($sourceFile));
		Fs::copy($sourceFile, $targetFile);
		
		// Copy a directory
		$sourceDir = $this->workspace . "/foo/bar/baz";
		$targetDir = $this->workspace . "/bar/baz";
		$this->assertFalse(Fs::exists($sourceDir));
		Fs::mkdir($sourceDir);
		$this->assertTrue(Fs::exists($sourceDir));
		$sourceFiles = [];
		for ($i = 0; $i < 10; $i++) {
			$sourceFile = $sourceDir . "/" . md5(microtime(TRUE) . rand()) . ".txt";
			$sourceFiles[] = $sourceFile;
			Fs::touch($sourceFile);
			$this->assertTrue(Fs::exists($sourceFile));
		}
		$this->assertFalse(Fs::exists($targetDir));
		Fs::copy($this->workspace . "/foo", $targetDir);
		$this->assertTrue(Fs::exists($targetDir));
		foreach ($sourceFiles as $c => $sourceFile)
			$this->assertTrue(Fs::exists($targetDir . "/bar/baz/" .
				basename($sourceFile)), $targetDir . "/" . basename($sourceFile) . " ($c) was not copied!");
		
	}
	
	public function _testCopyFailOnMissingFileDataProvider(): array {
		return [
			[$this->workspace . "/doesNotExist.txt"],
			[$this->workspace . "/does/Not/Exist"],
		];
	}
	
	/**
	 * @param $source
	 *
	 * @dataProvider _testCopyFailOnMissingFileDataProvider
	 */
	public function testCopyFailOnMissingFile($source): void {
		$this->expectException(FileNotFoundException::class);
		Fs::copy($source, $this->workspace . "/foo");
	}
	
	public function testPermissionsSettingAndLookup(): void {
		// Write - only
		$this->assertTrue(Fs::isReadable($this->workspace));
		$workDir = $this->workspace . "/foo";
		$this->assertFalse(Fs::isReadable($workDir));
		$this->assertTrue(Fs::isWritable($workDir));
		Fs::mkdir($workDir);
		$this->assertTrue(Fs::isReadable($workDir));
		$this->assertTrue(Fs::isWritable($workDir));
		$this->assertEquals("0777", Fs::getPermissions($workDir));
		Fs::setPermissions($workDir, 0222);
		if (function_exists("posix_getuid") && posix_getuid() !== 0)
			$this->assertFalse(Fs::isReadable($workDir));
		$this->assertTrue(Fs::isWritable($workDir));
		$this->assertEquals("0222", Fs::getPermissions($workDir));
		
		// Read - only
		$workDir = $this->workspace . "/bar";
		Fs::mkdir($workDir);
		Fs::setPermissions($workDir, 0444);
		$this->assertEquals("0444", Fs::getPermissions($workDir));
		$this->assertTrue(Fs::isReadable($workDir));
		if (function_exists("posix_getuid") && posix_getuid() !== 0)
			$this->assertFalse(Fs::isWritable($workDir));
	}
	
	public function testGetDirectoryIterator(): void {
		Fs::touch($this->workspace . "/a.txt");
		Fs::touch($this->workspace . "/b.txt");
		Fs::touch($this->workspace . "/c.txt");
		Fs::mkdir($this->workspace . "/subDir");
		Fs::touch($this->workspace . "/subDir/a.txt");
		Fs::touch($this->workspace . "/subDir/b.txt");
		
		// Non-Recursive
		$it = Fs::getDirectoryIterator($this->workspace);
		$this->assertInstanceOf(\FilesystemIterator::class, $it);
		$this->assertIsIterable($it);
		$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $it);
		$this->assertEquals(4, count(iterator_to_array($it)));
		$dirs = 0;
		$names = ["a.txt", "b.txt", "c.txt", "subDir"];
		foreach ($it as $file) {
			if ($file->isDir()) $dirs++;
			$this->assertTrue(in_array($file->getBasename(), $names));
		}
		$this->assertEquals(1, $dirs);
		
		// Recursive
		$it = Fs::getDirectoryIterator($this->workspace, TRUE);
		$this->assertInstanceOf(\RecursiveIteratorIterator::class, $it);
		$this->assertIsIterable($it);
		$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $it);
		$this->assertEquals(6, count(iterator_to_array($it)));
		$dirs = 0;
		$names = ["a.txt", "b.txt", "c.txt", "subDir"];
		foreach ($it as $file) {
			if ($file->isDir()) $dirs++;
			$this->assertTrue(in_array($file->getBasename(), $names));
		}
		$this->assertEquals(1, $dirs);
		
		// With regex
		$it = Fs::getDirectoryIterator($this->workspace, TRUE, ["regex" => "/a\./si"]);
		$this->assertInstanceOf(\RegexIterator::class, $it);
		$this->assertIsIterable($it);
		$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $it);
		$this->assertEquals(2, count(iterator_to_array($it)));
		$names = ["a.txt"];
		foreach ($it as $file) {
			$this->assertTrue(in_array($file->getBasename(), $names));
		}
		
		// With folders first
		$it = Fs::getDirectoryIterator($this->workspace, TRUE, ["dirFirst"]);
		$this->assertInstanceOf(\RecursiveIteratorIterator::class, $it);
		$this->assertIsIterable($it);
		$this->assertContainsOnlyInstancesOf(\SplFileInfo::class, $it);
		$this->assertEquals(6, count(iterator_to_array($it)));
		$hasSubDir = FALSE;
		foreach ($it as $file) {
			if (stripos($file->getPathname(), "/subDir/") !== FALSE && !$hasSubDir)
				$this->fail("The directory was not given before it's children");
			if ($file->isDir() && $file->getBasename() === "subDir") $hasSubDir = TRUE;
		}
	}
	
	public function testReadAndWriteFile(): void {
		$filename = $this->workspace . "/foo.txt";
		Fs::writeFile($filename, "foo bar");
		$this->assertTrue(Fs::exists($filename));
		$this->assertEquals("foo bar", Fs::readFile($filename));
		Fs::appendToFile($filename, "bar baz");
		$this->assertEquals("foo bar" . PHP_EOL . "bar baz", Fs::readFile($filename));
		Fs::appendToFile($filename, "bar baz", FALSE);
		$this->assertEquals("foo bar" . PHP_EOL . "bar bazbar baz", Fs::readFile($filename));
		$this->assertEquals(["foo bar" . PHP_EOL, "bar bazbar baz"], Fs::readFileAsLines($filename));
	}
	
	public function testFlushDirectory(): void {
		Fs::touch($this->workspace . "/a.txt");
		Fs::touch($this->workspace . "/b.txt");
		Fs::touch($this->workspace . "/c.txt");
		Fs::mkdir($this->workspace . "/subDir");
		Fs::touch($this->workspace . "/subDir/a.txt");
		Fs::touch($this->workspace . "/subDir/b.txt");
		$this->assertTrue(Fs::exists($this->workspace) && is_dir($this->workspace));
		$this->assertTrue(Fs::exists($this->workspace . "/subDir"));
		Fs::flushDirectory($this->workspace);
		$this->assertTrue(Fs::exists($this->workspace) && is_dir($this->workspace));
		$this->assertFalse(Fs::exists($this->workspace . "/subDir"));
		$this->assertEquals(0, count(iterator_to_array(Fs::getDirectoryIterator($this->workspace))));
	}

    public function testIsFile(): void {
        Fs::touch($this->workspace . '/a.txt');
        Fs::touch($this->workspace . '/b.txt');
        Fs::mkdir($this->workspace . '/c');

        static::assertTrue(Fs::isFile($this->workspace . '/a.txt'));
        static::assertTrue(Fs::isFile([
            $this->workspace . '/a.txt',
            $this->workspace . '/b.txt'
        ]));
        static::assertFalse(Fs::isFile($this->workspace . '/c'));
        static::assertFalse(Fs::isFile([
            $this->workspace . '/a.txt',
            $this->workspace . '/b.txt',
            $this->workspace . '/c'
        ]));
    }

    public function testIsDir(): void {
        Fs::touch($this->workspace . '/a.txt');
        Fs::touch($this->workspace . '/b.txt');
        Fs::mkdir($this->workspace . '/c');

        static::assertFalse(Fs::isDir($this->workspace . '/a.txt'));
        static::assertFalse(Fs::isDir([
            $this->workspace . '/a.txt',
            $this->workspace . '/b.txt'
        ]));
        static::assertTrue(Fs::isDir($this->workspace . '/c'));
        static::assertFalse(Fs::isDir([
            $this->workspace . '/a.txt',
            $this->workspace . '/b.txt',
            $this->workspace . '/c'
        ]));
    }
}