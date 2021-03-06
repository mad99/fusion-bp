<?php
namespace Flowpack\FusionBP\Generators;

use Neos\Flow\Annotations as Flow;
use Neos\ContentRepository\Domain\Model\NodeType;
use Neos\Neos\Domain\Service\DefaultPrototypeGeneratorInterface;

/**
 * Generate a Fusion prototype definition based on Neos.Fusion:Template and pass all node properties to it
 *
 * @Flow\Scope("singleton")
 */
class DefaultPrototypeGenerator implements DefaultPrototypeGeneratorInterface
{
    /**
     * Is Document prototype?
     *
     * @var boolean
     */
    protected $isDocument = false;

    /**
     * Generate a Fusion prototype definition for a given node type
     *
     * @param NodeType $nodeType
     * @return string
     */
    public function generate(NodeType $nodeType)
    {
        if (strpos($nodeType->getName(), ':') === false) {
            return '';
        }

        $basePrototypeName = $this->isDocument ? 'Neos.Neos.Document' : 'Neos.Neos.Content';
        $output = 'prototype(' . $nodeType->getName() . ') < prototype(' . $basePrototypeName . ') {' . chr(10);

        if ($this->isDocument) {
            $output .= 'body {' . chr(10);
        }
        $output .= "\t" . 'overridePrototypeName = \'' . $nodeType->getName() . '\'' . chr(10);

        foreach ($nodeType->getProperties() as $propertyName => $propertyConfiguration) {
            if (isset($propertyName[0]) && $propertyName[0] !== '_') {
                $output .= "\t" . $propertyName . ' = ${q(node).property("' . $propertyName . '")}' . chr(10);
                if (isset($propertyConfiguration['type']) && isset($propertyConfiguration['ui']['inlineEditable']) && $propertyConfiguration['type'] === 'string' && $propertyConfiguration['ui']['inlineEditable'] === true) {
                    $output .= "\t" . $propertyName . '.@process.convertUris = Neos.Neos:ConvertUris' . chr(10);
                }
            }
        }

        if ($this->isDocument) {
            $output .= '}' . chr(10);
        }
        $output .= '}' . chr(10);
        return $output;
    }
}
