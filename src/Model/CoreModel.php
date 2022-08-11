<?php

namespace Survos\Providence\Model;

use App\Annotations\ACConfig;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Survos\BaseBundle\Entity\SurvosBaseEntity;
use Doctrine\ORM\Mapping as ORM;

class CoreModel
{

    public function __construct()
    {
//        $this->relations = new ArrayCollection();
    }

    public function getDependencies()
    {
    }

    public function getRelatedClasses()
    {
        return match (self::class) {
            TourStop::class => [Tour::class],
            ObjectLot::class => [Obj::class],
            default => [],
        };
    }

    public function privateProperties()
    {
        $reflection = new \ReflectionClass($this);
        return $reflection->getProperties();
        $classAttributes = $reflection->getAttributes();
        foreach ($classAttributes as $attribute) {
            try {
                $instance = $attribute->newInstance();
                if (($instance::class === ACConfig::class) && ($typeClass = $instance->typeClass)) {
                    $typeReflection = new \ReflectionClass($typeClass);
                }
            } catch (\Exception $exception) {
                dump($exception);
            }
            dd($attribute->getName(), $instance, $attribute->getArguments());
        }
    }

}
