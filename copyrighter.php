<?php

/**
 * This file implements the code manager.
 *
 * Usage:
 *
 *   php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --keep-comments --copyright=copyright.txt
 *   php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --copyright=copyright.txt
 *   php copyrighter.php obfuscate -f test/Test.php --copyright=copyright.txt
 *   php copyrighter.php obfuscate -f test/Test.php
 *
 *   php copyrighter.php obfuscate -d test --no-obfuscation --keep-comments --copyright=copyright.txt
 *   php copyrighter.php obfuscate -d test --no-obfuscation --copyright=copyright.txt
 *   php copyrighter.php obfuscate -d test --copyright=copyright.txt
 *   php copyrighter.php obfuscate -d test
 *
 *   php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --keep-comments --copyright=copyright.txt --overwrite
 *   php copyrighter.php obfuscate -f test/Test.php --no-obfuscation --copyright=copyright.txt --overwrite
 *   php copyrighter.php obfuscate -f test/Test.php --copyright=copyright.txt --overwrite
 *   php copyrighter.php obfuscate -f test/Test.php --overwrite
 *
 *   php copyrighter.php obfuscate -d test --no-obfuscation --keep-comments --copyright=copyright.txt --overwrite
 *   php copyrighter.php obfuscate -d test --no-obfuscation --copyright=copyright.txt --overwrite
 *   php copyrighter.php obfuscate -d test --copyright=copyright.txt --overwrite
 *   php copyrighter.php obfuscate -d test --overwrite
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter;
use PhpParser\NodeTraverser;
use PhpParser\Node;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class privateVarChanger
 * This class implements the "visitor" used to change the source code.
 * @see \PhpParser\NodeVisitorAbstract
 * @see https://github.com/nikic/PHP-Parser/blob/master/doc/2_Usage_of_basic_components.markdown
 */

class privateVarChanger extends \PhpParser\NodeVisitorAbstract
{
    private $__properties = array();
    private $__methods = array();
    private $__variables = array();
    private $__inMethod = false;
    private $__obfuscate = true;
    private $__removeComments = true;

    /**
     * Configure the visitor: tells it whether it should obfuscate the code or not.
     * @param bool $inMode Obfuscation indicator:
     *        - true: obfuscate the code.
     *        - false: do not obfuscate the code.
     */
    public function obfuscate($inMode=true) {
        $this->__obfuscate = $inMode;
    }

    /**
     * Configure the visitor: tells it whether it should remove the comments or not.
     * @param bool $inMode Comments removel indicator:
     *        - true: remove the comments.
     *        - false: do not remove the comments.
     */
    public function removeComments($inMode=true) {
        $this->__removeComments = $inMode;
    }

    /**
     * Obfuscate a given identifier.
     * @param string $inIdentifier The identifier to obfuscate.
     * @return string The method returns the obfuscated identifier.
     */
    private function __obfuscateVarName($inIdentifier) {
        return '___' . md5($inIdentifier, false);
    }

    /**
     * Obfuscate the declaration of a property and remove documentation.
     * Example: private $var = null;
     * @param Node\Stmt\Property $inNode Node.
     */
    private function __doPropertyDeclaration(Node\Stmt\Property $inNode) {
        if (Node\Stmt\Class_::MODIFIER_PRIVATE == $inNode->type) {
            /** @var array $properties */
            $properties = $inNode->props;
            /** @var Node\Stmt\PropertyProperty $_property */
            foreach ($properties as $_property) {
                $name = $_property->name;
                $newName = $this->__obfuscateVarName($name);
                $this->__properties[$name] = $newName;
                $_property->name = $newName;
            }
        }
    }

    /**
     * Obfuscate the declaration of a method.
     * Example: private function __go($c) { ... };
     * @param Node\Stmt\ClassMethod $inNode Node.
     */
    private function __doMethodDeclaration(Node\Stmt\ClassMethod $inNode) {
        // Change the name of the method (for private methods only).
        if (Node\Stmt\Class_::MODIFIER_PRIVATE == $inNode->type) {
            $name = $inNode->name;
            $newName = $this->__obfuscateVarName($name);
            $this->__methods[$name] = $newName;
            $inNode->name = $newName;
        }

        // Change all local variables within the method.
        /** @var array $params */
        $params = $inNode->params;
        /** @var Node\Param $_param */
        foreach ($params as $_param) {
            $name = $_param->name;
            $newName = $this->__obfuscateVarName($name);
            $this->__variables[$name] = $newName;
            $_param->name = $newName;
        }
    }

