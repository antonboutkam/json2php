<?php

namespace Hurah\Generators\Command\Json2Php\Generator;

use Hurah\Generators\Command\Json2Php\Types\NamespaceCollection;
use Hurah\Generators\Service\Service;
use Hurah\Generators\Util\Collection;
use Hurah\Generators\Util\Detect;
use Hurah\Generators\Util\Naming;
use Hurah\Types\Exception\InvalidArgumentException;
use Hurah\Types\Exception\NullPointerException;
use Hurah\Types\Type\AbstractCollectionDataType;
use Hurah\Types\Type\Json;
use Hurah\Types\Type\Path;
use Hurah\Types\Type\PhpNamespace;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Method as MethodType;
use Nette\PhpGenerator\PhpNamespace as PhpNamespaceType;


class Main
{

    private Json $oJson;
    private Path $oPath;
    private Service $oService;
    private string $sBaseNamespaceName;
    private NamespaceCollection $oNamespaceCollection;

    public function __construct(PhpNamespace $oNamespace, Path $oSourceFile, Service $oService)
    {
        $this->oJson = $oSourceFile->getFile()->asJson();

        $this->oPath = $oSourceFile;
        $this->oService = $oService;
        $this->oNamespaceCollection = new NamespaceCollection();
        $this->sBaseNamespaceName = "{$oNamespace}";

    }

    public function generate()
    {
        $sMainClassName = Naming::className($this->oPath->basename());
        $aRootJson = $this->oJson->toArray();

        $this->createClass($this->sBaseNamespaceName, $sMainClassName, $aRootJson);
        $this->generateFiles();
    }

    private function addFromArray(ClassType $oClassType, array $aProperties, string $sReturnType)
    {
        $this->oService->getOutput()->writeln("Add from array method to: <info>{$oClassType->getName()}</info>");

        $oFromArrayMethod = $oClassType->addMethod('fromArray');
        $oFromArrayMethod->setStatic();
        $oFromArrayMethod->setPublic();
        $aBody = [];
        $aBody[] = '$new = new \\' . $sReturnType . '();';
        foreach ($aProperties as $sPropertyKey => $mPropertyValue)
        {
            $aBody[] = 'if(isset($input[\'' . $sPropertyKey . '\'])){';
            if (is_array($mPropertyValue))
            {
                $aBody[] = '    $new->' . $this->getSetterMethodName($sPropertyKey) . '($input[\'' . $sPropertyKey . '\']);';
            }
            else
            {
                $aBody[] = '$new->' . $this->getSetterMethodName($sPropertyKey) . '($input[\'' . $sPropertyKey . '\']);';
            }
            $aBody[] = '}';
        }
        $aBody[] = 'return $new;';

        $oFromArrayMethod->setBody(join(PHP_EOL, $aBody));
        $oFromArrayMethod->setReturnType($sReturnType);

        $oInputParameter = $oFromArrayMethod->addParameter('input');
        $oInputParameter->setType('array');
    }


    /**
     * @throws NullPointerException
     */
    private function createClass(string $sNamespaceName, string $sClassName, array $aProperties = null, array $aUse = null): ClassType{
        $this->oService->getOutput()->writeln("Create class <info>$sClassName</info>");
        $oBaseNamespace = $this->getOrCreateNamespace($sNamespaceName . '\\Base');
        $this->addUse($oBaseNamespace, $aUse);

        $oClassType = $oBaseNamespace->addClass($sClassName);
        $oClassType->setAbstract();
        $sBaseClassFqn = $oBaseNamespace->getName() . '\\' . $oClassType->getName();
        $sBaseClassAlias = 'Base' . $oClassType->getName();
        $oNamespace = $this->getOrCreateNamespace($sNamespaceName);
        $this->addUse($oNamespace, [$sBaseClassFqn => $sBaseClassAlias]);
        $oUserClass = $oNamespace->addClass($sClassName);
        $oUserClass->setExtends($sBaseClassFqn);

        if (is_iterable($aProperties))
        {
            $sFqnUserClassName = $oNamespace->getName()  . '\\' . $oUserClass->getName();
            $this->addProperties($sNamespaceName, $oClassType, $aProperties);
            $this->addFromArray($oClassType, $aProperties, $sFqnUserClassName);
        }
        foreach ($oClassType->getProperties() as $oExistingProperty)
        {
            $sPropName = $oExistingProperty->getName();
            if (!isset($aProperties[$sPropName]))
            {
                $this->oService->getOutput()->writeln("Making <info>$sPropName</info> nullable");
                $oExistingProperty->setNullable();
            }
        }

        return $oClassType;
    }

