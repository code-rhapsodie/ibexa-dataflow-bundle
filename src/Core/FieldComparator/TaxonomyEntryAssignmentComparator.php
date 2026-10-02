<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\FieldComparator;

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
        $currentEntriesId = array_map(fn (TaxonomyEntry $currentEntry) => $currentEntry->id, $currentEntries);

        $newEntriesId = array_map(fn (TaxonomyEntry $newEntry) => $newEntry->id, $newEntries);

        if (\count($currentEntriesId) !== \count($newEntriesId)) {
            return false;
        }

        sort($currentEntriesId);
        sort($newEntriesId);

        return $currentEntriesId === $newEntriesId;
    }
}