    /**
     * Obfuscate the usage of a variable within a function.
     * @param Node\Expr\Variable $inNode Node.
     * @return null
     */
    private function __doVariableUsage(Node\Expr\Variable $inNode) {
        $name = $inNode->name;
        if ('this' == $name) {
            return null;
        }
        $newName = null;
        if (array_key_exists($name, $this->__variables)) {
            $newName = $this->__variables[$name];
        } else {
            $newName = $this->__obfuscateVarName($name);
            $this->__variables[$name] = $newName;
        }
        $inNode->name = $newName;
    }

    /**
     * Obfuscate the usage of a property within a function.
     * @param Node\Expr\PropertyFetch $inNode Node.
     * @return null
     */
    private function __doPropertyUsage(Node\Expr\PropertyFetch $inNode) {
        /** @var Node\Expr\Variable $var */
        $var = $inNode->var;

        if ('this' == $var->name) {
            /** @var Node\Expr\Variable|string $name */
            $name = $inNode->name;

            $n = is_string($name) ? $name : $name->name;

            if (! array_key_exists($n, $this->__properties)) {
                return null;
            }
            $newName = $this->__properties[$n];
            $inNode->name = $newName;
        }
    }

    /**
     * Obfuscate the call of a (previously obfuscated) call of a method.
     * @param Node\Expr\MethodCall $inNode Node.
     * @return null
     */
    private function __doMethodCall(Node\Expr\MethodCall $inNode) {
        /** @var Node\Expr\Variable $var */
        $var = $inNode->var;
        if ('this' == $var->name) {
            $name = $inNode->name;
            if (! array_key_exists($name, $this->__methods)) {
                return null;
            }
            $newName = $this->__methods[$name];
            $inNode->name = $newName;
        }
    }

    /**
     * Remove all comments within a given node.
     * @param Node $inNode Node.
     */
    private function __removeComments(\PhpParser\Node $inNode) {
        if ($inNode->hasAttribute('comments')) {
            $inNode->setAttribute('comments', null);
        }
    }

    /**
     * Obfuscate the code whithin a given node.
     * @param Node $inNode Node
     * @return null
     */
    private function __obfuscate(\PhpParser\Node $inNode) {
        if ($inNode instanceof Node\Stmt\Property) {
            $this->__doPropertyDeclaration($inNode);
            return null;
        }

        if ($inNode instanceof Node\Stmt\ClassMethod) {
            $this->__inMethod = true;
            $this->__doMethodDeclaration($inNode);
            return null;
        }

        if ($inNode instanceof Node\Expr\Variable) {
            $this->__doVariableUsage($inNode);
            return null;
        }

        if ($inNode instanceof Node\Expr\PropertyFetch) {
            $this->__doPropertyUsage($inNode);
            return null;
        }

        if ($inNode instanceof Node\Expr\MethodCall) {
            $this->__doMethodCall($inNode);
            return null;
        }
    }

    /**
     * Method executed when the "visitor" enters a node.
     * @param Node $node Entered node.
     * @return null
     */
    public function enterNode(\PhpParser\Node $node) {
        if ($node instanceof Node\Stmt\Class_) {
            $this->__properties = array();
            $this->__methods = array();
            $this->__variables = array();
        }

        if ($this->__removeComments) {
            $this->__removeComments($node);
        }

        if ($this->__obfuscate) {
            $this->__obfuscate($node);
        }
    }

    /**
     * Method executed when the "visitor" leaves a node.
     * @param Node $node Left node.
     * @return null
     */
    public function leaveNode(\PhpParser\Node $node) {
        // Look for methods usages.
        if ($node instanceof Node\Expr\MethodCall) {
            $this->__variables = array();
            return null;
        }
    }
}


class Obfuscater extends Command {

    const CLO_DEBUG_PARSING = 'dbg-parser';
    const CLO_NO_OBFUSCATION = 'no-obfuscation';
    const CLO_KEEP_COMMENTS = 'keep-comments';
    const CLO_COPYRIGHT = 'copyright';
    const CLO_TMP_DIR = 'tmp';
    const CLO_OVERWRITE = 'overwrite';
    const CLO_DIR_INPUT  = 'dir';
    const CLO_FILE_INPUT = 'file';
    const CLOS_DIR_INPUT  = 'd';
    const CLOS_FILE_INPUT = 'f';

