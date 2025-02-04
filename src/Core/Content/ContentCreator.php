<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\Content;

use CodeRhapsodie\IbexaDataflowBundle\Core\Field\ContentStructFieldFillerInterface;
use CodeRhapsodie\IbexaDataflowBundle\Matcher\LocationMatcherInterface;
use CodeRhapsodie\IbexaDataflowBundle\Model\ContentCreateStructure;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;

class ContentCreator implements ContentCreatorInterface
{
    public function __construct(private readonly ContentService $contentService, private readonly ContentTypeService $contentTypeService, private readonly ContentStructFieldFillerInterface $filler, private readonly LocationMatcherInterface $matcher)
    {
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function createFromStructure(ContentCreateStructure $structure): Content
    {
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($structure->getContentTypeIdentifier());
        $contentCreateStruct = $this->contentService->newContentCreateStruct($contentType, $structure->getLanguageCode());
        $contentCreateStruct->remoteId = $structure->getRemoteId();
        $this->filler->fillFields($contentType, $contentCreateStruct, $structure->getFields());
        $content = $this->contentService->createContent($contentCreateStruct, $this->getLocationCreateStructs($structure->getLocations()));

        return $this->contentService->publishVersion($content->versionInfo);
    }

    /**
     * @return \Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct[]
     */
    private function getLocationCreateStructs(array $locations): array
    {
        $locationCreateStructs = [];

        foreach ($locations as $locationOrIdOrRemoteIdOrStruct) {
            if ($locationOrIdOrRemoteIdOrStruct instanceof LocationCreateStruct) {
                $locationCreateStructs[] = $locationOrIdOrRemoteIdOrStruct;

                continue;
            }

            $locationCreateStructs[] = new LocationCreateStruct([
                'parentLocationId' => $this->matcher->matchLocation($locationOrIdOrRemoteIdOrStruct)->id,
            ]);
        }

        return $locationCreateStructs;
    }
}
class_alias(ContentCreator::class, 'CodeRhapsodie\EzDataflowBundle\Core\Content\ContentCreator');
