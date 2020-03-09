<?php
namespace Webmgine;
use Exception;
use ReflectionClass;

class ObjectManager {

    const OPT_DATA = 'data';
    const OPT_SINGLETON = 'singleton';

    protected static array $loadedObjects = [];

    public static function getObject(string $key, array $options = []) {
        $singleton = (isset($options[self::OPT_SINGLETON]) && is_bool($options[self::OPT_SINGLETON]) ? $options[self::OPT_SINGLETON] : false);
        if ($singleton && isset(self::$loadedObjects[$key])) {
            return self::$loadedObjects[$key];
        }
        $namespace = self::toNamespace($key);
        // Reflect class
        $classReflection = new ReflectionClass($namespace);
        $construct = $classReflection->getConstructor();
        if (is_null($construct)) {
			self::$loadedObjects[$key] = new $namespace;
            return self::$loadedObjects[$key];
        }
        $constructRequirements = $construct->getParameters();
        if (count($constructRequirements) < 1) {
            self::$loadedObjects[$key] = new $namespace;
            return self::$loadedObjects[$key];
        }
        // List arguments
        $arguments = [];
		foreach ($constructRequirements AS $requirement) {
            $var = $requirement->getName();
            $type = $requirement->getType();
            $data = (isset($options[self::OPT_DATA]) && isset($options[self::OPT_DATA][$var]) ? $options[self::OPT_DATA][$var] : null);
            if (!is_null($data)) {
                $arguments[$var] = $data;
                continue;
            } else if (is_null($type)) {
                throw new Exception('Non typed variable not set: '. $var);
            }
            $arguments[$var] = self::getObject(str_replace('\\', '/', $type->getName()), [self::OPT_SINGLETON => true]);
        }
        // Create object
        self::$loadedObjects[$key] = $classReflection->newInstanceArgs($arguments);
        return self::$loadedObjects[$key];
    }

    protected static function toNamespace(string $key): string {
        return (substr($key, 0 , 1) !== '/' ? '\\' : '').str_replace('/', '\\', $key);
    }
}