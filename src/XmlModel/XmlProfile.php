<?php

namespace Survos\Providence\XmlModel;

class XmlProfile
{
    use XmlAttributesTrait;

    public $profileName;
    private string $profileId;

    public $profileDescription;
    public ProfileLists $lists;
    public ProfileLocales $locales;
    public $useForConfiguration;
    public $infoUrl;
    public $base;
    public ProfileElementSets $elementSets;
    public ?ProfileUserInterfaces $userInterfaces = null;
    public ?ProfileRelationshipTable $relationshipTable = null;
    public ?ProfileRelationshipTypes $relationshipTypes;
    public ?ProfileDisplays $displays = null;
    public $searchForms;
    public $logins;
    public $roles;
    private string $filename;
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
     * @param mixed $infoUrl
     * @return XmlProfile
     */
    public function setInfoUrl($infoUrl)
    {
        $this->infoUrl = $infoUrl;
        return $this;
    }

    /**
     * @return int
     */
    public function getMdeCount(): int
    {
        return $this->mdeCount;
    }

    /**
     * @param int $mdeCount
     * @return XmlProfile
     */
    public function setMdeCount(int $mdeCount): XmlProfile
    {
        $this->mdeCount = $mdeCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getUiCount(): int
    {
        return $this->uiCount;
    }

    /**
     * @param int $uiCount
     * @return XmlProfile
     */
    public function setUiCount(int $uiCount): XmlProfile
    {
        $this->uiCount = $uiCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getListCount(): int
    {
        return $this->listCount;
    }

    /**
     * @param int $listCount
     * @return XmlProfile
     */
    public function setListCount(int $listCount): XmlProfile
    {
        $this->listCount = $listCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayCount(): int
    {
        return $this->displayCount;
    }

    /**
     * @param int $displayCount
     * @return XmlProfile
     */
    public function setDisplayCount(int $displayCount): XmlProfile
    {
        $this->displayCount = $displayCount;
        return $this;
    }


    /**
     * @return string
     */
    public function getXml(): string
    {
        return $this->xml;
    }

    /**
     * @param string $xml
     * @return XmlProfile
     */
    public function setXml(string $xml): XmlProfile
    {
        $this->xml = $xml;
        return $this;
    }

    public $metadataAlerts;

    /** @returns ProfileList[] */
    public function getLists(): array { return $this->lists->list; }

    /** @returns ProfileMetaDataElement[] */
    public function getElements(): array { return $this->elementSets->metadataElement; }

    /** @returns ProfileDisplay[] */
    public function getDisplays(): array { return $this->displays ? $this->displays->display : []; }

    /** @returns ProfileUserInterface[] */
    public function getUserInterfaces(): array { return $this->userInterfaces ? $this->userInterfaces->userInterface: []; }

    /** @returns ProfileRelationshipTable[] */
    public function getRelationshipTypes(): array { return $this->relationshipTypes ? $this->relationshipTypes->relationshipTable: []; }

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

    /** @returns ProfileElementSets[] */
    public function getElementSets(): array { return $this->elementSets ? $this->elementSets->metadataElement: []; }

//<relationshipTypes>
//<relationshipTable name="ca_objects_x_entities">
//<types>
//<type code="assessor" default="1">

//    /** @returns ProfileRelationshipTable */
//    public function getRelationshipTable(): ?ProfileRelationshipTable  { return $this->relationshipTable ? $this->relationshipTypes->relationshipTable: null; }

    /** @returns ProfileRelationshipTableType[] */
    public function getRelationshipTables(): array  {
        return $this->relationshipTypes ? $this->relationshipTypes->relationshipTable: []; }

    public function getRelationshipTableByCode($code): ProfileRelationshipTable  {
        return current(array_filter($this->getRelationshipTables(), fn(ProfileRelationshipTable $table) => $table->name === $code)); }

    /** @returns ProfileMetaDataElement[] */
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
        $code = strtolower(str_replace('ca_attribute_', '', $code));
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

    /** @returns ProfileLocale[] */
    public function getLocales(): array
    {
        return isset($this->locales) ? $this->locales->locale: [];
    }

}