    private $__parser = null;
    private $__prettyPrinter = null;
    private $__parserDebug = false;
    private $__doNotObfuscate = false;
    private $__keepComments = false;

    /**
     * Obfuscater constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->addOption(self::CLO_DIR_INPUT,       self::CLOS_DIR_INPUT,   InputOption::VALUE_REQUIRED, 'Path to a directory', null)
             ->addOption(self::CLO_FILE_INPUT,      self::CLOS_FILE_INPUT,  InputOption::VALUE_REQUIRED, 'Path to a file', null)
             ->addOption(self::CLO_TMP_DIR,         null,                   InputOption::VALUE_REQUIRED, 'Path to the temporary directory', sys_get_temp_dir())
             ->addOption(self::CLO_DEBUG_PARSING,   null,                   InputOption::VALUE_NONE,     'Activate the parser debug')
             ->addOption(self::CLO_NO_OBFUSCATION,  null,                   InputOption::VALUE_NONE,     'Do NOT obfuscate the code')
             ->addOption(self::CLO_KEEP_COMMENTS,   null,                   InputOption::VALUE_NONE,     'Do NOT remove comments')
             ->addOption(self::CLO_COPYRIGHT,       null,                   InputOption::VALUE_REQUIRED, 'Path to the file that contains the copyright to insert', null)
             ->addOption(self::CLO_OVERWRITE,       null,                   InputOption::VALUE_NONE,     'Overwrite original file');

        $this->__parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        $this->__prettyPrinter = new PrettyPrinter\Standard();
    }

    /**
     * {@inheritdoc}
     * @see Command
     */
    protected function configure() {
        $this->setName('obfuscate')
             ->setDescription('Obfuscate the code and add the copyright.');
    }

    /**
     * {@inheritdoc}
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            // Get all files specified within the command line.
            $files = $this->__extractListForCloValue($input->getOption(self::CLO_FILE_INPUT));
            $directories = $this->__extractListForCloValue($input->getOption(self::CLO_DIR_INPUT));
            $this->__parserDebug = $input->getOption(self::CLO_DEBUG_PARSING);
            $this->__keepComments = $input->getOption(self::CLO_KEEP_COMMENTS);
            $this->__doNotObfuscate = $input->getOption(self::CLO_NO_OBFUSCATION);
            $copyrightPath = $input->getOption(self::CLO_COPYRIGHT);
            $overwrite = $input->getOption(self::CLO_OVERWRITE);
            $tmpDir = $input->getOption(self::CLO_TMP_DIR);

            if ($overwrite) {
                print PHP_EOL . PHP_EOL .
                    "WARNING !!!!!" . PHP_EOL . PHP_EOL .
                    "You will OVERWRITE the original files !!!" . PHP_EOL . PHP_EOL .
                    "Are you sure that you want to continue ? (\"Yes\" - case sensitive - or \"no\" - case insensitive)" . PHP_EOL . PHP_EOL;

                while(true){
                    $r = fgets(STDIN);
                    $r = preg_replace('/\r?\n$/', '', $r);
                    if ('Yes' === $r) {
                        break;
                    }
                    if (preg_match('/^\s*no\s*$/i', $r)) {
                        exit(0);
                    }
                    print 'Valid responses: "Yes" (case sensitive) or "no" (case insensitive)' . PHP_EOL;
                }
            }

            $copyright = null;
            if (! is_null($copyrightPath)) {
                $copyright = file_get_contents($copyrightPath);
                if (false === $copyright) {
                    throw new \Exception("Can not load the copyright from the file \"$copyrightPath\": " . $this->__lastErrorMessage());
                }
            }

            foreach ($directories as $_directory) {
                $files = array_merge($files, $this->__listAllFilesFromDirectory($_directory));
            }

            // Process all files.
            foreach ($files as $_file) {

                $code = $this->__obfuscate($_file);
                if (! is_null($copyright)) {
                    $code = $this->__addCopyright($code, $copyright);
                }
                if ($overwrite) {
                    print "[Overwrite] $_file" . PHP_EOL;
                    if (false === file_put_contents($_file, $code)) {
                        throw new \Exception("Can not write data into the file \"$_file\": " . $this->__lastErrorMessage());
                    }
                } else {
                    $output = $tmpDir . DIRECTORY_SEPARATOR . md5($_file, false);
                    print "$output: $_file" . PHP_EOL;
                    if (false === file_put_contents($output, $code)) {
                        throw new \Exception("Can not write data into the file \"$output\": " . $this->__lastErrorMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            fwrite(STDERR, "ERROR: " . $e->getMessage() . PHP_EOL);
            return false;
        }
        return true;
    }

    /**
     * Transform a text into a comment.
     * @param string $inText The text to comment.
     * @return string The commented text.
     */
    private function __addComment($inText) {
        $lines = explode(PHP_EOL, $inText);
        $res = array();
        /** @var string $_line */
        foreach ($lines as $_index => $_line) {
            $res[] = "//\t${_line}";
        }
        return implode(PHP_EOL, $res);
    }

