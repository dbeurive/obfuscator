# Description

This repository contains a simple script that can be used to modify PHP source files in different ways:

* Add a copyright.
* Remove all comments.
* Obfuscate private class members (properties and methods) and (global/local) variables.

> Be aware that there are limitations that apply to the obfuscation feature. See the section "Limitations" further in
> this document.

By default, for **security reasons** (obfuscation is a one way only operation!):

* The script does not modify the input (PHP) file. You must **explicitly** specify that you want to overwrite the file.
* Even when you explicitly specify that you want to overwrite the file, the script asks you for confirmation.

The modifications can be applied to a single (PHP) file or to all (PHP) files under a given directory.

# Installation

Clone this repository or download/extract the ZIP archive.

Then run the command:

    composer install

This command will install the required dependencies under the directory "`vendor`".

Then you can delete:

* The file `copyright.txt`. This is just an example.
* The directory `test`. This is just an example.

# Synopsis

    # Dry-runs on single files

    php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --keep-comments --copyright=copyright.txt
    php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --copyright=copyright.txt
    php copyrighter.php obfuscate -f test/Test.php --copyright=copyright.txt
    php copyrighter.php obfuscate -f test/Test.php
    
    # Dry-runs on all (PHP) files under a given directory 
    
    php copyrighter.php obfuscate -d test --no-obfuscation --keep-comments --copyright=copyright.txt
    php copyrighter.php obfuscate -d test --no-obfuscation --copyright=copyright.txt
    php copyrighter.php obfuscate -d test --copyright=copyright.txt
    php copyrighter.php obfuscate -d test
    
    # Non-dry-runs on single files
    
    php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --keep-comments --copyright=copyright.txt --overwrite
    php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --copyright=copyright.txt --overwrite
    php copyrighter.php obfuscate -f test/Test.php --copyright=copyright.txt --overwrite
    php copyrighter.php obfuscate -f test/Test.php --overwrite

    # Non-dry-runs on all (PHP) files under a given directory 
    
    php copyrighter.php obfuscate -d test --no-obfuscation --keep-comments --copyright=copyright.txt --overwrite
    php copyrighter.php obfuscate -d test --no-obfuscation --copyright=copyright.txt --overwrite
    php copyrighter.php obfuscate -d test --copyright=copyright.txt --overwrite
    php copyrighter.php obfuscate -d test --overwrite

# CLI

Obfuscate a single file:

    php copyrighter.php obfuscate (-f|--file) /path/to/file.php
        [--no-obfuscation]
        [--keep-comments]
        [--copyright=/path/to/copyright/file.txt]
        [--tmp=/tmp/to/temp/dir]
        [--dbg-parser]
        [--overwrite]

Obfuscate the content of a given directory:
        
    php copyrighter.php obfuscate (-d|--dir) /path/to/directory
        [--no-obfuscation]
        [--keep-comments]
        [--copyright=/path/to/copyright/file.txt]
        [--tmp=/tmp/to/temp/dir]
        [--dbg-parser]
        [--overwrite]

* `-f` ou `--file`: specify the path to the (PHP) file to modify.
* `-d` ou `--dir`: specify the path to the directory that contains the PHP files to modify.
* `--no-obfuscation`: deactivate the obfuscation. The code will not be obfuscated.
* `--keep-comments`: keeps the comments.
* `--copyright=/path/to/copyright/file.txt`: specify the path to a file that contains the text of the copyright to add.
* `--tmp=/tmp/to/temp/dir`: specify the path to the directory used to store temporary files.
* `--dbg-parser`: print data that can be used to debug the PHP parser.
* `--overwrite`: tells the script that it must overwrite input files.

# Examples

## Dry-runs on single files

### Add a copyright

    $ php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --keep-comments --copyright=copyright.txt
    /tmp/65bb9568619c25a9121dbdf406cc82ac: /home/denis/Desktop/Backups/tools/test/Test.php
    
This command line just adds a copyright to a given PHP file (`test/Test.php` in this case).