    /**
     * @throws NullPointerException
     */
    private function addProperties(string $sNamespaceName, ClassType $oClass, array $aJson)
    {
        $this->oService->getOutput()->writeln("Add properties to <info>$sNamespaceName\\{$oClass->getName()}</info>");

        foreach ($aJson as $sKey => $mValue)
        {
            if (is_iterable($mValue))
            {
                if (Collection::isAssoc($mValue))
                {
                    $this->oService->getOutput()->writeln('done: <comment>' . $sKey . '</comment>');
                    $sType = $this->createCollectionClass($sKey, $sNamespaceName, $mValue);
                }
                else
                {
                    foreach ($mValue as $iIndex => $aProperties)
                    {
                        $this->oService->getOutput()->writeln('<info>$sKey</info>: <error>' . $iIndex . '</error>');
                        $sType = $this->createCollectionClass($sKey, $sNamespaceName, $aProperties);
                    }
                }
                $this->getOrCreateNamespace($sNamespaceName . '\\Base')->addUse($sType);
            }
            else
            {
                $sType = Detect::type($mValue);
            }
            $this->addProperty($oClass, $sKey, $sType, $mValue);
            $this->addGetter($oClass, $sKey, $sType);
            $this->addSetter($oClass, $sKey, $sType);
        }
    }
    /**
     * @throws NullPointerException
     */
    private function addCollectionClass(string $sNamespaceName, string $sSubjectClassName, string $sCollectionClassName, string $oSubjectVarName): void
    {
        $this->oService->getOutput()->writeln("Create collection <info>{$sSubjectClassName}->\${$sCollectionClassName}</info>");

        $oNamespace = $this->getOrCreateNamespace($sNamespaceName);

        $sFullSubjectClassName = $oNamespace->getName() . '\\' . $sSubjectClassName;
        $this->oService->getOutput()->writeln($oNamespace->getName());
        $this->oService->getOutput()->writeln($sFullSubjectClassName);
        $this->oService->getOutput()->writeln('---------------------');
        $sSubjectAlias = 'Custom' . $sSubjectClassName;
        $sCustomCollectionAlias = 'Custom' . $sCollectionClassName;
        $sCustomCollectionFqn = $oNamespace->getName() . '\\' . $sCollectionClassName;
        $aUse = [
            '\\' . $sFullSubjectClassName => $sSubjectAlias,
            AbstractCollectionDataType::class,
            $sCustomCollectionFqn => $sCustomCollectionAlias
        ];

        $oClass = $this->createClass($oNamespace->getName(), $sCollectionClassName, [], $aUse);

        $oFromArrayMethod = $oClass->addMethod('fromArray');
        $oFromArrayMethod->setStatic();
        $oFromArrayMethod->setReturnType($sCustomCollectionFqn);
        $oFromArrayMethod->setPublic();
        $oFromArrayMethod->addParameter('input');
        $aBody = [];
        $aBody[] = '$oNew = new \\' . $sCustomCollectionFqn . '();';
        $aBody[] = 'if(is_iterable($input))';
        $aBody[] = '{';
        $aBody[] = '    foreach($input as $item)';
        $aBody[] = '    {';
        $aBody[] = '        $oNew->add(' . $sSubjectAlias . '::fromArray($item));';
        $aBody[] = '    }   ';
        $aBody[] = '}';
        $aBody[] = 'return $oNew;';
        $oFromArrayMethod->addBody(join(PHP_EOL, $aBody));


        $oClass->addExtend(AbstractCollectionDataType::class);
        $oCurrentMethod = $oClass->addMethod('current');

        $oCurrentMethod->setReturnType('\\' . $sFullSubjectClassName);
        $oCurrentMethod->setBody('return $this->array[$this->position];');
        $oCurrentMethod->setPublic();

        $oAddMethod = $oClass->addMethod('add');
        $oAddParameter = $oAddMethod->addParameter($oSubjectVarName);
        $oAddMethod->setPublic();
        $oAddParameter->setType('\\' . $sFullSubjectClassName);
        $oAddMethod->addBody('$this->array[] = $' . $oSubjectVarName . ';');

    }
    private function addProperty(ClassType $oClass, string $sPropertyName, string $sType, $mPropertyValue): void
    {
        if ($oClass->hasProperty($sPropertyName))
        {
            $oProperty = $oClass->getProperty($sPropertyName);
        }
        else
        {
            $this->oService->getOutput()->writeln("Create property <info>{$oClass->getName()}->\${$sPropertyName}</info>");
            $oProperty = $oClass->addProperty($sPropertyName);
        }

        if (is_null($mPropertyValue))
        {
            $this->oService->getOutput()->writeln("Making <info>$sPropertyName</info> nullable");
            $oProperty->setNullable(true);
        }
        // @todo: remove this in the future when directories are scanned instead of a single file
        $oProperty->setNullable(true);
        $oProperty->setPrivate();
        $oProperty->setType($sType);
    }
    private function addGetter(ClassType $oClass, string $sPropertyName, string $sType): MethodType
    {
        $sGetterName = Naming::methodName($sPropertyName, 'get');
        $this->oService->getOutput()->writeln("Create getter <info>{$oClass->getName()}->{$sGetterName}</info>");

        $oMethod = $oClass->addMethod($sGetterName);
        $oMethod->setPublic();
        $oMethod->setBody('return $this->' . $sPropertyName . ' ?? null;');
        $oMethod->setReturnType($sType);
        $oMethod->setReturnNullable();
        return $oMethod;
    }
    private function addSetter(ClassType $oClass, string $sPropertyName, string $sType): MethodType
    {
        $bObjectType = false;
        if(preg_match('/\\\\/', $sType))
        {
            $bObjectType = true;
        }
        $sSetterName = $this->getSetterMethodName($sPropertyName);
        $this->oService->getOutput()->writeln("Create setter <info>{$oClass->getName()}->{$sSetterName}</info>");
        $oMethod = $oClass->addMethod($sSetterName);
        $oParameter = $oMethod->addParameter('value');
        if($bObjectType)
        {
            $oParameter->setType('array');
        }
        else
        {
            $oParameter->setType($sType);
        }

        $oMethod->setPublic();
        $oMethod->setReturnType('void');
        if($bObjectType)
        {
            $sShortType = array_reverse(explode('\\', $sType))[0];
            $oMethod->setBody('$this->' . $sPropertyName . ' = ' . $sShortType . '::fromArray($value);');
        }
        else
        {
            $oMethod->setBody('$this->' . $sPropertyName . ' = $value;');
        }
        return $oMethod;
    }


