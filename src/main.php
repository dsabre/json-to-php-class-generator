<?php

require_once './vendor/autoload.php';

use Nette\PhpGenerator\ClassType;

if (!isset($_SERVER['argv'][1])) {
    throw new Exception('No json passed');
}

const OUTPUT_DIR = 'generated-classes';
$array = json_decode($_SERVER['argv'][1], true);
$counter = count(scandir(sprintf('./%s', OUTPUT_DIR))) - 2;
$class = new ClassType(sprintf('GeneratedClass%d', $counter));
$destination = sprintf('./%s/%s.php', OUTPUT_DIR, $class->getName());

foreach ($array as $k => $v) {
    $type = gettype($v);

    $class
        ->addProperty($k)
        ->setPrivate()
        ->addComment(' ')
        ->addComment(sprintf('@var %s', $type));

    $class
        ->addMethod(sprintf('%s%s', ($type === 'boolean' ? 'is' : 'get'), ucfirst($k)))
        ->addBody(sprintf('return %s$this->%s;', ($type === 'boolean' ? '!!' : ''), $k));

    $class
        ->addMethod(sprintf('set%s', ucfirst($k)))
        ->addBody(sprintf('$this->%s = $%s;%sreturn $this;', $k, $k, str_repeat(PHP_EOL, 2)))
        ->addParameter($k)
        ;
}

$output = sprintf('<?php%s%s', str_repeat(PHP_EOL, 2), $class);

file_put_contents($destination, $output);

echo sprintf('Class generated (see %s)%s', $destination, PHP_EOL);