> The source file (`test/Test.php`) is untouched.
> The command creates a new file which path is `/tmp/65bb9568619c25a9121dbdf406cc82ac`. 

### Add a copyright + remove comments

    $ php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --copyright=copyright.txt
    /tmp/65bb9568619c25a9121dbdf406cc82ac: /home/denis/Desktop/Backups/tools/test/Test.php
    
This command line adds a copyright and removes all comments from a given PHP file (`test/Test.php` in this case).

> The source file (`test/Test.php`) is untouched.
> The command creates a new file which path is `/tmp/65bb9568619c25a9121dbdf406cc82ac`.

### Add a copyright + remove comments + obfuscate

    $ php copyrighter.php obfuscate -f test/Test.php --copyright=copyright.txt
    /tmp/65bb9568619c25a9121dbdf406cc82ac: /home/denis/Desktop/Backups/tools/test/Test.php
    
This command line adds a copyright, removes all comments and obfuscates the private properties and methods of all classes from a given file (`test/Test.php` in this case).

> The source file (`test/Test.php`) is untouched.
> The command creates a new file which path is `/tmp/65bb9568619c25a9121dbdf406cc82ac`.

### Remove comments + obfuscate

    $ php copyrighter.php obfuscate -f test/Test.php
    /tmp/65bb9568619c25a9121dbdf406cc82ac: /home/denis/Desktop/Backups/tools/test/Test.php
    
This command line removes all comments, and obfuscates the private properties and methods of all classes from a given file
(`test/Test.php` in this case).

> The source file (`test/Test.php`) is untouched.
> The command creates a new file which path is `/tmp/65bb9568619c25a9121dbdf406cc82ac`.

## Dry-runs on directories

### Add a copyright

    $ php copyrighter.php obfuscate -d test --no-obfuscation --keep-comments --copyright=copyright.txt
    /tmp/b35ebdba142e56496111e6eb5cd34b4c: /home/denis/Desktop/Backups/tools/test/test1.php
    /tmp/ad8ee05cd758f83dcb41ea9d23b4c65f: /home/denis/Desktop/Backups/tools/test/lib/test1.php
    /tmp/a0dd2d71c81799bbd98bca708e422106: /home/denis/Desktop/Backups/tools/test/lib/test2.php
    /tmp/2f4be8318f1a368a6f72f4f23c0c7fc0: /home/denis/Desktop/Backups/tools/test/test2.php
    
This command line just adds a copyright to all PHP files under a given directory (`test` in this case).

> The source files remain untouched.
> The command creates a new files. 

### Add a copyright + remove comments

    $ php copyrighter.php obfuscate -d test --no-obfuscation --copyright=copyright.txt
    /tmp/b35ebdba142e56496111e6eb5cd34b4c: /home/denis/Desktop/Backups/tools/test/test1.php
    /tmp/ad8ee05cd758f83dcb41ea9d23b4c65f: /home/denis/Desktop/Backups/tools/test/lib/test1.php
    /tmp/a0dd2d71c81799bbd98bca708e422106: /home/denis/Desktop/Backups/tools/test/lib/test2.php
    /tmp/2f4be8318f1a368a6f72f4f23c0c7fc0: /home/denis/Desktop/Backups/tools/test/test2.php
    
This command line adds a copyright and removes all comments from all PHP files under a given directory (`test` in this case).

> The source files remain untouched.
> The command creates a new files. 

### Add a copyright + remove comments + obfuscate

    $ php copyrighter.php obfuscate -d test --copyright=copyright.txt
    /tmp/b35ebdba142e56496111e6eb5cd34b4c: /home/denis/Desktop/Backups/tools/test/test1.php
    /tmp/ad8ee05cd758f83dcb41ea9d23b4c65f: /home/denis/Desktop/Backups/tools/test/lib/test1.php
    /tmp/a0dd2d71c81799bbd98bca708e422106: /home/denis/Desktop/Backups/tools/test/lib/test2.php
    /tmp/2f4be8318f1a368a6f72f4f23c0c7fc0: /home/denis/Desktop/Backups/tools/test/test2.php
    
