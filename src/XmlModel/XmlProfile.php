<?php

namespace Survos\Providence\XmlModel;

use Symfony\Component\Serializer\Annotation\Groups;

class XmlProfile
{
    use XmlAttributesTrait;

    #[Groups('profile')]
    public $profileName;
    #[Groups('profile')]
    private string $profileId;

    #[Groups('profile')]
    public $profileDescription;
    #[Groups('profile')]
    public ?ProfileLists $lists=null;
    #[Groups('profile')]
    public ProfileLocales $locales;
    public $useForConfiguration;
    #[Groups('profile')]
    public $infoUrl;
    public $base;
    #[Groups('sets')]
    public ?ProfileElementSets $elementSets = null;
    #[Groups('ui')]
    public ?ProfileUserInterfaces $userInterfaces = null;
    #[Groups('profile')]
    public ?ProfileRelationshipTable $relationshipTable = null;
    #[Groups('profile')]
    public ?ProfileRelationshipTypes $relationshipTypes = null;
    #[Groups('profile')]
    public ?ProfileDisplays $displays = null;
    public $searchForms;
    public $logins;
    public $roles;
    private readonly string $filename;
    private string $xml;

    private int $mdeCount;
    private int $uiCount;
    private int $listCount;
    private int $displayCount;

    /**
     * @return mixed
     */
    public function getInfoUrl()
    {
        return $this->infoUrl;
    }

    /**
     * @return XmlProfile
     */
    public function setInfoUrl(mixed $infoUrl)
    {
        $this->infoUrl = $infoUrl;
        return $this;
    }

    public function getMdeCount(): int
    {
        return $this->mdeCount;
    }

    public function setMdeCount(int $mdeCount): XmlProfile
    {
        $this->mdeCount = $mdeCount;
        return $this;
    }

    public function getUiCount(): int
    {
        return $this->uiCount;
    }

    public function setUiCount(int $uiCount): XmlProfile
    {
        $this->uiCount = $uiCount;
        return $this;
    }

    public function getListCount(): int
    {
        return $this->listCount;
    }

    public function setListCount(int $listCount): XmlProfile
    {
        $this->listCount = $listCount;
        return $this;
    }

    public function getDisplayCount(): int
    {
        return $this->displayCount;
    }

    public function setDisplayCount(int $displayCount): XmlProfile
    {
        $this->displayCount = $displayCount;
        return $this;
    }


    public function getXml(): string
    {
        return $this->xml;
    }

    public function setXml(string $xml): XmlProfile
    {
        $this->xml = $xml;
        return $this;
    }

    public $metadataAlerts;

    /** @return ProfileList[] */
    public function getLists(): array { return $this->lists?->list ?: []; }

    /** @return ProfileMetaDataElement[] */
    public function getElements(): array { return $this->elementSets?->metadataElement ?: []; }

    /** @return ProfileDisplay[] */
    public function getDisplays(): array { return $this->displays ? $this->displays->display : []; }

    /** @return ProfileUserInterface[] */
    public function getUserInterfaces(): array { return $this->userInterfaces ? $this->userInterfaces->userInterface: []; }

    /** @return ProfileRelationshipTable[] */
    public function getRelationshipTypes(): array { return $this->relationshipTypes ? $this->relationshipTypes->relationshipTable: []; }

    /** @return int */
    public function getRelationshipTypesCount(): int { return $this->relationshipTypes ? count($this->relationshipTypes->relationshipTable): 0; }

    public function ElementsByRestriction(): array {
        $summary = [];
        /** @var ProfileMetaDataElement $element */
        foreach ($this->getElements() as $element) {
            foreach ($element->getTypeRestrictions() as $typeRestriction) {
                $summary[$typeRestriction->table][] = $element;
            }
        }
        return $summary;
    }

    /** @return ProfileElementSets[] */
    public function getElementSets(): array { return $this->elementSets ? $this->elementSets->metadataElement: []; }

//<relationshipTypes>
//<relationshipTable name="ca_objects_x_entities">
//<types>
//<type code="assessor" default="1">

//    /** @return ProfileRelationshipTable */
//    public function getRelationshipTable(): ?ProfileRelationshipTable  { return $this->relationshipTable ? $this->relationshipTypes->relationshipTable: null; }

    /** @return array|ProfileRelationshipTable[] */
    public function getRelationshipTables(): array  {
        return $this->relationshipTypes ? $this->relationshipTypes->relationshipTable: []; }

    public function getRelationshipTableByCode($code): ProfileRelationshipTable  {
        return current(array_filter($this->getRelationshipTables(), fn(ProfileRelationshipTable $table) => $table->name === $code)); }

    /** @return ProfileMetaDataElement[] */
    public function getElementsByCode(): array {
        static $mdes;
        if (empty($mdes)) {
            $mdes  = [];
            // @todo: recurse over all MDE's
            /** @var ProfileMetaDataElement $mde */
            foreach ($this->getElements() as $mde) {
                $mdes[strtolower($mde->code)] = $mde;
            }
        }
        return $mdes;
    }

    public function getListsByCode()
    {
        static $lists = [];
        if (empty($lists)) {
            $lists  = [];
            foreach ($this->getLists() as $list) {
                $lists[$list->code] = $list;
            }
        }
        return $lists;
    }

    public function getElementByCode($code) {
        $code = strtolower(str_replace('ca_attribute_', '', (string) $code));
        return $this->getElementsByCode()[$code] ?? null;
    }

    public function getListByCode($code) {
        return $this->getListsByCode()[$code] ?? null;
    }

    public function setFilename(string $path): self
    {
        $this->filepath = $path;
        return $this;
    }

    public function getFilename(): string
    {
        return $this->filepath;
    }

    public function getRp(array $attr = []): array
    {
        assert(isset($this->profileId));
        return array_merge($attr, ['profileId' => $this->getProfileId()]);
    }

    public function setProfileId(string $profileId): self
    {
        $this->profileId = $profileId;
        return $this;
    }
    public function getProfileId(): string
    {
        return $this->profileId;
    }

    public function getName(): string
    {
        return $this->profileName;
    }

    /** @return ProfileLocale[] */
    public function getLocales(): array
    {
        return isset($this->locales) ? $this->locales->locale: [];
    }

    public function getTranslationFilename($locale): string
    {
        return sprintf('/translations/%s+intl-icu.%s.yaml', $this->getProfileId(), $locale);
    }

    public function getDescription(): ?string {
        return $this->profileDescription;
    }

}
