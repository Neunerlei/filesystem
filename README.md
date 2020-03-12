# File System
This package contains a static wrapper around the [symfony file system component](https://symfony.com/doc/current/components/filesystem.html) with some additional features.

## Installation
Install this package using composer:

```
composer require neunerlei/filesystem
```

## Methods
The file system class can be found at ```Neunerlei\FileSystem\Fs```.

#### getFs()
Returns the singleton instance of the symfony file system class
```php
use Neunerlei\FileSystem\Fs;
// Access the symfony methods as you would normally
Fs::getFs()->isAbsolutePath("...");
```

#### copy()
Copies a file **or a directory**.
If the target file is older than the origin file, it's always overwritten.
```php
use Neunerlei\FileSystem\Fs;
// Copy a file
Fs::copy("/path/file.txt", "/anotherPath/file.txt");

// Copy / mirror a directory
Fs::copy("/path/to/directory", "/anotherPath");
```

#### mkdir()
Creates a directory recursively. 
```php
use Neunerlei\FileSystem\Fs;
Fs::mkdir("/create/directory/recursively");
```

#### exists()
Checks the existence of files or directories.
```php
use Neunerlei\FileSystem\Fs;
Fs::exists("/check/existence.txt"); // TRUE|FALSE
```

#### isReadable()
Tells whether a file or list of files exists and is readable.
```php
use Neunerlei\FileSystem\Fs;
Fs::isReadable("/check/readability.txt"); // TRUE|FALSE
```

#### isWritable()
Tells whether a file or list of files exists and is writable.
```php
use Neunerlei\FileSystem\Fs;
Fs::isWritable("/check/writeability.txt"); // TRUE|FALSE
```

#### touch()
Sets access and modification time of file.

NOTE: You can also pass DateTime objects as timestamps!
```php
use Neunerlei\FileSystem\Fs;
Fs::touch("/check/touchy.txt", new DateTime());
```

#### remove()
Removes files or directories.
```php
use Neunerlei\FileSystem\Fs;
Fs::remove("/path/to/remove.txt");
```

#### flushDirectory()
Removes all contents from a given directory without removing the element itself.
```php
use Neunerlei\FileSystem\Fs;
Fs::flushDirectory("/path/to/clean");
```

#### getDirectoryIterator()
Helper to create a directory iterator either for a single folder or recursively. Dots will
automatically be skipped. It can also find only files matching a regular expression. By default the folder will
come after the children, which can be toggled using the options.
```php
use Neunerlei\FileSystem\Fs;
// Traverse the direct children of a directory
Fs::getDirectoryIterator("/path/to/iterate");

// Traverse a directory recursively
Fs::getDirectoryIterator("/path/to/iterate", true);

// Filter by regex
Fs::getDirectoryIterator("/path/to/iterate", true, ["regex" => "/\.txt$/"]);

// Return the folders before returning the children
Fs::getDirectoryIterator("/path/to/iterate", true, ["dirFirst"]);
```

#### getPermissions()
Returns the unix file permissions for a given file like "0777" as a string.
```php
use Neunerlei\FileSystem\Fs;
Fs::getPermissions("/file/with/full/access.txt"); // "0777"
Fs::getPermissions("/file/with/read/access.txt"); // "0222"
```

#### setPermissions()
Can be used to set the unix permissions for a file or folder.
```php
use Neunerlei\FileSystem\Fs;
Fs::setPermissions("/file/path.txt", 0222);
Fs::setPermissions("/directory", 0222);
```

#### getOwner()
Returns the numeric unix user id for the given file or folder
```php
use Neunerlei\FileSystem\Fs;
Fs::getOwner("/file/access.txt"); // 1000
```

#### setOwner()
Can be used to update the owner of a given file or folder
```php
use Neunerlei\FileSystem\Fs;
Fs::setOwner("/file/access.txt", 1001);
```

#### getGroup()
Returns the numeric unix user group for the given filename
```php
use Neunerlei\FileSystem\Fs;
Fs::getGroup("/file/access.txt"); // 5
```

#### setGroup()
Can be used to update the group of a given file or folder
```php
use Neunerlei\FileSystem\Fs;
Fs::setGroup("/file/access.txt", 10);
```

#### readFile()
A wrapper around file_get_contents which reads the contents, but handles unreadable or non existing
files with speaking exceptions.
```php
use Neunerlei\FileSystem\Fs;
$content = Fs::readFile("/file/access.txt");
```

#### readFileAsLines()
A wrapper around file() which handles non existing, or unreadable files with speaking exceptions.
```php
use Neunerlei\FileSystem\Fs;
$lines = Fs::readFileAsLines("/file/access.txt");
```

#### writeFile()
Writes the given content into a file on your file system.
```php
use Neunerlei\FileSystem\Fs;
Fs::writeFile("/file.txt", "myContent");
```

#### appendToFile()
Appends content to an existing file. By default all content will be added on a new line.
```php
use Neunerlei\FileSystem\Fs;

// Add "myContent" as a new line to the file
Fs::appendToFile("/file.txt", "myContent");

// Add "myContent" directly at the end
Fs::appendToFile("/file.txt", "myContent", false);
```

## Running tests

- Clone the repository
- Install the dependencies with ```composer install```
- Run the tests with ```composer test```

## Special Thanks
Special thanks goes to the folks at [LABOR.digital](https://labor.digital/) (which is the word german for laboratory and not the english "work" :D) for making it possible to publish my code online.

## Postcardware
You're free to use this package, but if it makes it to your production environment I highly appreciate you sending me a postcard from your hometown, mentioning which of our package(s) you are using.

You can find my address [here](https://www.neunerlei.eu/). 

Thank you :D 