    /*
    public function addItem($mInput, $sKey = null)
    {
        if(is_array($mInput))
        {
            if(Collection::isAssoc($mInput))
            {
                foreach($mInput as $sVarName => $mVarValue)
                {
                    $this->addItem($sVarName, $mVarValue);
                }
            }
            else
            {
                foreach($this->)
            }
            $this->addCollection($mInput, $sKey);
            foreach ($mInput as $mInputIndex => $mInputPart)
            {
                if(is_array($mInputPart))
                {
                    $this->addCollection($mInput, $mInputIndex);
                }

            }
        }

    }
    public function addCollection($mInput, $sKey = null) {

    }
*/
    /**
     * @param PhpNamespaceType $oNamespaceType
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function generateFiles(): void
    {
        foreach ($this->oNamespaceCollection as $oNamespace)
        {
            $sNamespaceName = str_replace($this->sBaseNamespaceName, '', $oNamespace->getName());
            $sToDir = str_replace('\\', DIRECTORY_SEPARATOR, $sNamespaceName);
            $sToDir = preg_replace('/^\//', '', $sToDir);

            $oDestinationDir = Path::make(getcwd(), $sToDir)->makeDir();
            foreach ($oNamespace->getClasses() as $oClassType)
            {
                $oWorkNamespace = new PhpNamespaceType($oNamespace->getName());
                foreach($oNamespace->getUses() as $alias => $class)
                {

                    $oWorkNamespace->addUse($class, $alias);
                }


                $oWorkNamespace->add($oClassType);

                $oDestinationFile = $oDestinationDir->extend("{$oClassType->getName()}.php");
                $this->oService->getOutput()->writeln("Write: <info>{$oDestinationFile}</info>");

                $oDestinationFile->write('<?php' . PHP_EOL . $oWorkNamespace);
            }
        }

    }

    /**
     * @param string $sNamespaceName
     *
     * @return PhpNamespaceType
     * @throws NullPointerException
     */
    private function getOrCreateNamespace(string $sNamespaceName): PhpNamespaceType
    {
        if ($this->oNamespaceCollection->hasNamespace($sNamespaceName))
        {
            $this->oService->getOutput()->writeln("Opening namespace <info>$sNamespaceName</info>");
            return $this->oNamespaceCollection->getNamespace($sNamespaceName);
        }

        $this->oService->getOutput()->writeln("Create namespace <info>$sNamespaceName</info>");
        return $this->oNamespaceCollection->make($sNamespaceName);

    }

