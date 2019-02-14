<?php
namespace Webmgine;

class ObjectManager{

    protected $baseDir = '';
    protected $baseVarType = [
        "boolean",
        "integer",
        "double",
        "string",
        "array",
        "object",
        "resource",
        "resource (closed)",
        "NULL",
        "unknown type",
    ];
    protected $devMode = false;
    protected $state = [
        'error' => false,
        'logs' => []
    ];
    protected $jsonMemory = [];
    protected $objectMemory = [];
    protected $classRequirements = ['Webmgine/AutoLoader'];
    protected $tmpDir = '';

    public function __construct(string $baseDir, bool $devMode = false){
        $this->baseDir = realpath($baseDir);
        if($this->baseDir === false){
            $this->state['error'] = true;
            $this->state['logs'][] = 'Base directory '.$baseDir.' is missing';
            return;
        }
        $this->baseDir = $this->baseDir.'/';
        $this->devMode = $devMode;
        foreach($this->classRequirements AS $classRequirement){
            $classRequirement = '\\'.str_replace('/', '\\', $classRequirement);
            if(class_exists($classRequirement)){
                continue;
            }
            $this->state['error'] = true;
            $this->state['logs'][] = 'Class '.$classRequirement.' is missing';
        }
        $this->tmpDir = __DIR__.'/../tmp/';
    }

    public function filePathFromNamespace(string $namespace, string $fileExt = '.php'):string{
        $filePath = realpath($this->baseDir.str_replace('\\', DS, $namespace).$fileExt);
        if($filePath === false){
            return '';
        }
        return $filePath;
    }

    public function getObject(string $namespace, array $arguments = [], bool $reload = false){
        if($this->state['error']){
            return null;
        }
        if($reload == false && isset($this->objectMemory[$namespace])){
            return $this->objectMemory[$namespace];
        }
        $realNamespace = '\\'.str_replace('/', '\\', $namespace);
        $dependencies = $this->setDependencies($namespace, $arguments);
        if($this->state['error']){
            return null;
        }
        if(!$dependencies){
            $this->objectMemory[$namespace] = new $realNamespace;
            return $this->objectMemory[$namespace];
        }
        $indexFilePath = $this->tmpDir.md5(str_replace('\\', '/', $namespace)).'.tmp';
        $dependenciesList = json_decode(file_get_contents($indexFilePath));
        $dependencies = [];
        $objectData = '(';
        $first = true;
        foreach($dependenciesList AS $depVar => $depVal){
            $dependencies[$depVar] = $depVal;
            if(substr($depVar, 0, 2) === 'o_'){
                $dependencies[$depVar] = $this->getObject($depVal);
            }
            $objectData .= ($first?'':',').'$dependencies["'.$depVar.'"]';
            $first = false;
        }
        $objectData .= ')';
        eval('$this->objectMemory[$namespace] = new '.$realNamespace.$objectData.';');
        return $this->objectMemory[$namespace];
    }

    public function setDependencies(string $namespace, array $arguments = []):bool{
        $realNamespace = str_replace('/', '\\', $namespace);
        // Check if construct method exist
        if(!class_exists($realNamespace)){
            $this->state['error'] = true;
            $this->state['logs'][] = 'Class '.$namespace.' is missing';
            return false;
        }
        $classReflection = new \ReflectionClass($realNamespace);
        $construct = $classReflection->getConstructor();
        if(is_null($construct)){
			return false;
        }
        // Check if file index exists
        $indexFilePath = $this->tmpDir.md5(str_replace('\\', '/', $namespace)).'.tmp';
        if(!$this->devMode && realpath($indexFilePath) !== false){
            return true;
        }
        $constructRequirements = $construct->getParameters();
        if(count($constructRequirements) < 1){
            if(realpath($indexFilePath) !== false){
                unlink($indexFilePath);
            }
			return false;
        }
        if($this->devMode && realpath($indexFilePath) !== false){
            // Compare construct requirements with index file
            $currentIndexState = true;
            $currentIndexReq = json_decode(file_get_contents($indexFilePath));
            foreach($constructRequirements AS $req){
                if(
                    is_null($req->getType()) ||
                    in_array($req->getType(), $this->baseVarType)
                ){
                    if(isset($arguments[$req->getName()]) && $currentIndexReq['v_'.$req->getName()] === $arguments[$req->getName()]){
                        continue;
                    }
                    $currentIndexState = false;
                    break;
                }
                $reqName = $req->getName();
                $reqType = $req->getType()->getName();
                if(
                    !isset($currentIndexReq->$reqName) ||
                    $currentIndexReq->$reqName !== $reqType
                ){
                    $currentIndexState = false;
                    break;
                }
            }
            if($currentIndexState){
                return true;
            }
            unlink($indexFilePath);
        }
        // List dependencies
        $dependencies = [];
		foreach($constructRequirements AS $req){
            if(
                is_null($req->getType()) ||
                in_array($req->getType(), $this->baseVarType)
            ){
                if(!isset($arguments[$req->getName()])){
                    if($req->isOptional()){
                        continue;
                    }
                    $this->state['error'] = true;
                    $this->state['logs'][] = 'Argument '.$req->getName().' is missing';
                    return false;
                }
                $dependencies['v_'.$req->getName()] = $arguments[$req->getName()];
                continue;
            }
            $reqFilename = str_replace('\\', '/', $req->getType()->getName());
            $reqFilePath = realpath($this->filePathFromNamespace($reqFilename));
            if($reqFilePath === false){
				return false;
			}
            $dependencies['o_'.$req->getName()] = str_replace('\\', '/', $req->getType()->getName());
        }
        file_put_contents($indexFilePath, json_encode($dependencies));
        return true;
    }

    public function getState(){
        return $this->state;
    }
}