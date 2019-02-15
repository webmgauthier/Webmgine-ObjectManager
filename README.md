# Webmgine - ObjectManager

PHP object manager with dependencies injection based on namespaces/directories association

## Requirements

This class use Webmgine-AutoLoader as PHP class autoloader

## Getting Started

Use composer autload (or include **src/ObjectManager.php** in your project).
```
require __DIR__ . '/vendor/autoload.php';
```

Create object manager instance with your project base directory
```
$objectManager = new Webmgine\ObjectManager(__DIR__);
```

Load object
```
$object = $objectManager->getObject('Namespace/Class');
```

## Custom arguments

You can set custom arguments in a array using **array key** => **construct var name** association
```
$object = $objectManager->getObject('ExempleNamespace/ExempleClass', ['demo2' => 'exemple text']);
```
```
namespace ExempleNamespace;

class ExempleClass{

    public function __construct(
        \ExempleNamespace\ExempleClass2 $demo1,
        string $demo2
    ){
        // $demo1 -> Instance of \ExempleNamespace\ExempleClass2 class
        // $demo2 -> 'exemple text' (use custom argument from array associated by the array key and the var name)
    }
}
```

## Cache

Object are cached by namespace. If you load multiple time the same object you will receive a copy of the first one loaded (newly created object won't get new custom arguments if you set any after the inital load). It is possible to force the object to reset by setting true as the third argument for the **getObject** method.
```
$object = $objectManager->getObject('Namespace/Class', [], false);
```