This command line adds a copyright, removes all comments and obfuscates the private properties and methods of all PHP files under a given directory (`test` in this case).

> The source files remain untouched.
> The command creates a new files. 

### Remove comments + obfuscate

    $ php copyrighter.php obfuscate -d test
    /tmp/b35ebdba142e56496111e6eb5cd34b4c: /home/denis/Desktop/Backups/tools/test/test1.php
    /tmp/ad8ee05cd758f83dcb41ea9d23b4c65f: /home/denis/Desktop/Backups/tools/test/lib/test1.php
    /tmp/a0dd2d71c81799bbd98bca708e422106: /home/denis/Desktop/Backups/tools/test/lib/test2.php
    /tmp/2f4be8318f1a368a6f72f4f23c0c7fc0: /home/denis/Desktop/Backups/tools/test/test2.php
    
This command line removes all comments, and obfuscates the private properties and methods of all PHP files under a given directory (`test` in this case).

> The source files remain untouched.
> The command creates a new files. 

## Non dry-runs on single files

### Add a copyright

    $ php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --keep-comments --copyright=copyright.txt --overwrite
    WARNING !!!!!
    
    You will OVERWRITE the original files !!!
    
    Are you sure that you want to continue ? ("Yes" - case sensitive - or "no" - case insensitive)
    Yes
    [Overwrite] /home/denis/Desktop/Backups/tools/test/Test.php
    
This command line just adds a copyright to a given PHP file (`test/Test.php` in this case).

> The source file (`test/Test.php`) is modified.

### Add a copyright + remove comments

    $ php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --copyright=copyright.txt --overwrite
    WARNING !!!!!
        
    You will OVERWRITE the original files !!!
       
    Are you sure that you want to continue ? ("Yes" - case sensitive - or "no" - case insensitive)        
    Yes
    [Overwrite] /home/denis/Desktop/Backups/tools/test/Test.php
        
This command line adds a copyright and removes all comments from a given PHP file (`test/Test.php` in this case) .

> The source file (`test/Test.php`) is modified.

### Add a copyright + remove comments + obfuscate

    $ php copyrighter.php obfuscate -f test/Test.php --copyright=copyright.txt --overwrite
    WARNING !!!!!
    
    You will OVERWRITE the original files !!!
    
    Are you sure that you want to continue ? ("Yes" - case sensitive - or "no" - case insensitive)
    Yes
    [Overwrite] /home/denis/Desktop/Backups/tools/test/Test.php
    
This command line adds a copyright to a given PHP file, removes all comments and obfuscates
the private properties and methods of all classes from a given file (`test/Test.php` in this case).

> The source file (`test/Test.php`) is modified.

### Remove comments + obfuscate

    $ php copyrighter.php obfuscate -f test/Test.php --overwrite
    WARNING !!!!!
    
    You will OVERWRITE the original files !!!
    
    Are you sure that you want to continue ? ("Yes" - case sensitive - or "no" - case insensitive)
    Yes
    [Overwrite] /home/denis/Desktop/Backups/tools/test/Test.php
    
This command line removes all comments, and obfuscates the private properties and methods of all classes from a given file
(`test/Test.php` in this case).

> The source file (`test/Test.php`) is modified.

## Non dry-runs on directories

### Add a copyright

    $ php copyrighter.php obfuscate -d test --no-obfuscation --keep-comments --copyright=copyright.txt --overwrite
    WARNING !!!!!
    
    You will OVERWRITE the original files !!!
    
    Are you sure that you want to continue ? ("Yes" - case sensitive - or "no" - case insensitive)
    Yes
    [Overwrite] /home/denis/Desktop/Backups/tools/test/test1.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/lib/test1.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/lib/test2.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/test2.php
    
This command line just adds a copyright to all PHP files under a given directory (`test` in this case).

> The source files are modified.

