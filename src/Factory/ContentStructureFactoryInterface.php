<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Factory;

interface ContentStructureFactoryInterface
{
    public const int MODE_INSERT_OR_UPDATE = 1;
    public const int MODE_INSERT_ONLY = 2;
    public const int MODE_UPDATE_ONLY = 3;

    /**
     * @param int|string|array<int|string> $parentLocations Int for location id or string for remote location id (or a list of them)
     * @param int                          $mode            ContentStructureFactoryInterface
     *
     * @return false|\CodeRhapsodie\IbexaDataflowBundle\Model\ContentStructure
     */
    public function transform(array $data, string $remoteId, string $language, string $contentType, $parentLocations, int $mode = ContentStructureFactoryInterface::MODE_INSERT_OR_UPDATE);
}
