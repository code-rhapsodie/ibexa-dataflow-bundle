<?php

namespace CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator;

use CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator\AbstractFieldComparator;
use Ibexa\Contracts\Core\FieldType\Value;
use Ibexa\Contracts\Taxonomy\Value\TaxonomyEntry;

class TaxonomyEntryAssignmentComparator extends AbstractFieldComparator
{

    /**
     * @param \Ibexa\Taxonomy\FieldType\TaxonomyEntryAssignment\Value $currentValue
     * @param \Ibexa\Taxonomy\FieldType\TaxonomyEntryAssignment\Value $newValue
     */
    protected function compareValues(Value $currentValue, Value $newValue): bool
    {
        return $currentValue->getTaxonomy() === $newValue->getTaxonomy() && $this->compareEntries($currentValue->getTaxonomyEntries(), $newValue->getTaxonomyEntries());
    }

    /**
     * @param array<TaxonomyEntry> $currentEntries
     * @param array<TaxonomyEntry> $newEntries
     */
    private function compareEntries(array $currentEntries, array $newEntries): bool
    {
        $currentEntriesId = array_map(function (TaxonomyEntry $currentEntry) {
            return $currentEntry->id;
        }, $currentEntries);

        $newEntriesId = array_map(function (TaxonomyEntry $newEntry) {
            return $newEntry->id;
        }, $newEntries);

        return empty(array_diff($currentEntriesId, $newEntriesId));
    }
}
