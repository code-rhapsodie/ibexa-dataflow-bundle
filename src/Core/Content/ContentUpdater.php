<?php

declare(strict_types=1);

namespace CodeRhapsodie\IbexaDataflowBundle\Core\Content;

use CodeRhapsodie\IbexaDataflowBundle\Core\Field\ContentStructFieldFillerInterface;
use CodeRhapsodie\IbexaDataflowBundle\Exception\NoMatchFoundException;
use CodeRhapsodie\IbexaDataflowBundle\Model\ContentUpdateStructure;
use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Content;

class ContentUpdater implements ContentUpdaterInterface
{
    public function __construct(private readonly ContentService $contentService, private readonly ContentTypeService $contentTypeService, private readonly ContentStructFieldFillerInterface $filler)
    {
    }

    /**
     * @throws \CodeRhapsodie\IbexaDataflowBundle\Exception\NoMatchFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\BadStateException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentFieldValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\ContentValidationException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function updateFromStructure(ContentUpdateStructure $structure): Content
    {
        if (null !== $structure->getId()) {
            $content = $this->contentService->loadContent($structure->getId());
        } elseif (null !== $structure->getRemoteId()) {
            $content = $this->contentService->loadContentByRemoteId($structure->getRemoteId());
        } else {
            throw new NoMatchFoundException('ContentUpdateStructure should either have their id or their remoteId set in order to match the content to update');
        }

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $contentUpdateStruct->initialLanguageCode = $structure->getLanguageCode();
        $this->filler->fillFields(
            $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId),
            $contentUpdateStruct,
            $structure->getFields()
        );

        $draft = $this->contentService->createContentDraft($content->contentInfo);
        $this->contentService->updateContent($draft->versionInfo, $contentUpdateStruct);

        return $this->contentService->publishVersion($draft->versionInfo);
    }
}
