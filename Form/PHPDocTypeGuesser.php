<?php

namespace JasonMx\Components\Form;

use Doctrine\DBAL\Types\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormTypeGuesserInterface;
use Symfony\Component\Form\Guess\Guess;
use Symfony\Component\Form\Guess\TypeGuess;

class PHPDocTypeGuesser implements FormTypeGuesserInterface
{

    public function guessType($class, $property)
    {
        ddd($class, $property);
        $annotations = $this->readPhpDocAnnotations($class, $property);

        if (!isset($annotations['var'])) {
            return; // guess nothing if the @var annotation is not available
        }

        // otherwise, base the type on the @var annotation
        switch ($annotations['var']) {
            case 'string':
                // there is a high confidence that the type is text when
                // @var string is used
                return new TypeGuess(TextType::class, array(), Guess::HIGH_CONFIDENCE);

            case 'int':
            case 'integer':
                // integers can also be the id of an entity or a checkbox (0 or 1)
                return new TypeGuess(IntegerType::class, array(), Guess::MEDIUM_CONFIDENCE);

            case 'float':
            case 'double':
            case 'real':
                return new TypeGuess(NumberType::class, array(), Guess::MEDIUM_CONFIDENCE);

            case 'boolean':
            case 'bool':
                return new TypeGuess(CheckboxType::class, array(), Guess::HIGH_CONFIDENCE);

            default:
                // there is a very low confidence that this one is correct
                return new TypeGuess(TextType::class, array(), Guess::LOW_CONFIDENCE);
        }
    }

    protected function readPhpDocAnnotations($class, $property)
    {
        $reflectionProperty = new \ReflectionProperty($class, $property);
        $phpdoc = $reflectionProperty->getDocComment();

        //ddd($phpdoc);
        //$phpdocTags = ...;

        return $phpdocTags;
    }

    /**
     * @inheritDoc
     */
    public function guessRequired($class, $property)
    {
    }

    /**
     * @inheritDoc
     */
    public function guessMaxLength($class, $property)
    {
    }

    /**
     * @inheritDoc
     */
    public function guessPattern($class, $property)
    {
    }
}