### Add a copyright + remove comments

    $ php copyrighter.php obfuscate -d test --no-obfuscation --copyright=copyright.txt --overwrite
    WARNING !!!!!
        
    You will OVERWRITE the original files !!!
       
    Are you sure that you want to continue ? ("Yes" - case sensitive - or "no" - case insensitive)
    Yes
    [Overwrite] /home/denis/Desktop/Backups/tools/test/test1.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/lib/test1.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/lib/test2.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/test2.php
        
This command line adds a copyright and removes all comments from all PHP files under a given directory (`test` in this case) .

> The source files are modified.

### Add a copyright + remove comments + obfuscate

    $ php copyrighter.php obfuscate -f test/Test.php --copyright=copyright.txt --overwrite
    WARNING !!!!!
    
    You will OVERWRITE the original files !!!
    
    Are you sure that you want to continue ? ("Yes" - case sensitive - or "no" - case insensitive)    
    Yes
    [Overwrite] /home/denis/Desktop/Backups/tools/test/test1.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/lib/test1.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/lib/test2.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/test2.php
    
This command line adds a copyright, removes all comments and obfuscates the private properties and methods of all classes from all PHP files undef a given directory (`test` in this case).

> The source files are modified.

### Remove comments + obfuscate

    $ php copyrighter.php obfuscate -f test/Test.php --overwrite
    WARNING !!!!!
    
    You will OVERWRITE the original files !!!
    
    Are you sure that you want to continue ? ("Yes" - case sensitive - or "no" - case insensitive)
    Yes
    [Overwrite] /home/denis/Desktop/Backups/tools/test/test1.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/lib/test1.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/lib/test2.php
    [Overwrite] /home/denis/Desktop/Backups/tools/test/test2.php
    
This command line removes all comments, and obfuscates the private properties and methods of all classes from all PHP
files under a given directory (`test` in this case).

> The source files are modified.

# Limitations

Be aware that the obfuscation feature does not work if you access propreties and method _indirectly_.
For example, this script cannot obfuscate the code below:

    class TestMe {
    
        private $__p = 0;
    
        public function reflect($method) {
            $rm = new ReflectionMethod($this, $method);
            if (!$rm->isPublic()) {
                $rm->setAccessible(true);
            }
            $rm->invoke($this);
        }
    
        private function __m1() {
            print(__METHOD__);
        }
    
        public function m2() {
            // The string '__m1' will remain unchanged !!!!
            $this->reflect('__m1');
            // The string '__p' will remain unchanged !!!!
            $name = '__p';
            $this->$name = 10;
        }
    
    }
    
    $c = new TestMe();
    $c->m2();

In this case, the name of the method "`__m1`", as it appears in its declaration (`private function __m1()`), will be
obfuscated. However:

* the string `'__m1'`, that appears in `$this->reflect('__m1')` will remain unchanged.
* the string `'__p'`, that appears in `$name = '__p'` will remain unchanged.

See by yourself:

    class TestMe
    {
        private $___95ba2ca2f6ae83f5c55884928d4670b1 = 0;
        public function reflect($___ea9f6aca279138c58f705c8d4cb4b8ce)
        {
            $___d67f249b90615ca158b1258712c3a9fc = new ReflectionMethod($this, $___ea9f6aca279138c58f705c8d4cb4b8ce);
            if (!$___d67f249b90615ca158b1258712c3a9fc->isPublic()) {
                $___d67f249b90615ca158b1258712c3a9fc->setAccessible(true);
            }
            $___d67f249b90615ca158b1258712c3a9fc->invoke($this);
        }
        private function ___906d5d093218d2b6b934e91103ad2fb7()
        {
            print __METHOD__;
        }
        public function m2()
        {
            $this->reflect('__m1');
            $___b068931cc450442b63f5b3d276ea4297 = '__p';
            $this->{$___b068931cc450442b63f5b3d276ea4297} = 10;
        }
    }
    $___4a8a08f09d37b73795649038408b5f33 = new TestMe();
    $___4a8a08f09d37b73795649038408b5f33->m2();

