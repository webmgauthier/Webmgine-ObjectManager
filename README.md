# Webmgine - ObjectManager

PHP object manager with dependencies injection based on namespaces/directories association

## Getting Started

Include **ObjectManager.php** in your project.

Create object manager instance with your project base directory
```
$objectManager = new Webmgine\ObjectManager(__DIR__);
```

Load object
```
$object = $objectManager->getObject('Namespace/Class');
```

You can set arguments witch are not object in a array using array key => construct var name association
```
$object = $objectManager->getObject('Namespace/Class', ['varX' => 'exemple']);
```