    /**
     * @param string $sPropertyName
     *
     * @return string
     */
    private function getSetterMethodName(string $sPropertyName): string
    {
        $sSetterName = Naming::methodName($sPropertyName, 'set');
        return $sSetterName;
    }

    /**
     * @param $sKey
     * @param string $sNamespaceName
     * @param $mValue
     *
     * @return string
     * @throws NullPointerException
     */
    private function createCollectionClass($sKey, string $sNamespaceName, $mValue): string
    {
        // Create the class containing a single entity
        $oCollectionSharedName = Naming::className($sKey);
        $sItemClassName = $oCollectionSharedName . 'Item';
        $sSharedNamespace = $sNamespaceName . '\\' . $oCollectionSharedName;
        $this->createClass($sSharedNamespace, $sItemClassName, $mValue ?? []);

        // Create the variable name
        $oSubjectClassTypeVarName = Naming::propertyName($sKey);


        $sCollectionClassName = $oCollectionSharedName . 'Collection';


        $this->oService->getOutput()->writeln("Use <error>$sSharedNamespace</error>");
        $oNamespace = $this->getOrCreateNamespace($sNamespaceName);

        $this->addUse($oNamespace, [$sSharedNamespace]);



        $this->addCollectionClass($sSharedNamespace, $sItemClassName, $sCollectionClassName, $oSubjectClassTypeVarName);
        $sType = '\\' . $sSharedNamespace . '\\' . $sCollectionClassName;

        $oSharedNamespace = $this->getOrCreateNamespace($sSharedNamespace);
        $oSharedNamespace->addUse($sType);

        $this->addUse($oNamespace, [$sType => $sCollectionClassName]);
        $oNamespace->addUse($sType, $sCollectionClassName);

        return $sType;
    }

    /**
     * @param PhpNamespaceType $oBaseNamespace
     * @param array|null $aUse
     *
     * @return void
     */
    private function addUse(PhpNamespaceType $oBaseNamespace, ?array $aUse): void
    {
        if (is_array($aUse))
        {
            foreach ($aUse as $indexOrUseClass => $mUseClassOrAlias)
            {
                $sAlias = null;
                if (is_array($mUseClassOrAlias))
                {
                    $sFqn = $mUseClassOrAlias[0];
                    $sAlias = $mUseClassOrAlias[1];
                }
                elseif (is_int($indexOrUseClass))
                {
                    $sFqn = $mUseClassOrAlias;
                }
                elseif (is_string($indexOrUseClass))
                {
                    $sFqn = $indexOrUseClass;
                    $sAlias = $mUseClassOrAlias;
                }
                else
                {
                    var_dump($indexOrUseClass);
                    var_dump($mUseClassOrAlias);
                }
                if ($sAlias)
                {
                    $this->oService->getOutput()->writeln("Namespace <info>{$oBaseNamespace->getName()}</info> should include use <info>$sFqn</info> as <comment>$sAlias</comment>");
                    $oBaseNamespace->addUse($sFqn, $sAlias);
                }
                else
                {
                    $this->oService->getOutput()->writeln("Namespace <info>{$oBaseNamespace->getName()}</info> should include use <info>$sFqn</info>");
                    $oBaseNamespace->addUse($sFqn);
                }

            }
        }
    }
}