    /**
     * Add a copyright to a given PHP code.
     * @param string $inCode The PHP code.
     * @param string $inCopyright The copyright to add.
     * @return string The method returns the code with the added comment.
     * @throws Exception
     */
    private function __addCopyright($inCode, $inCopyright) {
        $inCode = trim($inCode);

        $start = substr($inCode, 0, 5);
        $reminder = substr($inCode, 5);
        if (1 !== preg_match('/<\?php$/i', $start)) {
            throw new \Exception("Invalide PHP code: missing \"<?php\".");
        }

        return  $start . PHP_EOL . PHP_EOL .
                $this->__addComment($inCopyright) .
                PHP_EOL . PHP_EOL .
                $reminder;
    }

    /**
     * Obfuscate a given file.
     * @param string $inPath Path to the file to obfuscate.
     * @return string The method returns the obfuscated code.
     * @throws Exception
     */
    private function __obfuscate($inPath) {

        $code = file_get_contents($inPath);
        if (false === $code) {
            throw new \Exception("Can not load file \"$inPath\": " . $this->__lastErrorMessage());
        }

        if ($this->__doNotObfuscate && $this->__keepComments) {
            return $code;
        }

        $stmts = $this->__parser->parse($code);

        if ($this->__parserDebug) {
            var_dump($stmts);
            exit(0);
        }

        $traverser = new NodeTraverser;
        $visitor = new privateVarChanger();

        if ($this->__doNotObfuscate) {
            $visitor->obfuscate(false);
        }

        if ($this->__keepComments) {
            $visitor->removeComments(false);
        }

        $traverser->addVisitor($visitor);
        $stmts = $traverser->traverse($stmts);
        return $this->__prettyPrinter->prettyPrintFile($stmts);
    }

    /**
     * Extract all paths from a given command line option's value that represents a list of paths.
     * @param string|null $inCloValue The command line option's value.
     * @return array The method returns a list of absolute paths.
     */
    private function __extractListForCloValue($inCloValue) {

        $callback = function($inPath) {
            if (is_null($inPath)) {
                return array();
            }

            $realPath = realpath(trim($inPath));
            if (false === $realPath) {
                throw new \Exception("Invalid path \"$inPath\": " . $this->__lastErrorMessage());
            }
            return realpath(trim($inPath));
        };

        if (is_null($inCloValue)) {
            return array();
        }

        return array_map($callback, explode(',', $inCloValue));
    }

    /**
     * Get the list of all PHP files within a given directory.
     * @param string $inDirectory Path to the directory to traverse.
     * @return array The method returns a list of absolute paths that represents PHP files.
     */
    private function __listAllFilesFromDirectory($inDirectory) {
        $Directory = new RecursiveDirectoryIterator($inDirectory);
        $Iterator = new RecursiveIteratorIterator($Directory);
        $files = new RegexIterator($Iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);
        $result = array();
        foreach ($files as $_path => $_value) {
            if (1 === preg_match('/\/vendor\//', $_path)) {
                continue;
            }
            $result[] = realpath($_path);
        }
        return $result;
    }

    /**
     * Return the message associated to the last error.
     * @return string The message associated to the last error.
     */
    private function __lastErrorMessage() {
        $error = error_get_last();
        return $error['message'];
    }



}


$application = new Application();
$application->setAutoExit(true);
$application->add(new Obfuscater());
$application->run();



