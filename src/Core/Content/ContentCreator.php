<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\Content;

use CodeRhapsodie\IbexaDataflowBundle\Core\Field\ContentStructFieldFillerInterface;
use CodeRhapsodie\IbexaDataflowBundle\Matcher\LocationMatcherInterface;
use CodeRhapsodie\IbexaDataflowBundle\Model\ContentCreateStructure;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\LocationCreateStruct;

readonly class ContentCreator implements ContentCreatorInterface
{
    public function __construct(private ContentService $contentService, private ContentTypeService $contentTypeService, private ContentStructFieldFillerInterface $filler, private LocationMatcherInterface $matcher, private Repository $repository)
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

        $this->repository->beginTransaction();
        try {
            $content = $this->contentService->createContent($contentCreateStruct, $this->getLocationCreateStructs($structure->getLocations()));

            $content = $this->contentService->publishVersion($content->versionInfo);
            $this->repository->commit();

            return $content;
        } catch (\Exception $exception) {
            $this->repository->rollback();
            throw $exception;
        }

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
