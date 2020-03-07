# Webmgine - ObjectManager

Static class to load objects with dependencies injection

## Requirements

PHP >=7.4
This class need composer autoload

## Getting Started
```
require <YOUR PROJECT DIR> . '/vendor/autoload.php';
```

To load an object, just call the **getObject** function and set namespace as string using / instead of \
```
use Webmgine\ObjectManager;
ObjectManager::getObject('Namespace/As/String');
```
or
```
\Webmgine\ObjectManager::getObject('Namespace/As/String');
```

## Options

### Custom arguments (data)

You can manually set arguments in a array using **array key** => **construct var name** association
```
namespace ExempleNamespace;

class ExempleClass{

    public function __construct(
        \ExempleNamespace\ExempleClass2 $demo1,
        string $demo2
    ) {
        // $demo1 -> Instance of \ExempleNamespace\ExempleClass2 class
        // $demo2 -> 'exemple text' (use custom argument from array associated by the array key and the var name)
    }
}
```
```
ObjectManager::getObject('ExempleNamespace/ExempleClass', ['data' => ['demo2' => 'exemple text']]);
```

### Singletons (singleton)

You can set singleton to true, this will make the object manager return an existing object if any
```
ObjectManager::getObject('ExempleNamespace/ExempleClass', ['singleton' => true]);
```