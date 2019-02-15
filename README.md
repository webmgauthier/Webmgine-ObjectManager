# Webmgine - ObjectManager

PHP object manager with dependencies injection based on namespaces/directories association

## Requirements

This class use Webmgine-AutoLoader as PHP class autoloader

## Getting Started

Use composer autload
```
require __DIR__ . '/vendor/autoload.php';
```
or include **src/ObjectManager.php** in your project.

Create object manager instance with your project base directory
```
$objectManager = new Webmgine\ObjectManager(__DIR__);
```

Load object
```
$object = $objectManager->getObject('Namespace/Class');
```

You can set custom arguments in a array using **array key** => **construct var name** association
```
$object = $objectManager->getObject('Namespace/Class', ['varX' => 'exemple']);
```